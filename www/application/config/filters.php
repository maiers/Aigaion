<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Filters configuration
| -------------------------------------------------------------------
|
| Note: The filters will be applied in the order that they are defined
|
| Example configuration:
|
| $filter['auth'] = array('exclude', array('login/*', 'about/*'));
| $filter['cache'] = array('include', array('login/index', 'about/*', 'register/form,rules,privacy'));
|
*/

/** LOGIN FILTERS
 * The login filter has three variations, each with its own action, and its 
 * own include/exclude parameters.
 * 1) 'redirect'
 *    by default, all page access is checked for login. If the user is not
 *    logged in, he is redirected to the login controller, which attempts to log
 *    the user in. If that fails (no form data, no cookies, etc) the user is 
 *    redirected to the login form page.
 * 2) 'redirectnoform'
 *    Same as above, but if the user cannot be logged in by the login 
 *    controller, he is not redirected to the login form but to a 'fail' page
 * 3) 'fail'
 *    If the user is not logged in, no attempt is made to perform a login from 
 *    forms or cookies. The user is immediately sent to a 'fail' page  
 */         
/** By default, when no user is logged in, control is passed to the login form.
    Add controllers for which this should not happen to the exclude array. */
$filter['login'][] = array(
	'exclude', array('login/*','version/*','authors/embed','embeddingtest/*','logintegration/*','rss/publicstream'), array('action'=>'redirect')
);
/** Of the excluded controlles above, some should still attempt to login, but
 * should not redirect the user to the login form page if that fails
 * Add controllers for which this should happen to the include array. */
$filter['login'][] = array(
	'include', array('authors/embed','embeddingtest/*'), array('action'=>'redirectnoform')
);
/** For some controllers, failure of the login check should simply result in the
    display of a div with an error message defined in the login/fail view.
    Add controllers for which this should happen to the include array below. */
$filter['login'][] = array(
	'include', array(), array('action'=>'fail')
);


?>