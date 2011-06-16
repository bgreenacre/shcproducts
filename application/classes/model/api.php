<?php
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
 * SHCP_Model_Api
 *
 */
class SHCP_Model_Api {

    protected $endpoint;
    protected $_params = array();

    public function __construct($group = NULL)
    {
        if ($group === NULL)
        {
            $group = 'default';
        }

        $config = (array) SHCP::config('api.' . $group);

        foreach ($config as $property => $value)
        {
            $this->{$property} = $value;
        }
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

    private function _request()
    {
        if ($this->endpoint === NULL)
        {
            throw new Exception('No endpoint provided for Sears API request');
        }

        $this->param('contentType', 'json')
            ->param('apikey', $this->apikey);

        $url = $this->build_url();
    }

}

