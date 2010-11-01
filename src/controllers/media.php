<?php
/**
 * src/controllers/media.php
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
 * Media controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class MediaController extends PHPFrame_ActionController
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
        parent::__construct($app, "manage");
    }

    public function manage()
    {
        //TODO: Have to check if media dir is writable and raise warning if not

        try {
            $api_controller = new MediaApiController($this->app(), true);
            $api_controller->format("php");
            $api_controller->returnInternalPHP(true);
            $node = $api_controller->get();

        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
            return;
        }

        $title = "Manage media";
        $view = $this->view("admin/media/manage");
        $view->addData("title", $title);
        $view->addData("current_dir", $node);

        $this->response()->title($title);
        $this->response()->body($view);
    }
}

