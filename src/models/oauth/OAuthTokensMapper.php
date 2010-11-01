<?php
/**
 * src/models/oauth/OAuthTokensMapper.php
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
 * OAuthTokens mapper class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class OAuthTokensMapper extends PHPFrame_Mapper
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
        parent::__construct("OAuthToken", $db, "#__oauth_tokens");
    }

    /**
     * Find an OAuth client by key.
     *
     * @param string $key The 30 char long consumer key.
     *
     * @return OAuthToken|null
     * @since  1.0
     */
    public function findByKey($key)
    {
        $id_obj = $this->getIdObject();
        $id_obj->where("`key`", "=", ":key");
        $id_obj->params(":key", $key);

        $collection = parent::find($id_obj);
        if (count($collection) > 0) {
            return $collection->current();
        }

        return null;
    }
}
