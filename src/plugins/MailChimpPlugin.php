<?php
/**
 * src/plugins/MailChimpPlugin.php
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
 * MailChimpPlugin class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class MailChimpPlugin extends AbstractPlugin
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

        $this->shortCodes()->add(
            "mailchimp",
            array($this, "handleMailChimpShortCode")
        );
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

    public function displayOptionsForm()
    {
        $prefix = $this->getOptionsPrefix();

        ob_start();
        ?>

<form class="validate" action="index.php" method="post">
  <fieldset>
    <legend>MailChimp details</legend>
    <p>
      <label for="">API Key:</label>
      <input
        class="tooltip required"
        title="This can be found in you Account page in the mailchimp website"
        type="text"
        name="options_<?php echo $prefix; ?>api_key"
        id="options_<?php echo $prefix; ?>api_key"
        value="<?php echo $this->options[$prefix."api_key"]; ?>"
        size="40"
      />
    </p>
    <p>
      <input type="button" value="&larr; Back" onclick="window.history.back();" />
      <input type="submit" value="Save &rarr;" />
    </p>
  </fieldset>
  <p>
    <input type="hidden" name="controller" value="plugins" />
    <input type="hidden" name="action" value="save_options" />
  </p>
</form>

        <?php
        return ob_get_clean();
    }

    /**
     * Handle [mailchimp] shortcode.
     *
     * @param array Associative array containing the shortcode attributes.
     *
     * @return string This method returns the string the shortcode will be
     *                replaced with.
     * @since  1.0
     */
    public function handleMailChimpShortCode($attr)
    {
        // check whether options are valid
        $api_key = $this->options[$this->getOptionsPrefix()."api_key"];
        if (!$api_key) {
            ob_start();
            ?>

<div class="error">
  <p>
    MailChimp API key has not been set. Please set it in the plugin
    <a href="admin/plugins">options</a>.
  </p>
</div>

            <?php
            return ob_get_clean();
        }

        // Flag this page as having a mailchimp subscription form so that
        // postApplyTheme() knowns that it has to add the js
        $this->app()->request()->param("_mailchimp_subscription_form", true);

        ob_start();
        ?>

<form id="mailchimp-subscription-form" action="index.php" method="post">
  <fieldset>
    <div class="indicate-required">* indicates required</div>
    <p>
      <label for="email">Email address *</label>
      <input type="text" name="email" id="email" class="email required" />
    </p>
    <p>
      <input
        id="mailchimp-subscribe-btn"
        type="submit"
        value="Subscribe"
        class="button"
      />
    </p>
  </fieldset>
  <input type="hidden" name="controller" value="mailchimpplugin" />
  <input type="hidden" name="action" value="subscribe" />
  <input type="hidden" name="listid" value="<?php echo $attr["listid"]; ?>" />
</form>

<script>
jQuery(document).ready(function ($) {
  var subscribeBtn = $('#mailchimp-subscribe-btn');
  var originalSubscribeLabel = subscribeBtn.val();

  EN.validate('#mailchimp-subscription-form', {
    submitHandler: function (e) {
      var form = $(e);

      subscribeBtn.attr('disabled', true).val('Please wait...');

      $.ajax({
        url: 'index.php',
        data: form.serialize() + '&ajax=1',
        method: 'POST',
        success: function (data) {
          var className = (/^SUCCESS/.test(data)) ? 'success' : 'error';
          subscribeBtn
            .val(originalSubscribeLabel)
            .removeAttr('disabled')
            .after('<div class="' + className + '">' + data + '</div>');
        },
        error:function () {
          alert('An error occurred while subscribing to mailing list.');
        }
      });
    }
  });
});
</script>
        <?php
        return ob_get_clean();
    }

    public function routeStartup()
    {
        $request = $this->app()->request();
        $sysevents = $this->app()->session()->getSysevents();
        $opts = $this->options->filterByPrefix($this->getOptionsPrefix());

        if ($request->controllerName() != "mailchimpplugin"
            && $request->action() != "subscribe"
        ) {
            return;
        }

        $email = filter_var($request->param("email"), FILTER_VALIDATE_EMAIL);
        $listid = $request->param("listid");

        if (!$email) {
            throw new Exception("Invalid email");
        }

        // Include MailChimp API wrapper library
        $lib_dir = $this->app()->getInstallDir().DS."lib";
        require_once $lib_dir.DS."MailChimp".DS."MCAPI.class.php";
        $mcapi = new MCAPI($opts["api_key"]);

        $merge_vars = array("");
        $retval = $mcapi->listSubscribe($listid, $email, $merge_vars);

        if ($mcapi->errorCode) {
            $msg  = "Error saving subscription. Code=".$mcapi->errorCode;
            $msg .= ". Msg=".$mcapi->errorMessage.".";
            $sysevents->append($msg, PHPFrame_Subject::EVENT_TYPE_ERROR);
        } else {
            $msg = "Email successfully subscribed to mailing list.";
            $sysevents->append($msg, PHPFrame_Subject::EVENT_TYPE_SUCCESS);
        }

        if ($request->ajax()) {
            $this->app()->request()->dispatched(true);
        } else {
            $this->app()->session()->getClient()->redirect(
                $request->header("Referer")
            );
        }
    }
}
