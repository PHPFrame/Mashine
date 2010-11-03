<?php
/**
 * src/controllers/api/media.php
 *
 * PHP version 5
 *
 * @category   PHPFrame_Applications
 * @package    Mashine
 * @subpackage ApiControllers
 * @author     Lupo Montero <lupo@e-noise.com>
 * @copyright  2010 E-NOISE.COM LIMITED
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://github.com/E-NOISE/Mashine
 */

/**
 * Media API Controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class MediaApiController extends PHPFrame_RESTfulController
{
    private $_mapper, $_image_processor;

    /**
     * Get media.
     *
     * @param int $node  [Optional] Relative path to directory or media file
     *                   (relative to the upload_dir).
     *
     * @return array|object Either a single media item object or an array
     *                      containing many media objects.
     * @since  1.0
     */
    public function get($node="", $limit=10, $page=1)
    {
        if (empty($limit)) {
            $limit = 10;
        }

        if (empty($page)) {
            $page = 1;
        }

        $ret = $this->_createNode($node);

        if (!$this->returnInternalPHP()) {
            $ret = $ret->getRestfulRepresentation();
        }

        return $this->handleReturnValue($ret);
    }

    /**
     * Handle image upload.
     *
     * @param string $parent [Optional]
     *
     * @todo this method uses global files array from request to access files
     * sent by client. need to find a nice and restful way of implementing this. 
     * It is a but messy now :-(
     *
     * @return void
     * @since  2.0
     */
    public function upload($parent="")
    {
        $parent = $this->_filterNodePath($parent);
        $files = $this->request()->files();

        if (!array_key_exists("upload_file", $files)
            || !array_key_exists("name", $files["upload_file"])
            || empty($files["upload_file"]["name"])
        ) {
            throw new Exception(MediaLang::UPLOAD_ERROR_NO_FILE_SENT);
        }

        $options = $this->request()->param("_options");
        $options = $options->filterByPrefix("mediaplugin_");
        $dest    = $this->app()->getInstallDir().DS."public".DS;
        $dest   .= $options["upload_dir"];
        $accept  = "image/jpeg,image/jpg,image/gif,image/png,application/zip";
        $accept .= ",application/x-zip,application/x-zip-compressed";

        if ($parent) {
            $dest .= DS.$parent;
        }

        $upload_file = PHPFrame_Filesystem::upload(
            $files["upload_file"],
            $dest,
            $accept
        );

        $parent = $this->_createNode($parent);
        $config = $parent->getConfig();
        $finfo = $upload_file["finfo"];
        $mimetype = $upload_file["mimetype"];

        switch ($mimetype) {
        case "image/jpeg" :
        case "image/jpg" :
        case "image/png" :
        case "image/gif" :
            $this->_resizeImage($finfo->getRealPath(), $config);
            $this->_createThumb($finfo->getRealPath(), $config);
            $rel_path = $parent->getRelativePath()."/".$finfo->getFilename();
            $ret = $this->_createNode($rel_path);
            break;
        case "application/zip" :
        case "application/x-zip" :
        case "application/x-zip-compressed" :
            $this->_handleZipUpload($finfo->getRealPath(), $config);
            $ret = $this->_createNode($parent->getRelativePath());
            break;
        default :
            throw new RuntimeException(MediaLang::UPLOAD_ERROR_FILETYPE);
            break;
        }

        if (!$this->returnInternalPHP()) {
            $ret = $ret->getRestfulRepresentation();
        }

        return $this->handleReturnValue($ret);
    }

    /**
     * Delete media.
     *
     * @param int $node The media item's relative path.
     *
     * @return void
     * @since  1.0
     */
    public function delete($node)
    {
        $node = $this->_createNode($node);

        if ($node->isDir()) {
            $thumb_dir = $node->getRealPath().DS."thumb";
            if (is_dir($thumb_dir) && !rmdir($thumb_dir)) {
                throw new Exception(MediaLang::DIR_DELETE_ERROR);
            }

            if (!rmdir($node->getRealPath())) {
                throw new Exception(MediaLang::DIR_DELETE_ERROR);
            }

            $ret = array("success"=>MediaLang::DIR_DELETE_OK);

        } else {
            $caption_path = $node->getCaptionPath();
            if (is_file($caption_path) && !unlink($caption_path)) {
                throw new Exception(MediaLang::CAPTION_DELETE_ERROR);
            }

            $thumb_path = $node->getThumbPath();
            if (!preg_match("/no_thumb\.png$/", $thumb_path)
                && is_file($thumb_path)
                && !unlink($thumb_path)
            ) {
                throw new Exception(MediaLang::THUMB_DELETE_ERROR);
            }

            if (!unlink($node->getRealPath())) {
                throw new Exception(MediaLang::FILE_DELETE_ERROR);
            }

            $ret = array("success"=>MediaLang::FILE_DELETE_OK);
        }

        return $this->handleReturnValue($ret);
    }

    public function mkdir($parent, $name)
    {
        $name         = PHPFrame_Filesystem::filterFilename($name, true);
        $parent_node  = $this->_createNode($parent, "dir");
        $new_dir_path = $parent_node->getRealPath().DS.$name;

        if (is_dir($new_dir_path)) {
            throw new Exception(MediaLang::NEW_DIR_ERROR_ALREADY_EXISTS);
        } elseif (!@mkdir($new_dir_path)) {
            throw new Exception(MediaLang::NEW_DIR_ERROR);
        }

        $parent = (empty($parent)) ? "" : $parent."/";
        $ret = $this->_createNode($parent.$name, "dir");

        if (!$this->returnInternalPHP()) {
            $ret = $ret->getRestfulRepresentation();
        }

        return $this->handleReturnValue($ret);
    }

    public function generate_thumbs($node)
    {
        $node = $this->_createNode($node);
        $config = $node->getConfig();

        $resize_ok = true;
        if ($node instanceof Image) {
            $resize_ok = $this->_createThumb($node->getRealPath(), $config);

        } elseif ($node instanceof MediaDirectory) {
            foreach ($node as $child) {
                if ($child instanceof Image) {
                    if (!$this->_createThumb($child->getRealPath(), $config)) {
                        $resize_ok = false;
                        break;
                    }
                }
            }
        }

        if (!$resize_ok) {
            throw new Exception("Error generating thumbnails.");
        }

        if (!$this->returnInternalPHP()) {
            $node = $node->getRestfulRepresentation();
        }

        return $this->handleReturnValue($node);
    }

    public function resize($node)
    {
        $node = $this->_createNode($node);
        $config = $node->getConfig();

        $resize_ok = true;
        if ($node instanceof Image) {
            $resize_ok = $this->_resizeImage($node->getRealPath(), $config);

        } elseif ($node instanceof MediaDirectory) {
            foreach ($node as $child) {
                if ($child instanceof Image) {
                    if (!$this->_resizeImage($child->getRealPath(), $config)) {
                        $resize_ok = false;
                        break;
                    }
                }
            }
        }

        if (!$resize_ok) {
            throw new Exception("Error resizing images.");
        }

        if (!$this->returnInternalPHP()) {
            $node = $node->getRestfulRepresentation();
        }

        return $this->handleReturnValue($node);
    }

    /**
     * Get/set current gallery node.
     *
     * @param string $str         Relative path to current node.
     * @param string $ensure_type [Optional] Either 'dir' or 'img'.
     *
     * @return MediaDirectory
     * @since  2.0
     */
    private function _createNode($str, $ensure_type=null)
    {
        $str = $this->_filterNodePath($str);

        if (!in_array($ensure_type, array(null, "dir", "img"))) {
            $msg = "Can not ensure unknown node type!";
            throw new InvalidArgumentException($msg);
        }

        $options    = $this->request()->param("_options");
        $upload_dir = $options["mediaplugin_upload_dir"];
        $node_path  = $this->app()->getInstallDir().DS."public".DS.$upload_dir;
        $node_path .= DS.$str;

        if (is_dir($node_path)) {
            $node_class = "MediaDirectory";
        } elseif (is_file($node_path)) {
            $node_class = "Image";
        } else {
            $msg  = "Can not create node object. Requested file does not exist.";
            throw new RuntimeException($msg);
        }

        $ensure_class = null;
        if ($ensure_type == "dir") {
            $ensure_class = "MediaDirectory";
        } elseif ($ensure_type == "img") {
            $ensure_class = "Image";
        }

        if ($ensure_class && $node_class != $ensure_class) {
            $msg  = "Node is not of expected type. The given path point's to a ";
            $msg .= ($ensure_type == "img") ? "directory" : "image";
            $msg .= " and tried to ensure type was of type '";
            $msg .= ($ensure_type == "img") ? "image" : "directory";
            $msg .= "'.";
            throw new RuntimeException($msg);
        }

        $options = $this->request()->param("_options");
        $config = $options->filterByPrefix("mediaplugin_");
        $config["site_path"] = $this->app()->getInstallDir().DS."public";
        $config["site_url"] = $this->app()->config()->get("base_url");

        return new $node_class($config, $str);
    }

    /**
     * Filter dir or img node relative path.
     *
     * @param string $str      The string to filter.
     * @param bool   $sanitise [Optional] Flag indicating whether or not to
     *                         sanitise (remove dodgy characters).
     *
     * @return string
     * @throws InvalidArgumentException
     * @since  2.0
     */
    private function _filterNodePath($str, $sanitise=false)
    {
        $parts = explode(DS, $str);
        $clean = array();
        foreach ($parts as $part) {
            $clean[] = PHPFrame_Filesystem::filterFilename($part, $sanitise);
        }

        return implode(DS, $clean);
    }

    /**
     * Handle zip file uploaded to gallery.
     *
     * @param string $fname Absolute path to uploaded zip file.
     * @param array  $config Configuration array of the parent dir.
     *
     * @return void
     * @since  2.0
     */
    private function _handleZipUpload($fname, array $config)
    {
        $archive_class_file  = $this->app()->getInstallDir().DS."lib".DS;
        $archive_class_file .= "PEAR".DS."Archive".DS."Zip.php";
        include $archive_class_file;

        // Extract to tmp dir
        $tmp = PHPFrame_Filesystem::getSystemTempDir().DS.uniqid();
        if (is_dir($tmp)) {
            PHPFrame_Filesystem::rm($tmp, true);
        }

        PHPFrame_Filesystem::ensureWritableDir($tmp);
        $tmp = realpath($tmp);

        $archive = new Archive_Zip($fname);
        if (!$archive->extract(array("add_path"=>$tmp))) {
            throw new RuntimeException($archive->errorInfo());
        }

        $dir_it   = new RecursiveDirectoryIterator($tmp);
        $mode     = RecursiveIteratorIterator::LEAVES_ONLY;
        $iterator = new RecursiveIteratorIterator($dir_it, $mode);

        foreach ($iterator as $file) {
            switch (PHPFrame_Filesystem::getMimeType($file->getRealPath())) {
            case "image/jpg" :
            case "image/jpeg" :
            case "image/png" :
            case "image/gif" :
                $dest = substr($fname, 0, strrpos($fname, "."));
                PHPFrame_Filesystem::ensureWritableDir($dest);

                $dest .= DS.$file->getFilename();
                if (!copy($file->getRealPath(), $dest)) {
                    $msg = MediaLang::UPLOAD_ERROR_EXTRACTING_ARCHIVE;
                    throw new RuntimeException($msg);
                }

                $this->_resizeImage($dest, $config);
                $this->_createThumb($dest, $config);
                break;
            }
        }

        // Clean up
        PHPFrame_Filesystem::rm($fname);
        PHPFrame_Filesystem::rm($tmp, true);
    }

    /**
     * Resize image.
     *
     * @param string $fname  Absolute path to image file.
     * @param array  $config Configuration array of the parent dir.
     *
     * @return bool
     * @since  1.0
     */
    private function _resizeImage($fname, array $config)
    {
        $img_processor = $this->_getImageProcessor();
        $resize_ok = $img_processor->resize(
            $fname,
            $fname,
            $config["max_width"],
            $config["max_height"],
            $config["imgcomp"]
        );

        if (!$resize_ok) {
            //$this->raiseError(end($img_processor->getMessages()));
            return false;
        }

        return true;
    }

    /**
     * Create thumbnail.
     *
     * @param string $fname Absolute path to image file.
     * @param array  $config Configuration array of the parent dir.
     *
     * @return void
     * @since  1.0
     */
    private function _createThumb($fname, array $config)
    {
        $array = explode(DS, $fname);
        $thumb_dir  = "";
        $thumb_name = "";

        for ($i=0; $i<count($array); $i++) {
            if ($i > 0) {
                $thumb_dir .= DS;
            }

            if ($i == count($array)-1) {
                $thumb_dir .= "thumb".DS;
                $thumb_name = $array[$i];
            } else {
                $thumb_dir .= $array[$i];
            }
        }

        if (!is_dir($thumb_dir) && !mkdir($thumb_dir)) {
            $msg = sprintf(MediaLang::GENERATE_THUMB_ERROR, $thumb_dir);
            throw new RuntimeException($msg);
        }

        $img_processor = $this->_getImageProcessor();
        $resize_ok = $img_processor->resize(
            $fname,
            $thumb_dir.DS.$thumb_name,
            $config["thumb_width"],
            $config["thumb_height"],
            $config["imgcomp"]
        );

        if (!$resize_ok) {
            //$this->raiseError(end($img_processor->getMessages()));
            return false;
        }

        return true;
    }

    /**
     * Get ImageProcessor object.
     *
     * @return PHPFrame_ImageProcessor
     * @since  1.0
     */
    private function _getImageProcessor()
    {
        if (is_null($this->_image_processor)) {
            $this->_image_processor = new PHPFrame_ImageProcessor();
        }

        return $this->_image_processor;
    }
}

