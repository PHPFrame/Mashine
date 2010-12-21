<?php
/**
 * src/plugins/MediaPlugin.php
 *
 * PHP version 5
 *
 * @category  PHPFrame_Applications
 * @package   Mashine
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/E-NOISE/Mashine
 */

/**
 * MediaPlugin class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class MediaPlugin extends AbstractPlugin
{
    /**
     * Constructor.
     *
     * @param PHPFrame_Application $app Instance of application object.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app);

        $def_config = array(
            "upload_dir" => "media",
            "mode" => "lightbox",
            "order_by" => "filename",
            "order_direction" => "ASC",
            "max_width" => 560,
            "max_height" => 400,
            "thumb_width" => 120,
            "thumb_height" => 90,
            "imgcomp" => 0
        );

        $prefix = $this->getOptionsPrefix();
        $config = $this->options->filterByPrefix($prefix);

        if ($def_config != $config) {
            $config = array_merge($def_config, $config);
            foreach ($config as $k=>$v) {
                $this->options[$prefix.$k] = $v;
            }
        }

        $this->shortCodes()->add("media", array($this, "handleMediaShortCode"));
    }

    /**
     * Install plugin.
     *
     * @return void
     * @since  1.0
     */
    public function install()
    {
        $def_media_dir = $this->app()->getInstallDir().DS."public".DS."media";
        if (!is_dir($def_media_dir)) {
            PHPFrame_Filesystem::ensureWritableDir($def_media_dir);
        }

        $this->options[$this->getOptionsPrefix()."version"] = "1.0";
    }

    public function handleMediaShortCode($attr)
    {
        // Init shortcode attributes (arguments)
        $path = array_key_exists("path", $attr) ? $attr["path"] : "";
        $mode = array_key_exists("mode", $attr) ? $attr["mode"] : null;
        $subdirs = array_key_exists("subdirs", $attr) ? $attr["subdirs"] : true;

        if (in_array($subdirs, array("", "0", "false", "off", "no", 0))) {
            $subdirs = false;
        }

        $req_node = $this->app()->request()->param("node");
        if ($req_node) {
            $path = $req_node;
        }

        $api_controller = new MediaApiController($this->app(), true);
        $api_controller->format("php");
        $api_controller->returnInternalPHP(true);
        $node = $api_controller->get($path);
        $config = $node->getConfig();

        if (!in_array($mode, array("simple","classic","lightbox","fullscreen"))) {
            $mode = $config["mode"];
        }

        // Flag this requests as having handled a media shortcode
        $this->app()->request()->param("_media_mode", $mode);

        $content = $this->app()->request()->param("_content_active");
        if ($content instanceof Content) {
            $slug = $content->slug();
        } else {
            $slug = null;
        }

        $breadcrumbs = $dirs = $files = "\n";

        if ($req_node) {
            $breadcrumbs .= $node->getBreadCrumbs($slug);
        }

        if ($node->isDir()) {
            foreach ($node as $child) {
                if ($child->isDir() && $subdirs) {
                    $dirs .= $this->_renderDir($child, $slug);
                } elseif ($child->isFile()) {
                    $files .= $this->_renderImage($child, $mode);
                }
            }
        } else {
            $files .= $this->_renderImage($node, $mode);
        }

        if (trim($dirs) != "") {
            $dirs  = "<div class=\"media-dirs\">\n".$dirs."\n";
            $dirs .= "</div><!-- .media-dirs -->\n";
        } else {
            $dirs = "";
        }

        if (!empty($files)) {
            $tmp = $files;
            $uid = uniqid();
            $files  = "<div id=\"".$uid."\" class=\"media-files media-files-".$mode;
            $files .= "\">\n".$tmp."\n</div><!-- .media-files -->\n";
            $files .= "<script>\n";
            $files .= "jQuery(document).ready(function ($) {\n";

            if (in_array($mode, array("classic","lightbox","fullscreen"))) {
                $req = $this->app()->request();
                $galleria_loaded = $req->param("_media_galleria_loaded");
                $files .= "  var options = { debug: false };\n";
                $files .= "  var galleriaTheme = '".$mode."';\n";
                if (!$galleria_loaded) {
                    $files .= "  var themeUrl = 'assets/js/galleria/themes/' + galleriaTheme + '/galleria.';\n";
                    $files .= "  themeUrl += galleriaTheme + '.js';\n";
                    $files .= "  Galleria.loadTheme(themeUrl);\n";
                    $req->param("_media_galleria_loaded", true);
                }
                $files .= "  if (galleriaTheme === 'lightbox') {\n";
                $files .= "    options.keep_source = true;\n";
                $files .= "  }\n";
                $files .= "  $('#".$uid."').galleria(options);\n";
            } else {
                $files .= "  $('#".$uid."').media();\n";
            }

            $files .= "});\n";
            $files .= "</script>\n";
        }

        return "\n\n".$breadcrumbs."\n".$dirs."\n".$files."\n";
    }

    private function _renderDir($node, $slug=null)
    {
        $str  = "<div class=\"media-dirs-item\">\n";
        $str .= "  <a href=\"".$slug."?node=".urlencode($node->getRelativePath())."\">\n";
        $str .= "    <img src=\"".$node->getThumbURL()."\" alt=\"".$node->getFilename()."\" />\n";
        $str .= "  </a>\n";
        $str .= "  <span class=\"media-dirs-item-name\">".$node->getFilename(16)."</span>\n";
        $str .= "</div><!-- .media-dirs-item -->\n";

        return $str;
    }

    private function _renderImage($node, $mode)
    {
        $config = $node->getConfig();
        $media_url = $config["site_url"].$config["upload_dir"]."/";

        if (in_array($mode, array("classic", "fullscreen"))) {
            $str  = "<img alt=\"".$node->getFilename()."\" src=\"";
            $str .= $media_url.$node->getRelativePath()."\" title=\"";
            $str .= $node->getCaption()."\" />\n";
        } else {
            $str  = "<a href=\"".$media_url.$node->getRelativePath()."\"";
            if ($mode == "lightbox") {
                $str .= " style=\"display: inline-block; height: ";
                $str .= $config["thumb_height"]."px; overflow: hidden;\"";
            }
            $str .= ">\n  <img alt=\"".$node->getCaption()."\" src=\"";
            $str .= $node->getThumbURL()."\" />\n";
            $str .= "</a>\n";
        }

        return $str;
    }

    public function postApplyTheme()
    {
        $base_url = $this->app()->config()->get("base_url");
        $document = $this->app()->response()->document();
        $is_html = $document instanceof PHPFrame_HTMLDocument;
        $ajax = $this->app()->request()->ajax();
        $media_mode = $this->app()->request()->param("_media_mode");

        if (!$is_html || $ajax || !$media_mode) {
            return;
        }

        switch ($media_mode) {
        case "lightbox" :
        case "fullscreen" :
        case "classic" :
            $js_src = $base_url."assets/js/galleria/galleria.js";
            break;
        case "simple" :
        default :
            $js_src = $base_url."assets/js/jquery/jquery.media.js";
        }

        $document->appendBody("\n<script src=\"".$js_src."\"></script>\n");
    }

    public function displayOptionsForm()
    {
        $prefix = $this->getOptionsPrefix();

        ob_start();
        ?>

<form class="validate" action="index.php" method="post">
  <fieldset>
    <legend>Media settings</legend>
    <p>
      <label for="options_<?php echo $prefix; ?>upload_dir">Relative path to upload dir:</label>
      <input
        class="tooltip required"
        title="Relative path (from public/) to where the media files should be stored. The default value is 'media'. NO preceding or trailing slashed."
        type="text"
        name="options_<?php echo $prefix; ?>upload_dir"
        id="options_<?php echo $prefix; ?>upload_dir"
        value="<?php echo $this->options[$prefix."upload_dir"]; ?>"
      />
    </p>
    <p>
      <label for="options_<?php echo $prefix; ?>mode">
        Mode:
      </label>
      <select
        class="tooltip required"
        title="This is the default mode for the 'media' shortcode."
        name="options_<?php echo $prefix; ?>mode"
        id="options_<?php echo $prefix; ?>mode"
        value="<?php echo $this->options[$prefix."mode"]; ?>"
      >
        <option value="lightbox">Lightbox</option>
        <option value="classic">Slider</option>
      </select>
    </p>
    <p>
      <label for="options_<?php echo $prefix; ?>order_by">
        Order by:
      </label>
      <select
        class="required"
        name="options_<?php echo $prefix; ?>order_by"
        id="options_<?php echo $prefix; ?>order_by"
        value="<?php echo $this->options[$prefix."order_by"]; ?>"
      >
        <option value="filename">Filename</option>
        <option value="date_modified">Date uploaded</option>
        <option value="caption">Caption</option>
      </select>
    </p>
    <p>
      <label for="options_<?php echo $prefix; ?>order_direction">
        Order direction:
      </label>
      <select
        class="required"
        name="options_<?php echo $prefix; ?>order_direction"
        id="options_<?php echo $prefix; ?>order_direction"
        value="<?php echo $this->options[$prefix."order_direction"]; ?>"
      >
        <option>ASC</option>
        <option>DESC</option>
      </select>
    </p>
  </fieldset>

  <fieldset>
    <legend>Images</legend>
    <p>
      <label for="options_<?php echo $prefix; ?>max_width">Max width:</label>
      <input
        class="tooltip required"
        title="Image maximum width in pixels."
        type="text"
        name="options_<?php echo $prefix; ?>max_width"
        id="options_<?php echo $prefix; ?>max_width"
        value="<?php echo $this->options[$prefix."max_width"]; ?>"
      />
    </p>
    <p>
      <label for="options_<?php echo $prefix; ?>max_height">Max height:</label>
      <input
        class="tooltip required"
        title="Image maximum height in pixels."
        type="text"
        name="options_<?php echo $prefix; ?>max_height"
        id="options_<?php echo $prefix; ?>max_height"
        value="<?php echo $this->options[$prefix."max_height"]; ?>"
      />
    </p>
    <p>
      <label for="options_<?php echo $prefix; ?>thumb_width">Thumb max width:</label>
      <input
        class="tooltip required"
        title="Thumbnail maximum width in pixels."
        type="text"
        name="options_<?php echo $prefix; ?>thumb_width"
        id="options_<?php echo $prefix; ?>thumb_width"
        value="<?php echo $this->options[$prefix."thumb_width"]; ?>"
      />
    </p>
    <p>
      <label for="options_<?php echo $prefix; ?>thumb_height">Thumb max height:</label>
      <input
        class="tooltip required"
        title="Thumbnail maximum height in pixels."
        type="text"
        name="options_<?php echo $prefix; ?>thumb_height"
        id="options_<?php echo $prefix; ?>thumb_height"
        value="<?php echo $this->options[$prefix."thumb_height"]; ?>"
      />
    </p>
    <p>
      <label for="options_<?php echo $prefix; ?>imgcomp">Image compression:</label>
      <input
        class="tooltip required"
        title="0 best quality | 1 worst quality."
        type="text"
        name="options_<?php echo $prefix; ?>imgcomp"
        id="options_<?php echo $prefix; ?>imgcomp"
        value="<?php echo $this->options[$prefix."imgcomp"]; ?>"
      />
    </p>
  </fieldset>

  <fieldset>
    <legend>Video</legend>
  </fieldset>

  <fieldset>
    <legend>Audio</legend>
  </fieldset>

  <fieldset>
    <legend>Other</legend>
  </fieldset>

  <p>
    <input type="button" value="&larr; Back" onclick="window.history.back();" />
    <input type="submit" value="Save &rarr;" />
  </p>

  <input type="hidden" name="controller" value="plugins" />
  <input type="hidden" name="action" value="save_options" />
</form>

        <?php
        return ob_get_clean();
    }
}
