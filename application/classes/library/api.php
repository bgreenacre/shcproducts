<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * shcproducts
 *
 * @author Brian Greenacre and Kyla Klein
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Thu 16 Jun 2011 11:34:46 AM
 */

// -----------------------------------------------------------------------------

/**
 * SHCP_Library_Api
 *
 */
class SHCP_Library_Api implements Countable, Iterator, SeekableIterator, ArrayAccess, Serializable {

    protected $endpoint;
    protected $apikey;
    protected $authid;
    protected $_content_type = 'json';
    protected $_curl_options = array(
        CURLOPT_RETURNTRANSFER  => 1,
        CURLOPT_HTTPHEADER      => array('X-SHCMMR-Client-Id: app_ui'),
    );
    protected $_params = array();
    protected $_url;

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

    public function __construct($group = NULL)
    {
        $this->_initialize();

        if ($group === NULL)
        {
            $group = 'default';
        }

        $config = (array) SHCP::config('api.' . $group);

        foreach ($config as $property => $value)
        {
            $this->{$property} = $value;
        }

        $settings = (array) get_option('shcproducts');

        foreach ($settings as $setting => $value)
        {
            $this->{$setting} = $value;
        }
    }

	protected function _initialize()
	{
		$this->_object = NULL;
		$this->_position = 0;
		$this->_total_rows = 0;
		$this->_params = NULL;
		$this->_request_made = FALSE;
	}

	public function reload($refresh = FALSE)
	{
		if ($refresh !== FALSE OR ( ! $this->_result AND ! $this->_object))
		{
			$this->_request();
		}

		return $this->_load();
	}

	public function load()
	{
		$this->_load();
		return $this;
	}

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

    protected function build_url()
    {
        $url = rtrim($this->endpoint, '/') . '/' . $this->method;

        if ($this->_params)
        {
            $qs = '?';

            foreach ($this->_params as $param => $value)
            {
                $qs .= $param . '=' . urlencode($value) . '&';
            }

            $url .= rtrim($qs, '&');
            unset($qs);
        }

        return $url;
    }

    protected function _request()
    {
        if ($this->endpoint === NULL)
        {
            throw new Exception('No endpoint provided for Sears API request');
        }

        $this
            ->param('contentType', $this->content_type)
            ->param('apikey', $this->apikey)
            ->param('storename', $this->storename);

        $this->url = $this->build_url();

        $ch = curl_init($this->url);

		// Set connection options
		if ( ! curl_setopt_array($ch, $this->curl_options))
		{
			throw new Exception('Failed to set CURL options, check CURL documentation.');
		}

		// Get the response body
		$body = curl_exec($ch);

		// Get the response information
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($body === FALSE)
		{
			$error = curl_error($ch);
		}

		// Close the connection
		curl_close($ch);

		if (isset($error))
		{
			throw new Exception('Error fetching remote ' . $this->url . ' [ status ' . $code . ' ] ' . $error);
		}

        $this->_object = json_decode($body);
        $this->_request_made = TRUE;
    }

	protected function _load()
	{
		if ($this->_request_made !== TRUE)
		{
			$this->_request();
		}

		if ($this->_object)
		{
			if (isset($this->_object->error) === TRUE)
			{
				throw new Exception($this->_object->error->message);
			}
		}
		else
		{
			return FALSE;
		}

		if (isset($this->_object->data) === TRUE)
		{
			$this->_total_rows = count($this->_object->data);
		}
		else
		{
		    $this->_total_rows = 0;
		}

		return TRUE;
	}

	public function serialize()
	{
		if ( ! $this->_request_made)
		{
			$this->_load();
		}

		foreach (array('_object', '_url', '_params', '_request_made') as $var)
		{
			$data[$var] = $this->{$var};
		}

		return serialize($data);
	}

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
		if ( ! $this->seek($this->_postion))
		{
			return NULL;
		}

		return $this->_object->data[$this->_postion];
	}

	public function valid()
	{
		if ( ! $this->_request_made)
		{
			$this->_load();
		}

		return isset($this->_object->data[$this->_position]);
	}

	public function offsetExists($offset)
	{
		if ( ! $this->_request_made)
		{
			$this->_load();
		}

		return ($offest >= 0 AND $offest < $this->_total_rows);
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

