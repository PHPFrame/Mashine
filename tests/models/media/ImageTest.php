<?php
$pattern     = "/(.*)(tests)([\/|\\\])(.*)Test(\.php)/";
$replacement = '$1$2$3TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);

class ImageTest extends MVCTestCase
{
    private $_fixture;

    public function setUp()
    {
        $options = new Options($this->app()->db());
        $this->_config = $options->filterByPrefix("mediaplugin_");
        $this->_config["site_path"] = $this->app()->getInstallDir().DS."public";
        $this->_config["site_url"] = $this->app()->config()->get("base_url");
        $this->_fixture = new Image($this->_config, "Invierno.jpg");
    }

    public function tearDown()
    {
        //...
    }

    public function test_getFilename()
    {
        $this->assertEquals("Invierno.jpg", $this->_fixture->getFilename());
    }

    public function test_getSize()
    {
        $this->assertType("int", $this->_fixture->getSize());
    }

    public function test_getMTime()
    {
        $this->assertType("int", $this->_fixture->getMTime());
    }

    public function test_getImageURL()
    {
        $this->assertEquals("media/Invierno.jpg", $this->_fixture->getImageURL());
    }

    public function test_getThumbURL()
    {
        $base_url = $this->app()->config()->get("base_url");
        $this->assertEquals(
            $base_url."media/thumb/Invierno.jpg",
            $this->_fixture->getThumbURL()
        );
    }

    public function test_getThumbURLNoThumb()
    {
        $base_url = $this->app()->config()->get("base_url");
        $fixture = new Image($this->_config, "aridiculouslylongnameforadirectoryinnit/dddd/2007-01-20-018.jpg");
        $this->assertEquals(
            $base_url."assets/img/no_thumb.png",
            $fixture->getThumbURL()
        );
    }

    public function test_getThumbPathNull()
    {
        $fixture = new Image($this->_config, "aridiculouslylongnameforadirectoryinnit/dddd/2007-01-20-018.jpg");
        $this->assertNull($fixture->getThumbPath());
    }

    public function test_getCaptionPath()
    {
        $fixture = new Image($this->_config, "aridiculouslylongnameforadirectoryinnit/dddd/2007-01-20-018.jpg");
        $this->assertNull($fixture->getCaptionPath());
    }

    public function test_getCaption()
    {
        $fixture = new Image($this->_config, "aridiculouslylongnameforadirectoryinnit/dddd/2007-01-20-018.jpg");
        $this->assertEquals("", $fixture->getCaption());
    }
}

