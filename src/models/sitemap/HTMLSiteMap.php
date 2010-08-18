<?php
/**
 * src/models/sitemap/HTMLSiteMap.php
 *
 * PHP version 5
 *
 * @category  PHPFrame_Applications
 * @package   Mashine
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/lupomontero/Mashine
 */

/**
 * This class wraps around a Content object and is responsible for
 * converting it to an HTML string that can be used for displaying navigation
 * menus and sitemap trees using HTML unordered lists and links.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class HTMLSiteMap
{
    private $_node, $_user;
    private $_exclude = array();
    private $_depth = 0;
    private $_show_root = true;
    private $_root_as_child = false;
    private $_show_forbidden = false;
    private $_parent = false;

    /**
     * Constructor.
     *
     * @param Content       $node          A Content object for the homepage
     *                                     with all it's child nodes.
     * @param PHPFrame_User $user          User object of user reading the
     *                                     sitemap.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(Content $node, PHPFrame_User $user)
    {
        $this->_node = $node;
        $this->_user = $user;
    }

    /**
     * Convert object to an HTML string.
     *
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        if ($this->parent()) {
            $has_active_parent = false;
            foreach ($this->_node->getChildren() as $child) {
                if ($child->active() || $child->activeParent()) {
                    $has_active_parent = true;
                    $this->_node = $child;
                    break;
                }
            }

            if (!$has_active_parent) {
                return "";
            }
        }

        $str = "";

        if (!$this->showRoot()) {
            foreach ($this->_node->getChildren() as $child) {
                $str .= $this->_nodeToHTML($child);
            }
        } elseif ($this->showRootAsChild()) {
            $str .= "    <li>\n";
            $str .= "        <a href=\"".$this->_node->slug()."\"";
            if ($this->_node->active()) {
                $str .= " class=\"active\"";
            }
            $str .= ">".$this->_node->shortTitle()."</a>\n";
            $str .= "    </li>\n";
            foreach ($this->_node->getChildren() as $child) {
                $str .= $this->_nodeToHTML($child);
            }
        } else {
            $str .= $this->_nodeToHTML($this->_node);
        }

        if ($str) {
            $str = "<ul>\n".$str."</ul>\n";
        }

        return $str;
    }

    public function node(Content $node=null)
    {
        if (!is_null($node)) {
            $this->_node = $node;
        }

        return $this->_node;
    }

    public function exclude(array $array=null)
    {
        if (!is_null($array)) {
            $this->_exclude = $array;
        }

        return $this->_exclude;
    }

    /**
     * Get/set flag to indicate how many levels deep we want to iterate.
     *
     * @param int $int [Optional]
     *
     * @return bool
     * @since  1.0
     */
    public function depth($int=null)
    {
        if (!is_null($int)) {
            $this->_depth = (int) $int;
        }

        return $this->_depth;
    }

    public function showRoot($bool=null)
    {
        if (!is_null($bool)) {
            $this->_show_root = (bool) $bool;
        }

        return $this->_show_root;
    }

    /**
     * Get/set flag to indicate whether home node should be treated as the same
     * level of its direct descendants (this is the normal case in most menus
     * and sitemap representations).
     *
     * @param bool $bool [Optional]
     *
     * @return bool
     * @since  1.0
     */
    public function showRootAsChild($bool=null)
    {
        if (!is_null($bool)) {
            $this->_root_as_child = (bool) $bool;
        }

        return $this->_root_as_child;
    }

    /**
     * Get/set flag to indicate whether to include items for which the current
     * user doesn't have read permission.
     *
     * @param bool $bool [Optional]
     *
     * @return bool
     * @since  1.0
     */
    public function showForbidden($bool=null)
    {
        if (!is_null($bool)) {
            $this->_show_forbidden = (bool) $bool;
        }

        return $this->_show_forbidden;
    }

    public function parent($bool=null)
    {
        if (!is_null($bool)) {
            $this->_parent = (bool) $bool;
        }

        return $this->_parent;
    }

    public function breadcrumbs(Content $node)
    {
        $str = "";

        $ancestors = $node->getAncestors();

        if (is_array($ancestors) && count($ancestors) > 0) {
            $i = 0;
            foreach (array_reverse($ancestors) as $ancestor) {
                if ($i > 0) {
                    $str .= " &rang; ";
                }

                $str .= "<a href=\"".$ancestor->slug()."\">".$ancestor->shortTitle()."</a>\n";
                $i++;
            }
        }

        if ($str) {
            $str  = "<div class=\"breadcrumbs\">".$str;
            $str .= " &rang; ".$node->shortTitle()."\n";
            $str .= "</div>";
        }

        return $str;
    }

    /**
     * Convert Content object to HTML sitemap. This method iterates
     * recursively and builds a complete tree of nested content items.
     *
     * @param Content $node   The Content object.
     * @param int     $depth  [Optional]
     * @param string  $indent [Optional]
     *
     * @return string
     * @since  1.0
     */
    private function _nodeToHTML(Content $node, $depth=0, $indent="    ")
    {
        $depth_limit = $this->depth();

        if ($depth_limit > 0 && $depth >= $depth_limit) {
            return "";
        }

        if (!$this->showForbidden() && !$node->canRead($this->_user)) {
            return "";
        }

        $node_url   = $node->slug();
        $node_title = $node->shortTitle();

        if (in_array($node->slug(), $this->_exclude)) {
            return "";
        }

        $str  = $indent."<li>\n";
        $str .= $indent."    <a href=\"".$node_url."\"";
        if ($node->active()) {
            $str .= " class=\"active\"";
        } elseif ($node->activeParent()) {
            $str .= " class=\"parent\"";
        }
        $str .= ">".$node_title."</a>\n";

        if ($node->hasChildren()) {
            $depth++;
            $children_str = "";
            foreach ($node->getChildren() as $child) {
                $children_str .= $this->_nodeToHTML($child, $depth, $indent."        ");
            }

            if ($children_str) {
                $str .= $indent."    <ul>\n".$children_str.$indent."    </ul>\n";
            }
        }

        $str .= $indent."</li>\n";

        return $str;
    }
}
