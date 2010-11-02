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
    private $_mapper;

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

        } else {
            var_dump("I have to delete a file!");
            exit;
        }
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

        $ret = $this->_createNode($parent."/".$name, "dir");

        if (!$this->returnInternalPHP()) {
            $ret = $ret->getRestfulRepresentation();
        }

        return $this->handleReturnValue($ret);
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
}

