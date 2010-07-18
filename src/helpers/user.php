<?php
class UserHelper extends PHPFrame_ViewHelper
{
    public function getGroups()
    {
        $array = array();

        $mapper     = new GroupsMapper($this->app()->db());
        $collection = $mapper->find();

        if (count($collection) > 0) {
            foreach ($collection as $group) {
                $array[$group->id()] = $group->name();
            }
        }

        return $array;
    }

    public function getGroupsPicker($selected=null)
    {
        $str = "<p>\n";

        foreach ($this->getGroups() as $key=>$value) {
            $str .= "<label class=\"inline\">".$value."</label>\n";
            $str .= "<input name=\"group\" type=\"radio\" value=\"".$key."\"";
            if ($key == $selected) {
                $str .= " checked";
            }
            $str .= " />\n";
        }

        $str .= "</p>\n";

        return $str;
    }

    public function contactTypeSelect($selected="billing")
    {
        $array = array("billing", "owner", "admin", "tech");
        echo "<select name=\"type\" id=\"type\">";
        foreach ($array as $item) {
            echo "<option value=\"$item\">$item</option>";
        }
        echo "</select>";
    }

    public function countrySelect($selected="GB")
    {
        $sql = "SELECT printable_name, iso FROM #__countries ORDER BY printable_name ASC";
        $countries = $this->app()->db()->fetchAssocList($sql);

        $str = "<select class=\"required\" name=\"country\" id=\"country\">\n";
        foreach ($countries as $country) {
            $str .= "<option value=\"".$country["iso"]."\"";
            if ($country["iso"] == $selected) {
                $str .= " selected";
            }
            $str .= ">\n";
            $printable_name = new PHPFrame_String($country["printable_name"]);
            $str .= $printable_name->limitChars(18)."\n";
            $str .= "</option>\n";
        }
        $str .= "</select>";

        return $str;
    }
}
