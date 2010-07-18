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
        if ($force_new) {
            $install_dir = substr(__FILE__, 0, strrpos(dirname(__FILE__), DS));
            $this->_app  = new PHPFrame_Application(array(
                "install_dir" => $install_dir
            ));
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

        $this->assertType("array", $array);
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
        $this->assertType("PHPFrame_PersistentObjectCollection", $collection);
        $this->assertTrue(count($collection) > 0);

        // Test findOne() method
        $obj = $this->fixture()->findOne($obj->id());
        $this->assertType("PHPFrame_PersistentObject", $obj);

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

abstract class AppTestCase extends MVCTestCase
{
    private $_install_dir;

    public function __construct()
    {
        parent::__construct();

        // $app_doc = new PHPFrame_AppDoc($this->app()->getInstallDir());
        //         $i=1;
        //         foreach ($app_doc as $controller) {
        //             foreach ($controller->getActions() as $action) {
        //                 echo $i++.". ".$controller->getName();
        //                 echo "::".$action->getName();
        //                 echo "\n";
        //             }
        //         }
        //
        //         exit;
    }
}

abstract class HTTPAppTestCase extends AppTestCase
{
    private $_http_request;

    public function setUp()
    {
        $base_url = $this->app()->config()->get("base_url");
        $this->_http_request  = new PHPFrame_HTTPRequest($base_url, "POST");


        // $response = $this->_http_request->send();
        //         var_dump($response);
    }

    public function request()
    {
        return $this->_http_request;
    }
}

abstract class XMLAppTestCase extends HTTPAppTestCase
{

}

abstract class JSONAppTestCase extends HTTPAppTestCase
{

}

abstract class CLIAppTestCase extends AppTestCase
{

}
