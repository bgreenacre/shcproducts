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
 * Library_Sears_Api
 *
 */
class Library_Sears_Api implements Countable, Iterator, SeekableIterator, ArrayAccess, Serializable {

    protected $endpoint;
    protected $apikey;
    protected $authid;
    protected $appid;
    protected $content_type = 'json';
    protected $curl_options = array(
        CURLOPT_RETURNTRANSFER  => 1,
        CURLOPT_HTTPHEADER      => array('X-SHCMMR-Client-Id: app_ui'),
    );
    protected $store;
    protected $_group;
    protected $_params = array();
    protected $_url;
    protected $_method;
    protected $_parent;
    protected $_data;

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

    public static function factory($class, $group = NULL, & $parent = NULL)
    {
        $class = 'Library_Sears_Api_' . ucfirst($class);

        try
        {
            return new $class($group, $parent);
        }
        catch(Exception $e)
        {
            throw new Exception($e);
        }
    }

    public function __construct($group = NULL, & $parent = NULL)
    {
        $this->_initialize();

        if ($group === NULL)
        {
            $this->_group = 'default';
        }
        else
        {
            $this->_group = $group;
        }

        $config = (array) SHCP::config('api.' . $this->_group);

        foreach ($config as $property => $value)
        {
            $this->{$property} = $value;
        }

        /*
        $settings = (array) get_option('shcproducts');

        foreach ($settings as $setting => $value)
        {
            $this->{$setting} = $value;
        }
        */

        if ($parent)
        {
            $this->_parent = $parent;
        }
    }

	protected function _initialize()
	{
		$this->_object = NULL;
		$this->_data = array();
		$this->_position = 0;
		$this->_total_rows = 0;
		$this->_params = array();
		$this->_request_made = FALSE;
		$this->_parent = NULL;
		$this->_url = NULL;
		$this->_method = NULL;
	}

    public function method($method = NULL)
    {
        if ($method === NULL)
        {
            return $this->_method;
        }

        $this->_method = $method;

        return $this;
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

    public function param($name, $value = NULL, $append = FALSE)
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

        if ($append !== FALSE)
        {
            if ($this->_params[$name])
            {
                $this->_params[$name] .= ',' . $value;
            }
            else
            {
                $this->_params[$name] = $value;
            }
        }
        else
        {
            $this->_params[$name] = $value;
        }

        return $this;
    }

    protected function build_url()
    {
        $url = rtrim($this->endpoint, '/') . '/' . $this->method();

        if ($this->_params)
        {
            $qs = '?';

            foreach (array_reverse($this->_params) as $param => $value)
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
            ->param('store', $this->store)
            ->param('contentType', $this->content_type)
            ->param('authid', $this->authid)
            ->param('appid', $this->appid)
            ->param('apikey', $this->apikey);

        $this->_url = $this->build_url();

        $ch = curl_init($this->_url);

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

		if (strpos($body, '<fault>') !== FALSE)
        {
            $fault = simplexml_load_string($body);

            if (isset($fault[0]) === TRUE)
            {
                var_dump($this);
                throw new Exception('Invalid API request made.');
            }
            elseif (isset($fault['faultstring']) === TRUE)
            {
                throw new Exception($fault['faultstring']);
            }

            return FALSE;
        }

        if ($this->content_type == 'json')
        {
            $this->_object = json_decode($body);
        }
        elseif ($this->content_type == 'xml')
        {
            $this->_object = simplexml_load_string($body);
        }

        unset($body);
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

	public function sort($sort)
	{
	    $this->param('sortBy', $sort);
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
