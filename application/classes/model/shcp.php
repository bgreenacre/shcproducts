<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * shcproducts
 *
 * @author Brian Greenacre and Kyla Klein
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Wed 15 Jun 2011 07:32:09 PM
 */

// ----------------------------------------------------------------------------

/**
 * Model_SHCP
 *
 */
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

    /**
     * Parameters to pass in the query_posts call.
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Tracks whether the posts have been queried.
     *
     * @var bool
     */
    protected $_executed;

    /**
     * Contents from the request made. Used as the iterator.
     *
     * @var array
     */
    protected $_data = array();

	/**
	 * Current position within the object.data
	 *
	 * @access	protected
	 * @var		int
	 */
	protected $_position = 0;

	/**
	 * Total number of rows in the object.data
	 *
	 * @access	protected
	 * @var		int
	 */
	protected $_total_rows;

    public function __construct()
    {
        $this->_initialize();
    }

    protected function _initialize()
    {
		$this->_data = array();
		$this->_position = 0;
		$this->_total_rows = 0;
		$this->_params = array();
		$this->_execute = FALSE;
    }

    /**
	 * _load - Load the results from the query.
	 *
	 * @return bool
	 */
	protected function _load()
	{
		if ($this->_executed !== TRUE)
		{
		    // Instantiate the query object to get posts.
		    $query = new WP_Query($this->params());

		    // Dump the posts into this object.
		    $this->_data = $query->posts;

		    // Destroy the query object.
		    unset($query);

		    $this->_executed = TRUE;
		}

		if ($this->_data)
		{
		    $this->_position = 0;
		    $this->_total_rows = count($this->_data);
		}
		else
		{
		    $this->_position = 0;
		    $this->_total_rows = 0;
			return FALSE;
		}

		return TRUE;
	}

    /**
	 * load - Public method to load up a API call.
	 *
	 * @return object $this
	 */
	public function load()
	{
		$this->_load();

		return $this;
	}

    /**
     * param - Add a parameter which be sent to the API in the form of $_GET
     * variable.
     *
     *  // Single name and value. This method can be chained.
     *  $this->param('store', 'Sears')->param('catalogId', 12605);
     *
     *  // Can take an Array of parameters.
     *  $this->param(array('store' => 'Sears', 'catalogId' => '12605'));
     *
     * @param string $name
     * @param string $value = NULL
     * @param bool $append = FALSE
     * @return mixed
     */
    public function param($name, $value = NULL)
    {
        if (is_array($name))
        {
            $this->_params += $name;
            return $this;
        }

        if ($value === NULL)
        {
            return SHCP::get($this->_params, $name);
        }

        $this->_params[$name] = $value;

        return $this;
    }

    /**
	 * serialize
	 *
	 * @return void
	 */
	public function serialize()
	{
        $this->load();

		foreach (array('_data', '_executed') as $var)
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

		$this->load();
	}

	public function rewind()
	{
		$this->_position = 0;
	}

	public function count()
	{
		if ( ! $this->_executed)
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
		if ( ! $this->_executed)
		{
			$this->_load();
		}

		return isset($this->_data[$this->_position]);
	}

	public function offsetExists($offset)
	{
		if ( ! $this->_executed)
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
