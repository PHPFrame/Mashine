<?php
/**
 * src/controllers/api/notifications.php
 *
 * PHP version 5
 *
 * @category  none
 * @package   none
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/lupomontero/Mashine
 */

/**
 * Notifications API Controller.
 *
 * @category none
 * @package  none
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class NotificationsApiController extends PHPFrame_RESTfulController
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

        if (!$this->session()->isAuth()) {
            $msg = "Permission denied.";
            throw new Exception($msg, 401);
        }
    }

    /**
     * Get notification(s).
     *
     * @param int $id    [Optional] if specified a single notification will be
     *                   returned.
     * @param int $limit [Optional] Default value is 10.
     * @param int $page  [Optional] Default value is 1.
     *
     * @return array|object Either a single notification object or an array
     *                      containing many notification objects.
     * @since  1.0
     */
    public function get($id=null, $limit=10, $page=1)
    {
        if (is_null($id)) {
            $id_obj = $this->_getMapper()->getIdObject();
            $id_obj->where("owner", "=", $this->user()->id());
            $ret = $this->_getMapper()->find($id_obj);

        } else {
            $ret = $this->_fetchNotification($id);
        }

        $this->response()->body($ret);
    }

    /**
     * Create/update notification.
     *
     * @param string $title  The notification title. Max 50 chars.
     * @param string $body   [Optional] Max 140 chars.
     * @param string $type   [Optional] "error", "warning", "notice", "info" and "success"
     * @param bool   $sticky [Optional] Default value is FALSE.
     *
     * @return void
     * @since  1.0
     */
    public function post($title, $body=null, $type="info", $sticky=false, $id=null)
    {
        if (!$this->session()->isAuth()) {
            $msg = "Permission denied.";
            throw new Exception($msg, 401);
        }

        $base_url = $this->config()->get("base_url");
        $request  = $this->request();
        $id       = filter_var($id, FILTER_VALIDATE_INT);

        try {
            if (!is_int($id) || $id <= 0) {
                $notification = new Notification();
                $notification->title($request->param("title", $title));
                $notification->body($request->param("body", $body));
                $notification->type($request->param("type", $type));
                $notification->sticky($request->param("sticky", $sticky));

            } else {

                $notification = $this->_fetchNotification($id, true);
                $notification->bind($request->params());
            }

            $notification->owner($this->user()->id());
            $notification->group(2);
            $notification->perms(664);

            $this->_getMapper()->insert($notification);

            $ret_obj = new StdClass();
            $ret_obj->notification = iterator_to_array($notification);

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 501);
        }

        $this->response()->body($ret_obj);
    }

    /**
     * Delete notification.
     *
     * @param int $id The notification id.
     *
     * @return void
     * @since  1.0
     */
    public function delete($id)
    {
        $notification = $this->_fetchNotification($id, true);

        $this->ensureIsStaff();

        try {
            $this->_getMapper()->delete($notification);

        } catch (Exception $e) {
            $msg = "An error occurred while deleting notification.";
            throw new Exception($msg, 501);
        }
    }

    /**
     * Fetch a notification by ID and check read access.
     *
     * @param int  $id The notification id.
     * @param bool $w  [Optional] Ensure write access? Default is FALSE.
     *
     * @return Notification
     * @since  1.0
     */
    private function _fetchNotification($id, $w=false)
    {
        return $this->fetchObj($this->_getMapper(), $id, $w);
    }

    /**
     * Get instance of mapper.
     *
     * @return PHPFrame_Mapper
     * @since  1.0
     */
    private function _getMapper()
    {
        if (is_null($this->_mapper)) {
            // if (!$this->db()->hasTable("#__notifications")) {
            //     $ort = new PHPFrame_ObjectRelationalToolbox();
            //     $ort->createTable($this->db(), new Notification(), "#__notifications");
            // }

            $this->_mapper = new PHPFrame_Mapper(
                "Notification",
                $this->db(),
                "#__notifications"
            );
        }

        return $this->_mapper;
    }
}
