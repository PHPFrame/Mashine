<?php
/**
 * src/controllers/api/contacts.php
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
 * Contacts API controller.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class ContactsApiController extends PHPFrame_RESTfulController
{
    private $_mapper;

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
     * Get contact(s).
     *
     * @param int $id    [Optional] if specified a single contact will be returned.
     * @param int $limit [Optional] Default value is 10.
     * @param int $page  [Optional] Default value is 1.
     *
     * @return array|object Either a single contact object or an array containing
     *                      contact objects.
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
            $ret = $this->_fetchContact($id);
        } else {
            $id_obj = $this->_getContactMapper()->getIdObject();
            $id_obj->limit($limit, ($page-1)*$limit);
            $ret = $this->_getContactMapper()->find($id_obj);
        }

        return $this->handleReturnValue($ret);
    }

    /**
     * Save contact passed in POST. If no 'id' is passed in request a new
     * Contact object will be created, otherwise the existing contact with a
     * matching 'id' will be updated if any of the other parameters are given.
     *
     * @param int    $id            [Optional]
     * @param string $org_name      [Optional]
     * @param string $first_name    [Optional]
     * @param string $last_name     [Optional]
     * @param string $address1      [Optional]
     * @param string $address2      [Optional]
     * @param string $city          [Optional]
     * @param string $post_code     [Optional]
     * @param string $county        [Optional]
     * @param string $country       [Optional] 2-letter iso country identifier
     * @param string $phone         [Optional]
     * @param string $email         [Optional]
     * @param string $fax           [Optional]
     * @param bool   $preferred     [Optional]
     * @param int    $owner         [Optional] user that owns this contact
     *
     * @return object The user object after saving it.
     * @since  1.0
     */
    public function post(
        $id=0,
        $org_name=null,
        $first_name=null,
        $last_name=null,
        $address1=null,
        $address2=null,
        $city=null,
        $post_code=null,
        $county=null,
        $country=null,
        $phone=null,
        $email=null,
        $fax=null,
        $preferred=null,
        $owner=null
    ) {
        $id       = filter_var($id, FILTER_VALIDATE_INT);
        $is_staff = ($this->session()->isAuth() && $this->user()->groupId() < 3);
        $is_owner = ($this->user()->id() == $owner);
        $crypt    = $this->crypt();

        if (($is_staff || $is_owner) && $owner) {
            $contact->owner($owner);
        } else if ($owner && !$is_owner) {
            $msg = "Permission denied.";
            throw new InvalidArgumentException($msg, 401);
        }

        if (!is_int($id) || $id <= 0) {
            $contact = new Contact();
            $contact->group(2);

            // if (!$first_name) {
            //     throw new InvalidArgumentException("First name is required", 401);
            // } else if (!$last_name) {
            //     throw new InvalidArgumentException("Last name is required", 401);
            // } else if (!$address1) {
            //     throw new InvalidArgumentException("Address1 is required", 401);
            // } else if (!$city) {
            //     throw new InvalidArgumentException("City is required", 401);
            // } else if (!$post_code) {
            //     throw new InvalidArgumentException("Post code is required", 401);
            // } else if (!$county) {
            //     throw new InvalidArgumentException("County is required", 401);
            // } else if (!$phone) {
            //     throw new InvalidArgumentException("Phone is required", 401);
            // } else if (!$email) {
            //     throw new InvalidArgumentException("Email is required", 401);
            // }
        } else {
            $contact = $this->_fetchContact($id, true);
        }

        if ($org_name) {
            $contact->orgName($org_name);
        }
        if ($first_name) {
            $contact->firstName($first_name);
        }
        if ($last_name) {
            $contact->lastName($last_name);
        }
        if ($address1) {
            $contact->address1($address1);
        }
        if ($address2) {
            $contact->address2($address2);
        }
        if ($city) {
            $contact->city($city);
        }
        if ($post_code) {
            $contact->postCode($post_code);
        }
        if ($county) {
            $contact->county($county);
        }
        if ($country) {
            $contact->country($country);
        }
        if ($phone) {
            $contact->phone($phone);
        }
        if ($email) {
            $contact->email($email);
        }
        if ($fax) {
            $contact->fax($fax);
        }
        if ($preferred) {
            $contact->preferred($preferred);
        }

        // Save the contact object in the database
        $this->_getContactMapper()->insert($contact);

        return $this->handleReturnValue($contact);
    }

    /**
     * Delete contact.
     *
     * @param int $id The contact id.
     *
     * @return void
     * @since  1.0
     */
    public function delete($id)
    {
        $contact = $this->_fetchContact($id, true);
        $this->ensureIsStaff();

        $this->_getContactMapper()->delete($contact);

        return $this->handleReturnValue(true);
    }

    /**
     * Fetch a contact by ID and check read access.
     *
     * @param int  $id The contact id.
     * @param bool $w  [Optional] Ensure write access? Default is FALSE.
     *
     * @return User
     * @since  1.0
     */
    private function _fetchContact($id, $w=false)
    {
        return $this->fetchObj($this->_getContactMapper(), $id, $w);
    }

    /**
     * Get instance of ContactMapper.
     *
     * @return ContactMapper
     * @since  1.0
     */
    private function _getContactMapper()
    {
        if (is_null($this->_mapper)) {
            $this->_mapper = new ContactsMapper($this->db());
        }

        return $this->_mapper;
    }
}
