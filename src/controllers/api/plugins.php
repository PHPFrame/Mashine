<?php
/**
 * src/controllers/api/plugins.php
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
 * Plugins API Controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class PluginsApiController extends PHPFrame_RESTfulController
{
    private $_mapper;

    /**
     * Constructor.
     *
     * @param PHPFrame_Application $app Instance of application.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app);

        $this->ensureIsStaff();
    }

    /**
     * Get plugin(s) info.
     *
     * @param int $id        [Optional] if specified a single plugin item will
     *                       be returned.
     * @param int $limit     [Optional] Default value is 10.
     * @param int $page      [Optional] Default value is 1.
     *
     * @return array|object Either a single content item object or an array
     *                      containing many content objects.
     * @since  1.0
     */
    public function get($id=null, $limit=10, $page=1)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if (empty($id)) {
            if (empty($limit)) {
                $limit = 10;
            }

            if (empty($page)) {
                $page = 1;
            }

            $plugins = iterator_to_array($this->app()->plugins());
            $limitstart = ($page-1)*$limit;
            $ret = array();


            for ($i=$limitstart; $i<count($plugins) && ($i-$limitstart)<$limit; $i++) {
                $ret[] = iterator_to_array($plugins[$i]);
            }

        } else {
            $ret = $this->_fetchPluginInfo($id);
        }

        $this->response()->body($ret);
    }

    /**
     * Create/update plugin info.
     *
     * @param int    $id      [Optional] The plugin id.
     * @param string $name    [Optional] The plugin name.
     * @param bool   $enabled [Optional] Whether the plugin is enabled.
     *
     * @return object The plugin object after saving it.
     * @since  1.0
     */
    public function post($id=null, $name=null, $enabled=null)
    {
        $request = $this->request();
        $id      = filter_var($id, FILTER_VALIDATE_INT);

        if (!is_int($id) || $id <= 0) {
            $obj = new PHPFrame_PluginInfo();
            $obj->owner($this->user()->id());

        } else {
            $obj = $this->_fetchPluginInfo($id, true);

        }

        $params = $request->params();
        unset($params["id"]);
        if (isset($params["name"]) && empty($params["name"])) {
            unset($params["name"]);
        }

        $obj->bind($params);

        $obj->group(2);
        $obj->perms(664);

        $this->app()->plugins()->insert($obj);

        $this->app()->plugins($this->app()->plugins());

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
        $obj = $this->_fetchPluginInfo($id, true);
        $this->app()->plugins()->delete($obj->id());
        $this->response()->body(true);
    }

    /**
     * Fetch a plugin by ID and check read access.
     *
     * @param int  $id The plugin id.
     * @param bool $w  [Optional] Ensure write access? Default is FALSE.
     *
     * @return Plugin
     * @since  1.0
     */
    private function _fetchPluginInfo($id, $w=false)
    {
        return $this->fetchObj($this->app()->plugins()->mapper(), $id, $w);
    }
}
