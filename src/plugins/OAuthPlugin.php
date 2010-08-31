<?php
/**
 * src/plugins/OAuthPlugin.php
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
 * OAuth Plugin class. Used for configuring and enforcing api actions that
 * require OAuth authentication.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class OAuthPlugin extends AbstractPlugin
{
    private $_options;
    private $_api_path;

    public function __construct(PHPFrame_Application $app)
    {
        //check app registry for api controller actions and store them if not defined
        $this->_api_path = $app->getInstallDir().DS."src".DS."controllers".DS."api";

        $registry = $app->registry();

        if (!$registry->get('api_controllers', false)) {
            $api_controllers = $this->_getApiControllers();
            $registry->set('api_controllers', $api_controllers);
        }

        parent::__construct($app);
    }

    public function getOptionsPrefix()
    {
        return "oauthplugin_";
    }

    public function routeStartup()
    {
        $slug = $this->app()->request()->param('slug');
        $slug_exploded = explode('/', $slug);

        if ($slug_exploded[0] == 'api' && isset($slug_exploded[1])) {
            //for api controllers check if action is specified in slug,
            //if not then use http_method as action if there is one defined
            $controller = strtolower($slug_exploded[1]);
            $action = strtolower($slug_exploded[count($slug_exploded)-1]);

            $registry = $this->app()->registry();
            $controller_actions = $registry->get('api_controllers');

            $http_method = strtolower($this->app()->request()->method());
            $method = $slug;
            if (count($slug_exploded) == 2){
                $method .= '/'.$http_method;
            } else if (count($slug_exploded) > 3) {
                $method = $slug_exploded[0].'/'.$slug_exploded[1];
                $method .= '/'.$slug_exploded[2];
            }

            $api_model = new OAuthMethodsMapper($this->app()->db());
            $oauth_access = $api_model->findByMethod($method);

            if (false){
                $clients_mapper = new OAuthClientsMapper($this->app()->db());
                $tokens_mapper = new OAuthTokensMapper($this->app()->db());
                $base_url = $this->app()->config()->get('base_url');
                $oauth_server = new OAuthServer(
                    $clients_mapper,
                    $tokens_mapper,
                    $base_url."api/oauth/request_token"
                );
                $this->app()->logger()->write("Got in routeStartup");
                $valid = 'unset';
                try {
                    $valid = $oauth_server->checkOAuthRequest();
                } catch (OAuthException $e) {
                    $this->app()->logger()->write(OAuthProvider::reportProblem($e));
                }
                var_dump($valid);
            }
        }
    }

    public function displayOptionsForm()
    {
        // Get api method info from db
        $mapper = new OAuthMethodsMapper($this->app()->db());
        $rows = $mapper->find();

        // Rebuild method info data into a new array using method names as keys
        // this will allow us to map both arrays when building the table
        $method_info = array();
        foreach ($rows as $row) {
            $method_info[$row["method"]] = $row;
        }

        $api_controllers = $this->app()->registry()->get("api_controllers");
        $api_methods = array();
        foreach ($api_controllers as $controller=>$methods) {
            foreach ($methods as $method) {
                $method_name = $controller."/".$method;
                $api_methods[] = array(
                    "method" => $method_name,
                    "oauth"  => @$method_info[$method_name]["oauth"],
                    "cookie" => @$method_info[$method_name]["cookie"]
                );
            }
        }

        $base_url = $this->app()->config()->get("base_url");
        ob_start();
        ?>

        <table>
        <tr>
            <th>method</th>
            <th>oauth</th>
            <th>cookie</th>
        </tr>
        <?php foreach ($api_methods as $method) : ?>
        <tr>
            <td><?php echo $method["method"]; ?></td>
            <td>
                <a
                    class="api-method-link api-method-link-oauth"
                    href="<?php echo $method["method"]; ?>"
                    title="<?php echo $method["oauth"]; ?>"
                >
                <?php echo ($method["oauth"]) ? $method["oauth"]." legged" : "No"; ?>
                </a>
            </td>
            <td>
                <a
                    class="api-method-link api-method-link-cookie"
                    href="<?php echo $method["method"]; ?>"
                    title="<?php echo $method["cookie"]; ?>"
                >
                <?php echo ($method["cookie"]) ? "Yes" : "No"; ?>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        </table>

        <script>
        jQuery('.api-method-link').click(function(e) {
            e.preventDefault();

            if (jQuery('#api-method-form').length > 0) {
                jQuery('#api-method-form').prev('a').css('display', 'inline');
                jQuery('#api-method-form').remove();
            }

            jQuery(this).css('display', 'none');


            var selectName;
            if (jQuery(this).hasClass('api-method-link-oauth')) {
                selectName = 'oauth';
            } else {
                selectName = 'cookie';
            }

            var apiOauthSelect = '<form id="api-method-form">';
            apiOauthSelect += '<select name="' + selectName + '" id="';
            apiOauthSelect += selectName + '" onchange="saveOAuthMethod(\'';
            apiOauthSelect += jQuery(this).attr('href');
            apiOauthSelect += '\', this);">';
            apiOauthSelect += '<option value="0">No</option>';
            if (selectName === 'oauth') {
                apiOauthSelect += '<option value="2">2 legged</option>';
                apiOauthSelect += '<option value="3">3 legged</option>';
            } else {
                apiOauthSelect += '<option value="1">Yes</option>';
            }
            apiOauthSelect += '</select>';
            apiOauthSelect += '</form>';

            jQuery(this).after(apiOauthSelect);

            jQuery('#' + selectName).val(jQuery(this).attr('title'));
        });

        var saveOAuthMethod = function(method, select)
        {
            var form = jQuery('#api-method-form');
            var selectedValue = select.options[select.selectedIndex].value;
            var data = {
                method: method,
                suppress_response_codes: 1,
                format: 'json'
            };

            if (select.name === 'oauth') {
                data.oauth = selectedValue;
            } else if (select.name === 'cookie') {
                data.cookie = selectedValue;
            }

            jQuery.ajax({
                url: '<?php echo $base_url; ?>api/oauth/save_method_auth',
                data: data,
                success: function (response) {
                    if (+response !== 1) {
                        alert('Something went wrong when saving API method info');
                    }

                    var anchorText = '';
                    if (selectedValue == 0) {
                        anchorText = 'No';
                    } else if (selectedValue == 1) {
                        anchorText = 'Yes';
                    } else if (selectedValue == 2) {
                        anchorText = '2 legged';
                    } else if (selectedValue == 3) {
                        anchorText = '3 legged';
                    }

                    form.prev('a')
                        .html(anchorText)
                        .attr('title', selectedValue)
                        .css('display', 'inline');

                    form.remove();
                }
            });
        }
        </script>

        <?php
        $str = ob_get_contents();
        ob_end_clean();

        return $str;
    }

    /**
     * Install plugin.
     *
     * @return void
     * @since  1.0
     */
    public function install()
    {
        $this->options[$this->getOptionsPrefix()."version"] = "1.0";
    }

    /**
     * Get an associative array where keys are the names of api controllers
     * and the values is an array containing the actions of that controller.
     * This method reads the api directory
     * in src/controllers/api for installed API components.
     *
     * @return array An associative array where each key is the name of an
     * API controller and the values are the actions of that api controller
     * @since  1.0
     */
    private function _getApiControllers()
    {
        $dir_it   = new DirectoryIterator($this->_api_path);
        $controllers_array = array();

        foreach ($dir_it as $file) {
            $fname = $file->getFilename();
            if ($file->isFile() && preg_match("/\.php$/", $fname)) {
                $controller_name = substr($fname, 0, strrpos($fname, "."));
                if (!class_exists($controller_name)) {
                    include $this->_api_path.DS.$controller_name.".php";
                }
                $class_name = ucfirst($controller_name)."ApiController";
                $controller_class = new ReflectionClass($class_name);
                $methods = $controller_class->getMethods();
                $actions = array();
                if (count($methods) > 0) {
                    foreach ($methods as $method) {
                        $declaring_class = $method->getDeclaringClass()->getName();
                        if ($method->getName() != "__construct"
                            && $method->isPublic()
                            && $declaring_class == $class_name
                        ) {
                            $actions[] = $method->name;
                        }
                    }
                }
                $controllers_array[$controller_name] = $actions;
            }
        }
        return $controllers_array;
    }
}
