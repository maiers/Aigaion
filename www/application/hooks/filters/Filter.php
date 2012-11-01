<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Base class for all filters. Defines empty before() and after() methods.
* Override them in your filter class to customize the filter behavior.
*/
class Filter {
	var $config; // filter configuration (you can pass it from config/filters.php)
	var $controller; // current controller
	var $method; // current action
	
	/**
	* Default constructor.
	* Allows to set filter configuration.
	*/
	function Filter($config = array())
	{
		$this->config = $config;
	}
	
	/**
	* Empty method - override it to run your code _before_ the controller.
	* You can access $this->config to check filter configuration params.
	*/
	function before() { }
	
	/**
	* Empty method - override it to run your code _after_ the controller.
	* You can access $this->config to check filter configuration params.
	*/
	function after() { }
}
?>