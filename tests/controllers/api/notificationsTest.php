<?php
$pattern     = "/(.*)(tests)([\/|\\\])(.*)Test(\.php)/";
$replacement = '$1$2$3/TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);
require_once preg_replace($pattern, '$1src$3$4$5', __FILE__);

class NotificationsApiControllerTest extends MVCTestCase
{
    public function setUp()
    {
        $app = $this->app(true);
        //$app->session()->setUser(new User);
        $this->fixture(new NotificationsApiController($app));
    }

    public function test_postGetAndDelete()
    {
        $this->fixture()->post("the title", "some content in the body...");
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $obj = get_object_vars(json_decode(trim($response->body())));
        $this->assertType("array", $obj);
        $this->assertArrayHasKey("title", $obj);
        $this->assertArrayHasKey("body", $obj);
        $this->assertArrayHasKey("type", $obj);
        $this->assertArrayHasKey("sticky", $obj);
        $this->assertArrayHasKey("id", $obj);
        $this->assertArrayHasKey("ctime", $obj);
        $this->assertArrayHasKey("mtime", $obj);
        $this->assertArrayHasKey("owner", $obj);
        $this->assertArrayHasKey("group", $obj);
        $this->assertArrayHasKey("perms", $obj);


        $this->fixture()->get();
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret_array = json_decode(trim($response->body()));
        $obj = get_object_vars($ret_array[0]);
        $this->assertArrayHasKey("title", $obj);
        $this->assertArrayHasKey("body", $obj);
        $this->assertArrayHasKey("type", $obj);
        $this->assertArrayHasKey("sticky", $obj);
        $this->assertArrayHasKey("id", $obj);
        $this->assertArrayHasKey("ctime", $obj);
        $this->assertArrayHasKey("mtime", $obj);
        $this->assertArrayHasKey("owner", $obj);
        $this->assertArrayHasKey("group", $obj);
        $this->assertArrayHasKey("perms", $obj);


        $this->fixture()->get($obj["id"]);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $obj2 = get_object_vars(json_decode(trim($response->body())));
        $this->assertArrayHasKey("title", $obj2);
        $this->assertArrayHasKey("body", $obj2);
        $this->assertArrayHasKey("type", $obj2);
        $this->assertArrayHasKey("sticky", $obj2);
        $this->assertArrayHasKey("id", $obj2);
        $this->assertArrayHasKey("ctime", $obj2);
        $this->assertArrayHasKey("mtime", $obj2);
        $this->assertArrayHasKey("owner", $obj2);
        $this->assertArrayHasKey("group", $obj2);
        $this->assertArrayHasKey("perms", $obj2);

        $this->assertEquals($obj["id"], $obj2["id"]);


        $this->fixture()->delete($obj["id"]);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret = json_decode(trim($response->body()));
        $this->assertTrue($ret);
    }
}
