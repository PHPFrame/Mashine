<?php
/**
 * src/models/Options.php
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
 * Options class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class Options
    implements IteratorAggregate, ArrayAccess, Countable, Serializable
{
    private $_db, $_data = array();

    /**
     * Constructor.
     *
     * @param PHPFrame_Database $db Instance of the app's database.
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
            if (preg_match("/^a:\d+:\{.*\}$/", $row["value"])) {
                $row["value"] = unserialize($row["value"]);
            }

            $this->_data[$row["name"]] = $row["value"];
        }
    }

    /**
     * Implements the IteratorAggregate interface.
     *
     * @return ArrayObject
     */
    public function getIterator()
    {
        return new ArrayObject($this->_data);
    }

    /**
     * Check whether an offset/key exists. Implements the ArrayAccess interface.
     *
     * @param string $offset The key or offset name.
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_data);
    }

    /**
     * Get value stored at given offset. Implements the ArrayAccess interface.
     *
     * @param string $offset The key or offset name.
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        return $this->_data[$offset];
    }

    /**
     * Set value at given offset. Implements the ArrayAccess interface.
     *
     * @param string $offset   The key or offset name.
     * @param mixed  $value    The value to store.
     * @param bool   $autoload Whether or not the option should be automatically
     *                         loaded.
     *
     * @return void
     */
    public function offsetSet($offset, $value, $autoload=true)
    {
        if ($this->offsetExists($offset)) {
            $sql = "UPDATE #__options SET value = :value WHERE name = :name";
        } else {
            $sql  = "INSERT INTO #__options (name, value, autoload) ";
            $sql .= "VALUES (:name, :value, 1)";
        }

        $params = array(":name"=>$offset, ":value"=>$value);

        if (is_array($value)) {
            $params[":value"] = serialize($value);
        }

        $this->_db->query($sql, $params);

        $this->_data[$offset] = $value;
    }

    /**
     * Remove value stored at given offset. Implements the ArrayAccess interface.
     *
     * @param string $offset The key or offset name.
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        $sql    = "DELETE FROM #__options WHERE name = :name";
        $params = array(":name"=>$offset);

        $this->_db->query($sql, $params);

        unset($this->_data[$offset]);
    }

    /**
     * Implements the Countable interface.
     *
     * @return int
     */
    public function count()
    {
        return count($this->_data);
    }

    /**
     * Implements the Serializable interface.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->_data);
    }

    /**
     * Implements the Serializable interface.
     *
     * @param string $serialized The serialised string respresentation.
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->_data = unserialize($serialized);
    }

    /**
     * Populate internal options array using an assoc array.
     *
     * @param array $array The assoc array containing the options data.
     *
     * @return void
     */
    public function bind(array $array)
    {
        if (count($array) > 0) {
            foreach ($array as $key=>$value) {
                $this->_data[$key] = $value;
            }
        }
    }

    /**
     * Get options that match a given prefix. Normally the plugin name.
     *
     * @param string $prefix The prefix to filter by.
     *
     * @return array
     */
    public function filterByPrefix($prefix)
    {
        $a = array();

        foreach ($this as $k=>$v) {
            $pos = strpos($k, $prefix);
            if ($pos === 0) {
                $a[substr($k, strlen($prefix))] = $v;
            }
        }

        return $a;
    }

    private function _installDB()
    {
        if ($this->_db->isSQLite()) {
            $sql = "CREATE TABLE `#__options` (
            `id` INTEGER PRIMARY KEY ASC,
            `name` varchar NOT NULL,
            `value` text NOT NULL,
            `autoload` tinyint NOT NULL DEFAULT '1'
            )";
        } else {
            $sql = "CREATE TABLE `#__options` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(100) NOT NULL,
            `value` text NOT NULL,
            `autoload` tinyint NOT NULL DEFAULT '1'
            )";
        }

        $this->_db->query($sql);
    }
}
