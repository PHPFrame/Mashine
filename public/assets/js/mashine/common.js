/**
 * public/assets/js/cms/common.js
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

// Init UI on document ready event
jQuery(document).ready(function($) {
    initToolTips();
    confirmDialog('.confirm');
    validateForm('.validate');
    //$('#user-form #password').password_strength();

    // close sidebar item
    var closeNote = $('a.close_button');
    closeNote.live('click', function(e) {
        e.preventDefault();
        $(this).closest('.sidebar-item-wrapper').fadeOut('2000');
        $(this).closest('.sysevent').fadeOut('1500');
    });
});

/**
 * Initialise tooltips using jQuery Tipsy plugin.
 *
 * @return void
 * @since  1.0
 */
function initToolTips()
{
    var options = {
        gravity: 'w',
        html: true
    };

    jQuery('.tooltip').tipsy(options).click(function(e) {
        e.preventDefault();
    });
}

/**
 * Initialise form validation using jQuery 'validate' plugin.
 *
 * @param string selector jQuery selctor to select form.
 *
 * @return void
 * @since  1.0
 */
function validateForm(selector, options)
{
    if (options == undefined) {
        options = {};
    }

    if (options.rules == undefined) {
        options.rules = {
            email: {
                required: true,
                email: true
            },
            password: 'required',
            confirm_password: {
                equalTo: '#password'
            }
        };
    }

    if (options.highlight == undefined) {
        options.highlight = function(e, errorClass) {
            jQuery(e).addClass('validate-error');
        };
    }

    if (options.unhighlight == undefined) {
        options.unhighlight = function(e, errorClass) {
            jQuery(e).removeClass('validate-error').css('color', '#222');
        };
    }

    if (options.errorPlacement == undefined) {
        options.errorPlacement = function(error, element) {
            //this hides error messages
        };
    }

    jQuery(selector).validate(options);
}

var confirmDialogDiv;
var confirmDialogActiveTrigger;

/**
 * Attach confirm dialog behaviour to links.
 *
 * @param string selector jQuery selctor to select anchor tags.
 *
 * @return void
 * @since  1.0
 */
function confirmDialog(selector)
{
    // Add HTML element to show the confirmation dialog
    if (typeof confirmDialogDiv === 'undefined') {
        $("body").append('<div id="confirm-dialog" title="Delete entry"></div>');

        confirmDialogDiv = $("#confirm-dialog");

        // Add dialog behaviour to the confirm box
        confirmDialogDiv.dialog({
            autoOpen: false,
            bgiframe: true,
            resizable: false,
            height:140,
            modal: true,
            overlay: {
                backgroundColor: '#000',
                opacity: 0.5
            },
            buttons: {
                'Ok': function() {
                    $(this).dialog('close');
                    window.location = confirmDialogActiveTrigger.attr('href');
                },
                Cancel: function() {
                    $(this).dialog('close');
                }
            }
        });
    }

    $(selector).click(function(e) {
        e.preventDefault();
        confirmDialogActiveTrigger = $(this);
        confirmDialogDiv
            .html(confirmDialogActiveTrigger.attr("title"))
            .dialog('open');
    });
}
