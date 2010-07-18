<?php
/**
 * src/plugins/CMSPlugin.php
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
 * CMS Plugin class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class CMSPlugin extends AbstractPlugin
{
    private $_mapper, $_slugs;
    private static $_hooks;

    /**
     * Constructor
     *
     * @param PHPFrame_Application $app Instance of application.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app);

        //$this->options[$this->getOptionsPrefix()."version"] = "0.0.20";

        if ($app->session()->isAdmin() && !$app->request()->ajax()) {
            $update_assistant = new CMSUpdateAssistant($app);
            try {
                if (!$update_assistant->isUpToDate()) {
                    $msg  = "A new version of the CMS available. <a href=\"";
                    $msg .= $app->config()->get("base_url")."admin/upgrade\">";
                    $msg .= "Click here</a> to upgrade automatically";
                    $app->session()->getSysevents()->append($msg);
                }
            } catch (RuntimeException $e) {
                // Be silent if an exception is thrown when checking for updates
            }
        }

        $this->_mapper = new ContentMapper(
            $app->db(),
            $app->getTmpDir().DS."cms"
        );

        if ($app->user()->groupId() < 3) {
            // $this->hooks->addCallBack(
            //     "dashboard_boxes",
            //     array($this, "getContentStats")
            // );
            $this->hooks->addCallBack(
                "dashboard_boxes",
                array($this, "getUserStats")
            );
        }

        $this->_init();
    }

    /**
     * Install plugin.
     *
     * @return void
     * @since  1.0
     */
    public function install()
    {
        if (!$this->app()->db()->hasTable("#__content")) {
            $installer = new CMSInstaller($this->app());
            $installer->installDB();
        }

        $this->options[$this->getOptionsPrefix()."version"] = "0.0.22";
    }

    /**
     * Get CMS Hooks object.
     *
     * @return CMSHooks
     * @since  1.0
     */
    public static function hooks()
    {
        if (is_null(self::$_hooks)) {
            self::$_hooks = new CMSHooks();
        }

        return self::$_hooks;
    }

    /**
     * Get content statistics. This method is registered with the CMS Hooks to
     * be called on the 'dashboard_boxes' action.
     *
     * @return array
     * @since  1.0
     */
    public function getContentStats()
    {
        $tree = $this->app()->request()->param("tree");

        $stats = $this->_doGetContentStats($tree);

        $str  = "<table>";
        $str .= "<tr>";
        $str .= "<td>".$stats["pages"]."</td>";
        $str .= "<td>Pages</td>";
        $str .= "</tr>";
        $str .= "<tr>";
        $str .= "<td>".$stats["posts"]."</td>";
        $str .= "<td>Posts</td>";
        $str .= "</tr>";
        $str .= "</table>";

        return array("title"=>"Content stats", "body"=>$str);
    }

    /**
     * Get user statistics. This method is registered with the CMS Hooks to
     * be called on the 'dashboard_boxes' action.
     *
     * @return array
     * @since  1.0
     */
    public function getUserStats()
    {
        $array = array();

        $mapper = new UsersMapper($this->app()->db());

        $array["title"] = "User stats";

        $array["body"]  = "<p>";
        $array["body"] .= $mapper->count("active")." active user(s)<br />";
        $array["body"] .= $mapper->count("pending")." pending user(s)<br />";
        $array["body"] .= $mapper->count("suspended")." suspended user(s)<br />";
        $array["body"] .= $mapper->count("cancelled")." cancelled user(s)<br />";
        $array["body"] .= $mapper->count()." user(s) in total";
        $array["body"] .= "</p>";

        $array["body"] .= "<p>";
        $array["body"] .= "<a href=\"admin/user/form\">Create new user</a>";
        $array["body"] .= "</p>";

        return $array;
    }

    /**
     * This method replaces the short tags in the HTML output.
     *
     * @return void
     * @since  1.0
     */
    public function postApplyTheme()
    {
        $request  = $this->app()->request();
        $response = $this->app()->response();
        $base_url = $this->app()->config()->get("base_url");

        // Show XML sitemap if requested
        if ($request->controllerName() == "cms" && $request->param("xml")) {
            $xml_sitemap = new XMLSiteMap($request->param("tree"), $base_url);

            $response->renderer(new PHPFrame_XMLRenderer());
            $response->document($xml_sitemap);
            $response->send();

            exit(0);
        }

        $this->_rewriteLinksInResponse();
        $this->_replaceShortTags();
    }

    /**
     * Check slugs and route content items when needed.
     *
     * @return void
     * @since  1.0
     */
    private function _init()
    {
        $request     = $this->app()->request();
        $request_uri = $request->requestURI();
        $script_name = $request->scriptName();
        $rewritten_query_string = "";

        // If there is no request uri (ie: we are on the command line) we do
        // not rewrite
        if (empty($request_uri)) {
            return;
        }

        // Get path to script
        $path = substr($script_name, 0, (strrpos($script_name, '/')+1));

        // Remove path from request uri.
        // This gives us the slug plus any query params
        if ($path != "/") {
            $params = str_replace($path, "", $request_uri);
        } else {
            // If app is in web root we simply remove preceding slash
            $params = substr($request_uri, 1);
        }

        $array = explode("?", $params);
        $slug  = $array[0];
        if (isset($array[1])) {
            $query_string = $array[1];
        } else {
            $query_string = "";
        }

        if (empty($slug) || $slug == "index.php") {
            $slug = "home";
        }

        $request->param("slug", $slug);

        // Do not build tree if API call
        if (preg_match("/^api\/([a-zA-Z0-9_]*)/", $slug, $matches)) {
            $request->controllerName("api");
            $request->action($matches[1]);
            return;
        }

        $id_obj = $this->_mapper->getIdObject();
        $id_obj->where("c.status", "=", 1);
        if ($slug != "admin/content") {
            $id_obj->where("c.type <> 'PostContent'", "OR", "c.slug = '".$slug."'");
        }
        $id_obj->orderby("c.pub_date", "DESC");

        $collection = $this->_mapper->find($id_obj);
        $this->_buildTree(iterator_to_array($collection));

        // If script name doesn't appear in the request URI we need to rewrite
        if (strpos($request_uri, $script_name) === false
            && $request_uri != $path
            && $request_uri != $path."index.php"
        ) {
            $controller = $request->controllerName();
            if ($controller != "cms") {
                $array = explode("/", $slug);
                $request->controllerName($array[0]);
                $rewritten_query_string = "controller=".$array[0];
                if (isset($array[1])) {
                    $request->action($array[1]);
                    $rewritten_query_string .= "&action=".$array[1];
                }
            }
        }

        if (!empty($_SERVER['QUERY_STRING'])) {
            $rewritten_query_string .= "&".$_SERVER['QUERY_STRING'];
        }

        $_SERVER['QUERY_STRING'] = $rewritten_query_string;

        // Update request uri
        $_SERVER['REQUEST_URI']  = $path."index.php?";
        $_SERVER['REQUEST_URI'] .= $_SERVER['QUERY_STRING'];
    }

    /**
     * Create content tree.
     *
     * @param array   $array
     * @param Content $parent [Optional]
     *
     * @return bool Whether the node is active
     * @since  1.0
     */
    private function _buildTree(array $array, Content $parent=null)
    {
        if (is_null($parent)) {
            $parent_id = 0;
        } else {
            $parent_id = $parent->id();
        }

        $request = $this->app()->request();
        $slug    = $request->param("slug");

        foreach ($array as $item) {
            if ($item->parentId() == $parent_id) {
                $this->_slugs[] = $item->slug();

                if (in_array($slug, $this->_slugs) && !$request->action()) {
                    $request->controllerName("cms");
                }

                if ($item->slug() == $slug) {
                    // Get fully fledged content data for active item
                    $item = $this->_mapper->findOne($item->id());
                    $item->active(true);
                    $request->param("active_content", $item);
                }

                if (!is_null($parent)) {
                    $parent->addChild($item);

                    if ($item->active()) {
                        $ancestors = $item->getAncestors();
                        if (is_array($ancestors) && count($ancestors) > 0) {
                            foreach ($ancestors as $ancestor) {
                                $ancestor->activeParent(true);
                            }
                        }
                    }
                } else {
                    $request->param("tree", $item);
                }

                $this->_buildTree($array, $item);
            }
        }
    }

    private function _rewriteLinksInResponse()
    {
        // Get response body
        $body     = $this->app()->response()->document()->body();
        $base_url = $this->app()->config()->get("base_url");

        // Build sub patterns
        $controller = 'controller=([a-zA-Z]+)';
        $action     = 'action=([a-zA-Z_]+)';
        $amp        = '(&amp;|&)';

        // Build patterns and replacements
        $patterns[]     = '/"index.php\?'.$controller.$amp.$action.$amp.'/';
        $replacements[] = '"'.$base_url.'${1}/${3}?';

        $patterns[]     = '/"index.php\?'.$controller.$amp.$action.'"/';
        $replacements[] = '"'.$base_url.'${1}/${3}"';

        $patterns[]     = '/"index.php\?'.$controller.$amp.'/';
        $replacements[] = '"'.$base_url.'${1}?';

        $patterns[]     = '/"index.php\?'.$controller.'"/';
        $replacements[] = '"'.$base_url.'${1}"';

        // Replace the patterns in response body
        $body = preg_replace($patterns, $replacements, $body);

        // Set the processed body back in the response
        $this->app()->response()->document()->body($body);
    }

    /**
     * Replace [cms] short tags.
     *
     * @return void
     * @since  1.0
     */
    private function _replaceShortTags()
    {
        $request        = $this->app()->request();
        $response       = $this->app()->response();
        $user           = $this->app()->user();
        $tree           = $request->param("tree");
        $active_content = $request->param("active_content");

        if (!$tree instanceof Content) {
            return;
        }

        $body = $response->body();

        $matches = array();
        $pattern = '/\[cms:?([a-zA-Z0-9_&=,;\/\-]*)\]/';
        if (preg_match_all($pattern, $body, $matches)) {
            for ($i=0; $i<count($matches[1]); $i++) {
                $options = array();
                parse_str(str_replace("&amp;", "&", $matches[1][$i]), $options);
                $html_sitemap = new HTMLSiteMap($tree, $user);

                if (array_key_exists("type", $options)) {
                    switch ($options["type"]) {
                    case "branch" :
                        $html_sitemap->node($active_content);
                        break;
                    case "parent" :
                        $html_sitemap->parent(true);
                        break;
                    case "breadcrumbs" :
                        $pattern = '/'.preg_quote($matches[0][$i], '/').'/';
                        if ($active_content instanceof Content) {
                            $html = $html_sitemap->breadcrumbs($active_content);
                            $body = preg_replace($pattern, $html, $body);
                        } else {
                            $body = preg_replace($pattern, "", $body);
                        }
                        continue;
                    }
                }

                if (array_key_exists("show_root", $options)) {
                    $html_sitemap->showRoot(false);
                }

                if (array_key_exists("show_root_as_child", $options)) {
                    $html_sitemap->showRootAsChild($options["show_root_as_child"]);
                }

                if (array_key_exists("depth", $options)) {
                    $html_sitemap->depth($options["depth"]);
                }

                if (array_key_exists("show_forbidden", $options)) {
                    $html_sitemap->showForbidden($options["show_forbidden"]);
                }

                if (array_key_exists("exclude", $options)) {
                    $exclude = explode(",", $options["exclude"]);
                    $html_sitemap->exclude($exclude);
                }

                $pattern = '/'.preg_quote($matches[0][$i], '/').'/';
                $body = preg_replace($pattern, $html_sitemap, $body);
            }
        }

        if ($request->controllerName() == "cms" && $request->action() == "form") {
            $body = preg_replace("/\[@@cms(.*)@@\]/", "[cms$1]", $body);
        }

        // Set processed response back in response
        $response->body($body, false);
    }

    /**
     * This method is called by CMSPlugin::getContentStats() to recursively
     * iterate the content tree and count the number of pages, posts and so on.
     *
     * @return array
     * @since  1.0
     */
    private function _doGetContentStats(
        $content,
        &$array=array("pages"=>0,"posts"=>0,"mvc_actions"=>0)
    ) {
        if ($content instanceof PageContent
            || $content instanceof PostsCollectionContent
        ) {
            $array["pages"]++;
        } elseif ($content instanceof PostContent) {
            $array["posts"]++;
        } elseif ($content instanceof MVCContent) {
            $array["mvc_actions"]++;
        }

        if ($content->hasChildren()) {
            foreach ($content->getChildren() as $child) {
                $this->_doGetContentStats($child, $array);
            }
        }

        return $array;
    }
}
