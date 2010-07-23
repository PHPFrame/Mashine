<?php
$pattern     = "/(.*)(tests)([\/|\\\])(.*)Test(\.php)/";
$replacement = '$1$2$3/TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);
require_once preg_replace($pattern, '$1src$3$4$5', __FILE__);

class UsersApiControllerTest extends MVCTestCase
{
    public function setUp()
    {
        $app = $this->app(true);
        // $app->session()->setUser(new User);
        $this->fixture(new UsersApiController($app));
    }

    public function test_get()
    {
        $this->fixture()->get();
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret_array = json_decode(trim($response->body()));
        $user = get_object_vars($ret_array[0]);
        $this->assertType("array", $ret_array);
        $this->assertType("array", $user);
        $this->assertArrayHasKey("status", $user);
        $this->assertArrayHasKey("notifications", $user);
        $this->assertArrayHasKey("activation", $user);
        $this->assertArrayHasKey("group_id", $user);
        $this->assertArrayHasKey("email", $user);
        $this->assertArrayHasKey("params", $user);
        $this->assertArrayHasKey("id", $user);
        $this->assertArrayHasKey("ctime", $user);
        $this->assertArrayHasKey("mtime", $user);
        $this->assertArrayHasKey("owner", $user);
        $this->assertArrayHasKey("group", $user);
        $this->assertArrayHasKey("perms", $user);
    }

    public function test_getOne()
    {
        $this->fixture()->get(1);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $user = get_object_vars(json_decode(trim($response->body())));
        $this->assertType("array", $user);
        $this->assertArrayHasKey("status", $user);
        $this->assertArrayHasKey("notifications", $user);
        $this->assertArrayHasKey("activation", $user);
        $this->assertArrayHasKey("group_id", $user);
        $this->assertArrayHasKey("email", $user);
        $this->assertArrayHasKey("params", $user);
        $this->assertArrayHasKey("id", $user);
        $this->assertArrayHasKey("ctime", $user);
        $this->assertArrayHasKey("mtime", $user);
        $this->assertArrayHasKey("owner", $user);
        $this->assertArrayHasKey("group", $user);
        $this->assertArrayHasKey("perms", $user);

        $this->assertEquals(1, $user["id"]);
    }

    public function test_postAdminNewUserAndDelete()
    {
        $request = $this->app()->request();
        $request->param("email", "tests@phpframe.org");

        $this->fixture()->post(null);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $user = get_object_vars(json_decode(trim($response->body())));
        $this->assertType("array", $user);
        $this->assertArrayHasKey("status", $user);
        $this->assertArrayHasKey("notifications", $user);
        $this->assertArrayHasKey("activation", $user);
        $this->assertArrayHasKey("group_id", $user);
        $this->assertArrayHasKey("email", $user);
        $this->assertArrayHasKey("params", $user);
        $this->assertArrayHasKey("id", $user);
        $this->assertArrayHasKey("ctime", $user);
        $this->assertArrayHasKey("mtime", $user);
        $this->assertArrayHasKey("owner", $user);
        $this->assertArrayHasKey("group", $user);
        $this->assertArrayHasKey("perms", $user);

        $this->assertEquals(3, $user["group_id"]);

        $this->fixture()->delete($user["id"]);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret = json_decode(trim($response->body()));
        $this->assertTrue($ret);
    }

    public function test_postAdminNewStaffAndDelete()
    {
        $request = $this->app()->request();
        $request->param("email", "tests@phpframe.org");

        $this->fixture()->post(null, 2);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $user = get_object_vars(json_decode(trim($response->body())));
        $this->assertType("array", $user);
        $this->assertArrayHasKey("status", $user);
        $this->assertArrayHasKey("notifications", $user);
        $this->assertArrayHasKey("activation", $user);
        $this->assertArrayHasKey("group_id", $user);
        $this->assertArrayHasKey("email", $user);
        $this->assertArrayHasKey("params", $user);
        $this->assertArrayHasKey("id", $user);
        $this->assertArrayHasKey("ctime", $user);
        $this->assertArrayHasKey("mtime", $user);
        $this->assertArrayHasKey("owner", $user);
        $this->assertArrayHasKey("group", $user);
        $this->assertArrayHasKey("perms", $user);

        $this->assertEquals(2, $user["group_id"]);
        $params = unserialize($user["params"]);
        $this->assertType("array", $params);
        $this->assertArrayHasKey("secondary_groups", $params);
        $this->assertEquals(3, $params["secondary_groups"]);

        $this->fixture()->delete($user["id"]);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret = json_decode(trim($response->body()));
        $this->assertTrue($ret);
    }

    public function test_postAdminNewCustomerAndDelete()
    {
        $request = $this->app()->request();
        $request->param("email", "tests@phpframe.org");

        $this->fixture()->post(null, 4);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $user = get_object_vars(json_decode(trim($response->body())));
        $this->assertType("array", $user);
        $this->assertArrayHasKey("status", $user);
        $this->assertArrayHasKey("notifications", $user);
        $this->assertArrayHasKey("activation", $user);
        $this->assertArrayHasKey("group_id", $user);
        $this->assertArrayHasKey("email", $user);
        $this->assertArrayHasKey("params", $user);
        $this->assertArrayHasKey("id", $user);
        $this->assertArrayHasKey("ctime", $user);
        $this->assertArrayHasKey("mtime", $user);
        $this->assertArrayHasKey("owner", $user);
        $this->assertArrayHasKey("group", $user);
        $this->assertArrayHasKey("perms", $user);

        $this->assertEquals(4, $user["group_id"]);
        $params = unserialize($user["params"]);
        $this->assertType("array", $params);
        $this->assertArrayHasKey("secondary_groups", $params);
        $this->assertEquals(3, $params["secondary_groups"]);

        $this->fixture()->delete($user["id"]);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret = json_decode(trim($response->body()));
        $this->assertTrue($ret);
    }

    public function test_postSignupNewUser()
    {

    }

    public function test_postSignupNewCustomer()
    {

    }

    public function test_postUpdateUser()
    {

    }

    public function test_postUpdateCustomer()
    {

    }

    public function test_postUpdateStaff()
    {

    }

    public function test_postUpdateRoot()
    {

    }

    public function test_search()
    {
        $this->fixture()->search("root");
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret = json_decode(trim($response->body()));
        $this->assertType("array", $ret);

        $row = get_object_vars($ret[0]);
        $this->assertType("array", $row);
        $this->assertArrayHasKey("label", $row);
        $this->assertArrayHasKey("value", $row);
    }
}
