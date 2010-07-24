/**
 * public/assets/js/cms/login.js
 *
 * Javascript
 *
 * @category  PHPFrame_Applications
 * @package   Mashine
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/lupomontero/Mashine
 */

/**
 * Initialise login form, including form validation and forgot pass toggle.
 *
 * @return void
 * @since  1.0
 */
function initLoginForm()
{
    var loginButton = $('#login-button');
    var loginAjaxResponse = $('#login-ajax-response');

    validateForm('#login-form', {
        submitHandler: function(e) {
            var form = $(e);
            var loginButtonOriginalVal = loginButton.val();

            loginAjaxResponse.html(' ');
            loginButton.attr('disabled', true);
            loginButton.val('Loggin in...');

            $.ajax({
                url: 'api/session/login',
                type: 'POST',
                data: form.serialize() + '&suppress_response_codes=1',
                success: function(data) {
                    if (typeof data.error !== 'undefined') {
                        loginAjaxResponse.html(data.error.message);
                    } else {
                        window.location = data.ret_url;
                        return false;
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(textStatus);
                },
                complete: function(XMLHttpRequest, textStatus) {
                    loginButton.attr('disabled', false);
                    loginButton.val(loginButtonOriginalVal);
                }
            });
        }
    });

    validateForm('#forgotpass-form');

    var forgotPassDiv = $('#forgotpass');

    forgotPassDiv.hide();

    $('a#forgotpass-link').bind('click', function(e) {
        e.preventDefault();
        forgotPassDiv.toggle('slow');
    });
}
