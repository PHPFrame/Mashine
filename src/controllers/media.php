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
    private $_api;

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

    public function manage($node="")
    {
        //TODO: Have to check if media dir is writable and raise warning if not

        try {
            $node = $this->_fetchNode($node);
        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
            return;
        }

        $title = MediaLang::MANAGE;
        $view  = $this->view("admin/media/manage");

        $view->addData("title", $title);
        $view->addData("current_dir", $node);

        $this->response()->title($title);
        $this->response()->body($view);
    }

    public function mkdir($parent, $name="")
    {
        if ($name) {
            try {
                $ret = $this->_getApiController()->mkdir($parent, $name);
                $this->notifySuccess(MediaLang::NEW_DIR_OK);
                $this->setRedirect("admin/media?node=".$parent);

            } catch (Exception $e) {
                $this->raiseError($e->getMessage());
            }

            return;
        }

        $parent = $this->_fetchNode($parent);
        $config = $parent->getConfig();
        $title  = MediaLang::NEW_DIR;
        $view   = $this->view("admin/media/mkdir");

        $view->addData("title", $title);
        $view->addData("parent", $parent);
        $view->addData("upload_dir", $config["upload_dir"]);

        $this->response()->title($title);
        $this->response()->body($view);
    }

    public function upload($parent)
    {
        $node  = $this->_fetchNode($parent);
        $title = MediaLang::UPLOAD_TITLE;
        $view  = $this->view("admin/media/upload");

        $view->addData("title", $title);
        $view->addData("current_dir", $node);

        $this->response()->title($title);
        $this->response()->body($view);
    }

    public function generate_thumbs()
    {
        //...
    }

    public function resize()
    {
        //...
    }

    public function caption()
    {
        //...
    }

    public function delete($node)
    {
        try {
            $this->_getApiController()->delete($node);
            $this->notifySuccess(MediaLang::DIR_DELETE_OK);

        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
        }

        $base_url = $this->config()->get("base_url");
        $parent = substr($node, 0, strrpos($node, "/"));
        $this->setRedirect($base_url."admin/media?node=".$parent);
    }

    private function _getApiController()
    {
        if (!$this->_api) {
            $this->_api = new MediaApiController($this->app(), true);
            $this->_api->format("php");
            $this->_api->returnInternalPHP(true);
        }

        return $this->_api;
    }

    private function _fetchNode($node)
    {
        return $this->_getApiController()->get($node);
    }
}

