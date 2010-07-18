<?php
/**
 * src/models/users/ContactsMapper.php
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
 * Users contacts mapper class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class ContactsMapper extends PHPFrame_Mapper
{
    private $_db;

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
        $this->_db = $db;

        parent::__construct("Contact", $db, "#__contacts");
    }

    /**
     * Find contacts by owner.
     *
     * @param int $user_id The user id for which to get the contacts.
     *
     * @return PHPFrame_PersistentObjectCollection
     * @since  1.0
     */
    public function findByOwner($user_id)
    {
        $id_obj = $this->getIdObject();
        $id_obj->where("owner", "=", ":owner");
        $id_obj->params(":owner", $user_id);

        $collection = $this->find($id_obj);

        if (count($collection) > 0) {
            return $collection;
        }

        return null;
    }

    public function insert(Contact $obj)
    {
        if ($obj->preferred()) {
            $sql = "UPDATE #__contacts SET preferred = 0 WHERE owner = :owner";
            $this->_db->query($sql, array(":owner"=>$obj->owner()));
        }

        // Make preferred if no other contact exists for user
        $sql  = "SELECT id FROM #__contacts WHERE preferred = '1' AND owner = :owner";
        $pref = $this->_db->fetchColumn($sql, array(":owner"=>$obj->owner()));
        if (!$pref) {
            $obj->preferred(true);
        }

        parent::insert($obj);
    }
}
