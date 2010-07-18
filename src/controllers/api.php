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

    public function __construct(PHPFrame_Application $app)
    {
        $this->_api_path = $app->getInstallDir().DS."src".DS."controllers".DS."api";

        $request = $app->request();
        $api_object = $request->action();

        if (!in_array($api_object, $this->_getApiControllers($app))) {
            $request->action("usage");
        } else {
            $request_uri = $request->requestURI();
            $pattern = "/api\/$api_object\/([a-zA-Z0-9_]+)/";
            if (preg_match($pattern, $request_uri, $matches)) {
                $api_method = $matches[1];
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
                    } elseif ($param->isDefaultValueAvailable()) {
                        $args[$param->getName()] = $param->getDefaultValue();
                    }
                }
            }

            $request->action("call");
            $request->param("args", $args);
            $request->param("api_object", $api_object);
            $request->param("api_method", $api_method);
        }

        parent::__construct($app);
    }

    public function call($api_object, $api_method, array $args=null)
    {
        if (!$this->_api_reflector->hasMethod($api_method)) {
            throw new Exception("Bad Request.", 400);
        }

        $reflection_method = $this->_api_reflector->getMethod($api_method);
        $instance = $this->_api_reflector->newInstance($this->app());

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

    private function _getApiControllerReflector($controller_name)
    {
        $class_name = ucfirst($controller_name)."ApiController";

        if (!class_exists($class_name)) {
            include $this->_api_path.DS.$controller_name.".php";
        }

        return new ReflectionClass($class_name);
    }
}
