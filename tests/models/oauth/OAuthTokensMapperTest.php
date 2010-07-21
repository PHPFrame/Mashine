<?php
require_once "PHPFrame.php";
require_once preg_replace("/(.*tests\/).+/", "$1TestCases.php", __FILE__);
require_once preg_replace("/(.*)tests\/(.+)Test(\.php)/", "$1src/$2$3", __FILE__);

class OAuthTokensMapperTest extends MapperTestCase
{
    public function setUp()
    {
        $this->fixture(new OAuthTokensMapper($this->app()->db()));
    }

    protected function createPersistentObj()
    {
        $token = new OAuthToken();
        $token->consumerKey("391f12aadbedf041e37bf06a9ushro");
        $token->type("request");

        return $token;
    }
}
