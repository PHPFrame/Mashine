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
 * @link      https://github.com/lupomontero/Mashine
 */

/**
 * Include external Javascript file.
 *
 * @param string src A string with the URL to the external script.
 *
 * @return void
 * @since  1.0
 */
var includeScript = function(src)
{
    var str = '<script type="text/javascript" src="' + src + '"></script>';

    document.write(str);
}

includeScript('assets/js/jquery/jquery.tipsy.js');
includeScript('assets/js/en.js');
includeScript('assets/js/mashine/common.js');
