<?php
/**
 * src/controllers/api/users.php
 *
 * PHP version 5
 *
 * @category   PHPFrame_Applications
 * @package    Mashine
 * @subpackage ApiControllers
 * @author     Lupo Montero <lupo@e-noise.com>
 * @copyright  2010 E-NOISE.COM LIMITED
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://github.com/E-NOISE/Mashine
 */

/**
 * Users API controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class UsersApiController extends PHPFrame_RESTfulController
{
    private $_mapper, $_contacts_mapper;

    /**
     * Constructor.
     *
     * @param PHPFrame_Application $app Instance of application.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app);
    }

    /**
     * Get user(s).
     *
     * @param int $id    [Optional] if specified a single user will be returned.
     * @param int $limit [Optional] Default value is 10.
     * @param int $page  [Optional] Default value is 1.
     *
     * @return array|object Either a single user object or an array containing
     *                      user objects.
     * @since  1.0
     */
    public function get($id=null, $limit=10, $page=1)
    {
        if (empty($id)) {
            $id = null;
        }

        if (empty($limit)) {
            $limit = 10;
        }

        if (empty($page)) {
            $page = 1;
        }

        if (!is_null($id)) {
            $ret = $this->_fetchUser($id);
        } else {
            $id_obj = $this->_getUsersMapper()->getIdObject();
            $id_obj->limit($limit, ($page-1)*$limit);
            $ret = $this->_getUsersMapper()->find($id_obj);
        }

        return $this->handleReturnValue($ret);
    }

    /**
     * Save User passed in POST. If no 'id' is passed in request a new User
     * object will be created, otherwise the existing user with a matching 'id'
     * will be updated.
     *
     * @param int    $id            [Optional]
     * @param int    $group_id      [Optional]
     * @param string $email         [Optional]
     * @param string $password      [Optional]
     * @param string $status        [Optional]
     * @param int    $notifications [Optional]
     *
     * @return object The user object after saving it.
     * @since  1.0
     */
    public function post(
        $id=0,
        $group_id=0,
        $email=null,
        $password=null,
        $status=null,
        $notifications=null
    ) {
        $id       = filter_var($id, FILTER_VALIDATE_INT);
        $group_id = filter_var($group_id, FILTER_VALIDATE_INT);
        $is_auth  = $this->session()->isAuth();
        $is_staff = ($is_auth && $this->user()->groupId() < 3);
        $crypt    = $this->crypt();

        if (!is_int($id) || $id <= 0) {
            $user = new User();
            $this->_ensureUniqueEmail($email);
            $user->email($email);
            $user->activation($crypt->genRandomPassword(32));

            if (!$group_id) {
                $user->groupId(3);
            } elseif ($is_staff || $group_id > 2) {
                $user->groupId($group_id);
            } else {
                $msg = "Permission denied.";
                throw new InvalidArgumentException($msg, 401);
            }

            if (!$password) {
                if (!$is_staff) {
                    throw new InvalidArgumentException("Password is required", 401);
                }

                $password = $crypt->genRandomPassword(8);
                $this->request()->param("password", $password);
            }

        } else {
            $user = $this->_fetchUser($id, true);

            if ($group_id && $is_staff) {
                $user->groupId($group_id);
            }

            if ($email != $user->email()) {
                $this->_ensureUniqueEmail($email);
                $user->email($email);
            }
        }

        if ($password) {
            //TODO: Check password format

            // Encrypt password and store along with salt
            $salt      = $crypt->genRandomPassword(32);
            $encrypted = $crypt->encryptPassword($password, $salt);
            $user->password($encrypted.":".$salt);
        }

        if ($notifications) {
            $user->notifications($notifications);
        }

        // Add 'registered' as secondary group to every other group except
        // wheel and the 'registered' group itself.
        if ($user->groupId() == 2 || $user->groupId() > 3) {
            $user->params(array("secondary_groups"=>3));
        }

        // Update user object in session if editing own profile
        if ($user->isDirty()
            && $user->id() == $this->session()->getUser()->id()
        ) {
            $this->session()->setUser($user);
        }

        // Save the user object in the database
        $this->_getUsersMapper()->insert($user);

        return $this->handleReturnValue($user);
    }

    /**
     * Delete user.
     *
     * @param int $id The contact id.
     *
     * @return void
     * @since  1.0
     */
    public function delete($id)
    {
        $this->ensureIsStaff();
        $user = $this->_fetchUser($id, true);
        $this->_getUsersMapper()->delete($user);

        return $this->handleReturnValue(true);
    }

    /**
     * Search users and return 'name' and 'id' in JSON format to be used by
     * AJAX autocomplete.
     *
     * @param string $s The search string.
     *
     * @return void
     * @since  1.0
     */
    public function search($s)
    {
        $this->ensureIsStaff();

        $sql  = "SELECT u.id, u.email, u.status, c.first_name, c.last_name ";
        $sql .= "FROM #__users AS u ";
        $sql .= "LEFT OUTER JOIN #__contacts c ON c.owner = u.id ";
        $sql .= "WHERE (u.email LIKE :s OR c.first_name LIKE :s ";
        $sql .= "OR c.last_name LIKE :s OR c.org_name LIKE :s) ";
        $sql .= "GROUP BY u.id ";
        $sql .= "LIMIT 0,10";

        $params = array(":s"=>"%".$s."%");
        $raw = $this->db()->fetchAssocList($sql, $params);
        $ret = array();
        foreach ($raw as $row) {
            if (!empty($row["first_name"])) {
                $label = $row["first_name"]." ".$row["last_name"];
            } else {
                $label = $row["email"];
            }
            $ret[] = array("label"=>$label, "value"=>$row["id"]);
        }

        return $this->handleReturnValue($ret);
    }

    /**
     * Ensure that email is not yet registered.
     *
     * @param string $email
     *
     * @return void
     * @since  1.0
     */
    private function _ensureUniqueEmail($email)
    {
        if (count($this->_getUsersMapper()->findByEmail($email)) > 0) {
            $msg = sprintf(
                UserLang::ERROR_EMAIL_ALREADY_REGISTERED,
                $email
            );

            throw new Exception($msg);
        }
    }

    /**
     * Fetch a user by ID and check read access.
     *
     * @param int  $id The user id.
     * @param bool $w  [Optional] Ensure write access? Default is FALSE.
     *
     * @return User
     * @since  1.0
     */
    private function _fetchUser($id, $w=false)
    {
        return $this->fetchObj($this->_getUsersMapper(), $id, $w);
    }

    /**
     * Get instance of UsersMapper.
     *
     * @return UsersMapper
     * @since  1.0
     */
    private function _getUsersMapper()
    {
        if (is_null($this->_mapper)) {
            $this->_mapper = new UsersMapper($this->db());
        }

        return $this->_mapper;
    }
}
