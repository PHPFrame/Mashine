<?php
/**
 * src/plugins/ContactFormPlugin.php
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
 * Contact Form Plugin class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class ContactFormPlugin extends AbstractPlugin
{
    private $_options;

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

        $this->shortCodes()->add("contactform", array(
            $this,
            "handleContactFormShortCode"
        ));
    }

    public function routeStartup()
    {
        $request = $this->app()->request();

        if ($request->controllerName() == "contactplugin"
            && $request->action() == "send"
        ) {
            $appname = $this->app()->config()->get("app_name");
            $name    = $request->param("name");
            $email   = $request->param("email");
            $subject = $request->param("subject");
            $body    = $request->param("body");

            $body = "Email sent from contact form in ".$appname.".\n---\n\n".$body;

            if (empty($name) || empty($subject) || empty($body)) {
                $msg = "Required field missing!";
                throw new RuntimeException($msg);
            }

            $email = filter_var($email, FILTER_VALIDATE_EMAIL);
            if ($email === false) {
                $msg = "Email not valid!";
                throw new RuntimeException($msg);
            }

            $mailer = $this->app()->mailer();
            if (!$mailer instanceof PHPFrame_Mailer) {
                $msg  = "Can not send contact email. Mailer object is not of ";
                $msg .= "type 'PHPFrame_Mailer'. Please make sure that SMTP ";
                $msg .= "is enabled in etc/phpframe.ini.";
                throw new RuntimeException($msg);
            }

            $to_address = $this->options[$this->getOptionsPrefix()."to_address"];
            $to_address = filter_var($to_address, FILTER_VALIDATE_EMAIL);
            if ($to_address === false) {
                $msg = "Email not valid!";
                throw new RuntimeException($msg);
            }

            $to_name = $this->options[$this->getOptionsPrefix()."to_name"];

            $mailer->Subject = $subject;
            $mailer->Body    = $body;
            $mailer->AddAddress($to_address, $to_name);

            $sysevents = $this->app()->session()->getSysevents();
            if (!$mailer->Send()) {
                $sysevents->append(
                    "Error sending email!",
                    PHPFrame_Subject::EVENT_TYPE_ERROR
                );
            } else {
                $sysevents->append(
                    "Email successfully sent!",
                    PHPFrame_Subject::EVENT_TYPE_SUCCESS
                );
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

    public function postApplyTheme()
    {
        // Only add js if shortcode was used in page
        if (!$this->app()->request()->param("_contactform")) {
            return;
        }

        $document = $this->app()->response()->document();
        if ($document instanceof PHPFrame_HTMLDocument) {
            ob_start();
                ?>
<script>
jQuery(document).ready(function () {
  EN.validate('form#contact-form', {
    submitHandler: function(form) {
      form = jQuery(form);
      var responseContainer = jQuery('#ajax-response');

      responseContainer.html('Loading...');

      jQuery.ajax({
        type: 'POST',
        url: base_url,
        data: form.serialize() + '&ajax=1',
        success: function(response) {
          responseContainer.addClass('success').html(response);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          responseContainer.addClass('error').html(XMLHttpRequest.responseText);
        }
      });
    }
  });
});
</script>
                <?php
                $document->appendBody(ob_get_clean());
        }
    }

    /**
     * Handle [contactform] shortcode.
     *
     * @param array Associative array containing the shortcode attributes.
     *
     * @return string This method returns the string the shortcode will be
     *                replaced with.
     * @since  1.0
     */
    public function handleContactFormShortCode($attr)
    {
        // check whether options are valid
        $to_address = $this->options[$this->getOptionsPrefix()."to_address"];
        if (filter_var($to_address, FILTER_VALIDATE_EMAIL) === false) {
            ob_start();
            ?>

<div class="error">
  <p>
    No email has been set for the contact form. Please set it in the plugin 
    <a href="admin/plugins">options</a>.
  </p>
</div>

            <?php
            return ob_get_clean();
        }

        // Flag this page as having a contactform so that postApplyTheme()
        // knowns that it has to add the js
        $this->app()->request()->param("_contactform", true);

        ob_start();
        ?>

<form id="contact-form" action="index.php" method="post">
<p>
  <label for="name">Name:</label>
  <input type="text" name="name" id="name" class="required" />
</p>
<p>
  <label for="email">Email:</label>
  <input type="text" name="email" id="email" class="email required" />
</p>
<p>
  <label for="subject">Subject:</label>
  <input type="text" name="subject" id="subject" class="required" />
</p>
<p>
  <label for="body">Body:</label>
  <textarea name="body" rows="8" cols="40" class="required"></textarea>
</p>
<p>
  <span class="button_wrapper">
    <input type="submit" value="Send" class="button" />
  </span>
</p>

<input type="hidden" name="controller" value="contactplugin" />
<input type="hidden" name="action" value="send" />

<div id="ajax-response"></div>

</form>
        <?php
        return ob_get_clean();
    }

    public function displayOptionsForm()
    {
        $prefix = $this->getOptionsPrefix();

        ob_start();
        ?>

<form class="validate" action="index.php" method="post">
  <fieldset>
    <legend>Contact details</legend>
    <p>
      <label for="options_<?php echo $prefix; ?>to_address">To address:</label>
      <input
        class="tooltip required email"
        title="Email address the contact emails will be sent to. ie: someone@somewhere.com"
        type="text"
        name="options_<?php echo $prefix; ?>to_address"
        id="options_<?php echo $prefix; ?>to_address"
        value="<?php echo $this->options[$prefix."to_address"]; ?>"
      />
    </p>

    <p>
      <label for="options_<?php echo $prefix; ?>to_name">To name:</label>
      <input
        class="tooltip required"
        title="Full name or the person or organisation receiving the email. ie: Homer Simpson"
        type="text"
        name="options_<?php echo $prefix; ?>to_name"
        id="options_<?php echo $prefix; ?>to_name"
        value="<?php echo $this->options[$prefix."to_name"]; ?>"
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
        return ob_get_clean();
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
}
