<?php
/**
 * src/plugins/MashinePlugin.php
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
 * Register Mashine's autoload function.
 */
spl_autoload_register("__mashineAutoload");

/**
 * Mashine's autoload function. This adds support for loading API controllers.
 *
 * @param string $class_name The name of the class to look for.
 *
 * @return void
 * @since  1.0
 */
function __mashineAutoload($class_name)
{
    if (preg_match("/([a-zA-Z0-9]*)ApiController$/", $class_name, $matches)) {
        $api_path = preg_replace("/plugins.*/", "controllers".DS."api", __FILE__);

        $file = $api_path.DS.strtolower($matches[1]).".php";
        if (is_file($file)) {
            include $file;
        }
    }
}

/**
 * Mashine Plugin class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class MashinePlugin extends AbstractPlugin
{
    private $_mapper, $_slugs;

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
        // include($app->getInstallDir().DS."scripts/Upgrade-0.0.28-to-0.0.29.php");
        // $upgrade_obj = new Upgrade_0_0_28_to_0_0_29($app);
        // var_dump($upgrade_obj->run());
        // exit;

        parent::__construct($app);

        $this->_mapper = new ContentMapper(
            $app->db(),
            $app->getTmpDir().DS."cms"
        );

        if ($app->user()->groupId() < 3) {
            $this->hooks()->addCallBack(
                "dashboard_boxes",
                array($this, "getContentStats")
            );
            $this->hooks()->addCallBack(
                "dashboard_boxes",
                array($this, "getUserStats")
            );
        }

        $this->shortCodes()->add("content", array($this, "handleContentShortCode"));
        $this->shortCodes()->add("nav", array($this, "handleNavShortCode"));

        $this->_mapRequest();

        $registry = $app->registry();
        if (!$registry->get('api_controllers', false)) {
            $api_controllers = $this->_getApiControllers();
            $registry->set('api_controllers', $api_controllers);
        }

        // $this->options[$this->getOptionsPrefix()."version"] = "0.1.3";

        if ($app->session()->isAdmin()
            && !$app->request()->ajax()
            && $app->request()->controllerName() != "api"
        ) {
            $update_assistant = new UpdateAssistant($app);
            $sysevents = $app->session()->getSysevents();

            $msg  = "A new version of Mashine is available. <a href=\"";
            $msg .= $app->config()->get("base_url")."admin/upgrade\">";
            $msg .= "Click here</a> to upgrade automatically";

            try {
                if (!$update_assistant->isUpToDate()
                    && !in_array(array($msg, 4), iterator_to_array($sysevents))
                ) {
                    $sysevents->append($msg);
                }
            } catch (RuntimeException $e) {
                // Be silent if an exception is thrown when checking for updates
            }
        }
    }

    /**
     * Display plugin options in admin panel.
     *
     * @return string
     * @since  1.0
     */
    public function displayOptionsForm()
    {
        $prefix = $this->getOptionsPrefix();
        $helper = new UserHelper($this->app());

        ob_start();
        ?>

<form action="index.php" method="post">

<fieldset id="front-end-signup" class="">
  <legend>Front-end signup</legend>
    <p>
      <label
        class="inline"
        for="options_<?php echo $prefix; ?>frontendsignup_enable"
      >
        Enable:
      </label>
      <input
        type="radio"
        name="options_<?php echo $prefix; ?>frontendsignup_enable"
        value="1"
        <?php if ($this->options[$prefix."frontendsignup_enable"]) : ?>
          checked="checked"
        <?php endif; ?>
      /> Yes /
      <input
        type="radio"
        name="options_<?php echo $prefix; ?>frontendsignup_enable"
        value="0"
        <?php if (!$this->options[$prefix."frontendsignup_enable"]) : ?>
          checked="checked"
        <?php endif; ?>
      /> No
    </p>
    <p>
      <label class="inline" for="options_<?php echo $prefix; ?>frontendsignup_show_billing">
        Show billing details:
      </label>
      <input
        type="radio"
        name="options_<?php echo $prefix; ?>frontendsignup_show_billing"
        value="1"
        <?php if ($this->options[$prefix."frontendsignup_show_billing"]) : ?>
          checked="checked"
        <?php endif; ?>
      /> Yes /
      <input
        type="radio"
        name="options_<?php echo $prefix; ?>frontendsignup_show_billing"
        value="0"
        <?php if (!$this->options[$prefix."frontendsignup_show_billing"]) : ?>
          checked="checked"
        <?php endif; ?>
      /> No
    </p>
    <p>
      <label class="inline" for="options_<?php echo $prefix; ?>frontendsignup_def_group">
        Default group for new users:
      </label>
      <?php
      echo $helper->getGroupsSelect(
        "options_".$prefix."frontendsignup_def_group",
        $this->options[$prefix."frontendsignup_def_group"]
      );
      ?>
    </p>
  </fieldset>

  <p>
    <input type="button" value="&larr; Back" onclick="window.history.back();" />
    <input type="submit" value="Save &rarr;" />
  </p>

  <input type="hidden" name="controller" value="plugins" />
  <input type="hidden" name="action" value="save_options" />
</form>

        <?php
        $str = ob_get_contents();
        ob_end_clean();

        return $str;
    }

    public function handleContentShortCode($attr)
    {
        var_dump($attr);
        exit;
    }

    /**
     * Handle [nav] shortcode.
     *
     * @param array Associative array containing the shortcode attributes.
     *
     * @return string This method returns the string the shortcode will be
     *                replaced with.
     * @since  1.0
     */
    public function handleNavShortCode($attr)
    {
        $request        = $this->app()->request();
        $user           = $this->app()->user();
        $tree           = $request->param("_content_tree");
        $active_content = $request->param("_content_active");

        if (!$tree instanceof Content) {
            return;
        }

        $html_sitemap = new HTMLSiteMap($tree, $user);

        if (array_key_exists("type", $attr)) {
            switch ($attr["type"]) {
            case "branch" :
                $html_sitemap->node($active_content);
                break;
            case "parent" :
                $html_sitemap->parent(true);
                break;
            case "breadcrumbs" :
                if ($active_content instanceof Content) {
                    return $html_sitemap->breadcrumbs($active_content);
                } else {
                    return "";
                }
            }
        }

        if (array_key_exists("show_root", $attr)) {
            $html_sitemap->showRoot(false);
        }

        if (array_key_exists("show_root_as_child", $attr)) {
            $html_sitemap->showRootAsChild($attr["show_root_as_child"]);
        }

        if (array_key_exists("depth", $attr)) {
            $html_sitemap->depth($attr["depth"]);
        }

        if (array_key_exists("show_forbidden", $attr)) {
            $html_sitemap->showForbidden($attr["show_forbidden"]);
        }

        if (array_key_exists("exclude", $attr)) {
            $exclude = explode(",", $attr["exclude"]);
            $html_sitemap->exclude($exclude);
        }

        return (string) $html_sitemap;
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
            $installer = new Installer($this->app());
            $installer->installDB();
        }

        $this->options[$this->getOptionsPrefix()."version"] = "0.1.3";
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
        $tree = $this->app()->request()->param("_content_tree");

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
        if ($request->controllerName() == "content" && $request->param("xml")) {
            $tree = $request->param("_content_tree");
            $xml_sitemap = new XMLSiteMap($tree, $base_url);

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
    private function _mapRequest()
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

        if ($request->method() == "POST" && $request->param("slug")) {
            $slug = $request->param("slug");
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
        if (!$this->app()->session()->isAuth() || $this->app()->user()->id() > 2) {
            $id_obj->where("c.status", "=", 1);
        }
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
            if ($controller != "content") {
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
                    $request->controllerName("content");
                }

                if ($item->slug() == $slug) {
                    // Get fully fledged content data for active item
                    $item = $this->_mapper->findOne($item->id());
                    $item->active(true);
                    $request->param("_content_active", $item);
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
                    $request->param("_content_tree", $item);
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
     * Replace short tags.
     *
     * @return void
     * @since  1.0
     */
    private function _replaceShortTags()
    {
        $request           = $this->app()->request();
        $response          = $this->app()->response();
        $body              = $response->body();
        $short_code_parser = new ShortCodeParser();
        $keywords          = $this->shortCodes()->getKeywords();
        $regex             = "/\[(".implode("|", $keywords).")(\s+.*)?\]/";

        if (preg_match_all($regex, $body, $matches)) {
            foreach ($matches[0] as $short_code) {
                $array = $short_code_parser->parse($short_code);

                $replace = $this->shortCodes()->call($array[0], $array[1]);

                $pattern = '/'.preg_quote($short_code, '/').'/';
                $body = preg_replace($pattern, $replace, $body);
            }
        }

        if ($request->controllerName() == "content" && $request->action() == "form") {
            $body = preg_replace("/\[@@(.*)@@\]/", "[$1]", $body);
        }

        // Set processed response back in response
        $response->body($body, false);
    }

    /**
     * This method is called by MashinePlugin::getContentStats() to recursively
     * iterate the content tree and count the number of pages, posts and so on.
     *
     * @return array
     * @since  1.0
     */
    private function _doGetContentStats(
        $content,
        &$array=array("pages"=>0,"posts"=>0,"mvc_actions"=>0)
    ) {
        $sql  = "SELECT type, COUNT(id) FROM `content` ";
        $sql .= "WHERE type IN ('PageContent', 'PostContent', 'MVCContent')";
        $sql .= "GROUP BY type";
        $rs = $this->app()->db()->fetchAssocList($sql);

        foreach ($rs as $row) {
            if ($row["type"] == "PageContent") {
                $array["pages"] = $row["COUNT(id)"];
            } elseif ($row["type"] == "PostContent") {
                $array["posts"] = $row["COUNT(id)"];
            } elseif ($row["type"] == "MVCContent") {
                $array["mvc_actions"] = $row["COUNT(id)"];
            }
        }

        return $array;
    }

    /**
     * Get an associative array where keys are the names of api controllers
     * and the values is an array containing the actions of that controller.
     * This method reads the api directory
     * in src/controllers/api for installed API components.
     *
     * @return array An associative array where each key is the name of an
     * API controller and the values are the actions of that api controller
     * @since  1.0
     */
    private function _getApiControllers()
    {
        $api_path = $this->app()->getInstallDir().DS."src".DS."controllers".DS."api";
        $dir_it   = new DirectoryIterator($api_path);
        $controllers_array = array();

        foreach ($dir_it as $file) {
            $fname = $file->getFilename();
            if ($file->isFile() && preg_match("/\.php$/", $fname)) {
                $controller_name = substr($fname, 0, strrpos($fname, "."));
                if (!class_exists($controller_name)) {
                    include $api_path.DS.$controller_name.".php";
                }
                $class_name = ucfirst($controller_name)."ApiController";
                $controller_class = new ReflectionClass($class_name);
                $methods = $controller_class->getMethods();
                $actions = array();
                if (count($methods) > 0) {
                    foreach ($methods as $method) {
                        $declaring_class = $method->getDeclaringClass()->getName();
                        if ($method->getName() != "__construct"
                            && $method->isPublic()
                            && $declaring_class == $class_name
                        ) {
                            $actions[] = $method->name;
                        }
                    }
                }
                $controllers_array[$controller_name] = $actions;
            }
        }
        return $controllers_array;
    }
}
