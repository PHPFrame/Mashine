<?php
require_once "PHPFrame.php";
require_once preg_replace("/(.*tests\/).+/", "$1TestCases.php", __FILE__);
require_once preg_replace("/(.*)tests\/(.+)Test(\.php)/", "$1src/$2$3", __FILE__);

class OAuthClientsMapperTest extends MapperTestCase
{
    public function setUp()
    {
        $this->fixture(new OAuthClientsMapper($this->app()->db()));
    }

    protected function createPersistentObj()
    {
        $oauth_client = new OAuthClient();
        $oauth_client->name("Mashine Frontend");
        $oauth_client->version("1.0");
        $oauth_client->vendor("Mashine Project");

        return $oauth_client;
    }
}
