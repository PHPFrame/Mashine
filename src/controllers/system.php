<?php
/**
 * src/controllers/system.php
 *
 * PHP version 5
 *
 * @category   PHPFrame_Applications
 * @package    Mashine
 * @subpackage Controllers
 * @author     Lupo Montero <lupo@e-noise.com>
 * @copyright  2010 E-NOISE.COM LIMITED
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://github.com/E-NOISE/Mashine
 */

/**
 * System controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class SystemController extends PHPFrame_ActionController
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
     * Display system info to admins.
     *
     * @return void
     * @since  1.0
     */
    public function index()
    {
        $content = $this->request()->param("_content_active");
        $options = $this->request()->param("_options");
        $view    = $this->view("admin/system/index");

        $view->addData("title", $content->title());
        $view->addData("config", $this->config());
        $view->addData("options", $options);

        $this->response()->title($content->title());
        $this->response()->body($view);
    }

    /**
     * Display app configuration.
     *
     * @return void
     * @since  1.0
     */
    public function sysconfig()
    {
        $content = $this->request()->param("_content_active");
        $view    = $this->view("admin/config/index");

        $view->addData("title", $content->title());
        $view->addData("config", $this->config());

        $this->response()->title($content->title());
        $this->response()->body($view);
    }

    /**
     * Display eb backup we interface.
     *
     * @return void
     * @since  1.0
     */
    public function backup()
    {
        $view = $this->view("admin/system/backup");
        $this->response()->title("Backup");
        $this->response()->body($view);
    }

    /**
     * Upgrade the CMS feature to latest release.
     *
     * @return void
     * @since  1.0
     */
    public function upgrade()
    {
        try {
            $update_assistant = new UpdateAssistant($this->app());

            if ($update_assistant->isUpToDate()) {
                $this->notifyInfo("Nothing to upgrade.");
            } else {
                $messages = $update_assistant->upgrade();

                // Clear system events before adding new messages
                $this->session()->getSysevents()->clear();

                foreach ($messages as $msg) {
                    $this->notifyInfo($msg);
                }

                // Remove application registry to refresh app cache
                PHPFrame_Filesystem::rm($this->app()->getTmpDir().DS."app.reg");

                $this->notifySuccess("Mashine upgraded successfully!");
            }

        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
        }

        $this->setRedirect($this->config()->get("base_url")."dashboard");
    }

    public function api()
    {
        $content = $this->request()->param("_content_active");
        $view    = $this->view("admin/api/index");

        $mapper  = new OAuthClientsMapper($this->db());
        $clients = $mapper->find();

        $mapper = new ApiMethodsMapper($this->app()->db());
        $rows   = $mapper->find();

        // Rebuild method info data into a new array using method names as keys
        // this will allow us to map both arrays when building the table
        $method_info = array();
        foreach ($rows as $row) {
            $method_info[$row["method"]] = $row;
        }

        $api_controllers = $this->app()->registry()->get("api_controllers");
        $api_methods = array();
        foreach ($api_controllers as $controller=>$methods) {
            foreach ($methods as $method) {
                $method_name = $controller."/".$method;
                $api_methods[] = array(
                    "method" => $method_name,
                    "oauth"  => @$method_info[$method_name]["oauth"],
                    "cookie" => @$method_info[$method_name]["cookie"]
                );
            }
        }

        $base_url = $this->app()->config()->get("base_url");
        $token = base64_encode($this->session()->getToken());

        $view->addData("title", $content->title());
        $view->addData("clients", $clients);
        $view->addData("api_methods", $api_methods);
        $view->addData("base_url", $base_url);
        $view->addData("token", $token);

        $this->response()->title($content->title());
        $this->response()->body($view);
    }
}
