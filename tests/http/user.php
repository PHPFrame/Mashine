<?php
$pattern     = "/(.*)(tests)([\/|\\\])(.*)Test(\.php)/";
$replacement = '$1$2$3/TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);

class UserControllerTest extends MVCTestCase
{
    public function setUp()
    {
        //$this->fixture(new Content());
    }

    public function tearDown()
    {
        //...
    }

    public function test_construct()
    {
        $request = new PHPFrame_Request();
        $request->requestURI("/dashboard");
        $request->scriptName("/index.php");
        $request->ajax(true);

        var_dump($this->app()->dispatch($request));
    }
}
