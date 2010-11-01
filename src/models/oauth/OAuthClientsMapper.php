<?php
/**
 * src/models/oauth/OAuthClientsMapper.php
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
 * OAuthClients mapper class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class OAuthClientsMapper extends PHPFrame_Mapper
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
        parent::__construct("OAuthClient", $db, "#__oauth_clients");
    }

    /**
     * Find an OAuth client by key.
     *
     * @param string $key The 30 char long consumer key.
     *
     * @return OAuthClient|null
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

    /**
     * This method overrides the default implementation to generate a key and
     * shared secret when saving new objects.
     *
     * @param OAuthClient $oauth_client Instance of OAuthClient to store.
     *
     * @return void
     * @since  1.0
     */
    public function insert(OAuthClient $oauth_client)
    {
        if (!$oauth_client->id()) {
            $token = new OAuthToken();
            $oauth_client->key($token->key());
            $oauth_client->secret($token->secret());
        }

        return parent::insert($oauth_client);
    }
}
