<?php
/**
 * src/models/users/OAuthToken.php
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
 * OAuthToken class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class OAuthToken extends PHPFrame_PersistentObject
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
            "consumer_key",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>30, "max_length"=>30))
        );
        $this->addField(
            "type",
            null,
            false,
            new PHPFrame_EnumFilter(array("enums"=>array(
                "request",
                "access"
            )))
        );
        $this->addField(
            "status",
            "active",
            false,
            new PHPFrame_EnumFilter(array("enums"=>array(
                "active",
                "used",
                "revoked"
            )))
        );
        $this->addField(
            "callback",
            null,
            true,
            new PHPFrame_StringFilter(array("min_length"=>5, "max_length"=>100))
        );

        parent::__construct($options);

        // Make group ownership belong to staff and make object writable both
        // for owner (the customer) and the group (staff)
        $this->group(0);
        $this->perms(440);

        if (!is_array($options) || !array_key_exists("id", $options)) {
            $this->_generateKeyAndSecret();
        }
    }

    /**
     * Generate new key and shared secret.
     *
     * @return void
     * @since  1.0
     */
    private function _generateKeyAndSecret()
    {
        $crypt = new PHPFrame_Crypt();
        $hash  = $crypt->genRandomPassword(40);

        // The first 30 bytes should be plenty for the consumer_key
        // We use the last 10 for the shared secret
        $this->key(substr($hash, 0, 30));
        $this->secret(substr($hash, 30, 10));
    }
}
