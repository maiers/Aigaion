<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class regulates the database access for Topic's. Several accessors are present that return a Topic or an
array of Topic's. 

For most methods a configuration for the tree structure can be provided. Depending on this configuration, 
the tree will be constructed e.g. for all topics, for only those topics to which a specific user is subscribed, 
etc... 
Furthermore, for some configurations each topic in the tree will be flagged with additional information.

Note: those topics for which you do not have sufficient rights will not be included in the topic tree, no matter 
what configuration you give... 

Possible configuration parameters:
    onlyIfUserSubscribed            -- if set to True, only those topics 
                                       will be included in the tree to which the user specified by 'user' is 
                                       subscribed
    user                            -- if set, the 'userIsSubscribed' will be set for all topics that this user 
                                       is subscribed to
    includeGroupSubscriptions       -- if set together with user, all topic subscriptions inherited from group
                                       memberships are taken into account as well
    flagCollapsed                   -- if set to True, the collapse status of the topic for the user specified by 
                                       'user' will be flagged by 'userIsCollapsed'
    
    onlyIfPublicationSubscribed     -- if set to True, only those topics 
                                       will be included in the tree to which the publication specified by 
                                       'publicationId' is subscribed
    publicationId                   -- if set, the 'publicationIsSubscribed' will be set for all topics that this 
                                       publication is subscribed to
                                       
Possible flags:
    userIsSubscribed
    userIsGroupSubscribed
    userIsCollapsed
    
    publicationIsSubscribed
    
 
*/

class Topic_db {
    
  
    function Topic_db()
    {
    }
   
