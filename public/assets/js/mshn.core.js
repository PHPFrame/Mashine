/**
 * The EN (E-NOISE) Javascript object
 *
 * @author  Lupo Montero <lupo@e-noise.com>
 * @version 0.0.1
 */

/*jslint eqeqeq: true */

(function (window, jQuery) {

var EN = function (options) {
  // Enforce that new instance is created if invoked without 'new' keyword
  // John Resig: http://ejohn.org/apps/learn/
  if (!(this instanceof arguments.callee)) {
    return new arguments.callee(arguments);
  }

  //console.log(options);
};

EN.prototype.parseString = function (str) {
  var
    pairs = str.split('&'),
    params = {},
    le = pairs.length,
    i,
    current;

  for (i=0; i<le; i++) {
    current = pairs[i].split('=');
    params[current[0]] = current[1];
  }

  return params;
};

EN.prototype.validate = function (selector, options) {
  var objs = jQuery(selector);
  var opts = {
    highlight: function (e, errorClass) {
      jQuery(e).addClass('validate-error');
    },
    unhighlight: function (e, errorClass) {
      jQuery(e).removeClass('validate-error').css('color', '#222');
    },
    errorPlacement: function (error, element) {
      if (jQuery(element).hasClass('strongpass')) {
        element.after(error);
      }
    }
  };

  if (objs.length < 1) {
    return false;
  }

  if (typeof options === 'object') {
    for (var key in options) {
      if (options.hasOwnProperty(key)) {
        opts[key] = options[key];
      }
    }
  }

  objs.validate(opts);
};

var confirmDiv;
var confirmActiveTrigger;

EN.prototype.confirm = function (selector) {
  // Add HTML element to show the confirmation dialog
  if (typeof confirmDiv === 'undefined') {
    jQuery("body").append('<div id="confirm-dialog" title="Delete entry"></div>');
    confirmDiv = jQuery("#confirm-dialog");

    // Add dialog behaviour to the confirm box
    confirmDiv.dialog({
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
        'Ok': function () {
          jQuery(this).dialog('close');
          window.location = confirmActiveTrigger.attr('href');
        },
        'Cancel': function () {
          jQuery(this).dialog('close');
        }
      }
    });
  }

  jQuery(selector).click(function (e) {
    e.preventDefault();
    confirmActiveTrigger = jQuery(this);
    confirmDiv.html(confirmActiveTrigger.attr("title")).dialog('open');
  });
};

EN.prototype.initToolTips = function (selector) {
  var options = {
    gravity: 'w',
    html: true,
    opacity: 0.8
  };

  jQuery(selector).tipsy(options).click(function (e) {
    if (jQuery(e).attr('href') === '#') {
      e.preventDefault();
    }
  });
};

EN.prototype.initLoginForm = function () {
  var that = this;
  var loginButton = jQuery('#login-button');
  var ajaxResponse = jQuery('#login-ajax-response');
  var forgotPassContainer = jQuery('#forgotpass');

  that.validate('#login-form', {
    submitHandler: function (e) {
      var form = jQuery(e);
      var loginButtonOriginalVal = loginButton.val();

      ajaxResponse.html(' ');
      loginButton.attr('disabled', true);
      loginButton.val('Logging in...');

      that.mashineApi({
        url: 'api/session/login',
        data: form.serialize(),
        success: function (data) {
          if (typeof data.error !== 'undefined') {
            ajaxResponse.html(data.error.message);
          } else {
            window.location = data.ret_url;
            return false;
          }
        },
        complete: function (XMLHttpRequest, textStatus) {
          loginButton.attr('disabled', false);
          loginButton.val(loginButtonOriginalVal);
        }
      });
    }
  });

  that.validate('#forgotpass-form');
  forgotPassContainer.hide();

  jQuery('a#forgotpass-link').bind('click', function (e) {
    e.preventDefault();
    forgotPassContainer.toggle('slow');
  });
};

EN.prototype.mashineApi = function (options) {
  var opts = {
    url: 'api/usage',
    type: 'POST',
    data: {
      suppress_response_codes: 1
    },
    success: function (response) {
      if (typeof response.error !== 'undefined') {
        alert(response.error.message);
      }
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      alert(textStatus);
    }
  };

  if (typeof options === 'object') {
    if (typeof options.data === 'string') {
      options.data = this.parseString(options.data);
    }

    if (typeof options.data === 'object' && typeof options.data.suppress_response_codes === 'undefined') {
      options.data.suppress_response_codes = 1;
    }

    jQuery.extend(opts, options);
  }

  jQuery.ajax(opts);
};

EN.prototype.renderPosts = function (posts) {
  var str = '';

  for (var i=0; i<posts.length; i++) {
    var post = posts[i];

    str += '<article';
    if (+post.status === 0) {
      str += ' class="unpublished"';
    }
    str += '>';

    str += '<header>';
    if (+post.status === 0) {
      str += '<div style="float: right;">Unpublished</div>';
    }
    str += '<h2 class="post-title">';
    str += '<a href="' + post.url + '">' + post.title + '</a>';
    str += '</h2>';
    str += '<p class="post-info">';
    str += 'Posted by <a href="">' + post.author + '</a>';
    str += ' on <time datetime="' + post.pub_date + '" pubdate>' + post.pub_date_human + '</time>';
    str += '</p>';
    str += '</header>';

    str += '<div class="post-excerpt">' + post.excerpt + '</div>';

    str += '<p class="post-readmore">';
    str += '<a href="' + post.url + '">[ read more... ]' + '</a>';
    str += '</p>';

    str += '<footer>';
    str += '<p>Share: ';
    str += '<a href="http://www.facebook.com/sharer.php?u=' + post.url + '&t=' + post.title + '">';
    str += 'Facebook</a> | ';
    str += '<a href="http://twitter.com/?status=' + post.title + ':%20' + post.url + '">';
    str += 'Twitter</a> | ';
    str += '<a href="http://www.delicious.com/save?jump=yes&url=' + post.url + '&title=' + post.title + '">';
    str += 'Del.icio.us</a>';
    str += '</p>';
    str += '</footer>';

    str += '</article>';
  }

  return str;
};

EN.prototype.infiniteScrolling = function (triggerSelector, renderer) {
  var that = this;
  var trigger = jQuery(triggerSelector);
  var loading = false;
  var end     = false;

  if (typeof renderer !== 'function') {
    renderer = that.renderPosts;
  }

  jQuery(window).scroll(function () {
    if (jQuery(window).scrollTop() === jQuery(document).height() - jQuery(window).height()) {
      trigger.click();
    }
  });

  trigger.click(function (e) {
    var el = jQuery(this);
    var elOriginalHtml = el.html();
    var classArray = el.attr('class').split('-');
    var href = el.attr('href');
    var hrefArray = href.split('?');
    var hrefParams = hrefArray[1].split('&');
    var data = { parent_id: classArray[1] };

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

    that.mashineApi({
      url: 'api/content',
      data: data,
      success: function (data) {
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

        var matches = href.match(/page=(\d+)/);
        var nextPage = parseInt(matches[1], 10) + 1;
        var nextHref = href.replace(/page=\d+/, 'page=' + nextPage);

        el.attr('href', nextHref);
        el.html(elOriginalHtml);

        jQuery('#content-body').append(renderer(data));
      },
      complete: function () {
          loading = false;
      }
    });
  });
};

window.EN = new EN();

})(window, jQuery);

// Init UI on dom ready
jQuery(document).ready(function() {
  EN.initToolTips('.tooltip');
  EN.confirm('.confirm');
  EN.validate('.validate');

  // close sysevent boxes
  jQuery('.sysevents-item-close-btn').live('click', function(e) {
    e.preventDefault();
    jQuery(this).closest('.sysevents-item').fadeOut('1500');
  });
});
