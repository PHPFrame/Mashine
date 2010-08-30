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

        $api_controller = new UsersApiController($app, true);
        $api_controller->format("php");
        $api_controller->returnInternalPHP(true);

        $this->fixture($api_controller);
    }

    public function test_getPHP()
    {
        $ret = $this->fixture()->get();
        $this->assertType("PHPFrame_PersistentObjectCollection", $ret);
        foreach ($ret as $obj) {
            $this->assertType("User", $obj);
        }
    }

    public function test_getJSON()
    {
        $api_controller = new UsersApiController($this->app());
        $api_controller->format("json");
        $api_controller->returnInternalPHP(false);

        $api_controller->get();

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

    public function test_getOnePHP()
    {
        $user = $this->fixture()->get(1);

        $this->assertType("User", $user);
        $this->assertEquals(1, $user->id());
    }

    public function test_getOneJSON()
    {
        $api_controller = new UsersApiController($this->app());
        $api_controller->format("json");
        $api_controller->returnInternalPHP(false);

        $api_controller->get(1);

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

    public function test_getOneNotFoundErrorPHP()
    {
        try {
            $user = $this->fixture()->get(9999);
            $this->fail("Exception should have been throw!");

        } catch (Exception $e) {
            $this->assertEquals("User not found.", $e->getMessage());
        }
    }

    public function test_postAdminNewUserAndDeletePHP()
    {
        $user = $this->fixture()->post(null, null, "tests@phpframe.org", "Passw0rd");

        $this->assertType("User", $user);
        $this->assertEquals(3, $user->groupId());

        $ret = $this->fixture()->delete($user->id());
        $this->assertTrue($ret);
    }

    public function test_postAdminNewUserAndDeleteJSON()
    {
        $api_controller = new UsersApiController($this->app());
        $api_controller->format("json");
        $api_controller->returnInternalPHP(false);

        $api_controller->post(null, null, "tests@phpframe.org", "Passw0rd");
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

        $api_controller->delete($user["id"]);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret = json_decode(trim($response->body()));
        $this->assertTrue($ret);
    }

    public function test_postAdminNewStaffAndDeletePHP()
    {
        $user = $this->fixture()->post(null, 2, "tests@phpframe.org");

        $this->assertType("User", $user);
        $this->assertEquals(2, $user->groupId());

        $params = $user->params();
        $this->assertType("array", $params);
        $this->assertArrayHasKey("secondary_groups", $params);
        $this->assertEquals(3, $params["secondary_groups"]);

        $ret = $this->fixture()->delete($user->id());
        $this->assertTrue($ret);
    }

    public function test_postAdminNewStaffAndDeleteJSON()
    {
        $api_controller = new UsersApiController($this->app());
        $api_controller->format("json");
        $api_controller->returnInternalPHP(false);

        $api_controller->post(null, 2, "tests@phpframe.org");
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

        $api_controller->delete($user["id"]);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret = json_decode(trim($response->body()));
        $this->assertTrue($ret);
    }

    public function test_postAdminNewCustomerAndDeletePHP()
    {
        $user = $this->fixture()->post(null, 4, "tests@phpframe.org");

        $this->assertType("User", $user);
        $this->assertEquals(4, $user->groupId());

        $params = $user->params();
        $this->assertType("array", $params);
        $this->assertArrayHasKey("secondary_groups", $params);
        $this->assertEquals(3, $params["secondary_groups"]);

        $ret = $this->fixture()->delete($user->id());
        $this->assertTrue($ret);
    }

    public function test_postAdminNewCustomerAndDeleteJSON()
    {
        $api_controller = new UsersApiController($this->app());
        $api_controller->format("json");
        $api_controller->returnInternalPHP(false);

        $api_controller->post(null, 4, "tests@phpframe.org");
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

        $api_controller->delete($user["id"]);
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

    public function test_searchPHP()
    {
        $ret = $this->fixture()->search("root");

        $this->assertType("array", $ret);

        $row = $ret[0];
        $this->assertType("array", $row);
        $this->assertArrayHasKey("label", $row);
        $this->assertArrayHasKey("value", $row);
    }

    public function test_searchJSON()
    {
        $api_controller = new UsersApiController($this->app());
        $api_controller->format("json");
        $api_controller->returnInternalPHP(false);

        $api_controller->search("root");
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
