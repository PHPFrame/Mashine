/**
 * This file's only role is to include all other scripts that need to be
 * available to all users throughout the whole site.
 *
 * This file gets replaced in the automated build. The newly created file will
 * contain a compressed script containing all the included files, so that form
 * the theme we only need to include this file.
 */

var includeScript = function (src) {
  document.write('<script src="' + src + '"></script>');
};

includeScript('assets/js/jquery/jquery-ui-1.8.6.custom.min.js');
includeScript('assets/js/jquery/jquery.strengthy.min.js');
includeScript('assets/js/jquery/jquery.tipsy.js');
includeScript('assets/js/jquery/jquery.validate.js');
includeScript('assets/js/x.js');
includeScript('assets/js/mshn.core.js');
