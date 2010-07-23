<?php
/**
 * src/models/users/User.php
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
 * User class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class User extends PHPFrame_User
{
    public $contacts;

    /**
     * Constructor.
     *
     * @param array $options [Optional]
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->addField(
            "status",
            "pending",
            false,
            new PHPFrame_EnumFilter(array(
                 "enums" => array(
                     "pending",
                     "active",
                     "suspended",
                     "cancelled"
                  )
            ))
        );
        $this->addField(
            "notifications",
            true,
            false,
            new PHPFrame_BoolFilter()
        );
        $this->addField(
            "activation",
            null,
            true,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>100))
        );

        parent::__construct($options);

        $this->contacts = new SplObjectStorage();

        if (is_array($options)) {
            if (array_key_exists("contacts", $options)
                && is_array($options["contacts"])
            ) {
                foreach ($options["contacts"] as $contact_array) {
                    $this->addContact(new Contact($contact_array));
                }
            }
        }
    }

    /**
     * Magic method invoked when object is serialised.
     *
     * @return array
     * @since  1.0
     */
    public function __sleep()
    {
        return array_merge(parent::__sleep(), array("contacts"));
    }

    /**
     * Magic method invoked when object is unserialised.
     *
     * @return array
     * @since  1.0
     */
    public function __wakeup()
    {
        //...
    }

    /**
     * Get user contacts.
     *
     * @return array containing Contact objects.
     * @since  1.0
     */
    public function contacts()
    {
        return iterator_to_array($this->contacts);
    }

    /**
     * Get preferred contact for user.
     *
     * @return Contact|null
     * @since  1.0
     */
    public function contact()
    {
        $contacts = $this->contacts();
        foreach ($contacts as $contact) {
            if ($contact->preferred()) {
                return $contact;
            }
        }
    }

    /**
     * Add contact object to user.
     *
     * @param Contact $contact Instance of Contact.
     *
     * @return void
     * @since  1.0
     */
    public function addContact(Contact $contact)
    {
        $contacts = $this->contacts();

        // If there is only one contact we ensure that it is set as preferred
        if (count($contacts) < 1) {
            $contact->preferred(true);
        }

        $contact->owner($this->id());

        $this->contacts->attach($contact);
    }

    /**
     * Remove contact object from user object.
     *
     * @param Contact $contact Instance of Contact.
     *
     * @return void
     * @since  1.0
     */
    public function removeContact(Contact $contact)
    {
        $this->contacts->attach($contact);
    }
}