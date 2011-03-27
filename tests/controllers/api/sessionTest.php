<?php
$pattern     = "/(.*)(tests)([\/|\\\])(.*)Test(\.php)/";
$replacement = '$1$2$3/TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);
require_once preg_replace($pattern, '$1src$3$4$5', __FILE__);

class SessionApiControllerTest extends MVCTestCase
{
    public function setUp()
    {
        $app = $this->app(true);
        $app->session()->setUser(new User);
        $this->fixture(new SessionApiController($app));
    }

    public function test_login()
    {
        $this->fixture()->login("root@example.com", "Passw0rd");
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret_array = get_object_vars(json_decode(trim($response->body())));
        $this->assertInternalType("array", $ret_array);
        $this->assertArrayHasKey("ret_url", $ret_array);
        $this->assertArrayHasKey("auth", $ret_array);
        $this->assertArrayHasKey("user_id", $ret_array);
        $this->assertEquals($this->app()->config()->get("base_url")."dashboard", $ret_array["ret_url"]);
        $this->assertEquals(true, $ret_array["auth"]);
        $this->assertEquals(1, $ret_array["user_id"]);
    }

    public function test_loginWithRetUrl()
    {
        $this->fixture()->login("root@example.com", "Passw0rd", false, "http://someuri");
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret_array = get_object_vars(json_decode(trim($response->body())));
        $this->assertInternalType("array", $ret_array);
        $this->assertArrayHasKey("ret_url", $ret_array);
        $this->assertArrayHasKey("auth", $ret_array);
        $this->assertArrayHasKey("user_id", $ret_array);
        $this->assertEquals("http://someuri", $ret_array["ret_url"]);
        $this->assertEquals(true, $ret_array["auth"]);
        $this->assertEquals(1, $ret_array["user_id"]);
    }

    public function test_loginBadEmailFailure()
    {
        try {
            $this->fixture()->login("root@", "Passw0rd");
        } catch (Exception $e) {
            $this->assertEquals("InvalidArgumentException", get_class($e));
            $this->assertEquals("Invalid email.", $e->getMessage());
            $this->assertEquals(400, $e->getCode());
        }
    }

    public function test_loginUnknownEmailFailure()
    {
        try {
            $this->fixture()->login("root@example.net", "Passw0rd");
        } catch (Exception $e) {
            $this->assertEquals("Exception", get_class($e));
            $this->assertRegExp("/Sorry/", $e->getMessage());
            $this->assertEquals(401, $e->getCode());
        }
    }

    public function test_loginWrongPasswordFailure()
    {
        try {
            $this->fixture()->login("root@example.com", "Pass");
        } catch (Exception $e) {
            $this->assertEquals("Exception", get_class($e));
            $this->assertRegExp("/Sorry/", $e->getMessage());
            $this->assertEquals(401, $e->getCode());
        }
    }

    public function test_logout()
    {
        $this->fixture()->logout();
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret_array = get_object_vars(json_decode(trim($response->body())));
        $this->assertInternalType("array", $ret_array);
        $this->assertArrayHasKey("ret_url", $ret_array);
        $this->assertArrayHasKey("auth", $ret_array);
        $this->assertArrayHasKey("user_id", $ret_array);
        $this->assertEquals($this->app()->config()->get("base_url"), $ret_array["ret_url"]);
        $this->assertEquals(false, $ret_array["auth"]);
        $this->assertEquals(0, $ret_array["user_id"]);
    }
}
