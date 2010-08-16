<?php
/**
 * src/models/cms/content/PostContent.php
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
 * Post Content class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class PostContent extends Content
{
    public function excerpt($limit_chars=null)
    {
        $str = current(explode("<!-- More -->", $this->body()));

        if ($limit_chars) {
            $str = new PHPFrame_String($str);
            $str = $str->limitWords($limit_chars);
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

            $str .= " | <a href=\"cms/delete?id=".$this->id()."\" ";
            $str .= "class=\"confirm\" title=\"Are you sure you want to delete ";
            $str .= "post ".$this->title()."?\">";
            $str .= "Delete</a>";

            $str .= "</div>";

            return $str;
        }
    }
}
