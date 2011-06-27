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

class Controller_Widget extends WP_Widget {

	protected $widget_template = 'widget/template';
	protected $content;
	protected $cache = TRUE;

	public function __construct($id_base = NULL, $name = NULL, array $widget_options = NULL, array $control_options = NULL)
	{
		$class_name = str_replace('controller_widget_', '', strtolower(get_class($this)));
		$config_file = 'widget/' . $class_name;

		if ($widget_template = SHCP::config($config_file . '.template'))
        {
            $this->widget_template = $widget_template;
        }

       	$id_base = ($id_base === NULL) ? SHCP::config($config_file . '.id') : $id_base;
       	$name = ($name === NULL) ? SHCP::config($config_file . '.name') : $name;
       	$widget_options = ($widget_options === NULL) ? (array) SHCP::config($config_file . '.widget_options') : $widget_options;
       	$control_options = ($control_options === NULL) ? (array) SHCP::config($config_file . '.control_options') : $control_options;
       	parent::__construct($id_base, $name, $widget_options, $control_options);
       	add_action('widgets_init', array($this, 'register'), (int) SHCP::config($config_file . '.action_priority'));
        add_action('save_post', array($this, 'flush_cache'));
        add_action('deleted_post', array($this, 'flush_cache'));
        add_action('switch_theme', array($this, 'flush_cache'));
    }

    public function register()
    {
    	register_widget(get_class($this));
    }

    public function widget($args, $instance)
    {
    	if ($this->cache)
        {
    	    $cache = (array) wp_cache_get($this->id_base, 'widget');

    	    if ($content = SHCP::get($cache, $args['widget_id']))
            {
         	    echo $content;
             	return;
            }
        }

    	$data = $args + $instance;
    	$data['title'] = apply_filters('widget_title', SHCP::get($instance, 'title', $this->name));
    	$data['content'] = $this->content;

    	$content = SHCP::view($this->widget_template, $data);

    	if ($this->cache)
        {
    	    $cache[$args['widget_id']] = $content;
        	wp_cache_set($this->id_base, $cache, 'widget');
        }

        echo $content;
    }

    public function flush_cache()
    {
    	wp_cache_delete($this->id_base, 'widget');
    }

}