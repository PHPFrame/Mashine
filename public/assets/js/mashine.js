/**
 * public/assets/js/mashine.js
 *
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

includeScript('assets/js/x.js');

/**
 * Include jQuery plugins bundle. This bundle file contains the following
 * plugins in the given order:
 *
 * - jQuery UI 1.8 (custom build incl. Autocomplete, Dialog and Datepicker)
 * - jQuery strengthy 0.0.3
 * - jQuery tipsy (not sure which version)
 * - jQuery validate 1.6
 */
includeScript('assets/js/jquery/jquery.bundle.min.js');

/**
 * Include mashine's core js
 */
includeScript('assets/js/mshn.core.js');
