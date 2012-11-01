<?php

  // set http header
  header("Content-Type: text/html; charset=UTF-8");
  
  // restrict direct script access
  if (!defined('BASEPATH')) exit('No direct script access allowed');
  
  // set title
  $var_title = 'A2.5d ' . $pageTitle;
  
  // set path to webcontent
  $var_webcontent = AIGAION_WEBCONTENT_URL;
  
  // set style name
  $var_style =  (getThemeName() != 'default') ? getThemeName() : 'default';
  
  // get user id
  $uid = $userlogin->userId();
    
  // get quicksearch form
  $var_quick = form_open('search/quicksearch')."\n";
  $var_quick .= "<div>\n";
  $var_quick .= form_hidden('formname','simplesearch');
  $var_quick .= form_input(array('name' => 'searchstring', 'size' => '35', 'placeholder' => __('Search me')));
  $var_quick .= form_submit('submit_search', __('Go'));
  $var_quick .= "</div>\n";
  $var_quick .= form_close();
  
  // action
  $var_action = array('title' => __('Bibliography'), 'items' => array());
  $var_action['items'][] = array('href' => site_url('import'), 'title' => __('Import one or more publications'), 'text' => __('Import'));
  $var_action['items'][] = array('href' => site_url('publications/add'), 'title' => __('Create a new Publication'), 'text' => __('New Publication'));
  $var_action['items'][] = array('href' => site_url('authors/add'), 'title' => __('Create a new Author'), 'text' => __('New Author'));
  $var_action['items'][] = array('href' => site_url('topics/add'), 'title' => __('Create a new Topic'), 'text' => __('New Topic'));
  $var_action['items'][] = array('href' => site_url('search'), 'title' => __('Search'), 'text' => __('Search'));
  
  // create main menu
  $var_view = array('title' => __('View'), 'items' => array());
  $var_view['items'][] = array('href' => site_url('topics'), 'title' => __('My Topics'), 'text' => __('My Topics'));
  if ($userlogin->hasRights('bookmarklist'))
  {
    $var_view['items'][] = array('href' => site_url('bookmarklist'), 'title' => __('My Bookmarks'), 'text' => __('My Bookmarks'));
  }
  $var_view['items'][] = array('href' => site_url('topics/all'), 'title' => __('All Topics'), 'text' => __('All Topics'));
  $var_view['items'][] = array('href' => site_url('publications'), 'title' => __('All Publications'), 'text' => __('All Publications'));
  $var_view['items'][] = array('href' => site_url('authors'), 'title' => __('All Authors'), 'text' => __('All Authors'));
  $var_view['items'][] = array('href' => site_url('keywords'), 'title' => __('All Keywords'), 'text' => __('All Keywords'));
  $var_view['items'][] = array('href' => site_url('publications/unassigned'), 'title' => __('Unassigned'), 'text' => __('Unassigned'));
  $var_view['items'][] = array('href' => site_url('publications/showlist/recent'), 'title' => __('Recent'), 'text' => __('Recent'));
  
  // create system menu
  $var_sys = array('title' => __('System'), 'items' => array());
  $var_sys['items'][] = array('href' => site_url('help'), 'title' => '', 'text' => __('Help'));
  $var_sys['items'][] = array('href' => site_url('help/viewhelp/about'), 'title' => '', 'text' => __('About this site'));
  if ($userlogin->hasRights('database_manage'))
  {
    $var_sys['items'][] = array('href' => site_url('site/configure'), 'title' => '', 'text' => __('Site Configuration'));
    $var_sys['items'][] = array('href' => site_url('site/maintenance'), 'title' => '', 'text' => __('Site Maintenance'));
  } 
  $var_sys['items'][] = array('href' => site_url('users/edit/' . $uid), 'title' => '', 'text' => __('My Profile'));
  $var_sys['items'][] = array('href' => site_url('users/setpassword/' . $uid), 'title' => '', 'text' => __('Set password'));
  $var_sys['items'][] = array('href' => site_url('help'), 'title' => '', 'text' => __('Help'));
  $var_sys['items'][] = array('href' => site_url('login/dologout'), 'title' => '', 'text' => __('Logout'));
  
  // merge variables
  $this->tbswrapper->tbsLoadTemplate(APPPATH . 'templates/header3.tpl.html');
  $this->tbswrapper->tbsMergeField('webroot', $var_webcontent);
  $this->tbswrapper->tbsMergeField('style', $var_style);
  $this->tbswrapper->tbsMergeField('quicksearch', $var_quick);
  $this->tbswrapper->tbsMergeBlock('navaction', $var_action['items']);
  $this->tbswrapper->tbsMergeField('navactiontitle', $var_action['title']);
  $this->tbswrapper->tbsMergeBlock('navview', $var_view['items']);
  $this->tbswrapper->tbsMergeField('navviewtitle', $var_view['title']);
  $this->tbswrapper->tbsMergeBlock('navsys', $var_sys['items']);
  $this->tbswrapper->tbsMergeField('navsystitle', $var_sys['title']);
  $this->tbswrapper->tbsMergeField('title', $var_title);
  $this->tbswrapper->tbsMergeField('autoCompleteUrl', site_url('/search/preview'));
  
  // render
  echo $this->tbswrapper->tbsRender();

?>