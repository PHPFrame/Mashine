<?php
/**
 * src/controllers/oauth.php
 *
 * PHP version 5
 *
 * @category   PHPFrame_Applications
 * @package    Mashine
 * @subpackage ApiControllers
 * @author     Lupo Montero <lupo@e-noise.com>
 * @copyright  2010 E-NOISE.COM LIMITED
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://github.com/E-NOISE/Mashine
 */

/**
 * OAuth API Controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class OauthApiController extends PHPFrame_RESTfulController
{
    private $_tokens_mapper, $_acl_mapper;

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

        $this->response()->header("Cache-Control", "private");
    }

    /**
     * Get request token.
     *
     * @return string|null
     * @since  1.0
     */
    public function request_token($oauth_consumer_key, $oauth_callback)
    {
        try {
            $token = new OAuthToken();
            $token->type("request");
            $token->consumerKey($oauth_consumer_key);
            $token->callback($oauth_callback);
            $this->_getTokensMapper()->insert($token);
        } catch (Exception $e) {
            $this->response()->body(OAuthProvider::reportProblem($e));
            return;
        }

        $str  = "login_url=".$this->config()->get("base_url");
        $str .= "oauth/authorise&oauth_token=".$token->key();
        $str .= "&oauth_token_secret=".$token->secret();

        if ($oauth_callback) {
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
    public function access_token($oauth_consumer_key, $oauth_token, $oauth_verifier)
    {
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
        $access_token->consumerKey($oauth_consumer_key);
        $this->_getTokensMapper()->insert($access_token);

        $acl->token($access_token->key());
        $this->_getACLMapper()->insert($acl);

        $str  = "oauth_token=".$access_token->key();
        $str .= "&oauth_token_secret=".$access_token->secret();

        $this->response()->body($str);
    }

    /**
     * Save auth info for given API method.
     *
     * @param string $method The API method name for which to save auth info.
     * @param int    $oauth  [Optional] Whether to allow OAuth. 3 possible
     *                       values: 0 = No, 2 = 2 legged, 3 = 3 legged. Note
     *                       that value 1 is not allowed. Default is 0.
     * @param int    $cookie [Optional] Whether or not to allow cookie based
     *                       auth. Two possible values: 0 = No, 1 = Yes.
     *                       Default value is 0.
     *
     * @return int
     * @since  1.0
     */
    public function save_method_auth($method, $oauth=null, $cookie=null)
    {
        if (!$this->session()->isAuth() || $this->user()->groupId() > 2) {
            throw new Exception("Access denied!");
        }

        $mapper = new ApiMethodsMapper($this->db());
        $mapper->insert($method, $oauth, $cookie);

        $this->response()->body(1);
    }

    /**
     * Get OAuth tokens mapper.
     *
     * @return OAuthTokensMapper
     * @since  1.0
     */
    private function _getTokensMapper()
    {
        if (is_null($this->_tokens_mapper)) {
            $this->_tokens_mapper = new OAuthTokensMapper($this->db());
        }

        return $this->_tokens_mapper;
    }

    /**
     * Get OAuth ACL mapper.
     *
     * @return OAuthACLMapper
     * @since  1.0
     */
    private function _getACLMapper()
    {
        if (is_null($this->_acl_mapper)) {
            $this->_acl_mapper = new OAuthACLMapper($this->db());
        }

        return $this->_acl_mapper;
    }
}
