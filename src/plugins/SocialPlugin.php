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

        $this->hooks()->addCallBack(
            "login_form",
            array($this, "getLoginForm")
        );

        $this->shortCodes()->add("social", array($this, "handleSocialShortCode"));
    }

    /**
     * Handle [social] shortcode.
     *
     * @param array Associative array containing the shortcode attributes.
     *
     * @return string This method returns the string the shortcode will be
     *                replaced with.
     * @since  1.0
     */
    public function handleSocialShortCode($attr)
    {
        if (array_key_exists("count", $attr)) {
            $count = (int) $attr["count"];
        } else {
            $count = 0;
        }

        $show_title = true;
        if (array_key_exists("show_title", $attr)) {
            $show_title = (bool) $attr["show_title"];
        }

        $show_description = true;
        if (array_key_exists("show_description", $attr)) {
            $show_description = (bool) $attr["show_description"];
        }

        $twitterify = false;
        if (array_key_exists("twitterify", $attr)) {
            $twitterify = (bool) $attr["twitterify"];
        }

        $user = null;
        if (array_key_exists("user", $attr)) {
            $user = (string) $attr["user"];
        }

        $user = null;
        if (array_key_exists("user", $attr)) {
            $user = (string) $attr["user"];
        }

        $fb = false;

        $ret = "";

        if (array_key_exists("type", $attr)) {
            switch ($attr["type"]) {
            case "twitter" :
                if (!$user) {
                    $msg  = "Social plugin error. Twitter user has to be ";
                    $msg .= "specified in the 'user' attribute when 'type' is ";
                    $msg .= "set to twitter.";
                    return $msg;
                }

                $url  = "http://twitter.com/statuses/user_timeline/";
                $url .= $attr["user"].".rss";
                $show_title = false;
                $show_description = true;
                $twitterify = true;
                break;

            case "facebook" :
                if (!$user) {
                    $msg  = "Social plugin error. Facebook page id has to be ";
                    $msg .= "specified in the 'user' attribute when 'type' is ";
                    $msg .= "set to facebook.";
                    return $msg;
                }

                $url  = "http://www.facebook.com/feeds/page.php?format=atom10&id=";
                $url .= $attr["user"];
                $show_title = true;
                $show_description = true;
                $twitterify = false;
                $fb = true;
                break;

            case "feed" :
                if (!array_key_exists("url", $attr)) {
                    $msg  = "Social plugin error. No URL was specified for ";
                    $msg .= "for RSS/Atom feed.";
                    return $msg;
                }

                $url = $attr["url"];
                break;

            default :
                $msg = "Unknown value for attribute 'type in [social] shortcode.'";
                return $msg;
            }

            $ret = $this->_renderFeed(
                $url,
                $count,
                $show_title,
                $show_description,
                $twitterify,
                $user,
                $fb
            );
        }

        return $ret;
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

        $content = $request->param("_content_active");
        if ($content instanceof Content
            && $content->slug() == "admin/content/form"
        ) {
            return;
        }

        $document = $this->app()->response()->document();
        if ($this->options[$this->getOptionsPrefix()."facebook_enable"]
            && $document instanceof PHPFrame_HTMLDocument
        ) {
            // Add HTML attributes
            $html_node = $document->dom()->getElementsByTagName("html")->item(0);
            $document->addNodeAttr($html_node, "xmlns:fb", "http://www.facebook.com/2008/fbml");

            $base_url = $this->app()->config()->get("base_url");
            if (strpos($base_url, "https://") !== false) {
                $script  = "https://ssl.connect.facebook.com/js/api_lib/v0.4/";
            } else {
                $script  = "http://static.ak.connect.facebook.com/js/api_lib/v0.4/";
            }

            $script .= "FeatureLoader.js.php";
            $document->addScript($script);
        }
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

    private function _renderFeed(
        $url,
        $count=0,
        $show_title=true,
        $show_description=true,
        $twitterify=false,
        $user=null,
        $fb=false
    ) {
        try {
            $items = $this->_fetchFeedItems($url);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        if (empty($count)) {
            $count = count($items);
        }

        $str = "<div class=\"social-feed\">\n";

        if ($count > 0 && count($items) > 0) {
            $str .= "<ul>\n";
            for ($i=0; $i<$count; $i++) {
                $title = trim($items[$i]["title"]);
                $description = trim($items[$i]["description"]);
                $link = trim($items[$i]["link"]);

                if ($twitterify && $user) {
                    $title = $this->_twitterify($title, $user);
                    $description = $this->_twitterify($description, $user);
                }

                if ($fb) {
                    $description = preg_replace("/^".preg_quote($title)."<br\/><br\/>(<br\/>)?/", "", $description);
                    $link = "http://www.facebook.com".$link;
                }

                $date = $this->_pubDateToHuman($items[$i]["pub_date"]);

                $str .= "<li>\n";

                if ($show_title) {
                    $str .= "<h5>".$title."</h5>\n";
                }

                if ($show_description) {
                    $str .= "<p>".$description."</p>\n";
                }

                if ($date) {
                    $str .= "<a href=\"".$link."\">";
                    $str .= $date."";
                    $str .= "</a>";
                }

                $str .= "</li>\n";
            }
            $str .= "</ul>\n";
        }

        $str .= "</div>";

        return $str;
    }

    private function _fetchFeedItems($url)
    {
        $feeds_cache_dir = $this->app()->getTmpDir().DS."cms".DS."feeds";
        PHPFrame_Filesystem::ensureWritableDir($feeds_cache_dir);

        $feed = new FeedContent();
        $feed->cacheDir($feeds_cache_dir);
        $feed->param("cache_time", 60*10);
        $feed->param("feed_url", $url);

        return $feed->items();
    }

    private function _pubDateToHuman($str)
    {
        if (!$str) {
            return "";
        } else {
            $time = strtotime($str);
            $seconds_ago = (time() - $time);

            if ($seconds_ago < 60) {
                $str = "just now";
            } elseif ($seconds_ago < 60*60) {
                $minutes = round($seconds_ago/60);
                $str = ($minutes>1) ? $minutes." minutes ago" : " 1 minute ago";
            } elseif ($seconds_ago < 60*60*24) {
                $hours = round($seconds_ago/(60*60));
                $str = ($hours>1) ? $hours." hours ago" : " 1 hour ago";
            } elseif ($seconds_ago < 60*60*24*7) {
                $days = round($seconds_ago/(60*60*24));
                $str = ($days>1) ? $days." days ago" : " yesterday";
            } elseif ($seconds_ago < 60*60*24*7*4) {
                $weeks = round($seconds_ago/(60*60*24*7));
                $str = ($weeks>1) ? $weeks." weeks ago" : " 1 week ago";
            } else {
                $months = round($seconds_ago/(60*60*24*30));
                $str = ($months>1) ? $months." months ago" : " 1 month ago";
            }
        }

        return $str;
    }

    private function _twitterify($tweet, $user)
    {
        // Remove username from beggining of title
        $tweet = preg_replace("/^".$user.": /i", "", $tweet);

        // twitterify
        $tweet = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\">\\2</a>", $tweet);
        $tweet = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\">\\2</a>", $tweet);
        $tweet = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\">@\\1</a>", $tweet);
        $tweet = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\">#\\1</a>", $tweet);

        return $tweet;
    }
}
