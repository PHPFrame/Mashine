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

    var contentInfiniteScrollingTrigger = $('#content-infinite-scrolling-trigger');
    var contentInfiniteScrollingLoading = false;
    var contentInfiniteScrollingEnd = false;

    $(window).scrollTop(0);

    $(window).scroll(function() {
        if ($(window).scrollTop() == $(document).height() - $(window).height()) {
           contentInfiniteScrollingTrigger.click();
        }
    });

    contentInfiniteScrollingTrigger.click(function(e) {
        e.preventDefault();

        if (contentInfiniteScrollingLoading === true
            || contentInfiniteScrollingEnd === true
        ) {
            return;
        }

        contentInfiniteScrollingLoading = true;

        var el = $(this);
        var elOriginalHtml = el.html();
        var classArray = el.attr('class').split('-');
        var href = el.attr('href');
        var hrefArray = href.split('?');
        var hrefParams = hrefArray[1].split('&');
        var data = {
            parent_id: classArray[1],
            suppress_response_codes: 1
        };

        for (var i in hrefParams) {
            var pair = hrefParams[i].split("=");
            data[pair[0]] = pair[1];
        }

        el.html('Loading...');

        $.ajax({
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
                    contentInfiniteScrollingEnd = true;
                    return false;
                }

                var str = '';

                for (var i in data) {
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
                var nextPage = parseInt(matches[1]) + 1;
                var nextHref = href.replace(/page=\d+/, 'page=' + nextPage);

                el.attr('href', nextHref);
                el.html(elOriginalHtml);

                $('#posts').append(str);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(textStatus);
            },
            complete: function() {
                contentInfiniteScrollingLoading = false;
            }
        });
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
        html: true,
        opacity: 0.8
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
