/**
 * public/assets/js/cms.admin.js
 *
 * This file's only role is to include all other scripts that need to be
 * available to all authors and admin users after they login.
 *
 * This file gets replaced in the automated build. The newly created file will
 * contain a compressed script containing all the included files, so that from
 * the theme we only need to include this file.
 *
 * @category  PHPFrame_AppTemplates
 * @package   PHPFrame_CmsAppTemplate
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/lupomontero/PHPFrame_CmsAppTemplate
 */

// Include TinyMCE jquery plugin
includeScript('assets/js/jquery/jquery.tinymce.js');

// Include CMS admin scripts
includeScript('assets/js/cms/admin.content.js');
includeScript('assets/js/cms/admin.user.js');
