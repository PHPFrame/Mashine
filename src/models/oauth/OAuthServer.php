<?php
/**
 * src/models/users/OAuthServer.php
 *
 * PHP version 5
 *
 * @category  PHPFrame_Applications
 * @package   Mashine
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/E-NOISE/Mashine
 */

/**
 * OAuthServer class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class OAuthServer
{
    private $_oauth_provider, $_oauth_clients_mapper, $_oauth_tokens_mapper;
    private $_oauth_client, $_token;
    private $_cli_params;

    /**
     * Constructor.
     *
     * @param OAuthClientsMapper $oauth_clients_mapper Instance of clients mapper.
     * @param OAuthTokensMapper  $oauth_tokens_mapper  Instance of tokens mapper.
     * @param string             $request_token_path   The request token path.
     * @param array              $cli_params           [Optional] Used when
     *                                                 invoking in the shell. Arroy
     *                                                 should contain:
     *                                                 - consumer_key
     *                                                 - uri
     *                                                 - method
     *
     * @return void
     * @since  1.0
     */
    public function __construct(
        OAuthClientsMapper $oauth_clients_mapper,
        OAuthTokensMapper $oauth_tokens_mapper,
        $request_token_path,
        array $cli_params=null
    ) {
        $this->_oauth_clients_mapper = $oauth_clients_mapper;
        $this->_oauth_tokens_mapper = $oauth_tokens_mapper;

        if (!is_null($cli_params)) {
            $cli_param_keys = array("consumer_key", "uri", "method");
            foreach ($cli_param_keys as $cli_param_key) {
                if (!array_key_exists($cli_param_key, $cli_params)) {
                    $msg  = "If cli_params array is passed it must contain the";
                    $msg .= " following keys: '".implode("', '", $cli_param_keys)."'.";
                    throw new InvalidArgumentException($msg);
                }
            }

            $this->_cli_params = $cli_params;
        }

        $this->_oauth_provider = new OAuthProvider($cli_params);
        $this->_oauth_provider->consumerHandler(array($this,'checkConsumer'));
        $this->_oauth_provider->timestampNonceHandler(array($this,'checkTimestampNonce'));
        $this->_oauth_provider->tokenHandler(array($this,'checkToken'));
        $this->_oauth_provider->setRequestTokenPath($request_token_path);
    }

    public function checkOAuthRequest()
    {
        if (is_array($this->_cli_params)) {
            return $this->_oauth_provider->checkOAuthRequest(
                $this->_cli_params["uri"],
                $this->_cli_params["method"]
            );
        }

        return $this->_oauth_provider->checkOAuthRequest();
    }

    /**
     * Check whether OAuth client is known and is active.
     *
     * @param object $provider
     *
     * @return int Possible return values are  OAUTH_CONSUMER_KEY_UNKNOWN,
     *             OAUTH_CONSUMER_KEY_REFUSED or OAUTH_OK.
     * @since  1.0
     */
    public function checkConsumer($provider)
    {
        $this->_oauth_client = $this->_oauth_clients_mapper->findByKey(
            $provider->consumer_key
        );

        if (!$this->_oauth_client instanceof OAuthClient) {
            return OAUTH_CONSUMER_KEY_UNKNOWN;
        } elseif ($this->_oauth_client->status() != "active") {
            return OAUTH_CONSUMER_KEY_REFUSED;
        }

        $provider->consumer_secret = $this->_oauth_client->secret();

        return OAUTH_OK;
    }

    /**
     * Check whether the timestamp of the request is sane and falls within the
     * window of our Nonce checks. And this method will, of course, also
     * check whether the provided Nonce has been used already to prevent replay
     * attacks.
     *
     */
    public function checkTimestampNonce($provider)
    {
        if ($provider->nonce === 'bad') {
            return OAUTH_BAD_NONCE;
        } elseif ($provider->timestamp == '0') {
            return OAUTH_BAD_TIMESTAMP;
        }

        return OAUTH_OK;
    }

    public function checkToken($provider)
    {
        $this->_token = $this->_oauth_tokens_mapper->findByKey(
            $provider->token
        );

        if (!$this->_token instanceof OAuthToken) {
            return OAUTH_TOKEN_REJECTED;
        }

        $type = $this->_token->type();
        $status = $this->_token->status();
        $verifier = md5($this->_token->secret().$this->_token->consumerKey());

        if ($type == "access" &&  $status == "revoked") {
            return OAUTH_TOKEN_REVOKED;
        } elseif ($type == "request" && $status == "used") {
            return OAUTH_TOKEN_USED;
        } elseif ($type == "request" && $verifier != trim($provider->verifier)) {
            return OAUTH_VERIFIER_INVALID;
        }

        $provider->token_secret = $this->_token->secret();

        return OAUTH_OK;
    }

    public function getConsumerKey()
    {
        return $this->_oauth_provider->consumer_key;
    }

    public function getCallback()
    {
        return $this->_oauth_provider->callback;
    }
}
