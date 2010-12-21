<?php
/**
 * src/controllers/user.php
 *
 * PHP version 5
 *
 * @category   PHPFrame_Applications
 * @package    Mashine
 * @subpackage Controllers
 * @author     Lupo Montero <lupo@e-noise.com>
 * @copyright  2010 E-NOISE.COM LIMITED
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://github.com/E-NOISE/Mashine
 */

/**
 * User controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class UserController extends PHPFrame_ActionController
{
    private $_mapper, $_contacts_mapper;

    /**
     * Constructor.
     *
     * @param PHPFrame_Application $app Reference to application object.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app, "index");
    }

    /**
     * Show the dashboard.
     *
     * @return void
     * @since  1.0
     */
    public function index()
    {
        $content = $this->request()->param("_content_active");
        $hooks = $this->request()->param("_hooks");
        $dashboard_boxes = $hooks->doAction("dashboard_boxes");

        $view = $this->view("user/dashboard");
        $view->addData("title", $content->title());
        $view->addData("dashboard_boxes", $dashboard_boxes);
        $view->addData("user", $this->user());
        $view->addData("session", $this->session());

        $this->response()->title($content->title());
        $this->response()->body($view);
    }

    /**
     * Show login form or process login request depending on whether an email
     * and password are provided.
     *
     * @param string $email    [Optional] The user's email address.
     * @param string $password [Optional] If the email is passed then the
     *                         password is required.
     * @param bool   $remember [Optional]
     * @param string $ret_url  [Optional] The URL to return the user to after
     *                         successful login.
     *
     * @return void
     * @since  1.0
     */
    public function login(
        $email=null,
        $password=null,
        $remember=false,
        $ret_url=null
    ) {
        $base_url = $this->config()->get("base_url");
        $request  = $this->request();

        if (is_null($ret_url)) {
            $ret_url = $request->param("ret_url", $base_url."dashboard");
        }

        if ($this->session()->isAuth()) {
            $this->setRedirect($ret_url);
            return;
        }

        // if login form has been submitted we try to auth
        if (isset($email) && isset($password)) {
            $this->checkToken();

            try {
                $api_controller = new SessionApiController($this->app());
                $api_controller->login($email, $password, $remember, $ret_url);
                $this->setRedirect($ret_url);

            } catch (Exception $e) {
                $this->raiseError($e->getMessage());
                $this->setRedirect($base_url."user/login");
            }

            return;
        }

        // else we show login form
        $content = $this->request()->param("_content_active");
        if ($content instanceof Content) {
            $title = $content->title();
        } else {
            $title = UserLang::LOGIN;
        }

        $view  = $this->view("user/login");

        $view->addData("title", $title);
        $view->addData("ret_url", $ret_url);
        $view->addData("email", $request->param("email", ""));
        $view->addData("token", base64_encode($this->session()->getToken()));
        $view->addData("ajax", $request->ajax());

        $hooks = $this->request()->param("_hooks");
        $login_plugins = $hooks->doAction("login_form");
        $view->addData("login_plugins", $login_plugins);

        $this->response()->title($title);
        $this->response()->body($view);
    }

    /**
     * Log out and destroy session.
     *
     * @return void
     * @since  1.0
     */
    public function logout()
    {
        $this->session()->destroy();

        $this->setRedirect($this->config()->get("base_url"));
    }

    /**
     * Show signup form.
     *
     * @return void
     * @since  1.0
     */
    public function signup()
    {
        $base_url = $this->config()->get("base_url");
        $options  = $this->request()->param("_options");
        $enable = (bool) $options["mashineplugin_frontendsignup_enable"];
        $show_billing = (bool) $options["mashineplugin_frontendsignup_show_billing"];
        $def_group = (int) $options["mashineplugin_frontendsignup_def_group"];

        if ($this->session()->isAuth()) {
            $this->setRedirect($base_url."dashboard");
            return;
        }

        if (!$enable) {
            $msg = "Front-end signup feature has been disabled.";
            $this->raiseError($msg);
            return;
        }

        $title = "Sign up";
        $view  = $this->view("user/signup");

        $view->addData("title", $title);
        $view->addData("ret_url", $this->request()->param("ret_url", null));
        $view->addData("email", $this->request()->param("email", null));
        $view->addData("helper", $this->helper("user"));
        $view->addData("show_billing", $show_billing);
        $view->addData("group_id", $def_group);

        $this->response()->title($title);
        $this->response()->body($view);
    }

    /**
     * Show user edit form.
     *
     * @param int $id [Optional] The user ID.
     *
     * @return void
     * @since  1.0
     */
    public function form($id=null)
    {
        $base_url = $this->config()->get("base_url");
        $content  = $this->request()->param("_content_active");
        $title    = $content->title();
        $ret_url  = $base_url."profile";

        if (!is_null($id)) {
            if (!$user = $this->_fetchUser($id, true)) {
                return;
            }

            if ($this->session()->getUser()->groupId() < 3) {
                $title = "Modify user details";
            }

            if ($user->id() != $this->user()->id()) {
                $ret_url = $base_url."admin/user/form?id=".$id;
            }

        } else {
            if ($this->session()->isAuth()
                && $this->request()->param("slug") == "profile"
            ) {
                $user = $this->user();

            } elseif ($this->session()->isAuth() && $this->user()->groupId() < 3) {
                $user    = new User();
                $title   = "Add new user";
                $ret_url = $base_url."admin/user";

            } else {
                $msg = "Permission denied.";
                $this->raiseError($msg);
                $this->response()->statusCode(401);
                return;
            }
        }

        $view = $this->view("admin/user/form");
        $view->addData("title", $title);
        $view->addData("user", $user);
        $view->addData("session", $this->session());
        $view->addData("helper", $this->helper("user"));
        $view->addData("ret_url", $ret_url);

        $this->response()->title($title);
        $this->response()->body($view);
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
     * @param string $ret_url       [Optional]
     *
     * @return object The user object after saving it.
     * @since  1.0
     */
    public function save(
        $id=0,
        $group_id=0,
        $email=null,
        $password=null,
        $status=null,
        $notifications=null,
        $ret_url=null
    ) {
        try {
            $api_controller = new UsersApiController($this->app(), true);
            $api_controller->format("php");
            $api_controller->returnInternalPHP(true);

            $user = $api_controller->post(
                $id,
                $group_id,
                $email,
                $password,
                $status,
                $notifications
            );

            // if new user
            if ($id <= 0 && $user->id() > 0) {
                // if contact details are passed we create contact
                $first_name = $this->request()->param("first_name", null);
                if ($first_name) {
                    $contact = new Contact($this->request()->params());
                    $user->addContact($contact);
                    $this->_getUsersMapper()->insert($user);
                }

                $password = $this->request()->param("password");
                $this->_sendConfirmationEmail($user, $password);

                if (!$this->session()->isAuth()) {
                    $this->session()->setUser($user);
                    $ret_url = $this->config()->get("base_url")."dashboard";
                }

                $msg = sprintf(UserLang::NEW_USER_SUCCESS, $user->email());
                $this->notifyInfo($msg);

            } else {
                $msg = UserLang::UPDATE_USER_SUCCESS;
                $this->notifySuccess($msg);
            }

            if (!$ret_url) {
                $ret_url = $_SERVER["HTTP_REFERER"];
            }

            $this->setRedirect($ret_url);

        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
            $this->setRedirect($_SERVER["HTTP_REFERER"]);
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
     * Request a password reset email to be sent to given address.
     *
     * @param string $forgot_email The user's email address.
     * @param string $token        [Optional] Token previously sent by invoking
     *                             this method only passing forgot_email.
     *
     * @return void
     * @since  1.0
     */
    public function reset($forgot_email="", $token="")
    {
        $base_url = $this->config()->get("base_url");
        $crypt = $this->crypt();

        if (array_key_exists("HTTP_REFERER", $_SERVER)) {
            $this->setRedirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->setRedirect($base_url."user/login");
        }

        $email = filter_var($forgot_email, FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $msg = "Wrong email format.";
            $this->raiseError($msg);
            return;
        }

        $mailer = $this->mailer();
        if (!$mailer instanceof PHPFrame_Mailer) {
            $this->raiseError("Mailer not enabled. Please check smtp config.");
            return;
        }

        try {
            $mapper = $this->_getUsersMapper();
            $user = $mapper->findByEmail($email);

            if (!$user instanceof User) {
                $msg  = "We couldn't find any registered users with that ";
                $msg .= "email address";
                $this->raiseError($msg);
                return;
            }

            $user_params = $user->params();

            if ($token) {
                $stored_token = @$user_params["reset_token"];
                if ($token != $stored_token) {
                    $msg = "Invalid token!";
                    $this->raiseError($msg);
                    return;
                }

                $new_pass  = $crypt->genRandomPassword(8);
                $salt      = $crypt->genRandomPassword(32);
                $encrypted = $crypt->encryptPassword($new_pass, $salt);
                $user->password($encrypted.":".$salt);

                unset($user_params["reset_token"]);
                $user->params($user_params);

                $mapper->insert($user);

                $mailer->Subject = "New password";
                $mailer->Body = "Your new password is: ".$new_pass;
                $mailer->AddAddress($user->email());
                if (!$mailer->send()) {
                    $this->raiseError("Error sending email.");
                    return;
                }

                $this->notifySuccess("New password sent to ".$email);
                return;
            }

            // If no token has been passed we create one, store it and send it
            $token = $crypt->genRandomPassword(32);
            $user_params["reset_token"] = $token;
            $user->params($user_params);
            $mapper->insert($user);

            $body  = "Please click on the link below to verify that you ";
            $body .= "requested a new password and we will reset your ";
            $body .= "password and send it in another email.\n\n";
            $body .= $base_url."user/reset?forgot_email=".urlencode($email);
            $body .= "&token=".urlencode($token);

            $mailer->Subject = "Password reset request";
            $mailer->Body = $body;
            $mailer->AddAddress($user->email());
            if (!$mailer->send()) {
                $this->raiseError("Error sending email.");
                return;
            }

            $msg = "An email has been sent with the password reset request.";
            $this->notifySuccess($msg);

        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
        }
    }

    /**
     * Show user management view.
     *
     * @return void
     * @since  1.0
     */
    public function manage()
    {
        $this->ensureIsStaff();

        $id_obj = $this->_getUsersMapper()->getIdObject();
        $id_obj->limit(20, 0);

        $users = $this->_getUsersMapper()->find($id_obj);

        $title = "Manage Users";

        $view = $this->view("admin/user/index");
        $view->addData("title", $title);
        $view->addData("users", $users);

        $this->response()->title($title);
        $this->response()->body($view);
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

        $user = $this->_fetchUser($id, true);

        $this->ensureIsStaff();

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

        $this->ensureIsStaff();

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
     * Show contacts form.
     *
     * @param int $id    [Optional] The contact id.
     * @param int $owner [Optional] The contact owner user ID.
     *
     * @return void
     * @since  1.0
     */
    public function contactform($id=null, $owner=null)
    {
        $base_url = $this->config()->get("base_url");
        $ret_url  = $this->request()->param("ret_url", $base_url."profile");
        $content  = $this->request()->param("_content_active");
        $title    = $content->title();

        if (is_null($id) && $content->slug() == "user/editcontact") {
            $msg = "Please select a contact before editing...";
            $this->raiseWarning($msg);
            $this->setRedirect($this->config()->get("base_url")."profile");
            return;
        }

        if (!is_null($id) && (!$contact = $this->_fetchContact($id, true))) {
            return;
        } elseif (is_null($id)) {
            $contact = new Contact();
        }

        if (!is_null($owner)
            && $owner != $this->user()->id()
            && $this->ensureIsStaff()
        ) {
            if (!$user = $this->_fetchUser($owner)) {
                return;
            }

            $ret_url = $base_url."admin/user/form?id=".$user->id();

        } else {
            $user = $this->user();
        }

        $contact->email($user->email());
        $contact->owner($user->id());
        $contact->group($user->groupId());

        $view = $this->view("admin/user/contacts");
        $view->addData("title", $title);
        $view->addData("contact", $contact);
        $view->addData("user", $user);
        $view->addData("ret_url", $ret_url);
        $view->addData("helper", $this->helper("user"));

        $this->response()->title($title);
        $this->response()->body($view);
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
     * Export users
     *
     * @param string $format [Optional] Default value is "csv"
     * @param array $status  [Optional] Array containing the user status to
     *                       filter by (pending, active, suspended, cancelled).
     *
     * @return void
     * @since  1.0
     */
    public function export($format="csv", array $status=null)
    {
        if (!$this->ensureIsStaff()) {
            return;
        }

        $id_obj = $this->_getUsersMapper()->getIdObject();

        if (is_array($status)) {
            $id_obj->where("status", "IN", "('".implode("','", $status)."')");
        }

        $users = $this->_getUsersMapper()->find($id_obj);

        // Build csv string
        $user_keys    = array_keys(iterator_to_array(new User));
        $contact_keys = array_keys(iterator_to_array(new Contact));
        $str          = implode(",", array_merge($user_keys, $contact_keys));

        foreach ($users as $user) {
            $str .= "\n\"".implode("\",\"", iterator_to_array($user))."\"";

            $contact = $user->contact();
            if ($contact instanceof Contact) {
                $str .= ",\"".implode("\",\"", iterator_to_array($contact))."\"";
            }
        }

        $file_name   = "Users-".date("Y-m-d").".csv";
        $disposition = "attachment; filename=".$file_name;

        $this->response()->document(new PHPFrame_PlainDocument());
        $this->response()->header("Cache-Control", "public");
        $this->response()->header("Content-Description", "File Transfer");
        $this->response()->header("Content-Disposition", $disposition);
        $this->response()->header("Content-Type", "text/csv");
        $this->response()->header("Content-Length", strlen($str));
        $this->response()->body($str);
    }

    /**
     * Search users and return 'name' and 'id' in JSON format to be used by
     * AJAX autocomplete.
     *
     * @param string $s
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

    private function _sendConfirmationEmail(User $user, $password)
    {
        $mailer = $this->mailer();
        if (!$mailer instanceof PHPFrame_Mailer) {
            $msg  = "Mailer not initialised. Please check mail configuration ";
            $msg .= "in etc/phpframe.ini";
            throw new Exception($msg);
        }

        $email        = $user->email();
        $name         = $email;
        $confirm_url  = $this->config()->get("base_url")."user/confirm?email=";
        $confirm_url .= urlencode($email)."&activation=".$user->activation();

        if ($this->session()->isAuth() && $this->user()->groupId() < 3) {
            $body = UserLang::NEW_USER_EMAIL_BODY;
        } else {
            $contact = $user->contact();
            if ($contact instanceof Contact) {
                $name = $user->contact()->firstName();
            }
            $body = UserLang::SIGNUP_EMAIL_BODY;
        }

        $mailer->Subject = UserLang::NEW_USER_EMAIL_SUBJECT;
        $mailer->Body = sprintf(
            $body,
            $name,
            $this->config()->get("app_name"),
            $email,
            $password,
            $confirm_url
        );
        $mailer->AddAddress($email);
        if (!$mailer->send()) {
            $this->raiseWarning($mailer->ErrorInfo);
        }
    }
}

