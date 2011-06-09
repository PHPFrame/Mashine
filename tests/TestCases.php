<?php
require_once "PHPFrame.php";

abstract class MVCTestCase extends PHPUnit_Framework_TestCase
{
    private $_app, $_fixture;

    public function __construct()
    {
        $this->app(true);
    }

    public function app($force_new=false)
    {
        global $argv, $argc;

        if ($force_new) {
            $tmp_argv = $argv;
            $tmp_argc = $argc;

            $argv = array($argv[0]);
            $argc = 1;

            $install_dir = substr(__FILE__, 0, strrpos(dirname(__FILE__), DS));
            $this->_app  = new PHPFrame_Application(array(
                "install_dir" => $install_dir
            ));

            $this->_app->request();

            $argv = $tmp_argv;
            $argc = $tmp_argc;
        }

        return $this->_app;
    }

    public function fixture($fixture=null)
    {
        if (!is_null($fixture)) {
            $this->_fixture = $fixture;
        }

        return $this->_fixture;
    }
}

abstract class PersistentObjectTestCase extends MVCTestCase
{
    abstract public function test_construct();
    abstract public function test_constructFailure();

    public function fixture(PHPFrame_PersistentObject $fixture=null)
    {
        return parent::fixture($fixture);
    }

    public function test_serialise()
    {
        $fixture = $this->fixture();

        $serialised   = serialize($fixture);
        $unserialised = unserialize($serialised);

        $this->assertEquals(
            iterator_to_array($fixture),
            iterator_to_array($unserialised)
        );
    }

    public function test_getIterator()
    {
        $fixture = $this->fixture();
        $fixture->owner(999);
        $array   = iterator_to_array($fixture);

        $this->assertInternalType("array", $array);
        $this->assertArrayHasKey("id", $array);
        $this->assertArrayHasKey("ctime", $array);
        $this->assertArrayHasKey("mtime", $array);
        $this->assertArrayHasKey("owner", $array);
        $this->assertArrayHasKey("group", $array);
        $this->assertArrayHasKey("perms", $array);
        $this->assertEquals(999, $array["owner"]);

        $class = get_class($fixture);
        $obj   = new $class($array);

        $this->assertEquals(iterator_to_array($fixture), iterator_to_array($obj));
    }
}

abstract class MapperTestCase extends MVCTestCase
{
    abstract protected function createPersistentObj();

    public function fixture(PHPFrame_Mapper $fixture=null)
    {
        return parent::fixture($fixture);
    }

    public function test_all()
    {
        $obj    = $this->createPersistentObj();
        $mapper = $this->fixture();

        // Insert object to make sure there is at least one entry in db
        // Test insert() method
        $mapper->insert($obj);

        // Test find() method
        $collection = $this->fixture()->find();
        $this->assertInstanceOf("PHPFrame_PersistentObjectCollection", $collection);
        $this->assertTrue(count($collection) > 0);

        // Test findOne() method
        $obj = $this->fixture()->findOne($obj->id());
        $this->assertInstanceOf("PHPFrame_PersistentObject", $obj);

        // Test delete() method
        $this->fixture()->delete($obj->id());
    }
}

abstract class ControllerTestCase extends MVCTestCase
{
    public function fixture(PHPFrame_ActionController $fixture=null)
    {
        return parent::fixture($fixture);
    }
}
