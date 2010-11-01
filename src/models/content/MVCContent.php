<?php
/**
 * src/models/content/MVCContent.php
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
 * MVC Content class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class MVCContent extends Content
{
    public function __construct(array $options=null)
    {
        if (is_null($options)) {
            $options = array();
        }

        if (!array_key_exists("robots_index", $options)) {
            $options["robots_index"] = false;
        }

        if (!array_key_exists("robots_follow", $options)) {
            $options["robots_follow"] = false;
        }

        parent::__construct($options);
    }

    /**
     * Get array containing subtype parameter definition.
     *
     * @return array
     * @since  1.0
     */
    public function getParamKeys()
    {
        $array = array(
            "controller" => array(
                "def_value"  => null,
                "allow_null" => false,
                "filter"     => new PHPFrame_StringFilter(array(
                    "min_length" => 2,
                    "max_length" => 50
                ))
            ),
            "action" => array(
                "def_value"  => null,
                "allow_null" => true,
                "filter"     => new PHPFrame_StringFilter(array(
                    "min_length" => 2,
                    "max_length" => 50
                ))
            ),
            "params" => array(
                "def_value"  => null,
                "allow_null" => true,
                "filter"     => new PHPFrame_StringFilter()
            )
        );

        return array_merge(parent::getParamKeys(), $array);
    }
}
