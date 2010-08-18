<?php
class Upgrade_0_0_28_to_0_0_29
{
    private $_app;

    public function __construct(PHPFrame_Application $app)
    {
        $this->_app = $app;
    }

    public function app()
    {
        return $this->_app;
    }

    public function run()
    {
        $this->_moveCmsViews();
        $this->_renameCmsControllerInMVCContent();
    }

    private function _moveCmsViews()
    {
        $views_dir = $this->app()->getInstallDir().DS."src".DS."views";
        PHPFrame_Filesystem::cp($views_dir.DS."cms/", $views_dir, true);
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
}
