<?php
/**
 * src/controllers/domain.php
 *
 * PHP version 5
 *
 * @category  PHPFrame_Applications
 * @package   Mashine
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/lupomontero/Mashine
 */

/**
 * OAuth API Controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class OauthApiController extends PHPFrame_RESTfulController
{
    private $_oauth_server, $_tokens_mapper, $_clients_mapper, $_acl_mapper;
    private $_oauth_error = false;

    /**
     * Constructor.
     *
     * @param PHPFrame_Application $app
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app);

        if (!$this->request()->param("jsonp_callback")) {
            $this->response()->document(new PHPFrame_PlainDocument);
            $this->response()->renderer(new PHPFrame_PlainRenderer);
        }

        $this->response()->header("Cache-Control", "private");

        try {
            $this->_oauth_server = new OAuthServer(
                $this->_getClientsMapper(),
                $this->_getTokensMapper(),
                $this->config()->get("base_url")."api/oauth/request_token"
            );

            $api_method = $this->request()->param("api_method");
            if (!in_array($api_method, array("authorise", "saveacl"))) {
                $this->_oauth_server->checkOAuthRequest();
            }

        } catch (OAuthException $e) {
            $this->response()->body(OAuthProvider::reportProblem($e));
            $this->_oauth_error = true;
        }
    }

    /**
     * Get request token.
     *
     * @return string|null
     * @since  1.0
     */
    public function request_token()
    {
        if ($this->_oauth_error) { return; }

        $consumer_key = $this->_oauth_server->getConsumerKey();
        $callback = $this->_oauth_server->getCallback();

        try {
            $token = new OAuthToken();
            $token->type("request");
            $token->consumerKey($consumer_key);
            $token->callback($callback);
            $this->_getTokensMapper()->insert($token);
        } catch (Exception $e) {
            $this->response()->body(OAuthProvider::reportProblem($e));
            return;
        }

        $str  = "login_url=".$this->config()->get("base_url");
        $str .= "api/oauth/authorise&oauth_token=".$token->key();
        $str .= "&oauth_token_secret=".$token->secret();

        if ($callback) {
            $str .= "&oauth_callback_accepted=1";
        }

        $this->response()->body($str);
    }

    /**
     * Get access token.
     *
     * @param string $oauth_token    The OAuth request token.
     * @param string $oauth_verifier The OAuth token verifier.
     *
     * @return string
     * @since  1.0
     */
    public function access_token($oauth_token, $oauth_verifier)
    {
        if ($this->_oauth_error) { return; }

        $consumer_key = $this->_oauth_server->getConsumerKey();
        $request_token = $this->_getTokensMapper()->findByKey($oauth_token);

        if (!$request_token instanceof OAuthToken) {
            $this->response()->body("oauth_problem=token_rejected&code=400");
            return;
        }

        $acl = $this->_getACLMapper()->findByToken($request_token->key());
        if (!$acl instanceof OAuthACL) {
            $this->response()->body("oauth_problem=permission_denied&code=400");
            return;
        }

        // Mark request token as used
        $request_token->status("used");
        $this->_getTokensMapper()->insert($request_token);

        $access_token = new OAuthToken();
        $access_token->type("access");
        $access_token->consumerKey($consumer_key);
        $this->_getTokensMapper()->insert($access_token);

        $acl->token($access_token->key());
        $this->_getACLMapper()->insert($acl);

        $str  = "oauth_token=".$access_token->key();
        $str .= "&oauth_token_secret=".$access_token->secret();

        $this->response()->body($str);
    }

    /**
     * Show authorisation page.
     *
     * @param string $oauth_token The OAuth request token.
     *
     * @return void
     * @since  1.0
     */
    public function authorise($oauth_token)
    {
        $base_url = $this->config()->get("base_url");
        $this->response()->document(new PHPFrame_HTMLDocument());
        $this->response()->renderer(new PHPFrame_HTMLRenderer(""));

        $token = $this->_getTokensMapper()->findByKey($oauth_token);
        $oauth_client = $this->_getClientsMapper()->findByKey($token->consumerKey());

        if (!$this->session()->isAuth()) {
            $redirect_url = $base_url."user/login";
            $ret_url      = $base_url."api/oauth/authorise?oauth_token=";
            $ret_url     .= $token->key()."&oauth_token_secret=".$token->secret();

            $redirect_url .= "?ret_url=".urlencode($ret_url);

            $this->setRedirect($redirect_url);
            return;
        }

        $app_name = $this->config()->get("app_name");
        $str = '<p>'.$oauth_client->name().' is requesting access to your '.$app_name.' account.</p>
<form name="oauth_authorise_form" action="'.$base_url.'api/oauth/saveacl" method="post">
<input type="submit" value="Deny" onclick="document.oauth_authorise_form.status.value = \'revoked\';" />
<input type="submit" value="Authorise" onclick="document.oauth_authorise_form.status.value = \'active\';" />
<input type="hidden" name="status" value="" />
<input type="hidden" name="oauth_token" value="'.$token->key().'" />
</form>';

        $this->response()->body($str);
    }

    /**
     * Save new OAuth authorisation.
     *
     * @param string $oauth_token The OAuth request token.
     * @param string $status      The token status (active or revoked).
     *
     * @return void
     * @since  1.0
     */
    public function saveacl($oauth_token, $status)
    {
        if (!$this->session()->isAuth()) {
            throw new Exception("Permission denied!", 401);
        }

        $request_token = $this->_getTokensMapper()->findByKey($oauth_token);
        $callback = $request_token->callback();
        $oauth_client = $this->_getClientsMapper()->findByKey($request_token->consumerKey());
        $str = "";

        switch ($status) {
        case "active" :
            if ($request_token->status() !== "active") {
                $this->raiseError("Invalid request token.");
                return;
            }

            $acl = new OAuthACL(array(
                "client_id" => $oauth_client->id(),
                "user_id"   => $this->user()->id(),
                "resource"  => "*",
                "token"     => $request_token->key(),
                "owner"     => $this->user()->id(),
                "group"     => $this->user()->groupId()
            ));

            $this->_getACLMapper()->insert($acl);

            $verifier = md5($request_token->secret().$request_token->consumerKey());

            $str .= "oauth_token=".$request_token->key();
            $str .= "&oauth_verifier=".$verifier;
            break;

        case "revoked" :
            $str .= "oauth_problem=permission_denied&code=403";
            break;

        default :
            throw new InvalidArgumentException("Unknown token status.");
        }

        if ($callback) {
            $this->setRedirect($callback."?".$str);
        } else {
            $this->response()->body($str);
        }
    }

    private function _getTokensMapper()
    {
        if (is_null($this->_tokens_mapper)) {
            $this->_tokens_mapper = new OAuthTokensMapper($this->db());
        }

        return $this->_tokens_mapper;
    }

    private function _getClientsMapper()
    {
        if (is_null($this->_clients_mapper)) {
            $this->_clients_mapper = new OAuthClientsMapper($this->db());
        }

        return $this->_clients_mapper;
    }

    private function _getACLMapper()
    {
        if (is_null($this->_acl_mapper)) {
            $this->_acl_mapper = new OAuthACLMapper($this->db());
        }

        return $this->_acl_mapper;
    }
}
