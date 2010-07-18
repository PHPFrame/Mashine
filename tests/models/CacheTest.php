<?php
require_once preg_replace("/(.*)tests\/.+/", "$1src/models/Cache.php", __FILE__);

class CacheTest extends PHPUnit_Framework_TestCase
{
    private $_cache;

    public function setUp()
    {
        $this->_cache = new Cache(30);
    }

    public function tearDown()
    {
        //...
    }

    public function test_()
    {
        //$this->_cache["package:e-noise.com"] = array("something"=>1, "foo"=>22);
        //var_dump($this->_cache);
    }

    public function test_serialise()
    {
        $this->_cache["package:e-noise.com"] = array("something"=>1, "foo"=>22);
        $serialised = serialize($this->_cache);
        $unserialised = unserialize($serialised);
        $this->assertEquals($this->_cache, $unserialised);
    }
}
