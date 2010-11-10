<?php
/**
 * src/models/Hooks.php
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
 * The Hooks class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class Hooks
{
    private $_actions = array(
        "dashboard_boxes",
        "login_form",
        "post_footer",
        "posts_footer"
    );
    private $_callbacks;

    /**
     * Constructor.
     *
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        $this->_callbacks = array();
    }

    /**
     * Get array of registered actions.
     *
     * @return array
     * @since  1.0
     */
    public function getActions()
    {
        return $this->_actions;
    }

    /**
     * Add a callback function to a given action. Note that the action needs
     * to have been registered before we add callbacks to it.
     *
     * @param string       $action   The action for which to add the callback.
     * @param string|array $callback The callback function or method.
     * @param int          $priority [Optional]
     *
     * @return void
     * @since  1.0
     */
    public function addCallBack($action, $callback, $priority=0)
    {
        if (!in_array($action, $this->_actions)) {
            $msg = "Unknown action hook '".$action."'!";
            throw new InvalidArgumentException($msg);
        }

        $this->_callbacks[] = array($action, $callback, (int) $priority);
    }

    /**
     * Do named action. This will cause all callbacks associated with the action
     * to be run.
     *
     * @param string $action The action to trigger.
     * @param array  $args   [Optional] Array containing arguments to be passed
     *                       to callback.
     *
     * @return array An array containing the output for each of the callbacks.
     * @since  1.0
     */
    public function doAction($action, array $args=null)
    {
        $array = array();

        foreach ($this->_callbacks as $item) {
            if ($item[0] == $action) {
                $array[] = $item;
            }
        }

        $output = array();
        foreach ($array as $item) {
            $callback = $item[1];
            if (is_array($callback) && is_object($callback[0])) {
                $output[] = $callback[0]->$callback[1]($args);
            } elseif (is_array($callback) && is_string($callback[0])) {
                $output[] = $callback[0]."::".$callback[1]($args);
            } else {
                $output[] = $callback($args);
            }
        }

        return $output;
    }
}

