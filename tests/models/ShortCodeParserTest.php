<?php
require_once preg_replace("/(.*)tests\/.+/", "$1src/models/ShortCodeParser.php", __FILE__);

class ShortCodeParserTest extends PHPUnit_Framework_TestCase
{
    private $_parser;

    public function setUp()
    {
        $this->_parser = new ShortCodeParser();
    }

    public function tearDown()
    {
        //...
    }

    public function test_parseSyntaxNotRecognisedFailure()
    {
        $array = array(
            "aaa",
            "[]",
            "{kjbkj}",
            "[jhkjhk",
            "kjbnk]",
            "[kjnkj&*()]",
            "[&* kjn]"
        );

        foreach ($array as $item) {
            try {
                $ret = $this->_parser->parse($item);
                $this->fail("Expected ShortCodeParserException not thrown! Value tested: ".$item);
            } catch (ShortCodeParserException $e) {
                $this->assertEquals("Short code syntax not recognised.", $e->getMessage());
            }
        }
    }

    public function test_parseNoOptions()
    {
        $array = array(
            array('[content]', "content"),
            array('[menu ]', "menu"),
            array('[  sitemap]', "sitemap"),
            array("[breadcrumbs\t]", "breadcrumbs")
        );

        foreach ($array as $item) {
            $ret = $this->_parser->parse($item[0]);
            $this->assertType("array", $ret);
            $this->assertTrue(count($ret) == 2);
            $this->assertType("string", $ret[0]);
            $this->assertEquals($item[1], $ret[0]);
            $this->assertType("array", $ret[1]);
            $this->assertTrue(count($ret[1]) == 0);
        }
    }

    public function test_parseOptionsExpectedDoubleQuoteFailure()
    {
        $array = array(
            "[content id=]",
            "[content id=jhb]",
            "[content id=87&^GJ]"
        );

        foreach ($array as $item) {
            try {
                $ret = $this->_parser->parse($item);
                $this->fail("Expected ShortCodeParserException not thrown! Value tested: ".$item);
            } catch (ShortCodeParserException $e) {
                $this->assertRegexp("/Expected opening double quote/", $e->getMessage());
            }
        }
    }

    public function test_parseOneOption()
    {
        $array = array(
            '[content id="10"]',
            '[content  id="10"   ]',
            '[content        id="10"]'
        );

        foreach ($array as $item) {
            $ret = $this->_parser->parse($item);
            $this->assertType("array", $ret);
            $this->assertTrue(count($ret) == 2);
            $this->assertType("string", $ret[0]);
            $this->assertEquals("content", $ret[0]);
            $this->assertType("array", $ret[1]);
            $this->assertArrayHasKey("id", $ret[1]);
            $this->assertEquals("10", $ret[1]["id"]);
        }
    }

    public function test_parseManyOptions()
    {
        $array = array(
            array(
                '[content parent_id="2" limit="3"]',
                array(
                    "content",
                    array(
                        "parent_id"=>"2",
                        "limit"=>"3"
                    )
                )
            ),
            array(
                '[menu show_root="false" depth="2" exclude="user/login,user/logout"]',
                array(
                    "menu",
                    array(
                        "show_root" => "false",
                        "depth" => "2",
                        "exclude" => "user/login,user/logout"
                    )
                )
            ),
            array(
                '[sitemap exclude="user/login"]',
                array(
                    "sitemap",
                    array(
                        "exclude" => "user/login"
                    )
                )
            ),
            array(
                '[breadcrumbs show_root="true"]',
                array(
                    "breadcrumbs",
                    array(
                        "show_root" => "true"
                    )
                )
            )
        );

        foreach ($array as $item) {
            $ret = $this->_parser->parse($item[0]);
            $this->assertType("array", $ret);
            $this->assertTrue(count($ret) == 2);
            $this->assertEquals($item[1], $ret);
        }
    }
}
