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
     validateForm('#login-form');
     validateForm('#forgotpass-form');

     var forgotPassDiv = $('#forgotpass');

     forgotPassDiv.hide();

     $('a#forgotpass-link').bind('click', function(e) {
         e.preventDefault();
         forgotPassDiv.toggle('slow');
     });
 }
