<?php
/**
 * src/models/CMSOptions.php
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
 * CMSOptions class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/Mashine
 * @since    1.0
 */
class CMSOptions
    implements IteratorAggregate, ArrayAccess, Countable, Serializable
{
    private $_db;
    private $_data = array();

    /**
     * Constructor.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Database $db)
    {
        $this->_db = $db;

        if (!$db->hasTable("#__options")) {
            $this->_installDB();
        }

        $sql = "SELECT name, value FROM #__options WHERE autoload = 1";
        foreach ($db->fetchAssocList($sql) as $row) {
            $this->_data[$row["name"]] = $row["value"];
        }
    }

    public function getIterator()
    {
        return new ArrayObject($this->_data);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_data);
    }

    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        return $this->_data[$offset];
    }

    public function offsetSet($offset, $value, $autoload=null)
    {
        if ($this->offsetExists($offset)) {
            $sql = "UPDATE #__options SET value = :value WHERE name = :name";
        } else {
            $sql  = "INSERT INTO #__options (name, value, autoload) ";
            $sql .= "VALUES (:name, :value, 1)";
        }

        $params = array(":name"=>$offset, ":value"=>$value);

        $this->_db->query($sql, $params);

        $this->_data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $sql    = "DELETE FROM #__options WHERE name = :name";
        $params = array(":name"=>$offset);

        $this->_db->query($sql, $params);

        unset($this->_data[$offset]);
    }

    public function count()
    {
        return count($this->_data);
    }

    public function serialize()
    {
        return serialize($this->_data);
    }

    public function unserialize($serialized)
    {
        $this->_data = unserialize($serialized);
    }

    public function bind(array $array)
    {
        if (count($array) > 0) {
            foreach ($array as $key=>$value) {
                $this->_data[$key] = $value;
            }
        }
    }

    private function _installDB()
    {
        $sql = "CREATE TABLE `#__options` (
        `id` INTEGER PRIMARY KEY ASC,
        `name` varchar NOT NULL,
        `value` text NOT NULL,
        `autoload` tinyint NOT NULL DEFAULT '1'
        )";

        $this->_db->query($sql);
    }
}
