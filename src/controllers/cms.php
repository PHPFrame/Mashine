<?php
/**
 * src/controllers/cms.php
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
 * CMS controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class CMSController extends PHPFrame_ActionController
{
    /**
     * Constructor.
     *
     * @param PHPFrame_Application $app Reference to application object.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app, "index");
    }

    /**
     * Display content.
     *
     * @return void
     * @since  1.0
     */
    public function index()
    {
        $content  = $this->request()->param("active_content");
        $base_url = $this->config()->get("base_url");

        if (!$content instanceof Content) {
            $this->response()->statusCode(404);
            $this->raiseError("Oooops... content not found");
            return;
        }

        // Check whether user has access to requested object
        if (!$content->canRead($this->user())) {
            if (!$this->session()->isAuth()) {
                $this->setRedirect(
                    $base_url."user/login?ret_url=".$content->slug()
                );
                return;
            } else {
                $this->response()->statusCode(401);
                $this->raiseError("Oooops... permission denied");
                return;
            }
        }

        $view = null;
        $custom_view = $content->param("view");
        if ($custom_view) {
            $view = $this->view($custom_view);
        }

        $doc = $this->response()->document();
        if ($doc instanceof PHPFrame_HTMLDocument) {
            $doc->addMetaTag("description", $content->description());
            $doc->addMetaTag("keywords", $content->keywords());

            $robots  = ($content->robotsIndex()) ? "index" : "noindex";
            $robots .= ($content->robotsFollow()) ? ", follow" : ", nofollow";
            $doc->addMetaTag("robots", $robots);
        }

        if ($content instanceof MVCContent) {
            $this->request()->controllerName($content->param("controller"));
            if ($content->param("action")) {
                $this->request()->action($content->param("action"));
            }

            $this->request()->dispatched(false);
            return;

        } elseif ($content instanceof PageContent) {
            if (is_null($view)) {
                $view = $this->view("cms/page");
            }

        } elseif ($content instanceof PostContent) {
            if (is_null($view)) {
                $view = $this->view("cms/post");
            }

        } elseif ($content instanceof PostsCollectionContent) {
            $page = $this->request()->param("page");
            $posts_per_page = $content->param("posts_per_page");
            $mapper = new ContentMapper(
                $this->db(),
                $this->app()->getTmpDir().DS."cms"
            );

            if (!$page) {
                $page = 1;
            }

            if (!$posts_per_page) {
                $posts_per_page = 10;
            }

            if ($this->db()->isSQLite()) {
                $select  = array(
                    "c.*",
                    "cd.params AS params",
                    "u.email AS author_email",
                    "(uc.first_name || ' ' ||  uc.last_name) AS author"
                );
            } else {
                $select  = array(
                    "c.*",
                    "cd.params AS params",
                    "u.email AS author_email",
                    "CONCAT(uc.first_name, ' ', uc.last_name) AS author"
                );
            }

            $select[] = "cd.description AS description";
            $select[] = "cd.keywords AS keywords";
            $select[] = "cd.body AS body";

            $id_obj = $mapper->getIdObject();
            $id_obj->select($select);
            $id_obj->where("parent_id", "=", ":parent_id");
            $id_obj->params(":parent_id", $content->id());
            $id_obj->orderby("c.pub_date DESC, c.id", "DESC");
            $id_obj->limit($posts_per_page, ($page-1)*$posts_per_page);

            if (!$this->session()->isAuth() || $this->user()->id() > 2) {
                $id_obj->where("c.status", "=", "1");
            }

            $posts  = $mapper->find($id_obj);
            $format = $this->request()->param("format", null);

            if ($format == "rss") {
                $rss      = new PHPFrame_RSSDocument();
                $rss->link($base_url.$content->slug());
                $rss->description($content->body());

                foreach ($posts as $post) {
                    $rss->addItem(
                        $post->title(),
                        $base_url.$post->slug(),
                        $post->excerpt(),
                        $post->pubDate(),
                        $post->author()
                    );
                }

                $this->response()->document($rss);
                return;
            } else {
                $this->response()->document()->addRSSLink(
                    $base_url.$content->slug()."?format=rss",
                    $content->title()
                );
            }

            if (is_null($view)) {
                $view = $this->view("cms/posts");
            }

            $view->addData("posts", $posts);

        } elseif ($content instanceof FeedContent) {
            if (is_null($view)) {
                $view = $this->view("cms/feed");
            }

        } else {
            $msg = "Unknown content type '".get_class($content)."'";
            $this->raiseError($msg);
            return;
        }

        $view->addData("title", $content->title());
        $view->addData("content", $content);
        $view->addData("user", $this->user());
        $view->addData("helper", $this->helper("cms"));

        $this->response()->title($content->title());
        $this->response()->body($view);
    }

    /**
     * Display the Content 'manager'.
     *
     * @return void
     * @since  1.0
     */
    public function manage()
    {
        $content = $this->request()->param("active_content");
        $tree    = $this->request()->param("tree");
        $view    = $this->view("cms/admin/content/index");

        $view->addData("title", $content->title());
        $view->addData("tree", $tree);
        $view->addData("helper", $this->helper("cms"));

        $this->response()->title($content->title());
        $this->response()->body($view);
    }

    /**
     * Display the Content form.
     *
     * @param int $parent_id [Optional]
     * @param int $id        [Optional]
     *
     * @return void
     * @since  1.0
     */
    public function form($parent_id=0, $id=null)
    {
        $tree = $this->request()->param("tree");

        $parent_id = filter_var($parent_id, FILTER_VALIDATE_INT);
        if ($parent_id === false) {
            $this->response()->statusCode(400);
            $this->raiseError("Invalid content parent id.");
            return;
        }

        if (!is_null($id)) {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false) {
                $this->response()->statusCode(400);
                $this->raiseError("Invalid content id.");
                return;
            }

            $mapper  = new ContentMapper(
                $this->db(),
                $this->app()->getTmpDir().DS."cms"
            );

            $content = $mapper->findOne($id);
            if (!$content instanceof Content) {
                $this->response()->statusCode(400);
                $msg = "Could not find the requested content item for editing.";
                $this->raiseError($msg);
                return;
            }

            if ($content->type() == "PostContent") {
                $title = "Edit post";
            } elseif ($content->type() == "PostsCollectionContent") {
                $title = "Edit blog page";
            } elseif ($content->type() == "MVCContent") {
                $title = "Edit MVC action";
            } elseif ($content->type() == "FeedContent") {
                $title = "Edit RSS/Atom Feed";
            } else {
                $title = "Edit page";
            }

        } else {
            $parent = $tree->getNodeById($parent_id);

            if ($parent->parentId() == 0) {
                $content = new PageContent();
                $title   = "New page in top level";

            } elseif ($parent->type() == "PostsCollectionContent") {
                $content = new PostContent();
                $title   = "New post in: ".$parent->title();

            } elseif ($parent->type() == "PostContent") {
                $this->response()->statusCode(400);
                $this->raiseError("Can not create child content in Post");
                return;

            } else {
                $content = new PageContent();
                $title   = "New child page in: ".$parent->title();
            }

            $content->owner($this->user()->id());
            $content->group($this->user()->groupId());
            $content->parent($parent);
        }

        $content->parentId();

        // Obsecure short tags to avoid rendering inside WYSIWYG editor
        $content->body(preg_replace(
            "/\[cms(.*)\]/",
            "[@@cms$1@@]",
            $content->body()
        ));

        $view = $this->view("cms/admin/content/form");

        $view->addData("title", $title);
        $view->addData("content", $content);
        $view->addData("session", $this->session());
        $view->addData("base_url", $this->config()->get("base_url"));
        $view->addData("helper", $this->helper("cms"));
        $view->addData("user_helper", $this->helper("user"));

        $this->response()->title($title);
        $this->response()->body($view);
    }

    /**
     * Save a Content entry.
     *
     * @param int $parent_id The numeric ID of the parent for the content item
     *                       to save.
     *
     * @return void
     * @since  1.0
     */
    public function save($parent_id)
    {
        $parent_id = filter_var($parent_id, FILTER_VALIDATE_INT);
        if ($parent_id === false) {
            $this->response()->statusCode(400);
            $this->raiseError("Invalid content parent id.");
            return;
        }

        $base_url = $this->config()->get("base_url");

        try {
            $params = $this->request()->params();

            if (!array_key_exists("ordering", $params)
                || !$params["ordering"]
            ) {
                $params["ordering"] = null;
            }

            $mapper = new ContentMapper(
                $this->db(),
                $this->app()->getTmpDir().DS."cms"
            );

            $id = $this->request()->param("id");
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id) {
                $content = $mapper->findOne($id);

                if (!$content instanceof Content) {
                    $this->response()->statusCode(400);
                    $msg = "Could not find the requested content item.";
                    $this->raiseError($msg);
                    return;
                }

            } else {
                unset($params["id"]);
                $content = new $params["type"];
                $content->owner($this->user()->id());
            }

            $pub_date   = $this->request()->param("pub_date", date("Y-m-d"));
            $pub_time_h = $this->request()->param("pub_time_h", date("H"));
            $pub_time_m = $this->request()->param("pub_time_m", date("i"));
            $params["pub_date"] = $pub_date." ".$pub_time_h.":".$pub_time_m.":00";

            if (!array_key_exists("robots_index", $params)) {
                $params["robots_index"] = false;
            }

            if (!array_key_exists("robots_follow", $params)) {
                $params["robots_follow"] = false;
            }

            $read  = $this->request()->param("read", "world");
            $write = $this->request()->param("write", "owner");
            $perms = array();

            switch ($read) {
            case "owner" :
                $perms[0] = 4;
                $perms[1] = 0;
                $perms[2] = 0;
                break;
            case "group" :
                $perms[0] = 4;
                $perms[1] = 4;
                $perms[2] = 0;
                break;
            case "world" :
                $perms[0] = 4;
                $perms[1] = 4;
                $perms[2] = 4;
                break;
            default :
                $msg  = "Unknown read access level. Allowed values are ";
                $msg .= "'owner', 'group' and 'world'.";
                throw new InvalidArgumentException($msg);
                break;
            }

            switch ($write) {
            case "owner" :
                $perms[0] = 6;
                break;
            case "group" :
                $perms[0] = 6;
                $perms[1] = 6;
                break;
            case "world" :
                $perms[0] = 6;
                $perms[1] = 6;
                $perms[2] = 6;
                break;
            default :
                $msg  = "Unknown write access level. Allowed values are ";
                $msg .= "'owner', 'group' and 'world'.";
                throw new InvalidArgumentException($msg);
                break;
            }

            $params["perms"] = (int) implode("", $perms);

            $custom_template = $this->request()->param("view", null);
            if (!is_null($custom_template)) {
                if (empty($custom_template)) {
                    $content->param("view", "");
                } else {
                    $content->param("view", "cms/custom/".$custom_template);
                }
            }

            if ($content instanceof PostsCollectionContent) {
                $posts_per_page = $this->request()->param("posts_per_page", 10);
                $content->param("posts_per_page", $posts_per_page);
            }

            if ($content instanceof FeedContent) {
                $feed_url   = $this->request()->param("feed_url", null);
                $cache_time = $this->request()->param("cache_time", 0);

                $content->param("feed_url", $feed_url);
                $content->param("cache_time", $cache_time);
            }

            $content->bind($params);
            $mapper->insert($content);

            $this->notifySuccess("Content items saved!");
            $this->setRedirect($base_url."admin/content/form?id=".$content->id());
            return;

        } catch (Exception $e) {
            $msg  = "An error occurred when saving the content item. ";
            $msg .= $e->getMessage();
            $this->response()->statusCode(500);
            $this->raiseError($msg);

            if ($content->id()) {
                $this->setRedirect(
                    $base_url."admin/content/form?id=".$content->id()
                );
            } else {
                $this->setRedirect(
                    $base_url."admin/content/form?parent_id=".$parent_id
                );
            }

            return;
        }
    }

    /**
     * Delete a Content entry.
     *
     * @param int $id The numeric ID of the content item to delete.
     *
     * @return void
     * @since  1.0
     */
    public function delete($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false) {
            $this->response()->statusCode(400);
            $this->raiseError("Invalid content id.");
            return;
        }

        $base_url = $this->config()->get("base_url");
        $mapper   = new ContentMapper(
            $this->db(),
            $this->app()->getTmpDir().DS."cms"
        );

        try {
            $mapper->delete($id);

            $this->notifySuccess("Content item deleted!");
            $this->setRedirect($base_url."admin/content");
            return;

        } catch (Exception $e) {
            $msg  = "An error occurred when deleting the content item. ";
            $msg .= $e->getMessage();
            $this->raiseError($msg);
            $this->response()->statusCode(500);
            $this->setRedirect($base_url."admin/content");
            return;
        }
    }
}
