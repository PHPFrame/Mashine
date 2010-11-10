<?php
/**
 * src/models/media/MediaNode.php
 *
 * PHP version 5
 *
 * @category  PHPFrame_Applications
 * @package   Mashine
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/E-NOISE/Mashine
 */

/**
 * MediaNode model.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
abstract class MediaNode
{
    private $_config, $_rel_path, $_finfo;

    /**
     * Constructor.
     *
     * @param array  $config
     * @param string $rel_path [Optional] The node's relative path to the
     *                         upload's dir.
     *
     * @return void
     * @since  2.0
     */
    public function __construct(array $config, $rel_path="")
    {
        $paths = array(
            $config["site_path"],
            $config["upload_dir"],
            $rel_path
        );

        foreach ($paths as $path) {
            if (preg_match("/^\./", $path)) {
                $msg = MediaLang::NODE_ERROR_DOT_FILE;
                throw new InvalidArgumentException($msg);
            }
        }

        foreach ($paths as $path) {
            if (preg_match("/^~/", $path)) {
                $msg = MediaLang::NODE_ERROR_INVALID_PATH;
                throw new InvalidArgumentException($msg);
            }
        }

        foreach (array($config["upload_dir"], $rel_path) as $path) {
            if (preg_match("/^(\/|\\\)/", $path)) {
                $msg = MediaLang::NODE_ERROR_INVALID_PATH;
                throw new InvalidArgumentException($msg);
            }
        }

        $this->_config   = $config;
        $this->_rel_path = $rel_path;

        $abs_path  = $config["site_path"].DS.$config["upload_dir"];
        if ($rel_path) {
            $abs_path .= DS.$this->_rel_path;
        }

        $this->_finfo = new SplFileInfo($abs_path);
    }

    /**
     * Get the node name.
     *
     * @param int $limit_chars [Optional] If passed the filename will be limited
     *                         to the number of characters specified.
     *
     * @return string
     * @since  2.0
     */
    public function getFilename($limit_chars=0)
    {
        if ($this->_rel_path == "") {
            return "";
        }

        $fname = $this->_finfo->getFilename();
        if ($limit_chars > 0 && strlen($fname) > $limit_chars) {
            return substr($fname, 0, $limit_chars)."...";
        }

        return $fname;
    }

    /**
     * Tells if the node is a directory.
     *
     * @return bool
     * @since  2.0
     */
    public function isDir()
    {
        return $this->_finfo->isDir();
    }

    /**
     * Tells if the node is a file.
     *
     * @return bool
     * @since  2.0
     */
    public function isFile()
    {
        return $this->_finfo->isFile();
    }

    /**
     * Is the node (file or dir) writable?
     *
     * @return bool
     * @since  2.0
     */
    public function isWritable()
    {
        return $this->_finfo->isWritable();
    }

    /**
     * Get the node's file size.
     *
     * @param bool $human_readable [Optional] The default value is FALSE. When
     *                             set to TRUE the method will return a human
     *                             readable string like 1.2Mb and so on, otherwise
     *                             it returns the number of bytes as an integer.
     *
     * @return int|string
     * @since  2.0
     */
    public function getSize($human_readable=false)
    {
        $size = $this->_finfo->getSize();
        if (!$human_readable) {
            return (int) $size;
        } elseif ($size < 1024) {
            return (string) round($size, 2)." Bytes";
        } elseif ($size < 1024*1024) {
            return (string) round($size/1024, 2)." Kb";
        } else {
            return (string) round(($size/1024)/1024, 2)." Mb";
        }
    }

    /**
     * Get the node's modified time.
     *
     * @param bool $human_readable [Optional] The default value is FALSE. When
     *                             set to TRUE the method will return a human
     *                             readable string like Today 19:11,
     *                             otherwise it returns the timestamp as an
     *                             integer.
     *
     * @return int|string
     * @since  2.0
     */
    public function getMTime($human_readable=false)
    {
        $mtime = $this->_finfo->getMTime();

        if (!$human_readable) {
            return (int) $mtime;
        } elseif (($mtime - time()) < 60*60*24) {
            return (string) date("\T\o\d\a\y H:i", $mtime);
        } elseif (($mtime - time()) < 60*60*24*2) {
            return (string) date("\Y\e\s\\t\e\\r\d\a\y H:i", $mtime);
        } else {
            return (string) date("l j F Y H:i", $mtime);
        }
    }

    /**
     * Get the node's absolute path.
     *
     * @return string
     * @since  2.0
     */
    public function getRealPath()
    {
        return $this->_finfo->getRealPath();
    }

    /**
     * Get reference to image browser config object.
     *
     * @return ImageBrowser_Config
     * @since  2.0
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Get the absolute path to the site.
     *
     * @return string
     * @since  2.0
     */
    public function getSitePath()
    {
        return $this->_config["site_path"];
    }

    /**
     * Get the relative path to the uploads dir.
     *
     * @return string
     * @since  2.0
     */
    public function getUploadDir()
    {
        return $this->_config["upload_dir"];
    }

    /**
     * Get the node's relative path within the component's root dir.
     *
     * @return string
     * @since  2.0
     */
    public function getRelativePath()
    {
        return $this->_rel_path;
    }

    /**
     * Get the parent node's relative path.
     *
     * @return string
     * @since  2.0
     */
    public function getParentRelativePath()
    {
        $rel_path = $this->getRelativePath();
        $parent   = substr($rel_path, 0, strrpos($rel_path, DS));

        return (string) $parent;
    }

    /**
     * Get URL to thumbnail.
     *
     * @return string
     * @since  2.0
     */
    abstract public function getThumbURL();

    /**
     * Get markup with breadcrumbs for this node.
     *
     * @return string
     * @since  2.0
     */
    public function getBreadCrumbs($base_url)
    {
        $str = "<a href=\"".$base_url."\">".MediaLang::ROOT."</a>";
        $array = explode(DS, $this->getRelativePath());

        $rel_path = "";
        foreach ($array as $item) {
            if ($item) {
                $str .= " / <a href=\"".$base_url."?node=";
                $str .= urlencode($rel_path.$item);
                $str .= "\">".$item."</a>";
                $rel_path .= $item."/";
            }
        }

        return $str;
    }

    public function getRestfulRepresentation()
    {
        $config = $this->getConfig();
        unset($config["site_path"]);
        $ret = array(
            "type" => get_class($this),
            "filename" => $this->getFilename(),
            "is_dir" => $this->isDir(),
            "is_file" => $this->isFile(),
            "is_writable" => $this->isWritable(),
            "size" => $this->getSize(),
            "mtime" => $this->getMTime(),
            //"real_path" => $this->getRealPath(),
            "config" => $config,
            "relative_path" => $this->getRelativePath(),
            "parent_relative_path" => $this->getParentRelativePath(),
            "thumb_url" => $this->getThumbURL()
        );

        return $ret;
    }
}

