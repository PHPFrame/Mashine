<?php
/**
 * src/models/CMSHooks.php
 *
 * PHP version 5
 *
 * @category  PHPFrame_AppTemplates
 * @package   PHPFrame_CmsAppTemplate
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/lupomontero/PHPFrame_CmsAppTemplate
 */

/**
 * CMSHooks class
 *
 * @category PHPFrame_AppTemplates
 * @package  PHPFrame_CmsAppTemplate
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/PHPFrame_CmsAppTemplate
 * @since    1.0
 */
class CMSHooks
{
    private $_actions = array(
        "dashboard_boxes",
        "login_form"
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

    public function getActions()
    {
        return $this->_actions;
    }

    public function addCallBack($action, $callback, $priority=0)
    {
        if (!in_array($action, $this->_actions)) {
            $msg = "Unknown action hook '".$action."'!";
            throw new InvalidArgumentException($msg);
        }

        $this->_callbacks[] = array($action, $callback, (int) $priority);
    }

    public function doAction($action)
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
                $output[] = $callback[0]->$callback[1]();
            } elseif (is_array($callback) && is_string($callback[0])) {
                $output[] = $callback[0]."::".$callback[1]();
            } else {
                $output[] = $callback();
            }
        }

        return $output;
    }
}
