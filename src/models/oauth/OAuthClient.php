<?php
/**
 * src/models/users/OAuthClient.php
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
 * OAuthClient class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class OAuthClient extends PHPFrame_PersistentObject
{
    /**
     * Constructor.
     *
     * @param array $options [Optional]
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->addField(
            "name",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>3, "max_length"=>50))
        );
        $this->addField(
            "version",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>1, "max_length"=>20))
        );
        $this->addField(
            "vendor",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>3, "max_length"=>100))
        );
        $this->addField(
            "key",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>30, "max_length"=>30))
        );
        $this->addField(
            "secret",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>10, "max_length"=>10))
        );
        $this->addField(
            "hosted",
            false,
            false,
            new PHPFrame_BoolFilter()
        );
        $this->addField(
            "status",
            "active",
            false,
            new PHPFrame_EnumFilter(array("enums"=>array(
                "active",
                "throttled",
                "blacklisted"
            )))
        );

        parent::__construct($options);

        // Make group ownership belong to staff and make object writable both
        // for owner (the customer) and the group (staff)
        $this->group(2);
        $this->perms(660);
    }
}
