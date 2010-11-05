/*!
 * jQuery plugin to handle mashine's front end media galleries in 'simple' mode.
 *
 * @author Lupo Montero <lupo@e-noise.com>
 */

/*jslint eqeqeq: true */

(function ($) {

var defSettings = {
  transition: [ 'fadeIn', 1000 ]
};

$.fn.media = function (options) {
  var settings = jQuery.extend(defSettings, options || {});
  var transitionMethod = settings.transition[0];
  var transitionDuration = Math.abs(+settings.transition[1]);

  if (!(transitionMethod in $.prototype)) {
    transitionMethod = 'show';
  }

  transitionMethod = $.prototype[transitionMethod];

  // build gallery for each selected element
  return this.each(function () {
    var thumbsContainer = $(this);
    var thumbs = thumbsContainer.children('a');
    var singleContainer = $('<div class="media-single-container" style="display:none;">');
    var singleLoading = $('<div class="media-loading">Loading...</div>');
    var singleImg = $('<img class="media-single-img" style="display:none;" />');
    var singleInfo = $('<div>');
    var singleCurrentSpan = $('<span>');
    var singleTotalSpan = $('<span>');
    var singleBackBtn = $('<button>Back</button>');
    var i, le = thumbs.length, index = {};

    singleContainer
      .append(singleLoading)
      .append(singleImg)
      .append(singleInfo.append(singleCurrentSpan).append(singleTotalSpan))
      .append(singleBackBtn);

    thumbsContainer.after(singleContainer);

    for (i=0; i<le; i++) {
      index[thumbs[i].href] = i;
    }

    singleCurrentSpan.html('1');
    singleTotalSpan.html(' / ' + le);

    thumbs.click(function (e) {
      e.preventDefault();
      thumbsContainer.hide();
      singleImg.attr('src', this.href);
      singleContainer.show();
    });

    singleImg.click(function () {
      var nextKey = index[this.src]+1;

      singleImg.hide();
      singleLoading.show();

      if (nextKey >= le) {
        nextKey = 0;
      }

      singleCurrentSpan.html(nextKey+1);
      singleImg.attr('src', thumbs[nextKey].href);
    });

    singleImg.load(function () {
      singleLoading.hide();
      transitionMethod.apply(singleImg, [ transitionDuration ]);
    });

    singleBackBtn.click(function () {
      singleContainer.hide();
      thumbsContainer.show();
    });
  });
};

})(jQuery);
