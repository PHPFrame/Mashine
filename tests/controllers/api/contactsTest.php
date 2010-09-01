<?php
$pattern     = "/(.*)(tests)([\/|\\\])(.*)Test(\.php)/";
$replacement = '$1$2$3/TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);
require_once preg_replace($pattern, '$1src$3$4$5', __FILE__);

class ContactsApiControllerTest extends MVCTestCase
{
    public function setUp()
    {
        $app = $this->app(true);
        // $app->session()->setUser(new User);

        $api_controller = new ContactsApiController($app, true);
        $api_controller->format("php");
        $api_controller->returnInternalPHP(true);

        $this->fixture($api_controller);
    }

    public function test_getPHP()
    {
        $ret = $this->fixture()->get();
        $this->assertType("PHPFrame_PersistentObjectCollection", $ret);
        foreach ($ret as $obj) {
            $this->assertType("Contact", $obj);
        }
    }

    public function test_getJSON()
    {
        $api_controller = new ContactsApiController($this->app());
        $api_controller->format("json");
        $api_controller->returnInternalPHP(false);

        $api_controller->get();

        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret_array = json_decode(trim($response->body()));
        $contact = get_object_vars($ret_array[0]);
        $this->assertType("array", $ret_array);
        $this->assertType("array", $contact);
        $this->assertArrayHasKey("org_name", $contact);
        $this->assertArrayHasKey("first_name", $contact);
        $this->assertArrayHasKey("last_name", $contact);
        $this->assertArrayHasKey("address1", $contact);
        $this->assertArrayHasKey("address2", $contact);
        $this->assertArrayHasKey("city", $contact);
        $this->assertArrayHasKey("post_code", $contact);
        $this->assertArrayHasKey("county", $contact);
        $this->assertArrayHasKey("country", $contact);
        $this->assertArrayHasKey("phone", $contact);
        $this->assertArrayHasKey("email", $contact);
        $this->assertArrayHasKey("fax", $contact);
        $this->assertArrayHasKey("preferred", $contact);
        $this->assertArrayHasKey("id", $contact);
        $this->assertArrayHasKey("ctime", $contact);
        $this->assertArrayHasKey("mtime", $contact);
        $this->assertArrayHasKey("owner", $contact);
        $this->assertArrayHasKey("group", $contact);
        $this->assertArrayHasKey("perms", $contact);
    }

    public function test_getOnePHP()
    {
        $contact = $this->fixture()->get(1);

        $this->assertType("Contact", $contact);
        $this->assertEquals(1, $contact->id());
    }

    public function test_getOneJSON()
    {
        $api_controller = new ContactsApiController($this->app());
        $api_controller->format("json");
        $api_controller->returnInternalPHP(false);

        $api_controller->get(1);

        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $contact = get_object_vars(json_decode(trim($response->body())));
        $this->assertType("array", $contact);
        $this->assertArrayHasKey("org_name", $contact);
        $this->assertArrayHasKey("first_name", $contact);
        $this->assertArrayHasKey("last_name", $contact);
        $this->assertArrayHasKey("address1", $contact);
        $this->assertArrayHasKey("address2", $contact);
        $this->assertArrayHasKey("city", $contact);
        $this->assertArrayHasKey("post_code", $contact);
        $this->assertArrayHasKey("county", $contact);
        $this->assertArrayHasKey("country", $contact);
        $this->assertArrayHasKey("phone", $contact);
        $this->assertArrayHasKey("email", $contact);
        $this->assertArrayHasKey("fax", $contact);
        $this->assertArrayHasKey("preferred", $contact);
        $this->assertArrayHasKey("id", $contact);
        $this->assertArrayHasKey("ctime", $contact);
        $this->assertArrayHasKey("mtime", $contact);
        $this->assertArrayHasKey("owner", $contact);
        $this->assertArrayHasKey("group", $contact);
        $this->assertArrayHasKey("perms", $contact);

        $this->assertEquals(1, $contact["id"]);
    }

    public function test_getOneNotFoundErrorPHP()
    {
        try {
            $contact = $this->fixture()->get(9999);
            $this->fail("Exception should have been throw!");

        } catch (Exception $e) {
            $this->assertEquals("Contact not found.", $e->getMessage());
        }
    }

    public function test_postAdminNewUserAndDeletePHP()
    {
        $contact = $this->fixture()->post(
            null,
            null,
            "Firstname",
            "Last name",
            "Address 1",
            "Adddress 2",
            "City",
            "SW2 4PH",
            "London",
            null,
            "01234567891",
            "tests@phpframe.org",
            null,
            null
        );

        $this->assertType("Contact", $contact);
        $this->assertEquals(2, $contact->group());
        $this->assertEquals("GB", $contact->country());
        $this->assertEquals("Firstname Last name", $contact->fullName());

        $ret = $this->fixture()->delete($contact->id());
        $this->assertTrue($ret);
    }

    public function test_postAdminNewUserAndDeleteJSON()
    {
        $api_controller = new ContactsApiController($this->app());
        $api_controller->format("json");
        $api_controller->returnInternalPHP(false);

        $api_controller->post(null,
            null,
            "Firstname",
            "Last name",
            "Address 1",
            "Adddress 2",
            "City",
            "SW2 4PH",
            "London",
            null,
            "01234567891",
            "tests@phpframe.org",
            null,
            null
        );
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $contact = get_object_vars(json_decode(trim($response->body())));
        $this->assertType("array", $contact);
        $this->assertArrayHasKey("org_name", $contact);
        $this->assertArrayHasKey("first_name", $contact);
        $this->assertArrayHasKey("last_name", $contact);
        $this->assertArrayHasKey("address1", $contact);
        $this->assertArrayHasKey("address2", $contact);
        $this->assertArrayHasKey("city", $contact);
        $this->assertArrayHasKey("post_code", $contact);
        $this->assertArrayHasKey("county", $contact);
        $this->assertArrayHasKey("country", $contact);
        $this->assertArrayHasKey("phone", $contact);
        $this->assertArrayHasKey("email", $contact);
        $this->assertArrayHasKey("fax", $contact);
        $this->assertArrayHasKey("preferred", $contact);
        $this->assertArrayHasKey("id", $contact);
        $this->assertArrayHasKey("ctime", $contact);
        $this->assertArrayHasKey("mtime", $contact);
        $this->assertArrayHasKey("owner", $contact);
        $this->assertArrayHasKey("group", $contact);
        $this->assertArrayHasKey("perms", $contact);

        $this->assertEquals(2, $contact["group"]);

        $api_controller->delete($contact["id"]);
        $response = $this->app()->response();

        $this->assertEquals(200, $response->statusCode());
        $this->assertEquals("application/json", $response->header("Content-Type"));

        $ret = json_decode(trim($response->body()));
        $this->assertTrue($ret);
    }
}
