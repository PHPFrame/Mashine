<?php
/**
 * src/models/ShortCodes.php
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
 * ShortCodes class.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class ShortCodes
{
    private $_short_codes = array();

    /**
     * Add a short code.
     *
     * @param string       $keyword  The shortcode keyword.
     * @param string|array $callback The function to be called. Class methods
     *                               may also be invoked statically using this
     *                               function by passing array($classname,
     *                               $methodname) to this parameter. Additionally
     *                               class methods of an object instance may be
     *                               called by passing array($objectinstance,
     *                               $methodname) to this parameter.
     *
     * @return void
     * @since  1.0
     */
    public function add($keyword, $callback)
    {
        $this->_short_codes[$keyword] = $callback;
    }

    /**
     * Get array containing registered shortcode keywords.
     *
     * @return array
     * @since  1.0
     */
    public function getKeywords()
    {
        return array_keys($this->_short_codes);
    }

    /**
     * Call registered callback for given keyword passing the attributes array.
     *
     * @param string $keyword The shortcode keyword.
     * @param array  $attr    Associative array containing shortcode attributes.
     *
     * @return string All callbacks should return a string that will be used to
     *                in replacement of the shortcode in the final output of the
     *                application.
     * @since  1.0
     */
    public function call($keyword, $attr)
    {
        if (!array_key_exists($keyword, $this->_short_codes)) {
            $msg = "Unknown shortcode keyword!";
            throw new InvalidArgumentException($msg);
        }

        return call_user_func($this->_short_codes[$keyword], $attr);
    }
}
