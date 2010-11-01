<?php
$pattern     = "/(.*)(tests)([\/|\\\])(.*)Test(\.php)/";
$replacement = '$1$2$3TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);

class MediaDirectoryTest extends MVCTestCase
{
    private $_config, $_fixture;

    public function setUp()
    {
        $options = new Options($this->app()->db());
        $this->_config = $options->filterByPrefix("mediaplugin_");
        $this->_config["site_path"] = $this->app()->getInstallDir().DS."public";
        $this->_config["site_url"] = $this->app()->config()->get("base_url");

        $this->_fixture = new MediaDirectory($this->_config);
    }

    public function tearDown()
    {
        //...
    }

    public function test_constructNoDirFailure()
    {
        try {
            $dir = new MediaDirectory($this->_config, "sdkjfnsdk");
        } catch (RuntimeException $e) {
            $this->assertEquals(
                "Path is not a directory!",
                $e->getMessage()
            );
        }
    }

    public function test_constructDotFileRelPathFailure()
    {
        try {
            $dir = new MediaDirectory($this->_config, ".ssh");
        } catch (InvalidArgumentException $e) {
            $this->assertEquals(
                "Dot files are not allowed.",
                $e->getMessage()
            );
        }
    }

    public function test_getFilename()
    {
        $this->assertEquals("", $this->_fixture->getFilename());
    }

    public function test_isDir()
    {
        $this->assertTrue($this->_fixture->isDir());
    }

    public function test_isFile()
    {
        $this->assertFalse($this->_fixture->isFile());
    }

    public function test_getSize()
    {
        $size = $this->_fixture->getSize();

        $this->assertType("int", $size);
        $this->assertTrue(($size > 0));
    }

    public function test_getSizeHumanReadable()
    {
        $size = $this->_fixture->getSize(true);
        $this->assertType("string", $size);
        $this->assertRegExp("/\d+ (Bytes|Kb)/", $size);
    }

    public function test_getMTime()
    {
        $mtime = $this->_fixture->getMTime();

        $this->assertType("int", $mtime);
        $this->assertTrue(($mtime > 0));
    }

    public function test_getMTimeHumanReadable()
    {
        $mtime = $this->_fixture->getMTime(true);

        $this->assertType("string", $mtime);
    }

    public function test_getRealPath()
    {
        $path = $this->_fixture->getRealPath();
        $this->assertTrue(is_dir($path));
    }

    public function test_getSitePath()
    {
        $this->assertEquals(
            preg_replace("/(.*\/)tests\/.+/", "$1public", __FILE__),
            $this->_fixture->getSitePath()
        );
    }

    public function test_getUploadDir()
    {
        $this->assertEquals("media", $this->_fixture->getUploadDir());
    }

    public function test_getRelativePath()
    {
        $this->assertEquals("", $this->_fixture->getRelativePath());
    }

    public function test_getParentRelativePath()
    {
        $this->assertEquals("", $this->_fixture->getParentRelativePath());
    }

    public function test_getThumbURL()
    {
        $this->assertEquals(
            "http://localhost/mashine/media/thumb/animagewithaveryrverylongnameorwhat.jpg",
            $this->_fixture->getThumbURL()
        );
    }

    public function test_getThumbURLNoThumb()
    {
        $fixture = new MediaDirectory($this->_config, "aridiculouslylongnameforadirectoryinnit/dddd");

        $this->assertEquals(
            "http://localhost/mashine/assets/img/no_thumb.png",
            $fixture->getThumbURL()
        );
    }

    public function test_getBreadCrumbs()
    {
        $this->assertEquals(
            "<a href=\"admin/media\">Root</a>",
            $this->_fixture->getBreadCrumbs()
        );
    }

    public function test_getIterator()
    {
        $this->assertType("array", iterator_to_array($this->_fixture));
    }
}

