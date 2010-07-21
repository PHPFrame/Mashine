/**
 * public/assets/js/cms/admin.user.js
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

// Wrap script in document ready event
jQuery(document).ready(function($) {
    var userExportDiv = $('#user-export');

    userExportDiv.hide();

    $('#user-export-toggle').click(function(e) {
        e.preventDefault();

        if (userExportDiv.css('display') !== 'none') {
            userExportDiv.slideUp('slow');
        } else {
            userExportDiv.slideDown('slow');
        }
    });

    userAutocomplete('input#owner');
});

/**
 * Attach user autocomplete behaviour to given input text and set hidden
 * input value to user id for form submission.
 *
 * @param string inputText   A jQuery selector used to target the input
 *                           field.
 *
 * @return void
 * @since  1.0
 */
function userAutocomplete(input)
{
    var inputOriginal = jQuery(input);
    var inputAutocomplete = jQuery('#autocomplete-owner');

    if (inputAutocomplete.length === 0) {
        inputOriginal.css('display', 'none');
        inputOriginal.after('<input class="required" type="text" id="autocomplete-owner" name="autocomplete-owner" />');
        inputAutocomplete = jQuery('#autocomplete-owner');
    }

    inputAutocomplete.autocomplete({
        minLength: 3,
        search: function(event, ui) {},
        source: function(req, callback) {
            $.ajax({
                url: 'user/search',
                dataType: 'json',
                data: { s: req.term },
                async: false,
                success: function(json) {
                    callback(json);
                }
            });
        },
        open: function() {},
        focus: function(event, ui) {
            inputAutocomplete.val(ui.item.label);
            inputOriginal.val(ui.item.value);
            return false;
        },
        select: function(event, ui) {
            return false;
        }
    });
}
