<?php
/**
 * src/models/oauth/OAuthACLMapper.php
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
 * OAuthACL mapper class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class OAuthACLMapper extends PHPFrame_Mapper
{
    /**
     * Constructor.
     *
     * @param PHPFrame_Database $db Instance of PHPFrame_Database.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Database $db)
    {
        parent::__construct("OAuthACL", $db, "#__oauth_acl");
    }

    public function findByToken($token)
    {
        $id_obj = $this->getIdObject();
        $id_obj->where("token", "=", ":token");
        $id_obj->params(":token", $token);

        $collection = parent::find($id_obj);
        if (count($collection) > 0) {
            return $collection->current();
        }

        return null;
    }
}