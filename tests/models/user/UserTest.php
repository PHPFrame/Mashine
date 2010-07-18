<?php
require_once "PHPFrame.php";
require_once preg_replace("/(.*tests\/).+/", "$1TestCases.php", __FILE__);
require_once preg_replace("/(.*)tests\/(.+)Test(\.php)/", "$1src/$2$3", __FILE__);

class UserTest extends PersistentObjectTestCase
{
    public function setUp()
    {
        $this->fixture(new User());
    }

    public function test_construct()
    {

    }

    public function test_constructFailure()
    {

    }
}
