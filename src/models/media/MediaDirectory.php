<?php
/**
 * src/models/media/MediaDirectory.php
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
 * MediaDirectory model.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class MediaDirectory extends MediaNode implements IteratorAggregate
{
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
        parent::__construct($config, $rel_path);

        if (!is_dir($this->getRealPath())) {
            $msg = MediaLang::NODE_ERROR_INVALID_DIR_PATH;
            throw new RuntimeException($msg);
        }
    }

    /**
     * Implementation of the IteratorAggregate interface.
     *
     * @return ArrayIterator
     * @since  2.0
     */
    public function getIterator()
    {
        $array = array();

        foreach (new DirectoryIterator($this->getRealPath()) as $node) {
            if ($node->isDot() || $node->getFileName() == "thumb") {
                continue;
            }

            $rel_path = $node->getFilename();
            if ($this->getRelativePath()) {
                $rel_path = $this->getRelativePath()."/".$rel_path;
            }

            if ($node->isDir()) {
                $node_class = "MediaDirectory";
            } elseif (!preg_match("/(\.(jpg|png|gif)$)/i", $node->getFileName())) {
                continue;
            } else {
                $node_class = "Image";
            }

            $array[] = new $node_class($this->getConfig(), $rel_path);
        }

        // Sort nodes
        uasort($array, array($this, "sort"));

        $config = $this->getConfig();
        if ($config["order_direction"] == "DESC") {
            $array = array_reverse($array);
        }

        return new ArrayIterator($array);
    }

    /**
     * Sorting function used in getIterator().
     *
     * @param MediaNode $node1 The left operator for the comparison.
     * @param MediaNode $node2 The right operator for the comparison.
     *
     * @return int Returns < 0 if node1 is less than node2; > 0 if node1 is
     *             greater than node2, and 0 if they are equal.
     * @since  2.0
     */
    public function sort($node1, $node2)
    {
        $config = $this->getConfig();

        switch($config["order_by"]) {
        case "date_modified" :
            return strcasecmp($node1->getMTime(), $node2->getMTime());
        case "caption" :
            return strnatcasecmp($node1->getCaption(), $node2->getCaption());
        case "filename" :
        default :
            return strnatcasecmp($node1->getFilename(), $node2->getFilename());
        }
    }

    /**
     * Get URL to thumbnail.
     *
     * @return string
     * @since  2.0
     */
    public function getThumbURL()
    {
        $thumb_url = $this->_findThumb($this);
        $config = $this->getConfig();

        if (!$thumb_url) {
            $thumb_url = $config["site_url"]."assets/img/no_thumb.png";
        }

        return $thumb_url;
    }

    public function getRestfulRepresentation($max_depth=2, $curr_depth=1)
    {
        $ret = parent::getRestfulRepresentation();
        $ret["children_count"] = 0;
        $children = array();
        $recurse = true;

        if ($curr_depth >= $max_depth) {
            $recurse = false;
        }

        foreach ($this as $child) {
            $ret["children_count"]++;

            if (!$recurse) {
                continue;
            }

            $children[] = $child->getRestfulRepresentation(
                $max_depth,
                ($curr_depth + 1)
            );
        }

        if ($recurse) {
            $ret["children"] = $children;
        }

        return $ret;
    }

    /**
     * Find thumbnails of child images recursively.
     *
     * @param MediaNode $node The node to start iteration.
     *
     * @return string|null
     * @since  2.0
     */
    private function _findThumb(MediaNode $node)
    {
        $child_dirs = array();
        foreach ($node as $child) {
            if ($child instanceof Image && !is_null($child->getThumbPath())) {
                return $child->getThumbURL();
            }

            $child_dirs[] = $child;
        }

        foreach ($child_dirs as $child_dir) {
            $thumb_url = $this->_findThumb($child_dir);
            if (!is_null($thumb_url)) {
                return $thumb_url;
            }
        }
    }
}

