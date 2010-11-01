<?php
/**
 * src/models/content/FeedContent.php
 *
 * PHP version 5
 *
 * @category  PHPFrame_Applications
 * @package   Mashine
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/E-NOISE/Mashine
 */

/**
 * RSS/Atom Feed Content class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class FeedContent extends Content
{
    private $_rss, $_cache_dir;

    public function __construct(array $options=null)
    {
        parent::__construct($options);
    }

    /**
     * Get array containing subtype parameter definition.
     *
     * @return array
     * @since  1.0
     */
    public function getParamKeys()
    {
        $array = array(
            "feed_url" => array(
                "def_value"  => null,
                "allow_null" => false,
                "filter"     => new PHPFrame_StringFilter(array(
                    "min_length" => 12,
                    "max_length" => 255
                ))
            ),
            "cache_time" => array(
                "def_value"  => 0,
                "allow_null" => false,
                "filter"     => new PHPFrame_IntFilter()
            )
        );

        return array_merge(parent::getParamKeys(), $array);
    }

    public function description()
    {
        return $this->_getRSSDocument()->description();
    }

    public function image()
    {
        return $this->_getRSSDocument()->image();
    }

    public function items()
    {
        return $this->_getRSSDocument()->items();
    }

    public function link()
    {
        return $this->_getRSSDocument()->link();
    }

    public function cacheDir($str=null)
    {
        if (!is_null($str)) {
            $this->_cache_dir = (string) $str;
        }

        return $this->_cache_dir;
    }

    private function _getRSSDocument()
    {
        if (is_null($this->_rss)) {
            $feed_url   = $this->param("feed_url");
            $cache_time = $this->param("cache_time");

            $http_request = new PHPFrame_HTTPRequest($feed_url);

            if ($cache_time > 0) {
                $http_request->cacheTime($cache_time);
                $http_request->cacheDir($this->cacheDir());
            }

            $http_response = $http_request->send();
            $content_type  = $http_response->getHeader("content-type");
            if ($http_response->getStatus() != 200
                || !preg_match("/application\/(rss|atom)\+xml/", $content_type)
            ) {
                $msg = "Error fetching feed from ".$feed_url.".";
                throw new RuntimeException($msg);
            }

            $xml = $http_response->getBody();

            $this->_rss = new PHPFrame_RSSDocument();
            $this->_rss->loadXML($xml);
        }

        return $this->_rss;
    }
}
