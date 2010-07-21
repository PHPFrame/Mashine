<?php
require_once "PHPFrame.php";
require_once preg_replace("/(.*tests\/).+/", "$1TestCases.php", __FILE__);

class OAuthTokenTest extends PersistentObjectTestCase
{
    public function setUp()
    {
        $this->fixture(new OAuthToken());
    }

    public function tearDown()
    {
        //...
    }

    public function test_construct()
    {

    }

    public function test_constructFailure()
    {

    }
}