    /** Returns the Topic with the given ID. 
    Note: because $configuration is passed by reference, 
    you must have a proper variable. You cannot call this method as 'getByID($some_id, array(some=>config))' !!!
    
    WB may 27, 2007 -> $configuration was passed by reference, but variables 
    passed by reference cannot have a default value in php4.
    Removed the reference pass
    
    DR: THAT SHOULD NOT HAVE BEEN DONE! BETTER CHANGE THE CALLS. PASS BY REFERENCE SAWES 40 % PERFORMANCE ON TOPIC TREES
    */
    function getByID($topic_id, &$configuration)
    {
        $CI = &get_instance();
        $Q = $CI->db->get_where('topics', array('topic_id' => $topic_id));
        //configuration stuff will be handled by the getFromRow method...
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row(), $configuration);
        } else {
            return null;
        }
    }

    /** Returns the Topic stored in the given table row, or null if insufficient rights 
    WB may 27, 2007 -> $configuration was passed by reference, but variables 
    passed by reference cannot have a default value in php4.
    Removed the reference pass

    DR: BETTER CHANGE THE CALLS NOT TO HAVE DEFAULT VALUE. PASS BY REFERENCE SAVES 40 % PERFORMANCE ON TOPIC TREES
    */
    function getFromRow($R, &$configuration)
    {
        $CI = &get_instance();
        $topic = new Topic;
        foreach ($R as $key => $value)
        {
            $topic->$key = $value;
        }
        $userlogin  = getUserLogin();
        //check rights, if fail return null
        if ( !$CI->accesslevels_lib->canReadObject($topic))return null;
        $topic->configuration = $configuration;
        //process configuration settings
        /*  onlyIfUserSubscribed            -- if set to True, only those topics 
                                               will be included in the tree to which the user specified by 'user' is 
                                               subscribed 
            user                            -- if set, the 'userIsSubscribed' flag will be set for all topics that this user 
                                               is subscribed to
            includeGroupSubscriptions       -- if set together with user, all topic subscriptions inherited from group
                                               memberships are taken into account as well
            flagCollapsed                   -- if set to True, the collapse status of the topic for the user specified by 
                                               'user' will be flagged by 'userIsCollapsed'
            Flags:                                   
                userIsSubscribed
                userIsCollapsed
                userIsGroupSubscribed
        */
        if (array_key_exists('user',$configuration)) {
            $userSubscribedQ = $CI->db->get_where('usertopiclink', array('topic_id' => $topic->topic_id,  
                                                                          'user_id' => $configuration['user']->user_id));
            $groupIrrelevant = True;
            $groupSubscribed = False;
            if (array_key_exists('includeGroupSubscriptions',$configuration)) {
                $groupIrrelevant = False;
                if (count($configuration['user']->group_ids)>0) {
                    $groupSubscribedQ = $CI->db->query('SELECT * FROM '.AIGAION_DB_PREFIX.'usertopiclink WHERE topic_id='.$CI->db->escape($topic->topic_id).' AND user_id IN ('.$CI->db->escape(implode(',',$configuration['user']->group_ids)).');');
                    $groupSubscribed = $groupSubscribedQ->num_rows()>0;
                } else {
                    $groupSubscribed = FALSE;
                }
                $topic->flags['userIsGroupSubscribed'] = $groupSubscribed;
            }
            if (array_key_exists('onlyIfUserSubscribed',$configuration) && $configuration['onlyIfUserSubscribed']) {
                if ($userSubscribedQ->num_rows() == 0) { //not subscribed: check group subscriptions
                    if ($groupIrrelevant || !$groupSubscribed) {
                        return null;
                    }
                }
            }
            if (($userSubscribedQ->num_rows() > 0) || $groupSubscribed) {
                $topic->flags['userIsSubscribed'] = True;
                if (array_key_exists('flagCollapsed',$configuration)) {
                    if ($userSubscribedQ->num_rows() > 0) {
                        $R = $userSubscribedQ->row();
                        $topic->flags['userIsCollapsed'] = $R->collapsed=='1';
                    } else {
                        $topic->flags['userIsCollapsed'] = True;
                    }
                }
            } else {
                $topic->flags['userIsSubscribed'] = False;
                $topic->flags['userIsCollapsed'] = True;
            }
                
        }
        /*  onlyIfPublicationSubscribed     -- if set to True, only those topics 
                                               will be included in the tree to which the publication specified by 
                                               'publicationId' is subscribed
            publicationId                   -- if set, the 'publicationIsSubscribed' will be set for all topics that this 
                                               publication is subscribed to
            Flags:                                   
                publicationIsSubscribed
                                               */
        if (array_key_exists('publicationId',$configuration)) {
            $pubSubscribedQ = $CI->db->get_where('topicpublicationlink', 
                                                       array('topic_id' => $topic->topic_id,  
                                                             'pub_id'=>$configuration['publicationId']));
            $topic->flags['publicationIsSubscribed'] = False;
            if (array_key_exists('onlyIfPublicationSubscribed',$configuration)) {
                if ($pubSubscribedQ->num_rows() == 0) { //not subscribed: return null!
                    return null;
                }
                $topic->flags['isPublicationSubscriptionTree'] = True;
            }
            if ($pubSubscribedQ->num_rows() > 0) {
                $topic->flags['publicationIsSubscribed'] = True;
            } 
        }
            
        //always get parent
        $topic->parent_id = $this->getParentId($topic->topic_id);
        return $topic;
    }

	/*
	Accepts an array with a topic structure.

	Starts with the last item of the list and searches forward through the array to find unique topic.
	Given that the two topic structures with the names and ids:
	a(id2)/b(id3)/c(id4)
	and
	d(id5)/e(id6)/c(id7)
	exist and the function is sent an array with a-b-c, the function first checks c, finds two topics with the same name,
	then checks b-c and returns topic c with id4.

	Given that Aigiona allows identical topic structures an array of matching topics is returned, if the structure is not unique. e.g.
	if there are two structures a(id10)/b(id11)/c(id12) and a(id20)/b(id21)/c(id22) both id12 and id22 are returned.

  Contribution by {\O}yvind
	*/
	function getTopicIDFromNames($topic_name, &$configuration)
	{
		$selected_topics = array();
		$num_topics = count($topic_name);
		$counter = $num_topics-1;
		$CI = &get_instance();
		$Q = $CI->db->getwhere('topics', array('name' => $topic_name[$counter]));

		$numMatches = $Q->num_rows();
		//NO topics match the last item in the $topic_name array
		if($numMatches == 0)
		{
			return $selected_topics;
		}
		//One topic with the given name (the last one in the array) is found.
		elseif($numMatches == 1)
		{
			$topic = $this->getFromRow($Q->row(), $configuration);
      $topic_id = $topic->topic_id;
			$selected_topics[] = $topic_id;
			return $selected_topics;
		}
		//More than one topic is found
		else
		{
		  
			$currentRow = $Q->row();
			$finished = false;
			$nextRow = $Q->next_row();

			//traverses all the topics with the given name (the last in the array)
			while(!$finished){
				$topic = $this->getFromRow($currentRow, $configuration);
        $topic_id = $topic->topic_id;
				$parent_id = $this->getParentId($topic_id);

				/*
					Compares the parent topics of the given topic with the items in the 'topic_name' array
			    Uses a depth first search and requires the whole topic structure to be statet until
			    the 'top' topic, for instance top/a/b/c and top/d/e/c.
			    A width first search could allow input of only a unique part of the topic structure
			    for instance b/c and e/c instead of the whole structure.
			    However, a width first search would require maitaining several lists with the structures
			    and I think it is easier to do it right with a depth first search.
				*/
				$counter = $num_topics-2;
				if ($counter<0) 
        {
          //structure of only one deep...
          $selected_topics[]  = $topic_id;
        }
        else
				while($counter >= 0)
				{
					$parent = $this->getByID($parent_id, $configuration);
					//top found...
					if(($counter == 0)&&($parent_id == 1) && ($topic_name[$counter]=="top"))
					{
							$selected_topics[]  = $topic_id;
							$counter--;
					} else
          if($topic_name[$counter] == $parent->name)
					{
						$counter--;
						$parent_id = $this->getParentId($parent_id);
						//we reached the last topic name , and we did not find any non-hit: select this topic, too
						if ($counter < 0) 
            {
              $selected_topics[] = $topic_id;
            }
					}
					else
					{
						$counter = -1;
					}
				}
				if($currentRow == $nextRow)
				{
					$finished = true;
				}
				$currentRow = $nextRow;
				$nextRow = $Q->next_row();
			}
			return $selected_topics;
		}
	}
	
    /** Construct a topic from the POST data present in the topics/edit view. 
    Return null if the POST data was not present. */
    function getFromPost()
    {
        $CI = &get_instance();
        $topic = new Topic;
        //correct form?
        if ($CI->input->post('formname')!='topic') {
            return null;
        }
        //get basic data
        $topic->topic_id           = $CI->input->post('topic_id');
        $topic->name               = $CI->input->post('name');
        $topic->description        = $CI->input->post('description');
        $topic->url                = $CI->input->post('url');
        $topic->parent_id          = $CI->input->post('parent_id');
        $topic->user_id            = $CI->input->post('user_id');
        
        //fetch custom fields
        $topic->customfields = $CI->customfields_db->getFromPost('topic');
        
        return $topic;
    }
    
    /** Return an array of Topic's retrieved from the database that are the children of the given topic. 
    WB may 27, 2007 -> $configuration was passed by reference, but variables 
    passed by reference cannot have a default value in php4.
    Removed the reference pass

    DR: BETTER CHANGE THE CALLS NOT TO HAVE DEFAULT VALUE. PASS BY REFERENCE SAVES 40 % PERFORMANCE ON TOPIC TREES
    */
    function getChildren($topic_id, &$configuration) {
        $CI = &get_instance();
        $children = array();
        //get children from database; add to array
        $query = $CI->db->query("SELECT ".AIGAION_DB_PREFIX."topics.* FROM ".AIGAION_DB_PREFIX."topics, ".AIGAION_DB_PREFIX."topictopiclink
                                        WHERE ".AIGAION_DB_PREFIX."topictopiclink.target_topic_id=".$CI->db->escape($topic_id)."
                                          AND ".AIGAION_DB_PREFIX."topictopiclink.source_topic_id=".AIGAION_DB_PREFIX."topics.topic_id
                                     ORDER BY name");
        foreach ($query->result() as $row) {
            $c = $this->getFromRow($row,$configuration);
            if ($c != null) {
                $children[] = $c;
            }
        }
        return $children;
    }

    /** Returns the topic_id of the parent of the given Topic through database access. */
    function getParentId($topic_id) {
        $CI = &get_instance();
        $query = $CI->db->get_where('topictopiclink',array('source_topic_id'=>$topic_id));
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->target_topic_id;
        } else {
            return null;
        }
    }
    
    /** Subscribe given publication to given topic in database. no recursion. */
    function subscribePublication($pub_id,$topic_id) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (    
                (!$userlogin->hasRights('publication_edit'))
            ) {
	        appendErrorMessage(__('Categorize publication').': '.__('insufficient rights').'.<br/>');
	        return;
        }
        $CI->db->delete('topicpublicationlink', array('pub_id' => $pub_id, 'topic_id' => $topic_id)); 
        $CI->db->insert('topicpublicationlink', array('pub_id' => $pub_id, 'topic_id' => $topic_id)); 
    }
    
    /** Unsubscribe given publication from given topic in database. no recursion. */
    function unsubscribePublication($pub_id,$topic_id) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (    
                (!$userlogin->hasRights('publication_edit'))
            ) {
	        appendErrorMessage(__('Categorize publication').': '.__('insufficient rights').'.<br/>');
	        return;
        }
        $CI->db->delete('topicpublicationlink', array('pub_id' => $pub_id, 'topic_id' => $topic_id)); 
    }

    
    /** Subscribe given user to given topic in database. no recursion. */
    function subscribeUser($user,$topic_id) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (    
                (!$userlogin->hasRights('topic_subscription'))
            ) {
	        appendErrorMessage(__('Change subscription').': '.__('insufficient rights').'.<br/>');
	        return;
        }
        $CI->db->delete('usertopiclink', array('user_id' => $user->user_id, 'topic_id' => $topic_id)); 
        $CI->db->insert('usertopiclink', array('user_id' => $user->user_id, 'topic_id' => $topic_id)); 
    }
    
    /** Unsubscribe given user from given topic in database. no recursion. */
    function unsubscribeUser($user,$topic_id) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        if (    
                (!$userlogin->hasRights('topic_subscription'))
            ) {
	        appendErrorMessage(__('Change subscription').': '.__('insufficient rights').'.<br/>');
	        return;
        }
        $CI->db->delete('usertopiclink', array('user_id' => $user->user_id, 'topic_id' => $topic_id)); 
    }


    /** Add a new topic with the given data. Returns the new topic_id, or -1 on failure. */
    function add($topic) {
        $CI = &get_instance();
        $CI->load->helper('cleanname');
        //check access rights (!)
        $userlogin = getUserLogin();
        if (    
                (!$userlogin->hasRights('topic_edit'))
            ) 
        {
	        appendErrorMessage(__('Add topic').': '.__('insufficient rights').'.<br/>');
	        return;
        }        
        $fields = array('name'=>$topic->name,
                        'cleanname'=>cleanTitle($topic->name),
                        'description'=>$topic->description,
                        'url'=>$topic->url,
                        'user_id'=>$userlogin->userId());
        //add new topic
        $CI->db->insert('topics', $fields);
                                               
        //add parent
        $new_id = $CI->db->insert_id();
        $topic->topic_id = $new_id;
        
        //add custom fields
        $CI->customfields_db->addForID($topic->topic_id, $topic->getCustomFields());

        if ($topic->parent_id < 0)$topic->parent_id=1;
        $CI->db->insert('topictopiclink',array('source_topic_id'=>$new_id,'target_topic_id'=>$topic->parent_id));
        //subscribe current user to new topic
        $this->subscribeUser($CI->user_db->getByID($userlogin->userId()),$new_id);
        $CI->accesslevels_lib->initTopicAccessLevels($topic);
        return $new_id;
    }

    /** Commit the changes in the data of the given topic. Returns TRUE or FALSE depending on 
    whether the operation was successful. */
    function update($topic) {
        $CI = &get_instance();
        $CI->load->helper('cleanname');
        if ($topic->topic_id==1) {
            appendErrorMessage(__("You cannot edit the top level topic.")."<br/>");
            return;
        }

        //check access rights (by looking at the original topic in the database, as the POST
        //data might have been rigged!)
        $userlogin  = getUserLogin();
        $user       = $CI->user_db->getByID($userlogin->userID());
        $config=array();
        $topic_testrights = $CI->topic_db->getByID($topic->topic_id,$config);
        if (    ($topic_testrights == null) 
             ||
                (!$userlogin->hasRights('topic_edit'))
             || 
                (!$CI->accesslevels_lib->canEditObject($topic_testrights))
            ) 
        {
	        appendErrorMessage(__('Edit topic').': '.__('insufficient rights').'.<br/>');
	        return;
        }
        
        if ($topic->parent_id < 0)$topic->parent_id=1;
        //check parent for non-circularity (!). Actually, this should be done in a validation callback on the edit forms!
    	$nexttopic_id = $topic->parent_id;
    	while ($nexttopic_id != 1) {
    		if ($nexttopic_id == $topic->topic_id) {
    			appendErrorMessage(__("You cannot set a topic to be its own ancestor.")."<br/>");
    			return False;
    		}
    		$CI->db->select('target_topic_id');
    		$Q = $CI->db->get_where('topictopiclink',array('source_topic_id'=>$nexttopic_id));
    		if ($Q->num_rows()>0) {
    		    $R = $Q->row();
    			$nexttopic_id = $R->target_topic_id;
    		} else {
    			appendErrorMessage(__("Error in the tree structure: the intended new parent is not connected to the top level topic.")."<br/>");
    			return False;
    		}
    	}

        $updatefields =  array('name'=>$topic->name,
                               'cleanname'=>cleanTitle($topic->name),
                               'description'=>$topic->description,
                               'url'=>$topic->url);

        $CI->db->update('topics', $updatefields, array('topic_id'=>$topic->topic_id));
        
        //update custom fields
        $CI->customfields_db->updateForID($topic->topic_id, $topic->getCustomFields());
        
        if ($topic_testrights->parent_id != $topic->parent_id) {
            //remove and set parent link
            $CI->db->delete('topictopiclink',array('source_topic_id'=>$topic->topic_id));
            $CI->db->insert('topictopiclink',array('source_topic_id'=>$topic->topic_id,'target_topic_id'=>$topic->parent_id));
    
        	#change membership of publications to reflect new tree structure (are there different strategies for this?)
        	//get all publications that are member of $topic_id
        	$CI->db->select('pub_id');
        	$Q = $CI->db->get_where('topicpublicationlink',array('topic_id' => $topic->topic_id));
        	if ($Q->num_rows()>0) {
        	    $pub_ids = array();
        		foreach ($Q->result() as $publication) {
        		    $pub_ids[] = $publication->pub_id;
        		}
        		$topic->subscribePublicationSetUpRecursive($pub_ids);
        	}        
        }
        return True;
    }
    /** delete given object. where necessary cascade. Checks for edit and read rights on this object and all cascades
    in the _db class before actually deleting. */
    function delete($topic) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        //collect all cascaded to-be-deleted-id's: none
        //check rights
        //check, all through the cascade, whether you can read AND edit that object
        if (!$userlogin->hasRights('topic_edit')
            ||
            !$CI->accesslevels_lib->canEditObject($topic)
            ) {
            //if not, for any of them, give error message and return
            appendErrorMessage(__('Cannot delete topic').': '.__('insufficient rights').'.<br/>');
            return;
        }
        if (empty($topic->topic_id)) {
            appendErrorMessage(__('Cannot delete topic').': '.__('erroneous ID').'.<br/>');
            return;
        }
        //no delete for object with children. check through tables, not through object
        #NOTE: if we want to change this, we should make sure that a user can only delete a topic
        #when (s)he has edit access to ALL descendants!
        $Q = $CI->db->get_where('topictopiclink',array('target_topic_id'=>$topic->topic_id));
        if ($Q->num_rows()>0) {
            appendErrorMessage(__('Cannot delete topic').': '.__('still has children (possibly invisible)').'.<br/>');
            return;
        }
        
        //delete customfields
        $CI->customfields_db->deleteForTopic($topic->topic_id);
        
        //otherwise, delete all dependent objects by directly accessing the rows in the table 
        $CI->db->delete('topics',array('topic_id'=>$topic->topic_id));
        //delete links
        $CI->db->delete('topictopiclink',array('source_topic_id'=>$topic->topic_id));
        $CI->db->delete('topicpublicationlink',array('topic_id'=>$topic->topic_id));
        $CI->db->delete('usertopiclink',array('topic_id'=>$topic->topic_id));
        //add the information of the deleted rows to trashcan(time, data), in such a way that at least manual reconstruction will be possible
    }      
    /** Collapse given topic for the given user, if that user is susbcribed to it */
    function collapse($topic, $user_id) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        $user = $CI->user_db->getByID($user_id);
        if (($user->type=='anon') && ($user_id == $userlogin->userId())) return;
        $CI->db->where('topic_id', $topic->topic_id);
        $CI->db->where('user_id', $user_id);
        $CI->db->update('usertopiclink', array('collapsed'=>'1'));
    }

    /** Expand given topic for the given user, if that user is susbcribed to it */
    function expand($topic, $user_id) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        $user = $CI->user_db->getByID($user_id);
        if (($user->type=='anon') && ($user_id == $userlogin->userId())) return;
        $CI->db->where('topic_id', $topic->topic_id);
        $CI->db->where('user_id', $user_id);
        $CI->db->update('usertopiclink', array('collapsed'=>'0'));
    }
  function getTopicCount() {
  	$CI = &get_instance();
  	return $CI->db->count_all("topics");

  }
  function getMainTopicCount() {
  	$CI = &get_instance();
  	$CI->db->select("source_topic_id");
  	$CI->db->where(array('target_topic_id'=>'1'));
	  $CI->db->from("topictopiclink");
	  return $CI->db->count_all_results();

  }
  function getPublicationCountForTopic($topic_id) {
    $CI = &get_instance();
    $CI->db->select("pub_id");
    $CI->db->distinct();
    $CI->db->where(array('topic_id'=>$topic_id));
    $CI->db->from("topicpublicationlink");
    return $CI->db->count_all_results();
  } 
  
  function getVisiblePublicationCountForTopic($topic_id) {
      # @TODO: (KK) this doubles functionality from libraries/publication_db.php->getVisibleCountForTopic()
    $CI = &get_instance();
    $userlogin=getUserLogin();
    
    if ($userlogin->hasRights('read_all_override'))
      return $this->getPublicationCountForTopic($topic_id);
    
    if ($userlogin->isAnonymous()) //get only public publications
    {
  	$Q = $CI->db->query("SELECT DISTINCT count(*) c FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."topicpublicationlink
  	    WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
  	    AND   ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id   = ".AIGAION_DB_PREFIX."publication.pub_id
        AND   ".AIGAION_DB_PREFIX."publication.derived_read_access_level = 'public'");
    }
    else //get all non-private publications and publications that belong to the user
    {
        $Q = $CI->db->query("SELECT DISTINCT count(*) c FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."topicpublicationlink
  	    WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
  	    AND   ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id   = ".AIGAION_DB_PREFIX."publication.pub_id
        AND  (".AIGAION_DB_PREFIX."publication.derived_read_access_level != 'private' 
           OR ".AIGAION_DB_PREFIX."publication.user_id = ".$userlogin->userId().")");
    }
  	
  	foreach ($Q->result() as $row)
  	{
  		return $row->c;
  	}

  	return 0;  
  }
  
  function getReadPublicationCountForTopic($topic_id) {
    $CI = &get_instance();
    $userlogin = getUserLogin();
    $query = "SELECT COUNT(DISTINCT ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id)
                FROM ".AIGAION_DB_PREFIX."topicpublicationlink,".AIGAION_DB_PREFIX."userpublicationmark
               WHERE topic_id=".$CI->db->escape($topic_id)."
                 AND ".AIGAION_DB_PREFIX."userpublicationmark.user_id = ".$CI->db->escape($userlogin->userId())."
                 AND ".AIGAION_DB_PREFIX."userpublicationmark.pub_id  = ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id
                 AND ".AIGAION_DB_PREFIX."userpublicationmark.hasread = 'y'";
    $Q = $CI->db->query($query);
    $R = $Q->row_array();
    return $R["COUNT(DISTINCT ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id)"];  
  }
  
  function getAuthorCountForTopic($topic_id) {
	# get nuber of authors for this topic
	$CI = &get_instance();
    $query = "SELECT COUNT(DISTINCT ".AIGAION_DB_PREFIX."author.author_id)
			FROM ".AIGAION_DB_PREFIX."publicationauthorlink, ".AIGAION_DB_PREFIX."topicpublicationlink, ".AIGAION_DB_PREFIX."author
			WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
				AND ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id = ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id
				AND ".AIGAION_DB_PREFIX."publicationauthorlink.author_id = ".AIGAION_DB_PREFIX."author.author_id";
    $Q = $CI->db->query($query);
    $R = $Q->row_array();
    return $R["COUNT(DISTINCT ".AIGAION_DB_PREFIX."author.author_id)"];  
  }
  function getAuthorsForTopic($topic_id) {
	# get authors for this topic
	$CI = &get_instance();
    $query = "SELECT DISTINCT ".AIGAION_DB_PREFIX."author.author_id
			FROM ".AIGAION_DB_PREFIX."publicationauthorlink, ".AIGAION_DB_PREFIX."topicpublicationlink, ".AIGAION_DB_PREFIX."author
			WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
				AND ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id = ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id
				AND ".AIGAION_DB_PREFIX."publicationauthorlink.author_id = ".AIGAION_DB_PREFIX."author.author_id
		   ORDER BY ".AIGAION_DB_PREFIX."author.cleanname";
    $Q = $CI->db->query($query);
    $result = array();
    foreach ($Q->result() as $R) {
        $result[] = $CI->author_db->getByID($R->author_id);
    }        
    return $result;
  }
  
  function getKeywordsForTopic($topic_id) {
    # get keywords for this topic
    $CI = &get_instance();
    $query = "SELECT DISTINCT ".AIGAION_DB_PREFIX."keywords.keyword_id, COUNT(".AIGAION_DB_PREFIX."keywords.keyword_id) AS sum
    FROM ".AIGAION_DB_PREFIX."keywords, ".AIGAION_DB_PREFIX."topicpublicationlink, ".AIGAION_DB_PREFIX."publicationkeywordlink
    WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
    AND ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id = ".AIGAION_DB_PREFIX."publicationkeywordlink.pub_id
    AND ".AIGAION_DB_PREFIX."publicationkeywordlink.keyword_id = ".AIGAION_DB_PREFIX."keywords.keyword_id
    GROUP BY ".AIGAION_DB_PREFIX."keywords.keyword_id ORDER BY ".AIGAION_DB_PREFIX."keywords.cleankeyword";

    $Q = $CI->db->query($query);
    $result = array();
    foreach ($Q->result() as $R) {
        $keyword = $CI->keyword_db->getByID($R->keyword_id);
        $keyword->count = $R->sum;
        $result[] = $keyword;
    }        
    return $result;
    
  }
}
?>