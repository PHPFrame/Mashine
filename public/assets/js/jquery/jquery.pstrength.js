/**
 * jquery.pstrength.js
 *
 * JavaScript
 *
 * jQuery password strength plugin
 *
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   MIT
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*jslint eqeqeq: true */

(function (jQuery) {

var defSettings = {
    minLength: 8,
    require: {
        numbers: true,
        upperAndLower: true,
        symbols: true
    },
    msgs: [
        'password is too short',
        'password must contain at least one number',
        'password must contain both lower case and upper case characters',
        'password must contain at least one symbol (ie: %!£@)',
        'password is valid'
    ]
};

var displayMsg = function (obj, msg, className) {
    var nodeName = obj.attr('name');
    var msgContainer = jQuery('#' + nodeName + '-pstrength-msg');

    if (msgContainer.length === 0) {
        obj.after('<span id="' + nodeName + '-pstrength-msg"></span>');
        msgContainer = jQuery('#' + nodeName + '-pstrength-msg');
    }

    msgContainer.attr('class', 'pstrength-' + className).html(msg);
};

var makeStrengthChecker = function (settings) {
    var tests = [
        { name: 'numbers', regex: /\d/, msg: settings.msgs[1] },
        { name: 'upperAndLower', regex: /([a-z].*[A-Z]|[A-Z].*[a-z])/, msg: settings.msgs[2] },
        { name: 'symbols', regex: /[^a-zA-Z0-9]/, msg: settings.msgs[3] }
    ];

    return function (obj) {
        var pass = obj.val();
        var score = 0;
        var testCount = 0;
        var i;

        obj.removeClass('valid');

        if (pass.length < +settings.minLength) {
            displayMsg(obj, settings.msgs[0], 'weak');
            return false;
        }

        for (i=0; i<tests.length; i++) {
            if (settings.require[tests[i].name] !== true) {
                continue;
            }

            testCount++;

            if (tests[i].regex.test(pass)) {
                score += 1;
            } else {
                displayMsg(obj, tests[i].msg, 'weak');
            }
        }

        if (score/testCount === 1) {
            displayMsg(obj, settings.msgs[4], 'strong');
            obj.addClass('valid');
        }
    };
};

// Augment the jQuery object with the password strength plugin
jQuery.fn.pStrength = function (options) {
    var settings = jQuery.extend(defSettings, options);
    var checkStrength = makeStrengthChecker(settings);

    // Add listener on keyup event for all selected nodes
    return this.each(function () {
        jQuery(this).keyup(function () {
            checkStrength(jQuery(this));
        });
    });
};

})(jQuery);

