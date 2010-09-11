<?php
$pattern     = "/(.*)(tests)([\/|\\\])(.*)Test(\.php)/";
$replacement = '$1$2$3/TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);

class UserControllerHttpTest extends MVCTestCase
{
    public function setUp()
    {
        //$this->fixture(new Content());
    }

    public function tearDown()
    {
        //...
    }

    public function test_()
    {
        //...
    }
}
