<?php
/**
 * src/controllers/api/session.php
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
 * Session API controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class SessionApiController extends PHPFrame_RESTfulController
{
    private $_mapper;

    /**
     * Process login request.
     *
     * @param string $email    The user's email address.
     * @param string $password The user's password.
     * @param bool   $remember [Optional]
     * @param string $ret_url  [Optional] The URL to return the user to after
     *                         successful login.
     *
     * @return array If login is successful response will include an array in
     *               the chosen format containing the following keys:
     *
     *               - ret_url (string) The URL to redirect the user to.
     *               - auth (bool) A boolean indicating whether the session is .
     *                 authenticated
     *               - user_id (int) The ID of the authenticated user.
     * @since  1.0
     */
    public function login($email, $password, $remember=false, $ret_url=null)
    {
        $base_url = $this->config()->get("base_url");
        $request  = $this->request();

        if (is_null($ret_url)) {
            $ret_url = $request->param("ret_url", $base_url."dashboard");
        }

        if ($this->session()->isAuth()) {
            $this->response()->body(array(
                "ret_url" => $ret_url,
                "auth"    => true,
                "user_id" => $this->user()->id())
            );
            return;
        }

        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if ($email === false) {
            throw new InvalidArgumentException(
                UserLang::LOGIN_ERROR_INVALID_EMAIL,
                400
            );
        }

        $user = $this->_getUsersMapper()->findByEmail($email);

        if (!$user instanceof User) {
            $msg = sprintf(
                UserLang::LOGIN_ERROR_UNKNOWN_EMAIL,
                $base_url."user/signup"
            );
            throw new Exception($msg, 401);

        } else {
            // check password
            $parts     = explode(':', $user->password());
            $crypt     = $parts[0];
            $salt      = @$parts[1];
            $testcrypt = $this->crypt()->encryptPassword($password, $salt);

            if ($crypt != $testcrypt) {
                throw new Exception(UserLang::LOGIN_ERROR_WRONG_PASSWORD, 401);

            } else {
                // Store user data in session
                $this->session()->setUser($user);

                $this->response()->body(array(
                    "ret_url" => $ret_url,
                    "auth"    => true,
                    "user_id" => $user->id())
                );
                return;
            }
        }
    }

    /**
     * Log out and destroy session.
     *
     * @return array The response array contains the following keys: "ret_url",
     *               "auth" and "user_id".
     * @since  1.0
     */
    public function logout()
    {
        $this->session()->destroy();

        $this->response()->body(array(
            "ret_url" => $this->config()->get("base_url"),
            "auth"    => false,
            "user_id" => $this->user()->id())
        );
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
