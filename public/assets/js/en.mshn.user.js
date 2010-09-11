/**
 * public/assets/js/en.mshn.user.js
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

/**
 * Content form
 */

// Wrap script in document ready event
jQuery(document).ready(function($) {

    var slug_input  = $('#slug');
    var title_input = $('#title');
    var tinymceTextArea = $('textarea.tinymce');

    // Load TinyMCE
    tinymceTextArea.tinymce({
        // Location of TinyMCE script
        script_url : base_url + 'assets/js/tiny_mce/tiny_mce.js',

        // General options
        theme : "advanced",
        plugins : "searchreplace,fullscreen,syntaxhl",

        // Theme options
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,|,bullist,numlist,|,blockquote,|,search,|,link,unlink,anchor,cleanup",
        theme_advanced_buttons2 : "hr,syntaxhl,fullscreen",
        theme_advanced_buttons3 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : false,
        width : "430px",
        height : "350px"
    });

    $('#tinymce-button-visual').click(function(e) {
        e.preventDefault();

        tinymceTextArea.tinymce().show();
    });

    $('#tinymce-button-html').click(function(e) {
        e.preventDefault();

        tinymceTextArea.tinymce().hide();
    });

    $('#content_form').validate({
        submitHandler: function(form) {
            // switch to visual mode before saving to avoid backslash issue
            tinymceTextArea.tinymce().show();

            slug_input.removeAttr('disabled');
            form.submit();
        }
    });

    //slug_input.attr('disabled', true);

    title_input.focusout(function() {
        updateSlug($(this).val());
    });

    title_input.keypress(function() {
        updateSlug($(this).val());
    });

    function updateSlug(slug)
    {
        slug = slug.toLowerCase();
        slug = slug.replace(/[^a-z0-9\-]/g, '-');
        slug_input.val($('#parent-slug').val() + '/' + slug);
    }

    var type_select = $('#type');
    var type_param  = $('.typeparam');

    type_select.change(function() {
        switch ($(this).val()) {
        case 'PostsCollectionContent' :
            updateType('posts_collection');
            break;
        case 'PageContent' :
            updateType('page');
            break;
        case 'MVCContent' :
            updateType('mvc');
            break;
        case 'FeedContent' :
            updateType('feed');
            break;
        }
    });

    function updateType(type)
    {
        type_param.hide();
        $('p.' + type).show();
    }

    type_select.change();

    var publishing_legend = $('#publishing legend');
    var publishing_fields = $('#publishing p');
    publishing_fields.hide();
    publishing_legend.click(function() {
        publishing_fields.toggle();
    });

    var meta_data_legend = $('#metadata legend');
    var meta_data_fields = $('#metadata p');
    meta_data_fields.hide();
    meta_data_legend.click(function() {
        meta_data_fields.toggle();
    });

    var permissions_legend = $('#permissions legend');
    var permissions_fields = $('#permissions p');
    permissions_fields.hide();
    permissions_legend.click(function() {
        permissions_fields.toggle();
    });

});
