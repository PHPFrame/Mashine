<?php
/**
 * src/controllers/domain.php
 *
 * PHP version 5
 *
 * @category  none
 * @package   none
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/lupomontero/PHPFrame_CmsAppTemplate
 */

/**
 * This controller provides a JSON API used to interact with the E-NOISE domain
 * registration service. All actions return a response in JSON format.
 *
 * @category none
 * @package  none
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/PHPFrame_CmsAppTemplate
 * @since    1.0
 */
class OauthApiController extends PHPFrame_RESTfulController
{
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app);

        try {
            $this->provider = new OAuthProvider();
            $this->provider->consumerHandler(array($this,'lookupConsumer'));
            $this->provider->timestampNonceHandler(array($this,'timestampNonceChecker'));
            $this->provider->tokenHandler(array($this,'tokenHandler'));
            //$this->provider->setParam('kohana_uri', NULL);  // Ignore the kohana_uri parameter
            $this->provider->setRequestTokenPath('/v1/oauth/request_token');  // No token needed for this end point
            $this->provider->checkOAuthRequest();

        } catch (OAuthException $E) {
            echo OAuthProvider::reportProblem($E);
            $this->oauth_error = true;
        }
    }

    public function get()
    {

    }

    public function lookupConsumer($provider)
    {
        $consumer = ORM::Factory("consumer", $provider->consumer_key);
        if ($provider->consumer_key != $consumer->consumer_key) {
            return OAUTH_CONSUMER_KEY_UNKNOWN;
        } else if ($consumer->key_status != 0) {  // 0 is active, 1 is throttled, 2 is blacklisted
            return OAUTH_CONSUMER_KEY_REFUSED;
        }

        $provider->consumer_secret = $consumer->secret;
        return OAUTH_OK;
    }

    public function requestToken()
    {
        $token = Token_Model::create($this->provider->consumer_key);
        $token->save();
        // Build response with the authorization URL users should be sent to
        echo 'login_url=https://'.Kohana::config('config.site_domain').
             '/session/authorize&oauth_token='.$token->tok.
             '&oauth_token_secret='.$token->secret.
             '&oauth_callback_confirmed=true';
    }

    public function accessToken()
    {
        $access_token = Token_Model::create($this->provider->consumer_key, 1);
        $access_token->save();
        $this->token->state = 2;  // The request token is marked as 'used'
        $this->token->save();
        // Now we need to find the user who authorized this request token
        $utoken = ORM::factory('utoken', $this->token->tok);
        if(!$utoken->loaded) {
                echo "oauth error - token rejected";
                break;
        }
        // And swap out the authorized request token for the access token
        $new_utoken = Utoken_Model::create(array(
            'token'          => $access_token->tok,
            'user_id'        => $utoken->user_id,
            'application_id' => $utoken->application_id,
            'access_type'    => $utoken->access_type)
        );

        $new_utoken->save();
        $utoken->delete();

        echo "oauth_token={$access_token->tok}&oauth_token_secret={$access_token->secret}";
    }

    public function tokenHandler($provider)
    {
        $this->token = ORM::Factory("token", $provider->token);
        if (!$this->token->loaded) {
                return OAUTH_TOKEN_REJECTED;
        } else if ($this->token->type==1 && $this->token->state==1) {
                return OAUTH_TOKEN_REVOKED;
        } else if ($this->token->type==0 && $this->token->state==2) {
                return OAUTH_TOKEN_USED;
        } else if ($this->token->type==0 && $this->token->verifier != $provider->verifier) {
                return OAUTH_VERIFIER_INVALID;
        }

        $provider->token_secret = $this->token->secret;

        return OAUTH_OK;
    }

    function new_consumer_key()
    {
        $fp = fopen("/dev/urandom", "rb");
        $entropy = fread($fp, 32);
        fclose($fp);

        // in case /dev/urandom is reusing entropy from its pool, let's add a bit more entropy
        $entropy .= uniqid(mt_rand(), true);
        $hash = sha1($entropy);  // sha1 gives us a 40-byte hash

        // The first 30 bytes should be plenty for the consumer_key
        // We use the last 10 for the shared secret
        return array(substr($hash, 0, 30), substr($hash, 30, 10));
    }
}
