<?php
class OAuthController extends PHPFrame_ActionController
{
    private $_tokens_mapper, $_clients_mapper, $_acl_mapper;

    /**
     * Constructor.
     *
     * @param PHPFrame_Application $app Reference to application object.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app, "authorise");
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

        $token = $this->_getTokensMapper()->findByKey($oauth_token);
        $oauth_client = $this->_getClientsMapper()->findByKey($token->consumerKey());

        if (!$oauth_client instanceof OAuthClient) {
            $this->raiseError("Invalid OAuth consumer key!");
            return;
        }

        if (!$this->session()->isAuth()) {
            $redirect_url = $base_url."user/login";
            $ret_url      = $base_url."oauth/authorise?oauth_token=";
            $ret_url     .= $token->key()."&oauth_token_secret=".$token->secret();

            $redirect_url .= "?ret_url=".urlencode($ret_url);

            $this->setRedirect($redirect_url);
            return;
        }

        $app_name = $this->config()->get("app_name");
        $str = '<p>'.$oauth_client->name().' is requesting access to your '.$app_name.' account.</p>
<form name="oauth_authorise_form" action="'.$base_url.'oauth/saveacl" method="post">
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
     * Get OAuth clients mapper. These are the client applications with OAuth
     * access. Each has a consumer key and a consumer secret.
     *
     * @return OAuthClientsMapper
     * @since  1.0
     */
    private function _getClientsMapper()
    {
        if (is_null($this->_clients_mapper)) {
            $this->_clients_mapper = new OAuthClientsMapper($this->db());
        }

        return $this->_clients_mapper;
    }
}
