<?php
/**
 * src/controllers/api/content.php
 *
 * PHP version 5
 *
 * @category   PHPFrame_Applications
 * @package    Mashine
 * @subpackage ApiControllers
 * @author     Lupo Montero <lupo@e-noise.com>
 * @copyright  2010 E-NOISE.COM LIMITED
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://github.com/E-NOISE/Mashine
 */

/**
 * Content API Controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class ContentApiController extends PHPFrame_RESTfulController
{
    private $_mapper;

    /**
     * Get content.
     *
     * @param int $parent_id [Optional] The parent node for which to get it's
     *                       children.
     * @param int $id        [Optional] if specified a single content item will
     *                       be returned.
     * @param int $limit     [Optional] Default value is 10.
     * @param int $page      [Optional] Default value is 1.
     *
     * @return array|object Either a single content item object or an array
     *                      containing many content objects.
     * @since  1.0
     */
    public function get($parent_id=null, $id=null, $limit=10, $page=1)
    {
        if (empty($limit)) {
            $limit = 10;
        }

        if (empty($page)) {
            $page = 1;
        }

        if (empty($id)) {
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

            $id_obj  = $this->_getMapper()->getIdObject();
            $id_obj->select($select);
            $id_obj->where("parent_id", "=", ":parent_id");
            $id_obj->params(":parent_id", (int) $parent_id);
            $id_obj->orderby("c.pub_date DESC, c.id", "DESC");
            $id_obj->limit($limit, ($page-1)*$limit);

            if (!$this->session()->isAuth() || $this->user()->id() > 2) {
                $id_obj->where("c.status", "=", "1");
            }

            $collection = $this->_getMapper()->find($id_obj);
            if ($this->format() == "php") {
                $ret = $collection;
            } else {
                $ret = array();
                foreach ($collection as $obj) {
                    $array = array(
                        "id" => $obj->id(),
                        "url" => $this->config()->get("base_url").$obj->slug(),
                        "title" => $obj->title(),
                        "pub_date" => date("Y-m-d\TH:i", strtotime($obj->pubDate())),
                        "pub_date_human" => date("l jS F Y", strtotime($obj->pubDate())),
                        "type" => str_replace("Content", "", $obj->type()),
                        "author" => $obj->author(),
                        "status" => $obj->status()
                    );

                    if (method_exists($obj, "excerpt")) {
                        $array["excerpt"] = $obj->excerpt();
                    }

                    $ret[] = $array;
                }
            }

        } else {
            $ret = $this->_fetchContent($id);
        }

        return $this->handleReturnValue($ret);
    }

    /**
     * Create/update content.
     *
     * @param int    $parent_id The content's parent id.
     * @param string $type      The content type.
     * @param int    $id        [Optional] The content id.
     *
     * @return object The content object after saving it.
     * @since  1.0
     */
    public function post($parent_id, $type, $id=null)
    {
        if (!$this->session()->isAuth()) {
            $msg = "Permission denied.";
            throw new Exception($msg, 401);
        }

        $request   = $this->request();
        $parent_id = filter_var($parent_id, FILTER_VALIDATE_INT);
        $id        = filter_var($id, FILTER_VALIDATE_INT);

        if (!is_int($id) || $id <= 0) {
            if (!class_exists($type)) {
                $msg = "Unsupported content type '".$type."'.";
                throw new InvalidArgumentException($msg, 400);
            }

            $obj = new $type();

            if (!$obj instanceof Content) {
                $msg = "Unsupported content type '".$type."'.";
                throw new InvalidArgumentException($msg, 400);
            }

            $obj->parentId($parent_id);

        } else {
            $obj = $this->_fetchContent($id, true);
            $obj->parentId($parent_id);
        }

        $params = $request->params();
        unset($params["id"]);

        $obj->bind($params);

        $obj->owner($this->user()->id());
        $obj->group(2);
        $obj->perms(664);

        $this->_getMapper()->insert($obj);

        $this->response()->body($obj);
    }

    /**
     * Delete content.
     *
     * @param int $id The content id.
     *
     * @return void
     * @since  1.0
     */
    public function delete($id)
    {
        $obj = $this->_fetchContent($id, true);
        $this->_getMapper()->delete($obj->id());
        $this->response()->body(true);
    }

    /**
     * Fetch a content item by ID and check read access.
     *
     * @param int  $id The content id.
     * @param bool $w  [Optional] Ensure write access? Default is FALSE.
     *
     * @return Content
     * @since  1.0
     */
    private function _fetchContent($id, $w=false)
    {
        return $this->fetchObj($this->_getMapper(), $id, $w);
    }

    /**
     * Get instance of mapper.
     *
     * @return ContentMapper
     * @since  1.0
     */
    private function _getMapper()
    {
        if (is_null($this->_mapper)) {
            $this->_mapper = new ContentMapper(
                $this->db(),
                $this->app()->getTmpDir().DS."cms"
            );
        }

        return $this->_mapper;
    }
}
