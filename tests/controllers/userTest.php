<?php
$pattern     = "/(.*)(tests)([\/|\\\])(.*)Test(\.php)/";
$replacement = '$1$2$3/TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);

class UserControllerTest extends MVCTestCase
{
    public function setUp()
    {
        $app = $this->app(true);

        // Automatically log in as system user
        $user = new PHPFrame_User();
        $user->id(1);
        $user->groupId(1);
        $user->email('cli@localhost.local');

        // Store user in session
        $app->session()->setUser($user);

        $response   = new PHPFrame_Response();
        $views_path = $app->getInstallDir().DS."src".DS."views";
        $document   = new PHPFrame_HTMLDocument();
        $renderer   = new PHPFrame_HTMLRenderer($views_path);

        $response->renderer($renderer);
        $response->document($document);
        $app->response($response);
    }

    public function tearDown()
    {
        //...
    }

    public function test_index()
    {
        $request = new PHPFrame_Request();
        $request->requestURI("/dashboard");
        $request->scriptName("/index.php");
        //$request->ajax(true);

        ob_start();
        $this->app()->dispatch($request);
        $output = ob_get_contents();
        ob_end_clean();
        // var_dump($output);
        // $this->assertRegExp("/<h1>Dashboard<\/h1>/i", $output);
    }

//     public function test_indexNoAuth()
//     {
//         $this->app()->session()->setUser(new User());
//
//         $request = new PHPFrame_Request();
//         $request->requestURI("/dashboard");
//         $request->scriptName("/index.php");
//         $request->ajax(true);
//
//         ob_start();
//         $this->app()->dispatch($request);
//         $output = ob_get_contents();
//         ob_end_clean();
//
//         $this->assertEquals("", $output);
//
//         $response = $this->app()->response();
//         $this->assertEquals(303, $response->statusCode());
//         $this->assertEquals(
//             $this->app()->config()->get("base_url")."user",
//             $response->header("Location")
//         );
//     }
//
//     public function test_loginForm()
//     {
//         $this->app()->session()->setUser(new User());
//
//         $request = new PHPFrame_Request();
//         $request->requestURI("/user/login");
//         $request->scriptName("/index.php");
//         $request->ajax(true);
//
//         ob_start();
//         $this->app()->dispatch($request);
//         $output = ob_get_contents();
//         ob_end_clean();
//
//         $this->assertRegExp("/<h1>Log in<\/h1>/i", $output);
//     }
//
//     public function test_loginAlreadyAuth()
//     {
//         $request = new PHPFrame_Request();
//         $request->requestURI("/user/login");
//         $request->scriptName("/index.php");
//         $request->ajax(true);
//
//         ob_start();
//         $this->app()->dispatch($request);
//         $output = ob_get_contents();
//         ob_end_clean();
//
//         $this->assertEquals("", $output);
//
//         $response = $this->app()->response();
//         $this->assertEquals(303, $response->statusCode());
//         $this->assertEquals(
//             $this->app()->config()->get("base_url")."user",
//             $response->header("Location")
//         );
//     }
//
//     public function test_loginProcess()
//     {
//         $this->app()->session()->setUser(new User());
//
//         $this->assertFalse( $this->app()->session()->isAuth());
//
//         $request = new PHPFrame_Request();
//         $request->requestURI("/user/login");
//         $request->scriptName("/index.php");
//         //$request->ajax(true);
//         $request->param("email", "root@e-noise.com");
//         $request->param("password", "Passw0rd");
//
//         ob_start();
//         $this->app()->dispatch($request);
//         $output = ob_get_contents();
//         ob_end_clean();
//
//         $this->assertEquals("", $output);
//         $this->assertTrue( $this->app()->session()->isAuth());
//
//         $response = $this->app()->response();
//         $this->assertEquals(303, $response->statusCode());
//         $this->assertEquals(
//             $this->app()->config()->get("base_url")."user",
//             $response->header("Location")
//         );
//     }
//
//     public function test_loginProcessInvalidEmail()
//     {
//         $this->app()->session()->setUser(new User());
//
//         $this->assertFalse( $this->app()->session()->isAuth());
//
//         $request = new PHPFrame_Request();
//         $request->requestURI("/user/login");
//         $request->scriptName("/index.php");
//         $request->ajax(true);
//         $request->param("email", "root");
//         $request->param("password", "Passw0rd");
//
//         ob_start();
//         $this->app()->dispatch($request);
//         $output = ob_get_contents();
//         ob_end_clean();
//
// var_dump($output); exit;
//         // $this->assertEquals("", $output);
//         //         $this->assertTrue( $this->app()->session()->isAuth());
//         //
//         //         $response = $this->app()->response();
//         //         $this->assertEquals(303, $response->statusCode());
//         //         $this->assertEquals(
//         //             $this->app()->config()->get("base_url")."user",
//         //             $response->header("Location")
//         //         );
//     }

    public function test_loginProcessEmailNotRegistered()
    {

    }

    public function test_loginProcessInvalidPassword()
    {

    }

    public function test_reset()
    {

    }

    public function test_logout()
    {

    }

    public function test_signup()
    {

    }

    public function test_form()
    {

    }

    public function test_save()
    {

    }

    public function test_confirm()
    {

    }

    public function test_manage()
    {

    }

    public function test_delete()
    {

    }

    public function test_contactform()
    {

    }

    public function test_savecontact()
    {

    }

    public function test_deletecontact()
    {

    }
}
