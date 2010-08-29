<?php
/**
 * src/models/ShortCodeParser.php
 *
 * PHP version 5
 *
 * @category  PHPFrame_Applications
 * @package   Mashine
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/lupomontero/Mashine
 */

/**
 * ShortCode Parser class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class ShortCodeParser
{
    /* This regular expression is run in order to identify shortcode format */
    const SHORTCODE_REGEX = "/^\[\s*([a-z0-9_-]+)(\s+.*)?\]$/";

    /* Status flags for options parser */
    const EATING_WHITESPACE = 0x00000001;
    const READING_KEY       = 0x00000002;
    const READING_VALUE     = 0x00000003;

    /* Parser variables */
    private $_str, $_keyword, $_options, $_tmp_key, $_tmp_value, $_count;
    private $_escape, $_status, $_next_status;

    /**
     * Parse string.
     *
     * @param string $str The string containing the short code.
     *
     * @return array An array containing the shortcode "keyword" and the
     *               "options" array.
     * @since  1.0
     */
    public function parse($str)
    {
        $this->_init();

        $this->_str = trim($str);

        if (!preg_match(self::SHORTCODE_REGEX, $this->_str, $matches)) {
            $msg = "Short code syntax not recognised.";
            throw new ShortCodeParserException($msg);
        }

        $this->_keyword = $matches[1];
        $this->_count   = (strlen($matches[1])+1);
        $this->_parseOptions();

        return array($this->_keyword, $this->_options);
    }

    /**
     * Initialise parse vars.
     *
     * @return void
     * @since  1.0
     */
    private function _init()
    {
        $this->_keyword = "";
        $this->_options = array();
        $this->_tmp_key = "";
        $this->_tmp_value = "";
        $this->_count = 0;
        $this->_escape = false;
        $this->_status = self::EATING_WHITESPACE;
        $this->_next_status = self::READING_KEY;
    }

    /**
     * Parse options string. This method is invoked by
     * {@link ShortCodeParser::parse()} and works with the data stored in the
     * instance's state.
     *
     * @return void
     * @since  1.0
     */
    private function _parseOptions()
    {
        while ($this->_count < strlen($this->_str)) {

            if ($this->_status == self::EATING_WHITESPACE) {
                if (!in_array($this->_str[$this->_count], array(" ", "\n", "\t"))) {
                    $this->_status = $this->_next_status;
                } else {
                    $this->_count++;
                    continue;
                }
            }

            switch ($this->_status) {
            case self::READING_KEY :
                if ($this->_str[$this->_count] == "=") {
                    $this->_options[$this->_tmp_key] = true;
                    $this->_count++;

                    if ($this->_str[$this->_count] != "\"") {
                        $msg  = "Expected opening double quote (\") after '";
                        $msg .= $this->_tmp_key."='.";
                        throw new ShortCodeParserException($msg);
                    }

                    $this->_status = self::READING_VALUE;

                } else {
                    $this->_tmp_key .= $this->_str[$this->_count];
                }
                break;

            case self::READING_VALUE :
                if ($this->_str[$this->_count] == "\"" && !$this->_escape) {
                    $this->_options[$this->_tmp_key] = $this->_tmp_value;
                    $this->_tmp_key = "";
                    $this->_tmp_value = "";

                    $this->_status = self::EATING_WHITESPACE;
                    $this->_next_status = self::READING_KEY;
                } else {
                    $this->_tmp_value .= $this->_str[$this->_count];
                }
                break;
            }

            $this->_count++;
        }
    }
}

/**
 * ShortCode Parser Exception class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class ShortCodeParserException extends Exception {}
