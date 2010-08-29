<?php
$pattern     = "/(.*)(tests)([\/|\\\])(.*)Test(\.php)/";
$replacement = '$1$2$3/TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);

class UserControllerTest extends ControllerTestCase
{
    public function setUp()
    {
        $app = $this->app(true);
        $app->session()->setUser(new User());

        $response   = new PHPFrame_Response();
        $views_path = $app->getInstallDir().DS."src".DS."views";
        $document   = new PHPFrame_HTMLDocument();
        $renderer   = new PHPFrame_HTMLRenderer($views_path);

        $response->renderer($renderer);
        $response->document($document);
        $app->response($response);

        $this->fixture(new UserController($app));
    }

    public function tearDown()
    {
        //...
    }

    private function _loginAsCliTestUser()
    {
        $user = new User();
        $user->id(1);
        $user->groupId(1);
        $user->email('cli@localhost.local');

        // Store user in session
        $this->app()->session()->setUser($user);
    }

    public function test_index()
    {
        $this->_loginAsCliTestUser();

        $request = new PHPFrame_Request();
        $request->requestURI("/dashboard");
        $request->scriptName("/index.php");

        ob_start();
        $this->app()->dispatch($request);
        $output = ob_get_contents();
        ob_end_clean();

        $response = $this->app()->response();
        $this->assertEquals(200, $response->statusCode());
        $this->assertRegExp("/<h1>Dashboard<\/h1>/", $output);
    }

    public function test_indexNotLoggedInRedirect()
    {
        $request = new PHPFrame_Request();
        $request->requestURI("/dashboard");
        $request->scriptName("/index.php");

        ob_start();
        $this->app()->dispatch($request);
        $output = ob_get_contents();
        ob_end_clean();

        $response = $this->app()->response();
        $this->assertEquals(303, $response->statusCode());
    }

//     public function test_login()
//     {
//         $request = new PHPFrame_Request();
//         $request->requestURI("/user/login");
//         $request->scriptName("/index.php");
//
//         ob_start();
//         $this->app()->dispatch($request);
//         $output = ob_get_contents();
//         ob_end_clean();
//
//         $response = $this->app()->response();
// var_dump($output, $response->body());
// exit;
//         $this->assertEquals(200, $response->statusCode());
//         $this->assertRegExp("/<h1>Log in<\/h1>/", $response->body());
//     }


    public function test_loginAlreadyAuth()
    {
        $this->_loginAsCliTestUser();

        $this->fixture()->login();
        $response = $this->app()->response();

        $this->assertEquals(303, $response->statusCode());
        $this->assertEquals(
            $this->app()->config()->get("base_url")."dashboard",
            $response->header("Location")
        );
    }


    // public function test_loginProcess()
    // {
    //
    // }

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
