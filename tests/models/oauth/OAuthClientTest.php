<?php
require_once "PHPFrame.php";
require_once preg_replace("/(.*tests\/).+/", "$1TestCases.php", __FILE__);

class OAuthClientTest extends PersistentObjectTestCase
{
    public function setUp()
    {
        $this->fixture(new OAuthClient());
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
