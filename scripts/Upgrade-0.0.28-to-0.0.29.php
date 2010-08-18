<?php
class Upgrade_0_0_28_to_0_0_29
{
    private $_app, $_install_dir;

    public function __construct(PHPFrame_Application $app)
    {
        $this->_app = $app;
        $this->_install_dir = $this->app()->getInstallDir();
    }

    public function app()
    {
        return $this->_app;
    }

    public function run()
    {
        $this->_removeObsoleteCmsController();
        $this->_moveCmsViews();
        $this->_renameCmsControllerInMVCContent();
        $this->_updateLinkToMashineFeed();
    }

    private function _removeObsoleteCmsController()
    {
        $cms_controller = $this->_install_dir.DS."src".DS."controllers".DS."cms.php";
        if (is_file($cms_controller)) {
            PHPFrame_Filesystem::rm($cms_controller);
        }
    }

    private function _moveCmsViews()
    {
        $views_dir = $this->_install_dir.DS."src".DS."views";
        PHPFrame_Filesystem::cp($views_dir.DS."cms/*", $views_dir, true);
        PHPFrame_Filesystem::rm($views_dir.DS."cms", true);
    }

    private function _renameCmsControllerInMVCContent()
    {
        $mapper = new ContentMapper(
            $this->app()->db(), $this->app()->getTmpDir().DS."cms"
        );

        $id_obj = $mapper->getIdObject();
        $id_obj->where("type", "IN", "('MVCContent', 'PageContent')");
        $collection = $mapper->find($id_obj);

        foreach ($collection as $item) {
            if ($item instanceof MVCContent) {
                $controller = $item->param("controller");
                if ($controller == "cms") {
                    $item->param("controller", "content");
                    $mapper->insert($item);
                }
            }

            if ($item instanceof PageContent) {
                $view = $item->param("view");
                if (preg_match("/^cms\/(.+)/", $view, $matches)) {
                    $item->param("view", $matches[1]);
                    $mapper->insert($item);
                }
            }
        }
    }

    private function _updateLinkToMashineFeed()
    {
        $mapper = new ContentMapper(
            $this->app()->db(), $this->app()->getTmpDir().DS."cms"
        );

        $old_url = "http://github.com/lupomontero/Mashine/commits/master.atom";
        $content = $mapper->findOne("mashine-on-github");
        if ($content instanceof FeedContent
            && $content->param("feed_url") == $old_url
        ) {
            $content->param(
                "feed_url",
                "http://github.com/E-NOISE/Mashine/commits/master.atom"
            );

            $mapper->insert($content);
        }
    }
}
