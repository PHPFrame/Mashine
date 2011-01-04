/*!
 * Mashine Javascript object.
 *
 * Copyright 2010, Lupo Montero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 */

/*jslint eqeqeq: true */

(function (window, $) {

var Mashine = function (options) {
  // Enforce that new instance is created if invoked without 'new' keyword
  // John Resig: http://ejohn.org/apps/learn/
  if (!(this instanceof arguments.callee)) {
    return new arguments.callee(arguments);
  }

  //console.log(options);
};

Mashine.prototype.validate = function (selector, options) {
  var
    objs = $(selector),
    opts = {
      highlight: function (e, errorClass) {
        $(e).addClass('validate-error');
      },
      unhighlight: function (e, errorClass) {
        $(e).removeClass('validate-error').css('color', '#222');
      },
      errorPlacement: function (error, element) {
        if ($(element).hasClass('strongpass')) {
          element.after(error);
        }
      }
    };

  if (objs.length < 1) {
    return false;
  }

  opts = $.extend(opts, options || {});
  objs.validate(opts);
};

var confirmDiv;
var confirmActiveTrigger;

Mashine.prototype.confirm = function (selector) {
  // Add HTML element to show the confirmation dialog
  if (typeof confirmDiv === 'undefined') {
    $("body").append('<div id="confirm-dialog" title="Delete entry"></div>');
    confirmDiv = $("#confirm-dialog");

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
          $(this).dialog('close');
          window.location = confirmActiveTrigger.attr('href');
        },
        'Cancel': function () {
          $(this).dialog('close');
        }
      }
    });
  }

  $(selector).click(function (e) {
    e.preventDefault();
    confirmActiveTrigger = $(this);
    confirmDiv.html(confirmActiveTrigger.attr("title")).dialog('open');
  });
};

Mashine.prototype.initToolTips = function (selector) {
  var options = {
    gravity: 'w',
    html: true,
    opacity: 0.8
  };

  $(selector).tipsy(options).click(function (e) {
    if ($(e).attr('href') === '#') {
      e.preventDefault();
    }
  });
};

Mashine.prototype.initLoginForm = function () {
  var
    self = this,
    loginButton = $('#login-button'),
    ajaxResponse = $('#login-ajax-response'),
    forgotPassContainer = $('#forgotpass');

  self.validate('#login-form', {
    submitHandler: function (e) {
      var
        form = $(e),
        loginButtonOriginalVal = loginButton.val();

      ajaxResponse.html(' ');
      loginButton.attr('disabled', true);
      loginButton.val('Logging in...');

      self.mashineApi({
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
        complete: function (xhr, textStatus) {
          loginButton.attr('disabled', false);
          loginButton.val(loginButtonOriginalVal);
        }
      });
    }
  });

  self.validate('#forgotpass-form');
  forgotPassContainer.hide();

  $('a#forgotpass-link').bind('click', function (e) {
    e.preventDefault();
    forgotPassContainer.toggle('slow');
  });
};

Mashine.prototype.mashineApi = function (options) {
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
      options.data = options.data.parse();
    }

    if (typeof options.data === 'object' &&
        typeof options.data.suppress_response_codes === 'undefined') {
      options.data.suppress_response_codes = 1;
    }

    $.extend(opts, options);
  }

  $.ajax(opts);
};

Mashine.prototype.renderPosts = function (posts) {
  var str = '', i, post;

  for (i=0; i<posts.length; i++) {
    post = posts[i];

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
    if (post.comments) {
      str += '<p><a href="' + post.slug + '#disqus_thread">Comments</a></p>';
    }
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

Mashine.prototype.infiniteScrolling = function (trigger, renderer) {
  var
    self = this,
    trigger = $(trigger),
    loading = false,
    end = false;

  if (typeof renderer !== 'function') {
    renderer = self.renderPosts;
  }

  $(window).scroll(function () {
    if ($(window).scrollTop() === $(document).height() - $(window).height()) {
      trigger.click();
    }
  });

  trigger.click(function (e) {
    var
      el = $(this),
      elOriginalHtml = el.html(),
      classArray = el.attr('class').split('-'),
      href = el.attr('href'),
      hrefArray = href.split('?'),
      hrefParams = hrefArray[1].split('&'),
      data = { parent_id: classArray[1] },
      i, pair;

    e.preventDefault();

    if (loading === true || end === true) {
      return;
    }

    loading = true;

    for (i=0; i<hrefParams.length; i++) {
      pair = hrefParams[i].split("=");
      data[pair[0]] = pair[1];
    }

    el.html('Loading...');

    self.mashineApi({
      url: 'api/content',
      data: data,
      success: function (data) {
        var
          matches = href.match(/page=(\d+)/),
          nextPage = parseInt(matches[1], 10) + 1,
          nextHref = href.replace(/page=\d+/, 'page=' + nextPage);

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

        el.attr('href', nextHref);
        el.html(elOriginalHtml);

        $('#content-body').append(renderer(data));
      },
      complete: function () {
          loading = false;
      }
    });
  });
};

window.Mashine = new Mashine();

})(window, jQuery);

// Init UI on dom ready
jQuery(document).ready(function($) {
  Mashine.initToolTips('.tooltip');
  Mashine.confirm('.confirm');
  Mashine.validate('.validate');

  // close sysevent boxes
  $('.sysevents-item-close-btn').live('click', function(e) {
    e.preventDefault();
    $(this).closest('.sysevents-item').fadeOut('1500');
  });
});
