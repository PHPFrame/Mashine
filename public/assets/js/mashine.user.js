/**
 * public/assets/js/mashine.user.js
 *
 * This file's only role is to include all other scripts that need to be
 * available to all authors and admin users after they login.
 *
 * This file gets replaced in the automated build. The newly created file will
 * contain a compressed script containing all the included files, so that from
 * the theme we only need to include this file.
 */

// Include TinyMCE jquery plugin
includeScript('assets/js/jquery/jquery.tinymce.min.js');

// Include Mashine's admin scripts
includeScript('assets/js/mshn.user.js');
