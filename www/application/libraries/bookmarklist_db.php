<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
class Bookmarklist_db {


  function Bookmarklist_db()
  {
  }

    function addPublication($pub_id)
    {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage(__("Changing bookmarklist").": ".__("insufficient rights").".<br/>");
            return;
        }
        $CI->db->query("INSERT IGNORE INTO ".AIGAION_DB_PREFIX."userbookmarklists (user_id,pub_id) VALUES (".$CI->db->escape($userlogin->userId()).",".$CI->db->escape($pub_id).")");
    	if (mysql_error()) {
    		appendErrorMessage(__("Error changing bookmarklist").".<br/>");
    	}

    }

    function removePublication($pub_id)
    {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage(__("Changing bookmarklist").": ".__("insufficient rights").".<br/>");
            return;
        }
        $CI->db->delete('userbookmarklists',array('user_id'=>$userlogin->userId(),'pub_id'=>$pub_id));
    	if (mysql_error()) {
    		appendErrorMessage(__("Error changing bookmarklist").".<br/>");
    	}

    }

    function addTopic($topic_id)
    {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage(__("Changing bookmarklist").": ".__("insufficient rights").".<br/>");
            return;
        }
        //get all accessible publications for this topic
        $pubs = $CI->publication_db->getVisibleForTopic($topic_id,'',-1);
        foreach ($pubs as $pub) {
            $this->addPublication($pub->pub_id);
        }

    }
    
    function removeTopic($topic_id)
    {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage(__("Changing bookmarklist").": ".__("insufficient rights").".<br/>");
            return;
        }
        //get all accessible publications for this topic
        $pubs = $CI->publication_db->getVisibleForTopic($topic_id,'',-1);
        foreach ($pubs as $pub) {
            $this->removePublication($pub->pub_id);
        }

    }
    function addAuthor($author_id, $include_synonyms = false)
    {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage(__("Changing bookmarklist").": ".__("insufficient rights").".<br/>");
            return;
        }
        //get all accessible publications for this author
        $pubs = $CI->publication_db->getForAuthor($author_id,'',-1,$include_synonyms);
        foreach ($pubs as $pub) {
            $this->addPublication($pub->pub_id);
        }

    }
    
    function removeAuthor($author_id,$include_synonyms=false)
    {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage(__("Changing bookmarklist").": ".__("insufficient rights").".<br/>");
            return;
        }
        //get all accessible publications for this author
        $pubs = $CI->publication_db->getForAuthor($author_id,'',-1,$include_synonyms);
        foreach ($pubs as $pub) {
            $this->removePublication($pub->pub_id);
        }

    }

    function addKeyword($keyword_id)
    {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage(__("Changing bookmarklist").": ".__("insufficient rights").".<br/>");
            return;
        }
        //get all accessible publications for this author
        $keyword = $CI->keyword_db->getByID($keyword_id);
        $pubs = $CI->publication_db->getForKeyword($keyword,-1);
        foreach ($pubs as $pub) {
            $this->addPublication($pub->pub_id);
        }
    }
    
    function removeKeyword($keyword_id)
    {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage(__("Changing bookmarklist").": ".__("insufficient rights").".<br/>");
            return;
        }
        //get all accessible publications for this author
        $keyword = $CI->keyword_db->getByID($keyword_id);
        $pubs = $CI->publication_db->getForKeyword($keyword,-1);
        foreach ($pubs as $pub) {
            $this->removePublication($pub->pub_id);
        }
    }

    function clear() {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage(__("Changing bookmarklist").": ".__("insufficient rights").".<br/>");
            return;
        }
        $CI->db->delete('userbookmarklists',array('user_id'=>$userlogin->userId()));
    	if (mysql_error()) {
    		appendErrorMessage(__("Error changing bookmarklist").".<br/>");
    	}
    }
    
    function addToTopic($topic) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist') || !$userlogin->hasRights('publication_edit')) {
            appendErrorMessage(__("Changing bookmarklist").": ".__("insufficient rights").".<br/>");
            return;
        }
        $pub_ids = array();
        foreach ($CI->publication_db->getForBookmarkList() as $publication) {
            $pub_ids[] = $publication->pub_id;
        }
        $topic->subscribePublicationSetUpRecursive($pub_ids);
        appendMessage(__("Bookmarked publications added to topic").".<br/>");
    }

    function removeFromTopic($topic) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist') || !$userlogin->hasRights('publication_edit')) {
            appendErrorMessage(__("Changing bookmarklist").": ".__("insufficient rights").".<br/>");
            return;
        }
        $pub_ids = array();
        foreach ($CI->publication_db->getForBookmarkList() as $publication) {
            $topic->configuration['publicationId'] = $publication->pub_id;
            $topic->unsubscribePublication();
        }
        appendMessage(__("Bookmarked publications removed from topic").".<br/>");
    }
}
?>