<?php
/**
 * src/models/users/Contact.php
 *
 * PHP version 5
 *
 * @category  PHPFrame_Applications
 * @package   PHPFrame_CmsAppTemplate
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/lupomontero/PHPFrame_CmsAppTemplate
 */

/**
 * Users contact class
 *
 * @category PHPFrame_Applications
 * @package  PHPFrame_CmsAppTemplate
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/PHPFrame_CmsAppTemplate
 * @since    1.0
 */
class Contact extends PHPFrame_PersistentObject
{
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
            "org_name",
            null,
            true,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>100))
        );
        $this->addField(
            "first_name",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>50))
        );
        $this->addField(
            "last_name",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>50))
        );
        $this->addField(
            "address1",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>100))
        );
        $this->addField(
            "address2",
            null,
            true,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>100))
        );
        $this->addField(
            "city",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>50))
        );
        $this->addField(
            "post_code",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>15))
        );
        $this->addField(
            "county",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>50))
        );
        $this->addField(
            "country",
            "GB",
            false,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>2))
        );
        $this->addField(
            "phone",
            null,
            false,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>20))
        );
        $this->addField(
            "email",
            null,
            false,
            new PHPFrame_EmailFilter(array("min_length"=>0, "max_length"=>255))
        );
        $this->addField(
            "fax",
            null,
            true,
            new PHPFrame_StringFilter(array("min_length"=>0, "max_length"=>20))
        );
        $this->addField(
            "preferred",
            false,
            true,
            new PHPFrame_BoolFilter()
        );

        parent::__construct($options);

        // Make group ownership belong to staff and make object writable both
        // for owner (the customer) and the group (staff)
        $this->group(2);
        $this->perms(660);
    }
}
