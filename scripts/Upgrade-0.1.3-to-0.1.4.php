<?php
class Upgrade_0_1_3_to_0_1_4
{
    private $_app;

    public function __construct(PHPFrame_Application $app)
    {
        $this->_app = $app;
    }

    public function run()
    {
        $this->_addMediaLinkToAdminMenu();
    }

    private function _addMediaLinkToAdminMenu()
    {
        $db = $this->_app->db();

        $mapper = new ContentMapper($db, $this->_app->getTmpDir().DS."cms");
        $dashboard = $mapper->findOne("dashboard");

        $content = new MVCContent();
        $content->parentId($dashboard->id());
        $content->title("Manage media");
        $content->slug("admin/media");
        $content->status(1);
        $content->description(null);
        $content->keywords(null);
        $content->pubDate("1970-01-01 00:03:30");
        $content->param("controller", "media");
        $content->param("action", "manage");
        $content->owner(1);
        $content->group(1);
        $content->perms(644);
        $mapper->insert($content);
    }
}
