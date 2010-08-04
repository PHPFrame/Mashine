<?php
/**
 * src/controllers/api/users.php
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
 * Users API controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
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

        if (!$this->session()->isAuth()) {
            $msg = "Permission denied.";
            throw new Exception($msg, 401);
        }
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

        $this->response()->body($ret);
    }

    /**
     * Save User passed in POST.
     *
     * @param int    $id      [Optional]
     * @param string $ret_url [Optional]
     *
     * @return object The user object after saving it.
     * @since  1.0
     */
    public function post($id=null, $group_id=null, $password=null)
    {
        $request  = $this->request();
        $email    = $request->param("email");
        $id       = filter_var($id, FILTER_VALIDATE_INT);
        $group_id = filter_var($group_id, FILTER_VALIDATE_INT);
        $is_staff = ($this->session()->isAuth() && $this->user()->groupId() < 3);
        $crypt    = $this->crypt();

        if (!is_int($id) || $id <= 0) {
            $user = new User();
            $user->email($email);

            if (!$group_id) {
                $user->groupId(3);
            } elseif ($is_staff || $group_id > 2) {
                $user->groupId($group_id);
            } else {
                $msg = "Permission denied.";
                throw new InvalidArgumentException($msg, 401);
            }

            if (!$password && $is_staff) {
                $password = $crypt->genRandomPassword(8);
                $request->param("password", $password);
            } else {
                $password = $request->param("password");
            }

            // Encrypt password and store along with salt
            $salt      = $crypt->genRandomPassword(32);
            $encrypted = $crypt->encryptPassword($password, $salt);

            $user->password($encrypted.":".$salt);
            $user->activation($crypt->genRandomPassword(32));
            $user->group(2);
            $user->perms(664);

        } else {
            $user = $this->_fetchUser($id, true);

            if ($group_id && $is_staff) {
                $user->groupId($group_id);
            }

            $user->email($request->param("email"));

            // Update password if needed
            $password = $request->param("password");
            if ($password) {
                // Encrypt password and store along with salt
                $salt      = $crypt->genRandomPassword(32);
                $encrypted = $crypt->encryptPassword($password, $salt);
                $user->password($encrypted.":".$salt);
            }
        }

        $this->_ensureUniqueEmail($email);

        // Add 'registered' as secondary group to every other group except
        // wheel and the 'registered' group itself.
        if ($user->groupId() == 2 || $user->groupId() > 3) {
            $user->params(array("secondary_groups"=>3));
        }

        // Save the user object in the database
        $this->_getUsersMapper()->insert($user);

        $this->response()->body($user);
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
        $user = $this->_fetchUser($id, true);
        $this->ensureIsStaff();

        $this->_getUsersMapper()->delete($user);

        $this->response()->body(true);
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

        $this->response()->body($ret);
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
