<?php
/**
 * src/models/media/Image.php
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
 * Image model.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class Image extends MediaNode
{
    /**
     * Get URL to image.
     *
     * @return string
     * @since  2.0
     */
    public function getImageURL()
    {
        $url = $this->getUploadDir()."/".$this->getRelativePath();
        return str_replace(DS, "/", $url);
    }

    /**
     * Get URL to delete node in backend.
     *
     * @return string
     * @since  2.0
     */
    public function getDeleteURL()
    {
        return $this->getNodeURL()."&task=unlink";
    }

    /**
     * Get URL to thumbnail.
     *
     * @return string
     * @since  2.0
     */
    public function getThumbURL()
    {
        $config = $this->getConfig();
        if (is_null($this->getThumbPath())) {
            $thumb_url = $this->getIbURL()."/assets/img/no_thumb.png";
        } else {
            $thumb_url = $config["site_url"].$this->getUploadDir();

            if ($this->getParentRelativePath()) {
                $thumb_url .= "/".$this->getParentRelativePath();
            }

            $thumb_url .= "/thumb/".$this->getFilename();
            $thumb_url  = str_replace(DS, "/", $thumb_url);
        }

        return $thumb_url;
    }

    /**
     * Get absolute path to thumbnail.
     *
     * @return string
     * @since  2.0
     */
    public function getThumbPath()
    {
        $thumb = $this->getUploadDir().DS;

        if ($this->getParentRelativePath()) {
            $thumb .= $this->getParentRelativePath().DS;
        }

        $thumb .= "thumb".DS;
        $thumb .= $this->getFilename();

        if (!is_file($this->getSitePath().DS.$thumb)) {
            return null;
        }

        return $this->getSitePath().DS.$thumb;
    }

    /**
     * Get URL to generate thumbnails.
     *
     * @return string
     * @since  2.0
     */
    public function getGenerateThumbURL()
    {
        return $this->getNodeURL()."&task=generateThumbs";
    }

    /**
     * Get absolute path to caption.
     *
     * @return string
     * @since  2.0
     */
    public function getCaptionPath()
    {
        $path  = $this->getSitePath().DS;
        $path .= $this->getUploadDir().DS;
        $path .= $this->getParentRelativePath().DS;
        $path .= $this->getFilename();
        $path  = preg_replace("/\.(jpg|png|gif)/i", ".txt", $this->getRealPath());

        if (!is_file($path)) {
            return null;
        }

        return $path;
    }

    /**
     * Get URL to edit caption.
     *
     * @return string
     * @since  2.0
     */
    public function getCaptionEditURL()
    {
        return $this->getNodeURL()."&task=caption";
    }

    /**
     * Get the image's caption.
     *
     * @return string
     * @since  2.0
     */
    public function getCaption()
    {
        if (is_file($this->getCaptionPath())) {
            return file_get_contents($this->getCaptionPath());
        } else {
            return "";
        }
    }
}
