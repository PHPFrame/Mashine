<?php
$pattern     = "/(.*)(tests)([\/|\\\])(.*)Test(\.php)/";
$replacement = '$1$2$3/TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);
require_once preg_replace($pattern, '$1src$3$4$5', __FILE__);

class ContentApiControllerTest extends MVCTestCase
{
    public function setUp()
    {
        $app = $this->app(true);
        //$app->session()->setUser(new User);
        $this->fixture(new ContentApiController($app));
    }

    public function test_get()
    {
        $this->fixture()->get(21);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret_array = json_decode(trim($response->body()));
        $obj = get_object_vars($ret_array[0]);
        $this->assertType("array", $ret_array);
        $this->assertType("array", $obj);
        $this->assertArrayHasKey("url", $obj);
        $this->assertArrayHasKey("title", $obj);
        $this->assertArrayHasKey("pub_date", $obj);
        $this->assertArrayHasKey("type", $obj);
        $this->assertArrayHasKey("author", $obj);
        $this->assertArrayHasKey("excerpt", $obj);
    }

    public function test_getOne()
    {
        $this->fixture()->get(null, 1);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $obj = get_object_vars(json_decode(trim($response->body())));
        $this->assertType("array", $obj);
        $this->assertArrayHasKey("parent_id", $obj);
        $this->assertArrayHasKey("slug", $obj);
        $this->assertArrayHasKey("title", $obj);
        $this->assertArrayHasKey("short_title", $obj);
        $this->assertArrayHasKey("pub_date", $obj);
        $this->assertArrayHasKey("status", $obj);
        $this->assertArrayHasKey("robots_index", $obj);
        $this->assertArrayHasKey("robots_follow", $obj);
        $this->assertArrayHasKey("description", $obj);
        $this->assertArrayHasKey("keywords", $obj);
        $this->assertArrayHasKey("body", $obj);
        $this->assertArrayHasKey("type", $obj);
        $this->assertArrayHasKey("params", $obj);
        $this->assertArrayHasKey("id", $obj);
        $this->assertArrayHasKey("ctime", $obj);
        $this->assertArrayHasKey("mtime", $obj);
        $this->assertArrayHasKey("owner", $obj);
        $this->assertArrayHasKey("group", $obj);
        $this->assertArrayHasKey("perms", $obj);

        $this->assertEquals(1, $obj["id"]);
    }

    public function test_post()
    {
        $request = $this->app()->request();
        $request->param("slug", "some-post");
        $request->param("title", "Some post");

        $this->fixture()->post(21, "PostContent", null);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $obj = get_object_vars(json_decode(trim($response->body())));
        $this->assertType("array", $obj);
        $this->assertArrayHasKey("parent_id", $obj);
        $this->assertArrayHasKey("slug", $obj);
        $this->assertArrayHasKey("title", $obj);
        $this->assertArrayHasKey("short_title", $obj);
        $this->assertArrayHasKey("pub_date", $obj);
        $this->assertArrayHasKey("status", $obj);
        $this->assertArrayHasKey("robots_index", $obj);
        $this->assertArrayHasKey("robots_follow", $obj);
        $this->assertArrayHasKey("description", $obj);
        $this->assertArrayHasKey("keywords", $obj);
        $this->assertArrayHasKey("body", $obj);
        $this->assertArrayHasKey("type", $obj);
        $this->assertArrayHasKey("params", $obj);
        $this->assertArrayHasKey("id", $obj);
        $this->assertArrayHasKey("ctime", $obj);
        $this->assertArrayHasKey("mtime", $obj);
        $this->assertArrayHasKey("owner", $obj);
        $this->assertArrayHasKey("group", $obj);
        $this->assertArrayHasKey("perms", $obj);

        $this->fixture()->delete($obj["id"]);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret = json_decode(trim($response->body()));
        $this->assertTrue($ret);
    }
}
