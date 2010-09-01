<?php
/**
 * src/controllers/api.php
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
 * REST API controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class ApiController extends PHPFrame_RESTfulController
{
    private $_api_path, $_api_reflector;
    private $_oauth_server, $_tokens_mapper, $_clients_mapper;
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
        $this->_mapRequest();
        $this->_doAuth();
    }

    /**
     * Call API method.
     *
     * @param string $api_object The API object.
     * @param string $api_method The method to call.
     * @param array  $args       [Optional]
     *
     * @return string
     * @since  1.0
     */
    public function call($api_object, $api_method, array $args=null)
    {
        if ($this->_oauth_error) { return; }

        if (!$this->_api_reflector->hasMethod($api_method)) {
            throw new Exception("Bad Request.", 400);
        }

        $reflection_method = $this->_api_reflector->getMethod($api_method);

        if ($this->_oauth_server instanceof OAuthServer) {
            $instance = $this->_api_reflector->newInstance($this->app(), $this->_oauth_server);
        } else {
            $instance = $this->_api_reflector->newInstance($this->app());
        }

        foreach ($reflection_method->getParameters() as $param) {
            if (!$param->isOptional()
                && !array_key_exists($param->getName(), $args)
            ) {
                $msg  = "Bad Request. API method ".$api_object."/".$api_method." ";
                $msg .= "requires argument '".$param->getName()."' to be ";
                $msg .= "passed in request. See ".$this->config()->get("base_url");
                $msg .= "api/".$api_object."/usage for more details.";
                throw new Exception($msg, 400);
            }
        }

        if ($api_method === "usage") {
            $base_url = $this->config()->get("base_url");
            $this->config()->set("base_url", $base_url."api/");
        }

        $reflection_method->invokeArgs($instance, $args);

        if ($api_method === "usage") {
            $this->config()->set("base_url", $base_url);
        }
    }

    /**
     * Display API usage info.
     *
     * @return string
     * @since  1.0
     */
    public function usage()
    {
        $array["api"] = $this->config()->get("app_name")." RESTful API";
        $array["version"] = $this->config()->get("version");
        $array["url"] = $this->config()->get("base_url")."api/";
        $array["timestamp"] = date("D M d H:i:s O Y");

        $array["controllers"] = array();
        foreach ($this->_getApiControllers($this->app()) as $controller_name) {
            $array["controllers"][] = $controller_name;
        }

        $this->response()->body($array);
    }

    private function _mapRequest()
    {
        $install_dir     = $this->app()->getInstallDir();
        $this->_api_path = $install_dir.DS."src".DS."controllers".DS."api";
        $request         = $this->app()->request();
        $api_object      = $request->action();

        if (!in_array($api_object, $this->_getApiControllers($this->app()))) {
            $request->action("usage");
        } else {
            $request_uri = $request->requestURI();
            $pattern = "/api\/$api_object\/([a-zA-Z0-9_]+)(?:\/([0-9]+))?/";
            if (preg_match($pattern, $request_uri, $matches)) {
                $api_method = $matches[1];
                if (count($matches) == 3){
                    $resource_id = $matches[2];
                }
            } else {
                $api_method = "get";
            }

            $this->_api_reflector = $this->_getApiControllerReflector($api_object);

            $args = array();
            if ($this->_api_reflector->hasMethod($api_method)) {
                $reflection_method = $this->_api_reflector->getMethod($api_method);
                foreach ($reflection_method->getParameters() as $param) {
                    if (array_key_exists($param->getName(), $request->params())) {
                        $args[$param->getName()] = $request->param($param->getName());
                    } else {
                        if ($param->getName() == 'id' && isset($resource_id)){
                            $args["id"] = $resource_id;
                        }
                        elseif ($param->isDefaultValueAvailable()) {
                            $args[$param->getName()] = $param->getDefaultValue();
                        }
                    }
                }
            }

            $request->action("call");
            $request->param("args", $args);
            $request->param("api_object", $api_object);
            $request->param("api_method", $api_method);
        }
    }

    private function _doAuth()
    {
        $api_obj    = $this->request()->param("api_object");
        $api_method = $this->request()->param("api_method");

        if ((!$api_obj && !$api_method) || $api_method == "usage") {
            return;
        }

        $api_method = $api_obj."/".$api_method;

        $api_methods_mapper = new OAuthMethodsMapper($this->app()->db());
        $api_method_info = $api_methods_mapper->findByMethod($api_method);

        if (!is_array($api_method_info) || empty($api_method_info)) {
            // No auth info has been set for method so to play it safe we do
            // not allow access.
            throw new RuntimeException("Permission denied!", 401);
        }

        if ($this->_isOAuthCall() && $api_method_info["oauth"] > 0) {
            // If we're doing OAuth we make sure we ignore session that could
            // have been passed in cookie and already processed
            $this->session()->setUser(new User());

            try {
                $this->_oauth_server = new OAuthServer(
                    $this->_getClientsMapper(),
                    $this->_getTokensMapper(),
                    $this->config()->get("base_url")."api/oauth/request_token"
                );

                $this->_oauth_server->checkOAuthRequest();

            } catch (OAuthException $e) {
                $this->response()->body(OAuthProvider::reportProblem($e));
                $this->_oauth_error = true;
            }

        } elseif ($this->_isFrontendCall() && $api_method_info["cookie"] > 0) {
            if ($api_method_info["cookie"] == 2) {
                try {
                    $this->checkToken();
                } catch (Exception $e) {
                    throw new RuntimeException("Permission denied!", 401);
                }
            }

        } elseif ($api_method_info["cookie"] == 0) {
            throw new RuntimeException("Permission denied!", 401);
        }
    }

    /**
     * Check whether current request originated in the site's web frontend.
     *
     * @return bool
     * @since  1.0
     */
    private function _isFrontendCall()
    {
        $session_name = $this->session()->getName();
        if (!array_key_exists($session_name, $_COOKIE)) {
            return false;
        }

        if ($_COOKIE[$session_name] !== $this->session()->getId()) {
            return false;
        }

        return true;
    }

    /**
     * Check whether current request is an API call using OAuth.
     *
     * @return bool
     * @since  1.0
     */
    private function _isOAuthCall()
    {
        $auth_header = $this->request()->header("Authorization");

        if ($auth_header && (strpos($auth_header, "OAuth") !== false)) {
            return true;
        }

        $oauth_signature = $this->request()->param("oauth_signature");
        $oauth_signature_method = $this->request()->param("oauth_signature_method");
        if ($oauth_signature && $oauth_signature_method) {
            return true;
        }

        return false;
    }

    /**
     * Get names of installed controllers. This method reads the api directory
     * in src/controllers/api for installed API components.
     *
     * To get a specific instance of an API controller use the
     * {@link ApiController::_getApiControllerInstance()} method.
     *
     * @param PHPFrame_Application $app Instance of the application.
     *
     * @return array An array containing the names of the controllers.
     * @since  1.0
     */
    private function _getApiControllers(PHPFrame_Application $app)
    {
        $dir_it   = new DirectoryIterator($this->_api_path);
        $array    = array();

        foreach ($dir_it as $file) {
            $fname = $file->getFilename();
            if ($file->isFile() && preg_match("/\.php$/", $fname)) {
                $array[] = substr($fname, 0, strrpos($fname, "."));
            }
        }

        return $array;
    }

    /**
     * Get ReflectionClass object of requested API controller.
     *
     * @param string $controller_name
     *
     * @return ReflectionClass
     * @since  1.0
     */
    private function _getApiControllerReflector($controller_name)
    {
        $class_name = ucfirst($controller_name)."ApiController";

        if (!class_exists($class_name)) {
            include $this->_api_path.DS.$controller_name.".php";
        }

        return new ReflectionClass($class_name);
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
}
