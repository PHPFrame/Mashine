<?php
require_once "PHPFrame.php";
require_once preg_replace("/(.*tests\/).+/", "$1TestCases.php", __FILE__);
require_once preg_replace("/(.*)tests\/(.+)Test(\.php)/", "$1src/$2$3", __FILE__);

class UsersMapperTest extends MapperTestCase
{
    public function setUp()
    {
        $this->fixture(new UsersMapper($this->app()->db()));
    }

    protected function createPersistentObj()
    {
        $user = new User();
        $user->email("lupo@e-noise.com");

        $crypt     = new PHPFrame_Crypt("aiGh2bu6oowahK8aichai7Lah6eecah7");
        $salt      = $crypt->genRandomPassword(32);
        $encrypted = $crypt->encryptPassword("Passw0rd", $salt);
        $user->password($encrypted.":".$salt);

        $user->addContact(new Contact(array(
            "first_name" => "Lupo",
            "last_name"  => "Montero",
            "address1"   => "55 Wallis Rd",
            "address2"   => "",
            "city"       => "London",
            "post_code"  => "E9 5EN",
            "county"     => "London",
            "country"    => "UK",
            "phone"      => "02089853999",
            "email"      => "lupo@e-noise.com",
            "preferred"  => true
        )));

        return $user;
    }
}
