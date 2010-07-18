<?php
/**
 * src/plugins/SocialPlugin.php
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
 * Social Plugin class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class SocialPlugin extends AbstractPlugin
{
    /**
     * Constructor.
     *
     * @param PHPFrame_Application $app Instance of application object.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app);

        $this->hooks->addCallBack(
            "login_form",
            array($this, "getLoginForm")
        );
    }

    /**
     * Returns a string with the markup to be added to the login form. This
     * method is registered with the 'login_form' hook (see constructor).
     *
     * @return string
     * @since  1.0
     */
    public function getLoginForm()
    {
        $str = "";

        if ($this->options[$this->getOptionsPrefix()."facebook_enable"]) {
            ob_start();
            ?>

<fb:login-button length="long" v="2" onlogin="facebook_connect();"></fb:login-button>

<script type="text/javascript" charset="utf-8">
FB.init('<?php echo $this->options[$this->getOptionsPrefix()."facebook_api_key"] ?>', 'xd_receiver.htm');

FB.ensureInit(function() {

    FB.Connect.get_status().waitUntilReady(function(status) {
        switch (status) {
        case FB.ConnectState.connected:
            facebook_connect();
            break;
        case FB.ConnectState.appNotAuthorized:
            //alert('you are logged on to Facebook but haven\'t authorised the E-NOISE app');
            break;
        case FB.ConnectState.userNotLoggedIn:
            alert('you have a facebook account but you\'re not logged in');
            //FB.Connect.requireSession();
            break;
        }
    });

});

function facebook_connect()
{
    var fbuid = FB.Connect.get_loggedInUser();
    var data  = 'controller=socialplugin&action=login';
    data     += '&fbuid=' + fbuid + '&ajax=1';

    jQuery.ajax({
        type: 'POST',
        url: base_url,
        data: data,
        success: function(response) {
            alert(response);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        }
    });
}
</script>

            <?php
            $str .= ob_get_contents();
            ob_end_clean();
        }

        if ($this->options[$this->getOptionsPrefix()."twitter_enable"]) {
            if (!empty($str)) {
                $str .= "\n    <br />or<br />\n";
            }

            $str .= "    <img src=\"assets/img/Sign-in-with-Twitter-lighter.png\" ";
            $str .= "alt=\"Log in with Twitter\" />";
        }

        return $str;
    }

    /**
     * Hook into PHPFrame's 'routeStartUp' event to handle plugin's 'login'
     * action.
     *
     * @return void
     * @since  1.0
     */
    public function routeStartUp()
    {
        $request  = $this->app()->request();
        $response = $this->app()->response();
        $session  = $this->app()->session();
        $base_url = $this->app()->config()->get("base_url");

        if ($request->controllerName() == "socialplugin"
            && $request->action() == "login"
        ) {
            $facebook_id = trim($request->param("fbuid"));
            if (!preg_match("/^\d+$/", $facebook_id)) {
                $msg = "Invalid Facebook ID!";
                throw new InvalidArgumentException($msg);
            }

            $sql     = "SELECT user_id FROM #__users_social ";
            $sql    .= "WHERE facebook_id = :facebook_id";
            $params  = array(":facebook_id"=>$facebook_id);
            $user_id = $this->app()->db()->fetchColumn($sql, $params);

            if (!$user_id) {
                $response->body("No user authorised for Facebook ID: ".$facebook_id);
                $request->dispatched(true);
                return;
            }

            $mapper = new UsersMapper($this->app()->db());
            $user   = $mapper->findOne((int) $user_id);

            if (!$user instanceof PHPFrame_User) {
                $response->body("User not found!");
                $request->dispatched(true);
                return;
            }

            $session->setUser($user);
            $session->getClient()->redirect($base_url);
        }
    }

    /**
     * Hook into PHPFrame's 'postApplyTheme' event to add Facebook's
     * FeatureLoader script.
     *
     * @return void
     * @since  1.0
     */
    public function postApplyTheme()
    {
        $request = $this->app()->request();

        if ($request->ajax()) {
            return;
        }

        $content = $request->param("active_content");
        if ($content instanceof Content
            && $content->slug() == "admin/content/form"
        ) {
            return;
        }

        $document = $this->app()->response()->document();
        if ($this->options[$this->getOptionsPrefix()."facebook_enable"]
            && $document instanceof PHPFrame_HTMLDocument
        ) {
            $base_url = $this->app()->config()->get("base_url");
            if (strpos($base_url, "https://") !== false) {
                $script  = "https://ssl.connect.facebook.com/js/api_lib/v0.4/";
            } else {
                $script  = "http://static.ak.connect.facebook.com/js/api_lib/v0.4/";
            }

            $script .= "FeatureLoader.js.php";
            $document->addScript($script);
        }

        $this->_replaceShortTags();
    }

    /**
     * Display plugin options in admin panel.
     *
     * @return string
     * @since  1.0
     */
    public function displayOptionsForm()
    {
        ob_start();
        ?>

        <form action="index.php" method="post">

        <fieldset id="facebook" class="">
            <legend>Facebook</legend>

            <p>
                <label class="inline">Enable:</label>
                <input
                    type="radio"
                    name="options_<?php echo $this->getOptionsPrefix(); ?>facebook_enable"
                    value="1"
                    <?php if ($this->options[$this->getOptionsPrefix()."facebook_enable"]) : ?>
                        checked="checked"
                    <?php endif; ?>
                /> Yes /
                <input
                    type="radio"
                    name="options_<?php echo $this->getOptionsPrefix(); ?>facebook_enable"
                    value="0"
                    <?php if (!$this->options[$this->getOptionsPrefix()."facebook_enable"]) : ?>
                        checked="checked"
                    <?php endif; ?>
                /> No
            </p>

            <p>
                <label>API Key:</label>
                <input
                    type="text"
                    name="options_<?php echo $this->getOptionsPrefix(); ?>facebook_api_key"
                    value="<?php echo $this->options[$this->getOptionsPrefix()."facebook_api_key"]; ?>"
                />
            </p>
        </fieldset>

        <fieldset id="twitter" class="">
            <legend>Twitter Auth</legend>

            <p>
                <label class="inline">Enable:</label>
                <input
                    type="radio"
                    name="options_<?php echo $this->getOptionsPrefix(); ?>twitter_enable"
                    value="1"
                    <?php if ($this->options[$this->getOptionsPrefix()."twitter_enable"]) : ?>
                        checked="checked"
                    <?php endif; ?>
                /> Yes /
                <input
                    type="radio"
                    name="options_<?php echo $this->getOptionsPrefix(); ?>twitter_enable"
                    value="0"
                    <?php if (!$this->options[$this->getOptionsPrefix()."twitter_enable"]) : ?>
                        checked="checked"
                    <?php endif; ?>
                /> No
            </p>

            <p>
                <label>API Key:</label>
                <input
                    type="text"
                    name="options_<?php echo $this->getOptionsPrefix(); ?>twitter_api_key"
                    value=""
                />
            </p>
        </fieldset>

        <p>
            <input type="button" value="&larr; Back" onclick="window.history.back();" />
            <input type="submit" value="Save &rarr;" />
        </p>

        <input type="hidden" name="controller" value="plugins" />
        <input type="hidden" name="action" value="save_options" />

        </form>

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
        if (!$this->app()->db()->hasTable("#__users_social")) {
            $this->_installDB();
        }

        $this->options[$this->getOptionsPrefix()."version"] = "1.0";
    }

    /**
     * Install plugin's database tables.
     *
     * @return void
     * @since  1.0
     */
    private function _installDB()
    {
        $db  = $this->app()->db();
        $tbl = new PHPFrame_DatabaseTable($db, "#__users_social");

        $tbl->addColumn(new PHPFrame_DatabaseColumn(array(
            "name"    => "id",
            "type"    => PHPFrame_DatabaseColumn::TYPE_INT,
            "null"    => true,
            "default" => null,
            "key"     => PHPFrame_DatabaseColumn::KEY_PRIMARY,
            "extra"   => PHPFrame_DatabaseColumn::EXTRA_AUTOINCREMENT
        )));

        $tbl->addColumn(new PHPFrame_DatabaseColumn(array(
            "name"    => "user_id",
            "type"    => PHPFrame_DatabaseColumn::TYPE_INT,
            "null"    => false,
            "default" => null
        )));

        $tbl->addColumn(new PHPFrame_DatabaseColumn(array(
            "name"    => "facebook_id",
            "type"    => PHPFrame_DatabaseColumn::TYPE_INT,
            "null"    => false,
            "default" => null
        )));

        $db->createTable($tbl);
    }

    /**
     * Replace [social] short tags.
     *
     * @return void
     * @since  1.0
     */
    private function _replaceShortTags()
    {
        $request        = $this->app()->request();
        $response       = $this->app()->response();

        $body = $response->body();

        $matches = array();
        if (preg_match_all('/\[(social.*)\]/', $body, $matches)) {
            for ($i=0; $i<count($matches[1]); $i++) {
                $options = $matches[1][$i];
                $options = substr($options, strpos($options, ":")+1);
                $options = str_replace("&amp;", "&", $options);
                parse_str($options, $options);

                if (array_key_exists("count", $options)) {
                    $count = $options["count"];
                } else {
                    $count = 0;
                }

                if (array_key_exists("user", $options)) {
                    $twitter_user = $options["user"];
                }

                if (array_key_exists("type", $options)) {
                    switch ($options["type"]) {
                    case "tweets" :
                        $url  = "http://twitter.com/statuses/user_timeline/";
                        $url .= $twitter_user.".rss";
                        $feed_content = new FeedContent();
                        $feed_content->param("feed_url", $url);
                        $replacement = $this->_renderTwitterFeed(
                            $feed_content,
                            $count,
                            $twitter_user
                        );
                        break;
                    }
                }

                $pattern = '/'.preg_quote($matches[0][$i], '/').'/';
                $body = preg_replace($pattern, $replacement, $body);
            }
        }

        // Set processed response back in response
        $response->body($body, false);
    }

    private function _renderTwitterFeed(
        FeedContent $feed,
        $count=0,
        $twitter_user
    ) {
        $feeds_cache_dir = $this->app()->getTmpDir().DS."cms".DS."feeds";
        PHPFrame_Filesystem::ensureWritableDir($feeds_cache_dir);
        $feed->cacheDir($feeds_cache_dir);
        $feed->param("cache_time", 60*10);

        try {
            $items = $feed->items();
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $count = (int) $count;

        if (empty($count)) {
            $count = count($items);
        }

        $str = "<div class=\"social-feed\">\n";

        if ($count > 0 && count($items) > 0) {
            $str .= "<ul>\n";
            for ($i=0; $i<$count; $i++) {
                $title = $items[$i]["title"];
                // Remove username from beggining of title
                $title = preg_replace("/^".$twitter_user.": /i", "", $title);

                // twitterify
                $title = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\">\\2</a>", $title);
                $title = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\">\\2</a>", $title);
                $title = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\">@\\1</a>", $title);
                $title = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\">#\\1</a>", $title);

                $pub_time = strtotime($items[$i]["pub_date"]);
                $seconds_ago = (time() - $pub_time);

                if ($seconds_ago < 60) {
                    $date = $seconds_ago." seconds ago";
                } elseif ($seconds_ago < 60*60) {
                    $date = round($seconds_ago/60)." minutes ago";
                } elseif ($seconds_ago < 60*60*24) {
                    $date = round($seconds_ago/60/60)." hours ago";
                } elseif ($seconds_ago < 60*60*24*7) {
                    $date = round($seconds_ago/60/60/24)." days ago";
                } else {
                    $date = round($seconds_ago/60/60/24/7)." weeks ago";
                }

                $str .= "<li>\n";
                $str .= $title." - \n";
                $str .= "<a href=\"".$items[$i]["link"]."\">\n";
                $str .= $date."\n";
                $str .= "</a>\n";
                $str .= "</li>\n";
            }
            $str .= "</ul>\n";
        }

        $str .= "</div>";

        return $str;
    }
}
