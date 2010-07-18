<?php
class Cache implements ArrayAccess, Countable, IteratorAggregate
{
    private $_data = array();
    private $_def_lifetime = 0;
    private $_timestamps = array();

    public function __construct($def_lifetime=0, ArrayAcces $data=null)
    {
        if (!is_null($data)) {
            $this->_data = $data;
        }

        $this->_def_lifetime = $def_lifetime;
    }

    public function offsetExists($offset)
    {
        $this->_refresh($offset);

        return array_key_exists($offset, $this->_data);
    }

    public function offsetGet($offset)
    {
        $this->_refresh($offset);

        if (!array_key_exists($offset, $this->_data)) {
            return null;
        }

        return $this->_data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
        $this->_timestamps[$offset] = time() + $this->_def_lifetime;
    }

    public function offsetUnset($offset)
    {
        if (array_key_exists($offset, $this->_data)) {
            unset($this->_data[$offset]);
            unset($this->_timestamps[$offset]);
        }
    }

    public function count()
    {
        $this->_refresh($offset);

        return count($this->_data);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->_data);
    }

    private function _refresh($offset)
    {
        if (time() > $this->_timestamps[$offset]) {
            $this->offsetUnset($offset);
        }
    }
}
