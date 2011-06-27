<?php defined('SHCP_PATH') OR die('No direct script access.');

class Model_SHCP implements Countable, Iterator, SeekableIterator, ArrayAccess, Serializable {

    public static function factory($model)
    {
        $model = 'Model_' . ucfirst($model);

        try
        {
            return new $model();
        }
        catch(Exception $e)
        {
            throw new Exception($e);
        }
    }

    protected $_db;
    protected $_executed;
    protected $_object = array();

    public function __construct()
    {
        global $wpdb;

        $this->_db =& $wpdb;
    }

    /**
	 * serialize
	 *
	 * @return void
	 */
	public function serialize()
	{
        $this->load();

		foreach (array('_object', '_executed') as $var)
		{
			$data[$var] = $this->{$var};
		}

		return serialize($data);
	}

    /**
	 * unserialize
	 *
	 * @param array $data
	 * @return void
	 */
	public function unserialize($data)
	{
		$this->_initialize();

		foreach (unserialize($data) as $name => $var)
		{
			$this->{$name} = $var;
		}

		$this->reload();
	}

	public function rewind()
	{
		$this->_position = 0;
	}

	public function count()
	{
		if ( ! $this->_request_made)
		{
			$this->_load();
		}

		return $this->_total_rows;
	}

	public function key()
	{
		return $this->_position;
	}

	public function prev()
	{
		--$this->_position;
		return $this;
	}

	public function next()
	{
		++$this->_position;
		return $this;
	}

	public function seek($offset)
	{
		if ($this->offsetExists($offset))
		{
			$this->_position = $offset;
			return TRUE;
		}

		return FALSE;
	}

	public function current()
	{
		if ( ! $this->seek($this->_position))
		{
			return NULL;
		}

		return $this->_data[$this->_position];
	}

	public function valid()
	{
		if ( ! $this->_request_made)
		{
			$this->_load();
		}

		return isset($this->_data[$this->_position]);
	}

	public function offsetExists($offset)
	{
		if ( ! $this->_request_made)
		{
			$this->_load();
		}

		return ($offset >= 0 AND $offset < $this->_total_rows);
	}

	public function offsetGet($offset)
	{
		if ( ! $this->seek($offset))
			return NULL;

		return $this->current();
	}

	public function offsetSet($offset, $value)
	{
	}

	public function offsetUnset($offset)
	{
	}
}
