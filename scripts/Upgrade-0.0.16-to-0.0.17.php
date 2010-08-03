<?php
class Upgrade_0_0_16_to_0_0_17
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
        $this->_addSignupLinkToAdminMenu();
    }

    private function _addSignupLinkToAdminMenu()
    {
        $db = $this->app()->db();

        $mapper = new ContentMapper($db, $this->app()->getTmpDir().DS."cms");

        $content = new MVCContent();
        $content->parentId(1);
        $content->title("Sign up");
        $content->slug("user/signup");
        $content->status(1);
        $content->description(null);
        $content->keywords(null);
        $content->param("controller", "user");
        $content->param("action", "signup");
        $content->owner(1);
        $content->group(1);
        $content->perms(644);
        $mapper->insert($content);
    }
}
