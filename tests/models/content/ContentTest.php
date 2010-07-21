<?php
$pattern     = "/(.*)(tests)([\/|\\\])(.*)Test(\.php)/";
$replacement = '$1$2$3TestCases.php';
require_once preg_replace($pattern, $replacement, __FILE__);

class ContentTest extends PersistentObjectTestCase
{
    public function setUp()
    {
        $this->fixture(new Content());
    }

    public function tearDown()
    {
        //...
    }

    public function test_construct()
    {
        // $content = new ViewContent();
        //         $content->slug($this->app()->config()->get("base_url"));
        //         $content->title("Home");
        //         // $content->slug();
        //         $content->description("Small London based company specialising in design, development and hosting of web apps, WordPress, Joomla! and PHPFrame sites.");
        //         $content->keywords("hosting, wordpress, joomla, london, uk");
        //         $content->param("view", "index");
        //
        //         var_dump($content);
    }

    public function test_constructFailure()
    {

    }
}
