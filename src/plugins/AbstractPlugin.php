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
 * All plugins in Mashine will need to descend from this class. This class
 * handles acquiring the Options, Hooks and ShortCodes objects. References to
 * these objects are then stored in the request object so that they can be
 * accessed from other places, normally controllers.
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

        if (!($this->hooks() instanceof Hooks)) {
            $app->request()->param("_hooks", new Hooks());
        }

        if (!($this->options() instanceof Options)) {
            $app->request()->param("_options", new Options($app->db()));
        }

        if (!($this->shortCodes() instanceof ShortCodes)) {
            $app->request()->param("_short_codes", new ShortCodes());
        }

        if (!$this->options[$this->getOptionsPrefix()."version"]) {
            $this->install();
        }
    }

    /**
     * Magic method to allow options() method to be used as a property so that
     * we can use array syntax on $this->options.
     *
     * @param string The name of the undefined property we are trying to access.
     *
     * @return Options|null
     * @since  1.0
     */
    final public function __get($name)
    {
        if ($name == "options") {
            return $this->options();
        }
    }

    /**
     * Get reference to Hooks object stored in request.
     *
     * @return Hooks|null
     * @since  1.0
     */
    final public function hooks()
    {
        return $this->app()->request()->param("_hooks");
    }

    /**
     * Get reference to Options object stored in request.
     *
     * @return Options|null
     * @since  1.0
     */
    final public function options()
    {
        return $this->app()->request()->param("_options");
    }

    /**
     * Get reference to ShortCodes object stored in request.
     *
     * @return ShortCodes|null
     * @since  1.0
     */
    final public function shortCodes()
    {
        return $this->app()->request()->param("_short_codes");
    }

    /**
     * Get prefix used to avoid name collisions.
     *
     * @return string
     * @since  1.0
     */
    public function getOptionsPrefix()
    {
        return strtolower(get_class($this))."_";
    }

    /**
     * Install plugin.
     *
     * @return void
     * @since  1.0
     */
    abstract public function install();
}
