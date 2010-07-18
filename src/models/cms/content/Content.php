<?php
/**
 * src/models/cms/content/Content.php
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
 * Content class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class Content extends PHPFrame_PolymorphicPersistentObject
{
    private $_parent;
    private $_children;
    private $_active = false;
    private $_active_parent = false;
    private $_author;

    /**
     * Constructor
     *
     * @param array $options [Optional]
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->addField(
            "parent_id",
            0,
            false,
            new PHPFrame_IntFilter()
        );
        $this->addField(
            "slug",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>1, "max_length"=>255))
        );
        $this->addField(
            "title",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>1, "max_length"=>255))
        );
        $this->addField(
            "short_title",
            null,
            true,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>50))
        );
        $this->addField(
            "pub_date",
            date("Y-m-d H:i:s"),
            false,
            new PHPFrame_DateFilter(array(
                "format" => PHPFrame_DateFilter::FORMAT_DATETIME
            ))
        );
        $this->addField(
            "status",
            0,
            false,
            new PHPFrame_IntFilter()
        );
        $this->addField(
            "robots_index",
            true,
            false,
            new PHPFrame_BoolFilter()
        );
        $this->addField(
            "robots_follow",
            true,
            false,
            new PHPFrame_BoolFilter()
        );
        $this->addField(
            "description",
            null,
            true,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>255))
        );
        $this->addField(
            "keywords",
            null,
            true,
            new PHPFrame_StringFilter()
        );
        $this->addField(
            "body",
            null,
            true,
            new PHPFrame_StringFilter()
        );

        parent::__construct($options);

        $this->_children = new SplObjectStorage();
    }

    /**
     * Get array containing subtype parameter definition.
     *
     * @return array
     * @since  1.0
     */
    public function getParamKeys()
    {
        $array = array(
            "view" => array(
                "def_value"  => null,
                "allow_null" => false,
                "filter"     => new PHPFrame_StringFilter(array(
                    "min_length" => 2,
                    "max_length" => 50
                ))
            )
        );

        return array_merge(parent::getParamKeys(), $array);
    }

    public function author($str=null)
    {
        if (!is_null($str)) {
            $this->_author = $str;
        }

        return $this->_author;
    }

    /**
     * Get/set reference to the object's parent content object.
     *
     * @param Content $parent [Optional]
     *
     * @return Content|null
     * @since  1.0
     */
    public function parent(Content $parent=null)
    {
        if (!is_null($parent)) {
            $this->_parent = $parent;
            $this->parentId($parent->id());
        }

        return $this->_parent;
    }

    /**
     * Get array containing all ancestors of the given content object.
     *
     * @param Content $node [Optional]
     *
     * @return array|null
     * @since  1.0
     */
    public function getAncestors(Content $node=null)
    {
        if (is_null($node)) {
            $node = $this;
        }

        $parent = $node->parent();
        if ($parent instanceof Content) {
            $array[] = $parent;
            if ($parent->parent() instanceof Content) {
                $array = array_merge($array, $this->getAncestors($parent));
            }

            return $array;
        }
    }

    /**
     * Add a child Content object
     *
     * @param Content $child An instance of Content class.
     *
     * @return void
     * @since  1.0
     */
    public function addChild(Content $child)
    {
        $child->parent($this);
        $this->_children->attach($child);
    }

    /**
     * Remove a child Content object
     *
     * @param Content $child An instance of Content class.
     *
     * @return void
     * @since  1.0
     */
    public function removeChild(Content $child)
    {
        $child->parent(null);
        $this->_children->detach($child);
    }

    /**
     * Get child content objects.
     *
     * @return SplObjectStorage
     * @since  1.0
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * Returns TRUE if content object contains child content objects or FALSE
     * if it doesn't.
     *
     * @return bool
     * @since  1.0
     */
    public function hasChildren()
    {
        return (count($this->_children) > 0);
    }

    /**
     * Get/set whether content object is active in navigation.
     *
     * @param bool $bool [Optional]
     *
     * @return bool
     * @since  1.0
     */
    public function active($bool=null)
    {
        if (!is_null($bool)) {
            $this->_active = (bool) $bool;
        }

        return $this->_active;
    }

    /**
     * Get/set whether content object has an active child in navigation.
     *
     * @param bool $bool [Optional]
     *
     * @return bool
     * @since  1.0
     */
    public function activeParent($bool=null)
    {
        if (!is_null($bool)) {
            $this->_active_parent = (bool) $bool;
        }

        return $this->_active_parent;
    }

    /**
     * Find a node with the given ID. The search includes this object and all
     * its descendants.
     *
     * @param int     $id
     * @param Content $node [Optional]
     *
     * @return Content|null
     * @since  1.0
     */
    public function getNodeById($id, Content $node=null)
    {
        if (is_null($node)) {
            $node = $this;
        }

        if ($node->id() == $id) {
            return $node;
        }

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $found = $this->getNodeById($id, $child);
                if ($found instanceof Content) {
                    return $found;
                }
            }
        }
    }

    /**
     * Find a node with the given slug. The search includes this object and
     * all its descendants.
     *
     * @param string  $slug
     * @param Content $node [Optional]
     *
     * @return Content|null
     * @since  1.0
     */
    public function getNodeBySlug($slug, Content $node=null)
    {
        if (is_null($node)) {
            $node = $this;
        }

        if ($node->slug() == $slug) {
            return $node;
        }

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $found = $this->getNodeBySlug($slug, $child);
                if ($found instanceof Content) {
                    return $found;
                }
            }
        }
    }

    public function shortTitle($str=null)
    {
        if (!is_null($str)) {
            $this->fields["short_title"] = $this->validate("short_title", $str);
        }

        $str = $this->fields["short_title"];

        if (!$str) {
            $str = $this->title();
        }

        return $str;
    }

    public function editLink(PHPFrame_User $user)
    {
        if ($this->canWrite($user)) {
            $str  = "<div class=\"edit-content\">";
            $str .= "<a href=\"admin/content/form?id=";
            $str .= $this->id()."\">";
            $str .= "Edit</a>";

            if ($this->type() == "PostsCollectionContent") {
                $str .= " | <a href=\"admin/content/form?parent_id=";
                $str .= $this->id()."\">";
                $str .= "Add post</a>";
            }

            $str .= "</div>";

            return $str;
        }
    }

    /**
     * Get next sibling.
     *
     * @return Content|null
     * @since   1.0
     */
    public function next()
    {
        $found = false;

        foreach ($this->parent()->getChildren() as $child) {
            if ($found) {
                return $child;
            }

            if ($child === $this) {
                $found = true;
            }
        }

        return null;
    }

    /**
     * Get previous sibling.
     *
     * @return Content|null
     * @since   1.0
     */
    public function prev()
    {
        $found = false;
        $children = iterator_to_array($this->parent()->getChildren());
        $children = array_reverse($children);

        foreach ($children as $child) {
            if ($found) {
                return $child;
            }

            if ($child === $this) {
                $found = true;
            }
        }

        return null;
    }

    /**
     * Get siblings.
     *
     * @return array
     * @since  1.0
     */
    public function getSiblings()
    {
        $array = array();

        foreach ($this->parent()->getChildren() as $sibling) {
            if ($sibling === $this) {
                continue;
            }

            $array[] = $sibling;
        }

        return $array;
    }
}
