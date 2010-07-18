<?php
class CMSHelper extends PHPFrame_ViewHelper
{
    public function id2title($id)
    {
        $tree = $this->app()->request()->param("tree");
        $found = $tree->getNodeById($id);
        if ($found instanceof Content) {
            return $found->title();
        }
    }

    public function manageList(Content $tree)
    {
        $str = "<ul id=\"manage-content\">\n";
        $str .= $this->_contentToAdminHTML($tree, "    ");
        $str .= "</ul>\n";

        return $str;
    }

    private function _contentToAdminHTML(Content $content, $indent="")
    {
        $pattern = "/^(admin|user\/login|user\/edit|sitemap)/";
        if (preg_match($pattern, $content->slug())) {
            return "";
        }

        $str  = $indent."<li>\n";
        $str .= $indent."    <div class=\"sortable-content-item\">\n";
        $str .= $indent."    <h3>".$content->title()." ";
        $str .= "<small>(";
        switch ($content->type()) {
        case "PageContent" :
            $str .= "page";
            break;
        case "PostsCollectionContent" :
            $str .= "blog";
            break;
        case "PostContent" :
            $str .= "blog post";
            break;
        case "MVCContent" :
            $str .= "mvc action";
            break;
        case "FeedContent" :
            $str .= "rss/atom feed";
            break;
        }
        $str .= ")</small></h3>\n";
        $str .= $indent."    <p>\n";

        if ($content->canWrite($this->app()->user())) {
            if (!$content instanceof PostContent
                && !$content instanceof FeedContent
            ) {
                if ($content instanceof PostsCollectionContent) {
                    $add_label = "Add post";
                } else {
                    $add_label = "Add child";
                }

                $str .= $indent."        <a href=\"admin/content/form";
                $str .= "?parent_id=".$content->id()."\">".$add_label."</a> | \n";
            }

            $str .= $indent."        <a href=\"admin/content/form?id=";
            $str .= $content->id()."\">Edit</a> | \n";
            $str .= $indent."        <a href=\"index.php?controller=cms";
            $str .= "&action=delete&id=".$content->id()."\" ";
            $str .= "class=\"confirm\" title=\"Are you sure you want to delete ";
            $str .= $content->title()."?\">Trash</a>\n";
            $str .= $indent."    </p>\n";
        }

        $str .= $indent."    </div><!-- .sortable-content-item -->\n";

        if ($content->hasChildren()) {
            $str .= $indent."    <ul>\n";
            $str .= $indent."        <li class=\"unmovable\"></li>\n";
            foreach ($content->getChildren() as $child) {
                $str .= $this->_contentToAdminHTML($child, $indent."        ");
            }
            $str .= $indent."    </ul>\n";
        }

        $str .= $indent."</li>\n";

        return $str;
    }

    public function selectCustomView($selected=null)
    {
        $str = "<select name=\"view\">\n";
        $str .= "<option value=\"\">none</option>\n";
        $custom_views_path  = $this->app()->getInstallDir().DS."src".DS;
        $custom_views_path .= "views".DS."cms".DS."custom";
        $dir_it = new RecursiveDirectoryIterator($custom_views_path);
        $iterator = new RecursiveIteratorIterator(
            $dir_it,
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            if ($file->isFile()
                && preg_match("/^[^\.].+\.php/", $file->getFilename())
            ) {
                $view = str_replace($custom_views_path.DS, "", $file->getRealPath());
                $view = str_replace(".php", "", $view);
                $str .= "<option value=\"".$view."\"";
                if ("cms/custom/".$view == $selected) {
                    $str .= " selected";
                }
                $str .= ">".$view."</option>\n";
            }
        }

        $str .= "</select>\n";

        return $str;
    }

    public function displayFeedImage(FeedContent $content)
    {
        $str = "";

        $img_array = $content->image();

        if (is_array($img_array) && count($img_array) > 0) {
            $str .= "<a href=\"".$img_array["link"]."\">";
            $str .= "<img src=\"".$img_array["url"]."\" alt=\"\" />";
            $str .= "</a>";
        }

        return $str;
    }
}
