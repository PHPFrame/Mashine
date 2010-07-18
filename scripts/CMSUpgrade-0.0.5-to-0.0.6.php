<?php
class CMSUpgrade_0_0_5_to_0_0_6
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
        $this->_addPluginsLinkToAdminMenu();
    }

    private function _addPluginsLinkToAdminMenu()
    {
        $db = $this->app()->db();

        $mapper = new ContentMapper($db, $this->app()->getTmpDir().DS."cms");

        $content = new MVCContent();
        $content->parentId(9);
        $content->title("Plugins");
        $content->slug("admin/plugins");
        $content->status(1);
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "plugins");
        $content->param("action", "index");
        $content->owner(1);
        $content->group(1);
        $content->perms(440);
        $mapper->insert($content);

        $content = new MVCContent(array("parent_id"=>$content->id()));
        //$content->parentId(9);
        $content->title("Plugin Options");
        $content->slug("admin/plugins/options");
        $content->status(1);
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "plugins");
        $content->param("action", "options");
        $content->owner(1);
        $content->group(1);
        $content->perms(440);
        $mapper->insert($content);
    }
}
