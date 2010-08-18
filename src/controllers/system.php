<?php
/**
 * src/controllers/system.php
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
 * System controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
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
        $content = $this->request()->param("active_content");
        $options = $this->request()->param("mashine_options");
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
        $content = $this->request()->param("active_content");
        $view    = $this->view("admin/config/index");

        $view->addData("title", $content->title());
        $view->addData("config", $this->config());

        $this->response()->title($content->title());
        $this->response()->body($view);
    }

    /**
     * Archive the whole site and send zipped tar to browser for download.
     *
     * @return void
     * @since  1.0
     */
    public function backup()
    {
        try {
            $tmp_file = PHPFrame_Filesystem::getSystemTempDir().DS."backup.tgz";
            $tar      = new Archive_Tar($tmp_file, "gz");

            $tar->createModify(
                $this->app()->getInstallDir(),
                "",
                $this->app()->getInstallDir()
            );

            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=backup.tgz");
            header("Content-Type: application/x-compressed");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".filesize($tmp_file));

            ob_clean();
            flush();

            // Read the file from disk
            readfile($tmp_file);

            // Delete temp file
            PHPFrame_Filesystem::rm($tmp_file);

        } catch (Exception $e) {
            $this->response()->statusCode(500);
            $this->raiseError($e->getMessage());
        }
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
        $content = $this->request()->param("active_content");
        $view    = $this->view("admin/api/index");
        $mapper  = new OAuthClientsMapper($this->db());
        $clients = $mapper->find();

        $view->addData("title", $content->title());
        $view->addData("clients", $clients);

        $this->response()->title($content->title());
        $this->response()->body($view);
    }
}
