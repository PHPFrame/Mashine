<?php
/**
 * src/plugins/GoogleAnalyticsPlugin.php
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
 * Google Analytics Plugin class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class GoogleAnalyticsPlugin extends AbstractPlugin
{
    private $_options;

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

    public function getOptionsPrefix()
    {
        return "gaplugin_";
    }

    public function postApplyTheme()
    {
        $document = $this->app()->response()->document();
        if (!$document instanceof PHPFrame_HTMLDocument
            || $this->app()->request()->ajax()
        ) {
            return;
        }

        $tracker_code = "
<script type=\"text/javascript\">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '".$this->options[$this->getOptionsPrefix()."web_property_id"]."']);
    _gaq.push(['_trackPageview']);

    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
</script>";

        $document->body($document->body().$tracker_code);
    }

    public function displayOptionsForm()
    {
        $prefix = $this->getOptionsPrefix();

        ob_start();
        ?>

<form class="validate" action="index.php" method="post">
  <fieldset>
    <legend>Google Analytics details</legend>
    <p>
      <label>Web Property ID:</label>
      <input
        class="tooltip required"
        title="Something like: UA-XXXXX-X"
        type="text"
        name="options_<?php echo $prefix; ?>web_property_id"
        value="<?php echo $this->options[$prefix."web_property_id"]; ?>"
      />
    </p>
    <p>
      <input type="button" value="&larr; Back" onclick="window.history.back();" />
      <input type="submit" value="Save &rarr;" />
    </p>
  </fieldset>
  <p>
    <input type="hidden" name="controller" value="plugins" />
    <input type="hidden" name="action" value="save_options" />
  </p>
</form>

        <?php
        return ob_get_clean();
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
}
