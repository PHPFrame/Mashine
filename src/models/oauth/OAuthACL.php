<?php
/**
 * src/models/users/OAuthACL.php
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
 * OAuthACL class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class OAuthACL extends PHPFrame_PersistentObject
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
            "client_id",
            null,
            false,
            new PHPFrame_IntFilter()
        );
        $this->addField(
            "user_id",
            null,
            false,
            new PHPFrame_IntFilter()
        );
        $this->addField(
            "resource",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>1, "max_length"=>255))
        );
        $this->addField(
            "token",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>30, "max_length"=>30))
        );

        parent::__construct($options);

        // Make group ownership belong to staff and make object writable both
        // for owner (the customer) and the group (staff)
        $this->group(0);
        $this->perms(440);
    }
}
