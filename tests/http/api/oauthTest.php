<?php
$pattern     = "/(.*)(tests)\/http(\/)(.*)Test(\.php)/";
$replacement = '$1$2$3TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);

class OAuthApiControllerHttpTest extends MVCTestCase
{
    private $_oauth_clients_mapper, $_oauth_client, $_oauth;

    public function setUp()
    {
        $this->_oauth_clients_mapper = new OAuthClientsMapper($this->app()->db());
        $this->_oauth_client = new OAuthClient();
        $this->_oauth_client->name("Mashine Tester");
        $this->_oauth_client->version("1.0");
        $this->_oauth_client->vendor("Mashine Project");
        $this->_oauth_clients_mapper->insert($this->_oauth_client);

        $this->_oauth = new OAuth(
            $this->_oauth_client->key(),
            $this->_oauth_client->secret(),
            OAUTH_SIG_METHOD_HMACSHA1,
            OAUTH_AUTH_TYPE_URI
        );

        $this->_oauth->enableDebug();
    }

    public function tearDown()
    {
        $this->_oauth_clients_mapper->delete($this->_oauth_client);
    }

    public function test_request_token()
    {
        $array = $this->_oauth->getRequestToken($this->_getEndPointURL()."request_token");

        $this->assertType("array", $array);
        $this->assertArrayHasKey("login_url", $array);
        $this->assertArrayHasKey("oauth_token", $array);
        $this->assertArrayHasKey("oauth_token_secret", $array);

        // // var_dump($array);
        //
        // $http_request = new PHPFrame_HTTPRequest(
        //     $array["login_url"],
        //     HTTP_Request2::METHOD_POST
        // );
        //
        // $http_request->addPostParameter("oauth_token", $array["oauth_token"]);
        // $http_request->addPostParameter("oauth_token_secret", $array["oauth_token_secret"]);
        // $http_response = $http_request->send();
        // var_dump($http_response->getBody());
        // exit;
    }

    public function test_access_token()
    {
        // $array = $this->_oauth->getRequestToken($this->_getEndPointURL()."request_token");
        //$this->_oauth->setToken($array["oauth_token"], $array["oauth_token_secret"]);
        //$array = $this->_oauth->getAccessToken($this->_getEndPointURL()."access_token");
        //
        // print_r($array);
        // exit;
        //
        // $this->assertType("array", $array);
        // $this->assertArrayHasKey("login_url", $array);
        // $this->assertArrayHasKey("oauth_token", $array);
        // $this->assertArrayHasKey("oauth_token_secret", $array);
        //
        // $http_request = new PHPFrame_HTTPRequest(
        //     $array["login_url"],
        //     HTTP_Request2::METHOD_POST
        // );
        //
        // $http_request->addPostParameter("oauth_token", $array["oauth_token"]);
        // $http_request->addPostParameter("oauth_token_secret", $array["oauth_token_secret"]);
        // $http_response = $http_request->send();
        // var_dump($http_response->getBody());
        // exit;
    }

    private function _getEndPointURL()
    {
        return $this->app()->config()->get("base_url")."api/oauth/";
    }
}
