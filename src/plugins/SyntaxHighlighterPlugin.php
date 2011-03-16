<?php
/**
 * src/plugins/SyntaxHighlighterPlugin.php
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
 * SyntaxHighlighterPlugin class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class SyntaxHighlighterPlugin extends AbstractPlugin
{
    private $_langs, $_themes, $_load_assets = false;

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

        $this->_langs = array(
            "bash"   => "shBrushBash.js",
            "cpp"    => "shBrushCpp.js",
            "css"    => "shBrushCss.js",
            "diff"   => "shBrushDiff.js",
            "erlang" => "shBrushErlang.js",
            "html"   => "shBrushXml.js",
            "js"     => "shBrushJScript.js",
            "php"    => "shBrushPhp.js",
            "plain"  => "shBrushPlain.js",
            "python" => "shBrushPython.js",
            "ruby"   => "shBrushRuby.js",
            "sql"    => "shBrushSql.js",
            "xml"    => "shBrushXml.js"
        );

        $this->_themes = array(
            "Default", "Django", "Eclipse", "Emacs",
            "FadeToGrey", "MDUltra", "Midnight", "RDark"
        );
    }

    /**
     * Install plugin.
     *
     * @return void
     * @since  1.0
     */
    public function install()
    {
        $this->options[$this->getOptionsPrefix()."version"] = "1.0";
    }

    public function preApplyTheme()
    {
        $document = $this->app()->response()->document();
        if (!$document instanceof PHPFrame_HTMLDocument
            || $this->app()->request()->ajax()
            || !preg_match("/<pre\s+class=\"brush:/", $document->body())
        ) {
            return;
        }

        // remember that we need to load assets in postApplyTheme, so we don't
        // need test conditions again
        $this->_load_assets = true;

        $theme = $this->options[$this->getOptionsPrefix()."theme"];
        $css_url = $this->app()->config()->get("base_url")."assets/css/";
        $document->addStyleSheet($css_url."syntaxhighlighter/".$theme.".min.css");
    }

    public function postApplyTheme()
    {
        if (!$this->_load_assets) {
            return;
        }

        $document = $this->app()->response()->document();
        $prefix   = $this->getOptionsPrefix();
        $options  = $this->options->filterByPrefix($prefix);
        $base_url = $this->app()->config()->get("base_url");

        ob_start();
        ?>
<script src="<?php echo $base_url; ?>assets/js/syntaxhighlighter/sh.min.js"></script>
<script>
jQuery(document).ready(function() {
  SyntaxHighlighter.autoloader(
<?php
$count = 0;
foreach ($this->_langs as $lang=>$script) {
    if (in_array($lang, $options["langs"])) {
        if ($count > 0) echo ",\n";
        echo "    '".$lang." assets/js/syntaxhighlighter/".$script."'";
        $count++;
    }
}
?>
  );

  SyntaxHighlighter.all();
});
</script>

        <?php
        $document->appendBody(ob_get_clean());
    }

    public function displayOptionsForm()
    {
        $prefix  = $this->getOptionsPrefix();
        $options = $this->options->filterByPrefix($prefix);

        ob_start();
        ?>

<form class="validate" action="index.php" method="post">
  <fieldset>
    <legend>Highlighter options</legend>
    <p>
      <label for="options_<?php echo $prefix; ?>langs">Languages:</label>
<?php foreach (array_keys($this->_langs) as $lang) : ?>
      <?php echo $lang; ?>
      <input
        type="checkbox"
        name="options_<?php echo $prefix; ?>langs[]"
        value="<?php echo $lang; ?>"
        <?php if (in_array($lang, $options["langs"])) echo "checked"; ?>
      />
      <br />
<?php endforeach; ?>
    </p>

    <p>
      <label for="options_<?php echo $prefix; ?>theme">Theme:</label>
      <select
        class="tooltip required"
        title="This is the syntax highlighter theme used to render pre tags"
        name="options_<?php echo $prefix; ?>theme"
        id="options_<?php echo $prefix; ?>theme"
      />
<?php foreach ($this->_themes as $theme) : ?>
        <option value="<?php echo $theme; ?>" <?php if ($theme == $options["theme"]) echo "selected"; ?>>
          <?php echo $theme; ?>
        </option>
<?php endforeach; ?>
      </select>
    </p>
  </fieldset>

  <p>
    <input type="button" value="&larr; Back" onclick="window.history.back();" />
    <input type="submit" value="Save &rarr;" />
  </p>

  <input type="hidden" name="controller" value="plugins" />
  <input type="hidden" name="action" value="save_options" />
</form>

<p>
This plugin uses
<a target="_blank" href="http://alexgorbatchev.com/SyntaxHighlighter/">Alex Gorbatchev's JavaScript SyntaxHighlighter</a>.
</p>

        <?php
        return ob_get_clean();
    }
}

