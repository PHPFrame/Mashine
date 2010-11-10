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
        $view->addData("node", $node);

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
        $files = $this->request()->files();
        if (count($files) > 0 && array_key_exists("upload_file", $files)) {
            $ret_url = "admin/media";
            if ($parent) {
                $ret_url .= "?node=".urlencode($parent);
            }

            try {
                $node = $this->_getApiController()->upload($parent);
                $this->notifySuccess(MediaLang::UPLOAD_OK);

            } catch (Exception $e) {
                $this->raiseError($e->getMessage());
            }

            $this->setRedirect($ret_url);
            return;
        }

        $parent = $this->_fetchNode($parent);
        $config = $parent->getConfig();
        $title  = MediaLang::UPLOAD_TITLE;
        $view   = $this->view("admin/media/upload");

        $view->addData("title", $title);
        $view->addData("parent", $parent);
        $view->addData("upload_dir", $config["upload_dir"]);

        $this->response()->title($title);
        $this->response()->body($view);
    }

    public function delete($node)
    {
        try {
            $array = $this->_getApiController()->delete($node);
            $this->notifySuccess($array["success"]);

        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
        }

        $base_url = $this->config()->get("base_url");
        $parent = substr($node, 0, strrpos($node, "/"));
        $this->setRedirect($base_url."admin/media?node=".$parent);
    }

    public function rename()
    {
        //...
    }

    public function generate_thumbs($node)
    {
        try {
            $node = $this->_getApiController()->generate_thumbs($node);
            $this->notifySuccess(MediaLang::GENERATE_THUMB_OK);

        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
        }

        $ret_url = $this->config()->get("base_url")."admin/media?node=";
        if ($node->isDir()) {
            $ret_url .= urlencode($node->getRelativePath());
        } else {
            $ret_url .= urlencode($node->getParentRelativePath());
        }

        $this->setRedirect($ret_url);
    }

    public function resize($node)
    {
        try {
            $node = $this->_getApiController()->resize($node);
            $this->notifySuccess(MediaLang::RESIZE_IMAGES_OK);

        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
        }

        $ret_url = $this->config()->get("base_url")."admin/media?node=";
        if ($node->isDir()) {
            $ret_url .= urlencode($node->getRelativePath());
        } else {
            $ret_url .= urlencode($node->getParentRelativePath());
        }

        $this->setRedirect($ret_url);
    }

    public function caption()
    {
        //...
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
