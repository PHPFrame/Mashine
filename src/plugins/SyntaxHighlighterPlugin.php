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
 * @link      https://github.com/lupomontero/Mashine
 */

/**
 * SyntaxHighlighterPlugin class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class SyntaxHighlighterPlugin extends AbstractPlugin
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

        if ($document instanceof PHPFrame_HTMLDocument) {
            $css_url = $this->app()->config()->get("base_url")."assets/css/";
            $document->addStyleSheet($css_url."syntaxhighlighter/shCore.css");
            $document->addStyleSheet($css_url."syntaxhighlighter/shThemeRDark.css");
        }
    }

    public function postApplyTheme()
    {
        $base_url = $this->app()->config()->get("base_url");
        $document = $this->app()->response()->document();

        if (!$document instanceof PHPFrame_HTMLDocument) {
            return;
        }

        ob_start();
        ?>

<script src="<?php echo $base_url; ?>assets/js/syntaxhighlighter/shCore.js"></script>
<script src="<?php echo $base_url; ?>assets/js/syntaxhighlighter/shAutoloader.js"></script>

<script>
jQuery(document).ready(function() {
  SyntaxHighlighter.autoloader(
    'js jscript javascript assets/js/syntaxhighlighter/shBrushJScript.js',
    'php assets/js/syntaxhighlighter/shBrushPhp.js',
    'css assets/js/syntaxhighlighter/shBrushCss.js',
    'bash shell assets/js/syntaxhighlighter/shBrushBash.js',
    'plain assets/js/syntaxhighlighter/shBrushPlain.js',
    'python assets/js/syntaxhighlighter/shBrushPython.js',
    'ruby assets/js/syntaxhighlighter/shBrushRuby.js',
    'sql assets/js/syntaxhighlighter/shBrushSql.js',
    'xml html assets/js/syntaxhighlighter/shBrushXml.js'
  );

  SyntaxHighlighter.all();
});
</script>

        <?php
        $document->appendBody(ob_get_clean());
    }
}

