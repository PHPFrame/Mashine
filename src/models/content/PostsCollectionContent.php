<?php
/**
 * src/models/cms/content/PostsCollectionContent.php
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
 * Posts Collection Content class.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class PostsCollectionContent extends Content
{
    /**
     * Get array containing subtype parameter definition.
     *
     * @return array
     * @since  1.0
     */
    public function getParamKeys()
    {
        $array = array(
            "posts_per_page" => array(
                "def_value"  => 10,
                "allow_null" => false,
                "filter"     => new PHPFrame_IntFilter()
            )
        );

        return array_merge(parent::getParamKeys(), $array);
    }

    public function editLink(PHPFrame_User $user)
    {
        if ($this->canWrite($user)) {
            $str  = "<div class=\"edit-content\">";
            $str .= "<a href=\"admin/content/form?id=";
            $str .= $this->id()."\">";
            $str .= "Edit</a>";

            $str .= " | <a href=\"admin/content/form?parent_id=";
            $str .= $this->id()."\">";
            $str .= "Add post</a>";

            $str .= "</div>";

            return $str;
        }
    }
}
