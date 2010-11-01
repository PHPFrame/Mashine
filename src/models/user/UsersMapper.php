<?php
/**
 * src/models/users/UsersMapper.php
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
 * Users mapper class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class UsersMapper extends PHPFrame_CompositeMapper
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
        parent::__construct(
            "User",
            $db,
            "#__users",
            array(
                array(
                    "contacts",
                    "Contact",
                    "#__contacts",
                    "owner"
                )
            )
        );
    }

    /**
     * Create a new IdObject to work with the target class
     *
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function getIdObject()
    {
        $id_obj = parent::getIdObject();
        $id_obj->select("#__users.*, g.name AS group_name");
        $id_obj->join("LEFT JOIN #__groups g ON #__users.group_id = g.id");

        return $id_obj;
    }

    /**
     * Find an user object using by email.
     *
     * @param string $email             The email address to search for.
     * @param bool   $include_cancelled [Optional] Whether to include cancelled
     *                                  users or not. Default value is FALSE.
     *
     * @return User|null
     * @since  1.0
     */
    public function findByEmail($email, $include_cancelled=false)
    {
        $id_obj = $this->getIdObject();
        $id_obj->where("#__users.email", "=", ":email");
        $id_obj->params(":email", $email);

        if (!$include_cancelled){
            $id_obj->where('status', '<>', ':status');
            $id_obj->params(':status', 'cancelled');
        }

        $collection = $this->find($id_obj);
        if (count($collection) > 0) {
            $collection->rewind();
            return $collection->current();
        }

        return null;
    }

    /**
     * Insert/update object in db.
     *
     * @param User $obj The object to store.
     *
     * @return void
     * @since  1.0
     */
    public function insert(User $obj)
    {
        $is_new = (!is_int($obj->id()) || $obj->id() <= 0);

        parent::insert($obj);

        if ($is_new) {
            $obj->owner($obj->id());
            parent::insert($obj);
        }
    }

    /**
     * Count users.
     *
     * @param string $status Filter count by status.
     *
     * @return int
     * @since  1.0
     */
    public function count($status=null)
    {
        $enums = array(null, "pending", "active", "suspended", "cancelled");
        if (!in_array($status, $enums)) {
            $msg  = "Unknown user status passed to ".get_class($this)."::";
            $msg .= __FUNCTION__."()";
            throw new InvalidArgumentException($msg);
        }

        $sql = "SELECT COUNT(id) FROM #__users";

        if ($status) {
            $sql   .= " WHERE status = :status";
            $params = array(":status"=>$status);

            return $this->getFactory()->getDB()->fetchColumn($sql, $params);

        } else {
            return $this->getFactory()->getDB()->fetchColumn($sql);
        }
    }
}
