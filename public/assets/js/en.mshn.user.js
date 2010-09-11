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

"use strict";

(function() {

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
EN.userAutocomplete = function(input) {
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
            jQuery.ajax({
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
};

EN.initContentForm = function() {
    var slugInput = jQuery('#slug');
    var titleInput = jQuery('#title');
    var tinymceTextArea = jQuery('textarea.tinymce');
    var typeSelect = jQuery('#type');
    var typeParam = jQuery('.typeparam');
    var publishingFields = jQuery('#publishing p');
    var metaDataFields = jQuery('#metadata p');
    var permissionsFields = jQuery('#permissions p');
    var parentSlug = jQuery('#parent-slug');

    var updateSlug = function(slug) {
        slug = slug.toLowerCase();
        slug = slug.replace(/[^a-z0-9\-]/g, '-');

        if (typeof parentSlug.val() !== 'undefined') {
            slug = parentSlug.val() + '/' + slug;
        }

        slugInput.val(slug);
    };

    var updateType = function(type) {
        typeParam.hide();
        jQuery('p.' + type).show();
    };

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

    jQuery('#tinymce-button-visual').click(function(e) {
        e.preventDefault();
        tinymceTextArea.tinymce().show();
    });

    jQuery('#tinymce-button-html').click(function(e) {
        e.preventDefault();
        tinymceTextArea.tinymce().hide();
    });

    jQuery('#content_form').validate({
        submitHandler: function(form) {
            // switch to visual mode before saving to avoid backslash issue
            tinymceTextArea.tinymce().show();

            slugInput.removeAttr('disabled');
            form.submit();
        }
    });

    //slugInput.attr('disabled', true);

    titleInput.keyup(function() {
        var value = jQuery(this).val();
        value = jQuery.trim(value);
        updateSlug(value);
    });

    typeSelect.change(function() {
        switch (jQuery(this).val()) {
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

    typeSelect.change();

    publishingFields.hide();
    jQuery('#publishing legend').click(function() {
        publishingFields.toggle();
    });

    metaDataFields.hide();
    jQuery('#metadata legend').click(function() {
        metaDataFields.toggle();
    });

    permissionsFields.hide();
    jQuery('#permissions legend').click(function() {
        permissionsFields.toggle();
    });
};

})(jQuery, EN);

// Wrap script in document ready event
jQuery(document).ready(function(jQuery) {
    var userExportDiv = jQuery('#user-export');

    userExportDiv.hide();

    jQuery('#user-export-toggle').click(function(e) {
        e.preventDefault();

        if (userExportDiv.css('display') !== 'none') {
            userExportDiv.slideUp('slow');
        } else {
            userExportDiv.slideDown('slow');
        }
    });

    EN.userAutocomplete('input#owner');
});
