/**
 * public/assets/js/mashine.js
 *
 * This file's only role is to include all other scripts that need to be
 * available to all users throughout the whole site.
 *
 * This file gets replaced in the automated build. The newly created file will
 * contain a compressed script containing all the included files, so that form
 * the theme we only need to include this file.
 *
 * @category  PHPFrame_Applications
 * @package   Mashine
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/E-NOISE/Mashine
 */

/**
 * Include external Javascript file.
 *
 * @param string src A string with the URL to the external script.
 *
 * @return void
 * @since  1.0
 */
var includeScript = function(src) {
    document.write('<script src="' + src + '"></script>');
};

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

