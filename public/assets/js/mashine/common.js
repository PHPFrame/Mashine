/**
 * public/assets/js/mashine/common.js
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

"use strict";

(function() {

/**
 * Initialise tooltips using jQuery Tipsy plugin.
 *
 * @return void
 * @since  1.0
 */
EN.initToolTips = function() {
    var options = {
        gravity: 'w',
        html: true,
        opacity: 0.8
    };

    jQuery('.tooltip').tipsy(options).click(function(e) {
        e.preventDefault();
    });
};

/**
 * Initialise form validation using jQuery 'validate' plugin.
 *
 * @param string selector jQuery selctor to select form.
 *
 * @return void
 * @since  1.0
 */
EN.validate = function(selector, options) {
    var opts = {
        rules: {
            email: {
                required: true,
                email: true
            },
            password: 'required',
            confirm_password: {
                equalTo: '#password'
            }
        },
        highlight: function(e, errorClass) {
            jQuery(e).addClass('validate-error');
        },
        unhighlight: function(e, errorClass) {
            jQuery(e).removeClass('validate-error').css('color', '#222');
        },
        errorPlacement: function(error, element) {
            //this hides error messages
        }
    };

    if (typeof options === 'object') {
        for (var key in options) {
            if (options.hasOwnProperty(key)) {
                opts[key] = options[key];
            }
        }
    }

    jQuery(selector).validate(opts);
};

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
EN.confirm = function(selector)
{
    // Add HTML element to show the confirmation dialog
    if (typeof confirmDialogDiv === 'undefined') {
        jQuery("body").append('<div id="confirm-dialog" title="Delete entry"></div>');

        confirmDialogDiv = jQuery("#confirm-dialog");

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
                    jQuery(this).dialog('close');
                    window.location = confirmDialogActiveTrigger.attr('href');
                },
                Cancel: function() {
                    jQuery(this).dialog('close');
                }
            }
        });
    }

    jQuery(selector).click(function(e) {
        e.preventDefault();
        confirmDialogActiveTrigger = jQuery(this);
        confirmDialogDiv
            .html(confirmDialogActiveTrigger.attr("title"))
            .dialog('open');
    });
};

/**
 * Initialise login form, including form validation and forgot pass toggle.
 *
 * @return void
 * @since  1.0
 */
EN.initLoginForm = function() {
    var loginButton = jQuery('#login-button');
    var ajaxResponse = jQuery('#login-ajax-response');
    var forgotPassContainer = jQuery('#forgotpass');

    EN.validate('#login-form', {
        submitHandler: function(e) {
            var form = jQuery(e);
            var loginButtonOriginalVal = loginButton.val();

            ajaxResponse.html(' ');
            loginButton.attr('disabled', true);
            loginButton.val('Logging in...');

            jQuery.ajax({
                url: 'api/session/login',
                type: 'POST',
                data: form.serialize() + '&suppress_response_codes=1',
                success: function(data) {
                    if (typeof data.error !== 'undefined') {
                        ajaxResponse.html(data.error.message);
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

    EN.validate('#forgotpass-form');
    forgotPassContainer.hide();

    jQuery('a#forgotpass-link').bind('click', function(e) {
        e.preventDefault();
        forgotPassContainer.toggle('slow');
    });
};

EN.mashineApiClient = function() {
    return {
        fetch: function(method, args) { return true; },
        getRequestToken: function() {},
        getAccessToken: function() {}
    };
};

EN.infiniteScrolling = function(triggerSelector) {
    var trigger = jQuery(triggerSelector);
    var loading = false;
    var end     = false;

    jQuery(window).scroll(function() {
        if (jQuery(window).scrollTop() == jQuery(document).height() - jQuery(window).height()) {
           trigger.click();
        }
    });

    trigger.click(function(e) {
        var el = jQuery(this);
        var elOriginalHtml = el.html();
        var classArray = el.attr('class').split('-');
        var href = el.attr('href');
        var hrefArray = href.split('?');
        var hrefParams = hrefArray[1].split('&');
        var data = {
            parent_id: classArray[1],
            suppress_response_codes: 1
        };

        e.preventDefault();

        if (loading === true || end === true) {
            return;
        }

        loading = true;

        for (var i=0; i<hrefParams.length; i++) {
            var pair = hrefParams[i].split("=");
            data[pair[0]] = pair[1];
        }

        el.html('Loading...');

        jQuery.ajax({
            url: 'api/content',
            data: data,
            success: function(data) {
                if (typeof data.error !== 'undefined') {
                    alert(data.error.message);
                    return;
                }

                if (data.length < 1) {
                    el.css('display', 'none');
                    el.after('<p>-- The end --</p>');
                    end = true;
                    return false;
                }

                var str = '';

                for (var i=0; i<data.length; i++) {
                    var post = data[i];

                    str += '<li>';
                    str += '<div class="article">';

                    str += '<h2 class="post-title">';
                    str += '<a href="' + post.url + '">' + post.title + '</a>';
                    str += '</h2>';

                    str += '<div class="post-excerpt">' + post.excerpt + '</div>';

                    str += '<span class="post-info">';
                    str += 'Posted by ' + post.author + ' on ' + post.pub_date;
                    str += '</span>';

                    str += '<span class="post-info-readmore">';
                    str += '<a href="' + post.url + '">read more...' + '</a>';
                    str += '</span>';

                    str += '<div style="clear:both;"></div>';
                    str += '</div><!-- #article -->';
                    str += '</li>';
                }

                var matches = href.match(/page=(\d+)/);
                var nextPage = parseInt(matches[1], 10) + 1;
                var nextHref = href.replace(/page=\d+/, 'page=' + nextPage);

                el.attr('href', nextHref);
                el.html(elOriginalHtml);

                jQuery('#posts').append(str);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(textStatus);
            },
            complete: function() {
                loading = false;
            }
        });
    });
};

})(jQuery, EN);

// Init UI on document ready event
jQuery(document).ready(function() {
    try {
        EN({ debug: true });
    } catch(e) {
        alert(e);
    }

    EN.initToolTips();
    EN.confirm('.confirm');
    EN.validate('.validate');

    // close sysevent boxes
    var closeNote = jQuery('a.close_button');
    closeNote.live('click', function(e) {
        e.preventDefault();
        jQuery(this).closest('.sidebar-item-wrapper').fadeOut('2000');
        jQuery(this).closest('.sysevent').fadeOut('1500');
    });

    EN.infiniteScrolling('#content-infinite-scrolling-trigger');
});
