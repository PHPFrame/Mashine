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
        $obj = new PostContent();
        $obj->parentId(21);
        $obj->slug("test-page");
        $obj->title("Test page");
        $obj->status(1);
        $obj->owner(1);
        $obj->group(1);
        $obj->perms(664);

        return $obj;
    }
}
