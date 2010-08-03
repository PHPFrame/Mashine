<?php
/**
 * src/plugins/AbstractPlugin.php
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
 * Abstract Plugin class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
abstract class AbstractPlugin extends PHPFrame_Plugin
{
    protected $options, $hooks;

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

        $this->options = $app->request()->param(
            "mashine_options",
            new Options($app->db())
        );

        $this->hooks = MashinePlugin::hooks();

        if (!$this->options[$this->getOptionsPrefix()."version"]) {
            $this->install();
        }
    }

    public function getOptionsPrefix()
    {
        return strtolower(get_class($this))."_";
    }

    abstract public function install();
}
