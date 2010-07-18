<?php
/**
 * src/controllers/user.php
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
 * User controller.
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
     * @return void
     * @since  1.0
     */
    public function get($id=null)
    {
        if (!is_null($id)) {
            $ret = $this->_fetchUser($id);
        } else {
            $ret = $this->_getUsersMapper()->find();
        }

        $this->response()->body($ret);
    }

    /**
     * Request a password reset email to be sent to given address.
     *
     * @param string $forgot_email
     *
     * @return void
     * @since  1.0
     */
    public function reset($forgot_email)
    {
        $this->setRedirect($_SERVER["HTTP_REFERER"]);

        $email = filter_var($forgot_email, FILTER_VALIDATE_EMAIL);

        if ($email === false) {
            $msg = "Wrong email format.";
            $this->raiseError($msg);
            return;
        }

        try {
            $user = $this->_getUsersMapper()->findByEmail($email);

            if (!$user instanceof User) {
                $msg  = "We couldn't find any registered users with that ";
                $msg .= "email address";
                $this->raiseError($msg);
                return;
            }

            $body  = "Please click on the link below to verify that you ";
            $body .= "requested a new password and we will reset your ";
            $body .= "password and send it in another email.\n\n";
            $body .= $this->config()->get("base_url")."user/reset?token=";
            $body .= "SOME_TOKEN";

            $mailer = $this->mailer();
            $mailer->Subject = "Password reset request";
            $mailer->Body = $body;
            $mailer->AddAddress($user->email());
            $mailer->send();

            $msg = "An email has been sent with the password reset request.";
            $this->notifySuccess($msg);

        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
        }
    }

    /**
     * Save User passed in POST
     *
     * @param int    $id      [Optional]
     * @param string $ret_url [Optional]
     *
     * @return void
     * @since  1.0
     */
    public function post($id=null, $ret_url=null)
    {
        $base_url = $this->config()->get("base_url");
        $request  = $this->request();
        $email    = $request->param("email");
        $id       = filter_var($id, FILTER_VALIDATE_INT);

        try {
            if (!is_int($id) || $id <= 0) {
                // if new user it is signup or admin
                if (!$this->_ensureUniqueEmail($email, $ret_url)) {
                    return;
                }

                $new_user = true;
                $user = new User();
                $user->email($email);

                if ($this->session()->isAuth() && $this->user()->groupId() < 3) {
                    $user->groupId($request->param("group_id"));
                    $password = $this->crypt()->genRandomPassword(8);
                } else {
                    $user->groupId(3); // Initially set group to 'registered'
                    $password = $request->param("password");
                }

                // Encrypt password and store along with salt
                $salt      = $this->crypt()->genRandomPassword(32);
                $encrypted = $this->crypt()->encryptPassword($password, $salt);
                $user->password($encrypted.":".$salt);
                $user->activation($this->crypt()->genRandomPassword(32));

                // Store first time to generate id
                $this->_getUsersMapper()->insert($user);

                // Set ownership to itself once we have an id
                $user->owner($user->id());
                $user->group(2);
                $user->perms(664);

                // if contact details are passed we create contact
                $first_name = $request->param("first_name", null);
                if ($first_name) {
                    $contact = new Contact($this->request()->params());
                    $user->addContact($contact);
                    // If contact is being added we set group to 'customer'
                    $user->groupId(4);
                }

            } else {
                // update existing user details
                $new_user = false;

                if (!$user = $this->_fetchUser($id, true)) {
                    return;
                }

                if ($this->session()->isAuth() && $this->user()->groupId() < 3) {
                    $user->groupId($this->request()->param("group_id"));
                }

                $user->email($this->request()->param("email"));

                // Update password if needed
                $password = $this->request()->param("password");
                if ($password) {
                    // Encrypt password and store along with salt
                    $salt      = $this->crypt()->genRandomPassword(32);
                    $encrypted = $this->crypt()->encryptPassword($password, $salt);
                    $user->password($encrypted.":".$salt);
                }
            }

            // Add 'registered' as secondary group to every other group except
            // wheel and the 'registered' group itself.
            if ($user->groupId() == 2 || $user->groupId() > 3) {
                $user->params(array("secondary_groups"=>3));
            }

            // Save the user object in the database
            $this->_getUsersMapper()->insert($user);

            // Notify user if new signup
            $mailer = $this->mailer();
            if ($new_user && $mailer instanceof PHPFrame_Mailer) {
                $confirm_url  = $base_url."user/confirm?email=";
                $confirm_url .= urlencode($user->email());
                $confirm_url .= "&activation=".$user->activation();
                $email        = $user->email();

                if ($this->session()->isAuth() && $this->user()->groupId() < 3) {
                    $body = UserLang::NEW_USER_EMAIL_BODY;
                    $body = sprintf($body, $email, $password, $confirm_url);
                } else {
                    $name = $user->contact()->firstName();
                    $body = UserLang::SIGNUP_EMAIL_BODY;
                    $body = sprintf($body, $name, $email, $password, $confirm_url);
                }

                $mailer->Subject = UserLang::NEW_USER_EMAIL_SUBJECT;
                $mailer->Body    = $body;
                $mailer->AddAddress($user->email());
                if (!$mailer->send()) {
                    $this->raiseWarning($mailer->ErrorInfo);
                }

                $msg = sprintf(UserLang::NEW_USER_SUCCESS, $user->email());
                $this->notifyInfo($msg);

                // Automatically log in the new user signup
                if (!$this->session()->isAuth()) {
                    $this->session()->setUser($user);
                }

            } else {
                $this->notifySuccess(UserLang::UPDATE_USER_SUCCESS);
            }

            $ret_url = $request->param("ret_url", "index.php");
            $this->setRedirect($ret_url);

        } catch (Exception $e) {
            $this->raiseError($e->getMessage());

            if ($new_user) {
                if (!$ret_url) {
                    $ret_url  = "index.php?controller=user&action=signup";
                    $ret_url .= "&email=".$request->param("email");
                }

            } else {
                $ret_url = $_SERVER["HTTP_REFERER"];
            }

            $this->setRedirect($ret_url);
        }
    }

    /**
     * Confirm an email address.
     *
     * @param string $email      The email address to confirm.
     * @param string $activation The activation code sent previously via email.
     *
     * @return void
     * @since  1.0
     */
    public function confirm($email, $activation)
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);

        if ($email === false) {
            $msg = "Wrong email format.";
            $this->raiseError($msg);
            return;
        }

        try {
            $user = $this->_getUsersMapper()->findByEmail($email);

            if (!$user instanceof User) {
                $msg  = "We couldn't find any registered users with that ";
                $msg .= "email address";
                $this->raiseError($msg);
                return;
            }

            if ($user->activation() != $activation) {
                $this->raiseError("Wrong activation code.");
                return;
            }

            $user->activation(null);
            $user->status("active");
            $this->_getUsersMapper()->insert($user);
            $this->session()->setUser($user);

            $body  = "Your email address is now verified.";

            $mailer = $this->mailer();
            $mailer->Subject = "Email verified";
            $mailer->Body = $body;
            $mailer->AddAddress($user->email());
            $mailer->send();

            $msg = "Your email address is now verified.";
            $this->notifySuccess($msg);

            $base_url = $this->config()->get("base_url");
            $this->setRedirect($base_url."dashboard");

        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
        }
    }

    /**
     * Set user status.
     *
     * @param int    $id      The user id.
     * @param string $status  The new user status.
     * @param string $ret_url [Optional]
     *
     * @return void
     * @since  1.0
     */
    public function status($id, $status, $ret_url=null)
    {
        if (is_null($ret_url)) {
            $ret_url = $this->config()->get("base_url")."admin/user";
        }

        if (!$user = $this->_fetchUser($id, true)) {
            return;
        }

        if (!$this->ensureIsStaff()) {
            $msg = "Permission denied.";
            $this->raiseError($msg);
            $this->response()->statusCode(401);
            return;
        }

        try {
            $user->status($status);
            $this->_getUsersMapper()->insert($user);

            $this->notifySuccess("User status updated successfully.");

        } catch (Exception $e) {
            $this->raiseError("An error occurred while updating user status.");
            $this->response()->statusCode(501);
            return;
        }

        $this->setRedirect($ret_url);
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
        if (!$user = $this->_fetchUser($id, true)) {
            return;
        }

        if (!$this->ensureIsStaff()) {
            $msg = "Permission denied.";
            $this->raiseError($msg);
            $this->response()->statusCode(401);
            return;
        }

        try {
            $this->_getUsersMapper()->delete($user);

            $this->notifySuccess("User deleted successfully.");

        } catch (Exception $e) {
            $this->raiseError("An error occurred while deleting user.");
            $this->response()->statusCode(501);
            return;
        }

        $base_url = $this->config()->get("base_url");
        $this->setRedirect($base_url."admin/user");
    }

    /**
     * Save contact.
     *
     * @param int    $id      [Optional] The contact id.
     * @param string $ret_url [Optional] Return URL.
     *
     * @return void
     * @since  1.0
     */
    public function savecontact($id=null, $ret_url=null)
    {
        $base_url = $this->config()->get("base_url");

        if (is_null($ret_url)) {
            $ret_url = $this->config()->get("base_url")."profile";
        }

        if (!empty($id)) {
            $contact = $this->_fetchContact($id, true);

        } else {
            $contact = new Contact();
        }

        try {
            $params = $this->request()->params();
            unset($params["id"]);

            // Prevent non admins from saving as other users
            if (array_key_exists("owner", $params)
                && $params["owner"] != $this->user()->id()
            ) {
                $this->ensureIsStaff();
            }

            $contact->bind($params);

            $this->_getContactsMapper()->insert($contact);

            // If updating contact for current session's user we update the user in
            // the session
            if ($contact->owner() == $this->user()->id()) {
                $user = $this->_fetchUser($this->user()->id());
                $this->session()->setUser($user);
            }

            $this->notifySuccess("Contact saved!");

        } catch (Exception $e) {
            $this->raiseError("Error saving contact.");
        }

        $this->setRedirect($ret_url);
    }

    /**
     * Delete a contact.
     *
     * @param int $id The contact id.
     *
     * @return void
     * @since  1.0
     */
    public function deletecontact($id)
    {
        $base_url = $this->config()->get("base_url");
        $ret_url  = $base_url."profile";

        if (!$contact = $this->_fetchContact($id, true)) {
            return;
        }

        try {
            $this->_getContactsMapper()->delete($contact->id());

            // If updating contact for current session's user we update the user in
            // the session
            if ($contact->owner() == $this->user()->id()) {
                $user = $this->_fetchUser($this->user()->id());
                $this->session()->setUser($user);
            } else {
                $ret_url = $base_url."admin/user/form?id=".$contact->owner();
            }

            $this->notifySuccess("Contact deleted!");

        } catch (Exception $e) {
            $this->raiseError("Error deleting contact.");
        }

        $this->setRedirect($ret_url);
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

        $this->request()->ajax(true);
        $this->response()->document(new PHPFrame_PlainDocument());
        $this->response()->renderer(new PHPFrame_JSONRenderer());
        $this->response()->header("Content-Type", "application/json");
        $this->response()->body($ret);
    }

    /**
     * Ensure that email is not yet registered.
     *
     * @param string      $email
     * @param string      $ret_url [Optional]
     *
     * @return void
     * @since  1.0
     */
    private function _ensureUniqueEmail($email, $ret_url=null)
    {
        if (count($this->_getUsersMapper()->findByEmail($email)) > 0) {
            $this->raiseError(sprintf(
                UserLang::ERROR_EMAIL_ALREADY_REGISTERED,
                $email
            ));

            if (!$ret_url) {
                $ret_url  = $base_url."user/login?&username=".$user->email();
            }

            $this->setRedirect($ret_url);
            return false;
        }

        return true;
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
     * Fetch a user by ID and check read access.
     *
     * @param int $id The user id.
     * @param bool $w [Optional] Ensure write access? Default is FALSE.
     *
     * @return Contact
     * @since  1.0
     */
    private function _fetchContact($id, $w=false)
    {
        return $this->fetchObj($this->_getContactsMapper(), $id, $w);
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

    /**
     * Get instance of ContactsMapper.
     *
     * @return UsersMapper
     * @since  1.0
     */
    private function _getContactsMapper()
    {
        if (is_null($this->_contacts_mapper)) {
            $this->_contacts_mapper  = new ContactsMapper($this->db());
        }

        return $this->_contacts_mapper;
    }
}
