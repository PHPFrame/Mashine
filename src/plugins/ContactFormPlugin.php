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
            $name    = $request->param("name");
            $email   = $request->param("email");
            $subject = $request->param("subject");
            $body    = $request->param("body");

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

<script type="text/javascript" charset="utf-8">
validateForm('form#contact-form', {
    submitHandler: function(form) {
        form = jQuery(form);
        var response_container = jQuery('#ajax-response');

        response_container.html('Loading...');

        jQuery.ajax({
            type: 'POST',
            url: base_url,
            data: form.serialize() + '&ajax=1',
            success: function(response) {
                response_container.html(response);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                response_container.html(XMLHttpRequest.responseText);
            }
        });
    }
});
</script>

        <?php
        $str = ob_get_contents();
        ob_end_clean();

        return $str;
    }

    public function displayOptionsForm()
    {
        ob_start();
        ?>

        <form action="index.php" method="post">

        <p>
            <label>To address:</label>
            <input
                type="text"
                name="options_<?php echo $this->getOptionsPrefix(); ?>to_address"
                value="<?php echo $this->options[$this->getOptionsPrefix()."to_address"]; ?>"
            />
        </p>

        <p>
            <label>To name:</label>
            <input
                type="text"
                name="options_<?php echo $this->getOptionsPrefix(); ?>to_name"
                value="<?php echo $this->options[$this->getOptionsPrefix()."to_name"]; ?>"
            />
        </p>


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
        $this->options[$this->getOptionsPrefix()."version"] = "1.0";
    }
}
