<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
 * Hooks for filters system
 */

require(APPPATH.'hooks/filters/Filter'.EXT);
require(APPPATH.'hooks/filters/Pipe'.EXT);

/**
 * Pipe singleton
 */
function &get_pipe()
{
	global $class, $method;
	static $PIPE;
	
	if (!is_object($PIPE)) {
		// Load the filters list file
		@include(APPPATH.'config/filters'.EXT);
		if ( ! isset($filter) OR ! is_array($filter))
		{
			log_message('debug', 'Your filters configuration file doesn\'t appear to be formatted correctly.');
		}
		$PIPE = new Pipe($filter, $class, $method);
	}

	return $PIPE;
}

/**
 * Before controller processing
 */
function pre_filter()
{
	$PIPE = get_pipe();
	$PIPE->process_before();
}

/**
 * After controller processing
 */
function post_filter()
{
	$PIPE = get_pipe();
	$PIPE->process_after();
}
?>