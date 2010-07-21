<?php
$pattern     = "/(.*)(tests)([\/|\\\])(.*)Test(\.php)/";
$replacement = '$1$2$3TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);

class ContentMapperTest extends MapperTestCase
{
    public function setUp()
    {
        $this->fixture(
            new ContentMapper(
                $this->app()->db(),
                $this->app()->getTmpDir().DS."cms"
            )
        );
    }

    public function tearDown()
    {
        //...
    }

    protected function createPersistentObj()
    {
        $obj = new Content();
        $obj->slug("test-page");
        $obj->title("Test page");
        return $obj;
    }
}
