<?php

function installNewDatabase($AIGAION2_DB_HOST, $AIGAION2_DB_USER, $AIGAION2_DB_PWD, $AIGAION2_DB_NAME, $AIGAION2_DB_PREFIX)
{
        #
        # connect to aigaion 2 database, execute install query
        #
        
        //Connect to the database, feedback html when an error occurs.
        $theDatabase = mysql_connect($AIGAION2_DB_HOST,
                                     $AIGAION2_DB_USER,
                                     $AIGAION2_DB_PWD);
        if ($theDatabase)
        {
            if (!mysql_select_db($AIGAION2_DB_NAME)) {
                die("Aigaion 2.0 migration script: database connection to new database failed<br>
                Error: Aigaion did not succeed in selecting the correct 
                database. Please check the database settings in your migration script.");
            }
        } else {
            die("Aigaion: database connection to new database failed<br>
            Error: Aigaion did not succeed in connecting to the database 
            server. Please check the database settings in config.php.");
        }        
              
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."aigaiongeneral` (  `version` varchar(10) NOT NULL default '',  `releaseversion` varchar(10) NOT NULL,  PRIMARY KEY  (`version`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("INSERT INTO  `".$AIGAION2_DB_PREFIX."aigaiongeneral` (`version`,`releaseversion`) 
                     VALUES  ('V2.0','2.0');");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."attachments` (  `pub_id` int(10) unsigned NOT NULL default '0',  `location` varchar(255) NOT NULL default '',  `note` varchar(255) NOT NULL default '',  `ismain` enum('TRUE','FALSE') NOT NULL default 'FALSE',  `user_id` int(11) NOT NULL default '0',  `mime` varchar(100) NOT NULL default '',  `name` varchar(255) NOT NULL default '',  `isremote` enum('TRUE','FALSE') NOT NULL default 'FALSE',  `att_id` int(10) unsigned NOT NULL auto_increment,  `read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `group_id` int(10) unsigned NOT NULL default '0',  `derived_read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `derived_edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  PRIMARY KEY  (`att_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."author` (  `author_id` int(10) unsigned NOT NULL auto_increment,  `surname` varchar(255) NOT NULL,  `von` varchar(255) NOT NULL default '',  `firstname` varchar(255) NOT NULL,  `email` varchar(255) NOT NULL,  `url` varchar(255) NOT NULL default '',  `institute` varchar(255) NOT NULL,  `specialchars` enum('FALSE','TRUE') NOT NULL default 'FALSE',  `cleanname` varchar(255) NOT NULL default '',  PRIMARY KEY  (`author_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."availablerights` (  `name` varchar(20) NOT NULL,  `description` varchar(255) NOT NULL,  PRIMARY KEY  (`name`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("INSERT INTO  `".$AIGAION2_DB_PREFIX."availablerights` (`name`,`description`) 
                     VALUES  ('attachment_read','read attachments'), 
                             ('attachment_edit','add, edit and delete attachments'), 
                             ('database_manage','manage the database'), 
                             ('note_read','read comments'), 
                             ('note_edit','add, edit and delete own comments'), 
                             ('publication_edit','add, edit and delete publications'), 
                             ('topic_subscription','change own topic subscriptions'), 
                             ('topic_edit','add, edit and delete topics'), 
                             ('user_edit_self','edit own profile (user rights not included)'), 
                             ('user_edit_all','edit all profiles (user rights not included)'), 
                             ('user_assign_rights','assign user rights'), 
                             ('bookmarklist','use a persistent bookmarklist'), 
                             ('read_all_override','read all attachments, publications, topics and notes, overriding access levels'), 
                             ('edit_all_override','edit all attachments, publications, topics and notes, overriding access levels');");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."changehistory` (  `version` varchar(20) NOT NULL,  `type` varchar(50) NOT NULL,  `description` text NOT NULL,  PRIMARY KEY  (`version`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("INSERT INTO  `".$AIGAION2_DB_PREFIX."changehistory` (`version`,`type`,`description`) 
                     VALUES  ('2.0.0.beta','bugfix,features,layout,security','
              Aigaion 2.0 introduces many new features, among which:
			
			  Customizable access levels (public, intern, private) for publications, topics, notes and attachments
			  Improved import and export code
			  User group management
			  Better support for guest users
			  Modules for integrating login management with other systems such as wiki or CMS systems
			  Persistent bookmark lists
			  Improved topic assignment for publications
			  Better update management
			  Flexible sort options for publication list display
			');");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."config` (  `setting` varchar(255) NOT NULL,  `value` mediumtext NOT NULL,  PRIMARY KEY  (`setting`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("INSERT INTO  `".$AIGAION2_DB_PREFIX."config` (`setting`,`value`) 
                     VALUES  ('CFG_ADMIN','Admin'), 
                             ('CFG_ADMINMAIL','admin@... (mail server)'), 
                             ('ALLOWED_ATTACHMENT_EXTENSIONS','.doc,.gif,.gz,.htm,.html,.jpeg,.jpg,.pdf,.png,.ps,.tar,.tif,.tiff,.txt,.zip,.z'), 
                             ('ALLOW_ALL_EXTERNAL_ATTACHMENTS','FALSE'), 
                             ('WINDOW_TITLE','A Web Based Annotated Bibliography'), 
                             ('ALWAYS_INCLUDE_PAPERS_FOR_TOPIC','TRUE'), 
                             ('SHOW_TOPICS_ON_FRONTPAGE','FALSE'), 
                             ('SHOW_TOPICS_ON_FRONTPAGE_LIMIT','5'), 
                             ('SERVER_NOT_WRITABLE','FALSE'), 
                             ('PUBLICATION_XREF_MERGE','FALSE'), 
                             ('BIBTEX_STRINGS_IN',''), 
                             ('ENABLE_ANON_ACCESS','FALSE'), 
                             ('ANONYMOUS_USER',''),
                             ('ATT_DEFAULT_READ','intern'),
                             ('ATT_DEFAULT_EDIT','intern'),
                             ('PUB_DEFAULT_READ','intern'),
                             ('PUB_DEFAULT_EDIT','intern'),
                             ('NOTE_DEFAULT_READ','intern'),
                             ('NOTE_DEFAULT_EDIT','intern'),
                             ('TOPIC_DEFAULT_READ','intern'),
                             ('TOPIC_DEFAULT_EDIT','intern');");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."grouprightsprofilelink` (  `group_id` int(10) NOT NULL,  `rightsprofile_id` int(10) NOT NULL,  PRIMARY KEY  (`group_id`,`rightsprofile_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("INSERT INTO  `".$AIGAION2_DB_PREFIX."grouprightsprofilelink` (`group_id`,`rightsprofile_id`) 
                     VALUES  (2,1),(2,2),(2,3),(2,4),(3,3),(3,4),(4,2),(4,3),(4,4),(5,4);");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."keywords` (  `keyword_id` int(10) NOT NULL auto_increment,  `keyword` text NOT NULL,  PRIMARY KEY  (`keyword_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."notecrossrefid` (  `note_id` int(10) NOT NULL,  `xref_id` int(10) NOT NULL) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."notes` (  `note_id` int(10) unsigned NOT NULL auto_increment,  `pub_id` int(10) unsigned NOT NULL default '0',  `user_id` int(11) NOT NULL default '0',  `rights` enum('public','private') NOT NULL default 'public',  `text` mediumtext,  `read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `group_id` int(10) unsigned NOT NULL default '0',  `derived_read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `derived_edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  PRIMARY KEY  (`note_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."publication` (  `pub_id` int(10) unsigned NOT NULL auto_increment,  `user_id` int(10) unsigned NOT NULL default '0',  `year` varchar(12) NOT NULL default '0000',  `actualyear` varchar(12) NOT NULL default '0000',  `title` mediumtext NOT NULL,  `bibtex_id` varchar(255) NOT NULL,  `report_type` varchar(255) NOT NULL default '',  `pub_type` enum('Article','Book','Booklet','Inbook','Incollection','Inproceedings','Manual','Mastersthesis','Misc','Phdthesis','Proceedings','Techreport','Unpublished') default NULL,  `survey` tinyint(1) NOT NULL default '0',  `mark` int(11) NOT NULL default '5',  `series` varchar(64) NOT NULL default '',  `volume` varchar(16) NOT NULL default '',  `publisher` varchar(127) NOT NULL default '',  `location` varchar(127) NOT NULL default '',  `issn` varchar(32) NOT NULL default '',  `isbn` varchar(32) NOT NULL default '',  `firstpage` varchar(10) NOT NULL default '0',  `lastpage` varchar(10) NOT NULL default '0',  `journal` varchar(255) NOT NULL default '',  `booktitle` varchar(255) NOT NULL default '',  `number` varchar(255) NOT NULL default '',  `institution` varchar(255) NOT NULL default '',  `address` varchar(255) NOT NULL default '',  `chapter` varchar(10) NOT NULL default '0',  `edition` varchar(255) NOT NULL default '',  `howpublished` varchar(255) NOT NULL default '',  `month` varchar(255) NOT NULL default '',  `organization` varchar(255) NOT NULL default '',  `school` varchar(255) NOT NULL default '',  `note` mediumtext NOT NULL,  `abstract` mediumtext NOT NULL,  `url` varchar(255) NOT NULL default '',  `doi` varchar(255) NOT NULL default '',  `crossref` varchar(255) NOT NULL,  `namekey` varchar(255) NOT NULL,  `userfields` mediumtext NOT NULL,  `specialchars` enum('FALSE','TRUE') NOT NULL default 'FALSE',  `cleanjournal` varchar(255) NOT NULL default '',  `cleantitle` varchar(255) NOT NULL default '',  `cleanauthor` TEXT, `read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `group_id` int(10) unsigned NOT NULL default '0',  `derived_read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `derived_edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  PRIMARY KEY  (`pub_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."publicationauthorlink` (  `pub_id` int(10) unsigned NOT NULL default '0',  `author_id` int(10) unsigned NOT NULL default '0',  `rank` int(10) unsigned NOT NULL default '1',  `is_editor` enum('Y','N') NOT NULL default 'N',  PRIMARY KEY  (`pub_id`,`author_id`,`is_editor`),  KEY `pub_id` (`pub_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."publicationkeywordlink` (  `pub_id` int(10) NOT NULL,  `keyword_id` int(10) NOT NULL,  PRIMARY KEY  (`pub_id`,`keyword_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."rightsprofilerightlink` (  `rightsprofile_id` int(10) NOT NULL,  `right_name` varchar(20) NOT NULL,  PRIMARY KEY  (`rightsprofile_id`,`right_name`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("INSERT INTO  `".$AIGAION2_DB_PREFIX."rightsprofilerightlink` (`rightsprofile_id`,`right_name`) 
                     VALUES  (1,'database_manage'), 
                             (1,'edit_all_override'), 
                             (1,'read_all_override'), 
                             (1,'user_assign_rights'), 
                             (1,'user_edit_all'), 
                             (2,'attachment_edit'), 
                             (2,'note_edit'), 
                             (2,'publication_edit'), 
                             (2,'topic_edit'), 
                             (2,'user_edit_self'), 
                             (3,'attachment_read'), 
                             (3,'bookmarklist'), 
                             (3,'note_read'), 
                             (3,'topic_subscription');");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."rightsprofiles` (  `rightsprofile_id` int(10) NOT NULL auto_increment,  `name` varchar(20) NOT NULL,  PRIMARY KEY  (`rightsprofile_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("INSERT INTO  `".$AIGAION2_DB_PREFIX."rightsprofiles` (`rightsprofile_id`,`name`) 
                     VALUES  (1,'admin_rights'), 
                             (2,'editor_rights'), 
                             (3,'reader_rights'), 
                             (4,'guest_rights');");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."topicpublicationlink` (  `topic_id` int(10) unsigned NOT NULL default '0',  `pub_id` int(10) unsigned NOT NULL default '0',  PRIMARY KEY  (`topic_id`,`pub_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."topics` (  `topic_id` int(10) NOT NULL auto_increment,  `name` varchar(50) default NULL,  `cleanname` VARCHAR(50) NOT NULL default '', `description` mediumtext,  `url` varchar(255) NOT NULL default '',  `user_id` int(10) unsigned NOT NULL default '0',  `read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `group_id` int(10) unsigned NOT NULL default '0',  `derived_read_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  `derived_edit_access_level` enum('private','public','intern','group') NOT NULL default 'intern',  PRIMARY KEY  (`topic_id`),  KEY `name` (`name`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("INSERT INTO  `".$AIGAION2_DB_PREFIX."topics` (`topic_id`,`name`,`description`,`url`,`user_id`,`read_access_level`,`edit_access_level`,`group_id`,`derived_read_access_level`,`derived_edit_access_level`) VALUES  (1,'Top','No description. This topic is in itself not relevant, it is just a \'topmost parent\' for the topic hierarchy.','',0,'public','intern',0,'public','intern'); ");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."topictopiclink` (  `source_topic_id` int(10) NOT NULL default '0',  `target_topic_id` int(10) NOT NULL default '0',  PRIMARY KEY  (`source_topic_id`),  KEY `target_topic_id` (`target_topic_id`)) ENGINE=MyISAM CHARACTER SET utf8 COMMENT='Hierarchy of topics; typed relations';");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."userbookmarklists` (  `user_id` int(10) NOT NULL,  `pub_id` int(10) NOT NULL,  PRIMARY KEY  (`user_id`,`pub_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."usergrouplink` (  `user_id` int(10) NOT NULL,  `group_id` int(10) NOT NULL,  PRIMARY KEY  (`user_id`,`group_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."userpublicationmark` (  
                             `pub_id` int(10) NOT NULL default '0',  
                             `user_id` int(11) NOT NULL default '0',  
                             `mark` enum('','1','2','3','4','5') NOT NULL default '3',  
                             `hasread` enum('y','n') NOT NULL default 'y',  
                PRIMARY KEY  (`pub_id`,`user_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."userrights` (  `user_id` int(10) NOT NULL,  `right_name` varchar(20) NOT NULL,  PRIMARY KEY  (`right_name`,`user_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("INSERT INTO  `".$AIGAION2_DB_PREFIX."userrights` (`user_id`,`right_name`) 
                     VALUES  (1,'attachment_edit'), (1,'edit_all_override'), (1,'read_all_override'), (1,'attachment_read'), (1,'database_manage'), (1,'note_edit'), (1,'note_edit_all'), (1,'note_edit_self'), (1,'note_read'), (1,'publication_edit'), (1,'topic_edit'), (1,'topic_subscription'), (1,'user_assign_rights'), (1,'user_edit_all'), (1,'user_edit_self'), (1,'bookmarklist');");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."users` (  `user_id` int(10) NOT NULL auto_increment,  `theme` varchar(255) NOT NULL default 'darkdefault',  `newwindowforatt` enum('TRUE','FALSE') NOT NULL default 'FALSE',  `summarystyle` varchar(255) NOT NULL default 'author',  `authordisplaystyle` varchar(5) NOT NULL default 'vlf',  `liststyle` smallint(6) NOT NULL default '0',  `login` varchar(20) NOT NULL default '',  `password` varchar(255) NOT NULL default '',  `initials` varchar(10) default NULL,  `firstname` varchar(20) default NULL,  `betweenname` varchar(10) default NULL,  `surname` varchar(100) default NULL,  `csname` varchar(10) default NULL,  `abbreviation` varchar(10) NOT NULL default '',  `email` varchar(30) NOT NULL default '',  `u_rights` tinyint(2) NOT NULL default '0',  `lastreviewedtopic` int(10) NOT NULL default '1',  `type` enum('group','anon','normal') NOT NULL default 'normal',  `lastupdatecheck` int(10) unsigned NOT NULL default '0',  `exportinbrowser` enum('TRUE','FALSE') NOT NULL default 'TRUE',  `utf8bibtex` enum('TRUE','FALSE') NOT NULL default 'FALSE',  PRIMARY KEY  (`user_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("INSERT INTO  `".$AIGAION2_DB_PREFIX."users` (`user_id`,`theme`,`newwindowforatt`,`summarystyle`,`authordisplaystyle`,`liststyle`,`login`,`password`,`initials`,`firstname`,`betweenname`,`surname`,`abbreviation`,`email`,`lastreviewedtopic`,`type`,`lastupdatecheck`,`exportinbrowser`,`utf8bibtex`) 
                     VALUES  (1,'default','TRUE','title','fvl',50,'admin','21232f297a57a5a743894a0e4a801fc3','AA','Admin','the','Admin','ADM','',1,'normal',0,'TRUE','FALSE');");
        _query("INSERT INTO  `".$AIGAION2_DB_PREFIX."users` (`user_id`,`surname`,`abbreviation`,`type`) 
                     VALUES  (2,'admins','adm_grp','group'),
                             (3,'readers','read_grp','group'),
                             (4,'editors','ed_grp','group'),
                             (5,'guests','gue_grp','group');");
        _query("CREATE TABLE `".$AIGAION2_DB_PREFIX."usertopiclink` (  `collapsed` int(2) NOT NULL default '0',  `user_id` int(10) NOT NULL default '0',  `topic_id` int(10) NOT NULL default '0',  `star` int(2) NOT NULL default '0',  PRIMARY KEY  (`user_id`,`topic_id`),  KEY `topic_id` (`topic_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        _query("INSERT INTO  `".$AIGAION2_DB_PREFIX."usertopiclink` (`collapsed`,`user_id`,`topic_id`,`star`) 
                     VALUES  (0,1,1,0),
                             (0,2,1,0),
                             (0,3,1,0),
                             (0,4,1,0),
                             (0,5,1,0); ");
}

function migrateOldDatabase($AIGAION1_DB_HOST, $AIGAION1_DB_USER, $AIGAION1_DB_PWD, $AIGAION1_DB_NAME, $AIGAION2_DB_HOST, $AIGAION2_DB_USER, $AIGAION2_DB_PWD, $AIGAION2_DB_NAME, $AIGAION2_DB_PREFIX) {
  
  include_once('migration/UTF8.php');
  
  if ((($AIGAION1_DB_NAME==$AIGAION2_DB_NAME) && ($AIGAION1_DB_HOST==$AIGAION2_DB_HOST)) && ($AIGAION2_DB_PREFIX == "")) {
	    die('The new Aigaion 2 database must be different from the old Aigaion 1.x database or you should use a table prefix for your Aigaion2 installation.');
	}

	#
	# connect to aigaion 1 database, construct migration query
	#

	//Connect to the database, feedback html when an error occurs.
	$theDatabase = mysql_connect(
			$AIGAION1_DB_HOST,
			$AIGAION1_DB_USER,
			$AIGAION1_DB_PWD);
	if ($theDatabase) {
		if (!mysql_select_db($AIGAION1_DB_NAME)) {
				die("<div class='errormessage'>Aigaion 2.0 migration script: database connection to version 1 database failed<br>
				Error: Aigaion did not succeed in selecting the correct
				database. Please check the database settings in your migration script.</div>");
		}
	} else {
		die("<div class='errormessage'>Aigaion: database connection to version 1 database failed<br>
		Error: Aigaion did not succeed in connecting to the database
		server. Please check the database settings in config.php.</div>");
	}

	//construct migration queries
	$migrate_queries = array();

	$databaseTables = array();
	$Q = _query("SHOW TABLES FROM ".$AIGAION1_DB_NAME);
    if (!$Q || (mysql_num_rows($Q) == 0)) {
        $Q = _query("SHOW TABLES");
    } 
    if (mysql_num_rows($Q) > 0) {
		while ($R = mysql_fetch_array($Q)) {
			$databaseTables[] = $R['Tables_in_'.$AIGAION1_DB_NAME];
		}
	}

	foreach ($databaseTables as $table) {
		$Q = _query("SHOW CREATE TABLE ".$AIGAION1_DB_NAME.".".$table);
		if (mysql_num_rows($Q) > 0) {
			$R = mysql_fetch_row($Q);
			$create_statement = $R[1].";";
			$len_create_table = strlen("CREATE TABLE '".$table."'");// + 5; //just to be sure some extra length
			$create_statement_first = substr($create_statement, 0, $len_create_table);
			$create_statement_last = substr($create_statement, $len_create_table + 1);
			$create_statement_first = str_replace($table, $AIGAION2_DB_PREFIX.$table, $create_statement_first);
			$migrate_queries[] = $create_statement_first.$create_statement_last;
//			echo $create_statement_first.$create_statement_last."<br/>";
		}

		$Q = _query("SELECT * FROM ".$AIGAION1_DB_NAME.".".$table);
		if (mysql_num_rows($Q) == 0)
			continue;

		$fields 		= array();
		$num_fields	= mysql_num_fields($Q);
		for ($i = 0; $i < $num_fields; $i++) {
			$fields[] = mysql_fetch_field($Q, $i);
		}

		$values = array();
		$insertTo = "INSERT INTO ".$AIGAION2_DB_PREFIX.$table." VALUES ";
		while ($R = mysql_fetch_row($Q)) {
			for ($i = 0; $i < $num_fields; $i++) {
				if (!isset($R[$i]) || is_null($R[$i])) {
					$values[]     = 'NULL';
				} else {
					$values[] = "'".mysql_real_escape_string($R[$i])."'";
				}
			}
			$migrate_queries[] = $insertTo."(".implode(", ", $values).");";
			unset($values);
		}
	}
	mysql_close();

	#
	# connect to aigaion 2 database, execute migration query
	#

	//Connect to the database, feedback html when an error occurs.
	$theDatabase = mysql_connect(
			$AIGAION2_DB_HOST,
			$AIGAION2_DB_USER,
			$AIGAION2_DB_PWD);
	if ($theDatabase)
	{
			if (!mysql_select_db($AIGAION2_DB_NAME)) {
					die("Aigaion 2.0 migration script: database connection to new database failed<br>
					Error: Aigaion did not succeed in selecting the correct
					database. Please check the database settings in your migration script.");
			}
	} else {
			die("Aigaion: database connection to new database failed<br>
			Error: Aigaion did not succeed in connecting to the database
			server. Please check the database settings in config.php.");
	}

	$success = true;
	$utf8 = new UTF8();
	foreach ($migrate_queries as $query) {
			_query($utf8->smartUtf8_encode($query)); //DR: I use the utf8 smart encode here because some Aigaion 1.x installations did not convert latin1 chars on import; in those cases that data should be converted to utf8...
			if (mysql_error()) {
					$success = false;
					echo mysql_error().'<br/>';
			}
 	}

	//execute the checkschema upgrades of Aigaion 1.x on the new database
	include('migration/checkschema.php');
	if (!checkDatabase($AIGAION2_DB_PREFIX) ) {
			die("<div class='errormessage'>Could not upgrade old database to the latest Aigaion 1.x version, preparatory to migrating to version 2</div>");
	}

	#
	# do all database changes for version 2.0, originally stored in database_changes.txt
	#
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."topic` RENAME TO `".$AIGAION2_DB_PREFIX."topics`;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."topics` CHANGE COLUMN `ID` `topic_id` INT(10) NOT NULL AUTO_INCREMENT;");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."person` RENAME TO `".$AIGAION2_DB_PREFIX."users`;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."users` CHANGE COLUMN `ID` `user_id` INT(10) NOT NULL AUTO_INCREMENT;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."users` ADD COLUMN `type` ENUM('group','anon','normal') NOT NULL DEFAULT 'normal';");

	_query("CREATE TABLE `".$AIGAION2_DB_PREFIX."rightsprofiles`
		(`rightsprofile_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`name` VARCHAR( 20 ) NOT NULL);");
	_query("CREATE TABLE `".$AIGAION2_DB_PREFIX."rightsprofilerightlink`
		(`rightsprofile_id` INT( 10 ) NOT NULL,
		`right_name` VARCHAR( 20 ) NOT NULL,
		PRIMARY KEY (`rightsprofile_id`, `right_name` ));");
	_query("CREATE TABLE `".$AIGAION2_DB_PREFIX."usergrouplink` (`user_id` INT( 10 ) NOT NULL ,`group_id` INT( 10 ) NOT NULL ,PRIMARY KEY ( `user_id` ,`group_id`));");
	_query("CREATE TABLE `".$AIGAION2_DB_PREFIX."grouprightsprofilelink`
		(`group_id` INT( 10 ) NOT NULL,
		`rightsprofile_id` INT( 10 ) NOT NULL,
		PRIMARY KEY ( `group_id` , `rightsprofile_id`));");
	_query("INSERT INTO  `".$AIGAION2_DB_PREFIX."grouprightsprofilelink` (`group_id`,`rightsprofile_id`) VALUES  (2,1),(2,2),(2,3),(2,4),(3,3),(3,4),(4,2),(4,3),(4,4),(5,4);");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."topicpublication` RENAME TO `".$AIGAION2_DB_PREFIX."topicpublicationlink`;");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."persontopic` RENAME TO `".$AIGAION2_DB_PREFIX."usertopiclink`;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."usertopiclink` CHANGE COLUMN `person_id` `user_id` INT(10) NOT NULL DEFAULT 0;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` CHANGE COLUMN `pub_type` `report_type` VARCHAR(255) NOT NULL DEFAULT '';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."author` CHANGE COLUMN `ID` `author_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publicationfile` DROP PRIMARY KEY;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publicationfile`
		ADD COLUMN `att_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `isremote`;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publicationfile`
		MODIFY COLUMN `att_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
		ADD PRIMARY KEY(`att_id`);");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publicationfile` RENAME TO `".$AIGAION2_DB_PREFIX."attachments`;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."attachments` CHANGE COLUMN `person_id` `user_id` INTEGER NOT NULL DEFAULT 0;");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` CHANGE COLUMN `entered_by` `user_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."personpublicationnote` CHANGE COLUMN `person_id` `user_id` INTEGER NOT NULL DEFAULT 0;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."topics`
		ADD COLUMN `user_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."attachments`
		ADD COLUMN `read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication`
		ADD COLUMN `read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."topics`
		ADD COLUMN `read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."personpublicationnote`
		ADD COLUMN `read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."attachments`
		ADD COLUMN `edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication`
		ADD COLUMN `edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."topics`
		ADD COLUMN `edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."personpublicationnote`
		ADD COLUMN `edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");

	_query("UPDATE ".$AIGAION2_DB_PREFIX."topics SET read_access_level = 'public' WHERE topic_id = 1;");

	_query("INSERT INTO `".$AIGAION2_DB_PREFIX."availablerights` (`name`,`description`) VALUES
		('attachment_read_all', 'read all attachments, overriding access levels'),
		('topic_read_all','read all topics, overriding access levels'),
		('note_read_all','read all notes, overriding access levels');");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."personpublicationnote` RENAME TO `".$AIGAION2_DB_PREFIX."notes`;");

	_query("UPDATE ".$AIGAION2_DB_PREFIX."notes SET read_access_level='private',edit_access_level='private' WHERE rights='private';");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication`
		CHANGE `type` `pub_type` ENUM( 'Article', 'Book', 'Booklet', 'Inbook', 'Incollection', 'Inproceedings',
		'Manual', 'Mastersthesis', 'Misc', 'Phdthesis', 'Proceedings', 'Techreport', 'Unpublished' ) NULL DEFAULT NULL ;");

	_query("INSERT INTO `".$AIGAION2_DB_PREFIX."availablerights` (`name`,`description`) VALUES
		('attachment_edit_all','edit all attachments, overriding access levels'),
		('topic_edit_all','edit all topics, overriding access levels');");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publicationauthor` CHANGE `author` `author_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publicationauthor` RENAME TO `".$AIGAION2_DB_PREFIX."publicationauthorlink`;");

	_query("CREATE TABLE `".$AIGAION2_DB_PREFIX."keywords` (`keyword_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`keyword` text NOT NULL);");

	_query("CREATE TABLE `".$AIGAION2_DB_PREFIX."publicationkeywordlink`
		(`pub_id` INT( 10 ) NOT NULL,
		`keyword_id` INT( 10 ) NOT NULL,
		PRIMARY KEY (`pub_id`, `keyword_id` ));");

	//copy keyword values to new keyword table
	$res = _query("SELECT pub_id, keywords FROM ".$AIGAION2_DB_PREFIX."publication");

	$keyword_array = array();
	if ($res) {
			while ($row = mysql_fetch_array($res) ) {
				$keywords = preg_replace(
							'/ *([^,;]+)/',
							"###\\1",
							$row['keywords']);

				$keywords = explode('###', $keywords);
				foreach ($keywords as $keyword) {
					if (trim($keyword) != '') {
						if ((substr($keyword, -1, 1) == ',') || (substr($keyword, -1, 1) == ';'))
							$keyword = substr($keyword, 0, strlen($keyword) - 1);

						$keyword_array[] = array('pub_id' => $row['pub_id'], 'keyword' => $keyword);
					}
				}
			}

			foreach ($keyword_array as $entry) {
				$res = _query("SELECT keyword_id FROM ".$AIGAION2_DB_PREFIX."keywords WHERE keyword='".mysql_real_escape_string($entry['keyword'])."'");
				if (mysql_num_rows($res) > 0) {
					while ($row = mysql_fetch_array($res)) {
						$keyword_id = $row['keyword_id'];
					}
				} else {
					$res = _query("INSERT IGNORE INTO ".$AIGAION2_DB_PREFIX."keywords (keyword) VALUES ('".mysql_real_escape_string($entry['keyword'])."')");
					$keyword_id = mysql_insert_id();
				}

				$res = _query("INSERT IGNORE INTO ".$AIGAION2_DB_PREFIX."publicationkeywordlink (pub_id, keyword_id) VALUES (".$entry['pub_id'].", ".$keyword_id.");");

				if (mysql_affected_rows() == 1) {
					//echo "Insert pub_id ".$entry['pub_id'].": keyword_id ".$keyword_id."<br/>";
				}
			}
		}

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` DROP `keywords`;");

	_query("CREATE TABLE `".$AIGAION2_DB_PREFIX."userbookmarklists` (`user_id` INT( 10 ) NOT NULL ,`pub_id` INT( 10 ) NOT NULL ,PRIMARY KEY (`user_id`, `pub_id` ));");

	_query("INSERT INTO `".$AIGAION2_DB_PREFIX."availablerights` (`name`,`description`) VALUES  ('bookmarklist','use a persistent bookmarklist');");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."topics`		ADD COLUMN `group_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication`	ADD COLUMN `group_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."notes`		ADD COLUMN `group_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."attachments`	ADD COLUMN `group_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;");


	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."topics`		ADD COLUMN `derived_read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication`	ADD COLUMN `derived_read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."notes`		ADD COLUMN `derived_read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."attachments`	ADD COLUMN `derived_read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."topics`		ADD COLUMN `derived_edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication`	ADD COLUMN `derived_edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."notes`		ADD COLUMN `derived_edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."attachments`	ADD COLUMN `derived_edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");

	_query("INSERT INTO `".$AIGAION2_DB_PREFIX."availablerights` (`name`,`description`) VALUES
			('read_all_override',	'read all attachments, publications, topics and notes, overriding access levels'),
			('edit_all_override',	'edit all attachments, publications, topics and notes, overriding access levels');");
	// _query("DELETE FROM availablerights WHERE
			// name='attachment_edit_all'
			// OR name='attachment_read_all'
			// OR name='note_read_all'
			// OR name='note_edit_all'
			// OR name='topic_read_all'
			// OR name='topic_edit_all';");
	_query("DELETE FROM ".$AIGAION2_DB_PREFIX."availablerights
			WHERE name IN
			('attachment_edit_all',
			'attachment_read_all',
			'note_read_all',
			'note_edit_all',
			'topic_read_all',
			'topic_edit_all')");	// SMS 070922: equivalent, is it not?

	_query("UPDATE ".$AIGAION2_DB_PREFIX."availablerights SET name='note_edit' WHERE name='note_edit_self';");

	_query("UPDATE ".$AIGAION2_DB_PREFIX."topics SET read_access_level='public',derived_read_access_level='public' where topic_id=1;");

	_query("INSERT INTO ".$AIGAION2_DB_PREFIX."rightsprofiles VALUES
			('1', 'admin_rights'),
			('2', 'editor_rights'),
			('3', 'reader_rights'),
			('4', 'guest_rights')");
	_query("INSERT INTO ".$AIGAION2_DB_PREFIX."rightsprofilerightlink VALUES
			('1', 'database_manage'),
			('1', 'edit_all_override'),
			('1', 'read_all_override'),
			('1', 'user_assign_rights'),
			('1', 'user_edit_all'),
			('2', 'attachment_edit'),
			('2', 'note_edit'),
			('2', 'publication_edit'),
			('2', 'topic_edit'),
			('2', 'user_edit_self'),
			('3', 'attachment_read'),
			('3', 'note_read'),
			('3', 'topic_subscription'),
			('3', 'bookmarklist')");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."users` MODIFY COLUMN `surname` VARCHAR(100) DEFAULT NULL;");

	_query("CREATE TABLE `".$AIGAION2_DB_PREFIX."changehistory`
			(`version` varchar(20) NOT NULL,
			`type` varchar(50) NOT NULL,
			`description` text NOT NULL,
			PRIMARY KEY  (`version`)) ENGINE=MyISAM;");
	_query("INSERT INTO `".$AIGAION2_DB_PREFIX."changehistory` (`version`,`type`,`description`) VALUES
			('1.99.0','bugfix,features,layout,security',
			'Introduction of Aigaion version 2.0; this installation was migrated from an older 1.x version.'),
			('2.0.0.beta','bugfix,features,layout,security',
			'Aigaion 2.0 introduces many new features, among which:
			
			  Customizable access levels (public, intern, private) for publications, topics, notes and attachments
			  Improved import and export code
			  User group management
			  Better support for guest users
			  Modules for integrating login management with other systems such as wiki or CMS systems
			  Persistent bookmark lists
			  Improved topic assignment for publications
			  Better update management
			  Flexible sort options for publication list display
			');");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."users`	ADD COLUMN `lastupdatecheck` INTEGER UNSIGNED NOT NULL DEFAULT 0;");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."personpublicationmark`
			CHANGE COLUMN `person_id` `user_id` INTEGER NOT NULL DEFAULT 0,
			DROP PRIMARY KEY,
			ADD PRIMARY KEY  (`pub_id`, `user_id`);");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."personpublicationmark` RENAME TO `".$AIGAION2_DB_PREFIX."userpublicationmark`;");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."users`	ADD COLUMN `exportinbrowser` ENUM('TRUE','FALSE') NOT NULL DEFAULT 'TRUE';");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."users`	ADD COLUMN `utf8bibtex` ENUM('TRUE','FALSE') NOT NULL DEFAULT 'FALSE';");
	_query("ALTER DATABASE ".$AIGAION2_DB_NAME." CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."aigaiongeneral CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."attachments CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."author CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."availablerights CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."changehistory CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."config CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."grouprightsprofilelink CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."keywords CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."notecrossrefid CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."notes CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."publication CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."publicationauthorlink CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."publicationkeywordlink CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."rightsprofilerightlink CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."rightsprofiles CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."topicpublicationlink CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."topics CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."topictopiclink CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."userbookmarklists CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."usergrouplink CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."userpublicationmark CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."userrights CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."users CONVERT TO CHARACTER SET utf8;");
	_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."usertopiclink CONVERT TO CHARACTER SET utf8;");
	//DR: following query needs to be dependent on current maximum user_id
	//_query("INSERT INTO users (`user_id`,`surname`,`abbreviation`,`type`) VALUES
			//(2,'admins','adm_grp','group'),
			//(3,'readers','read_grp','group'),
			//(4,'editors','ed_grp','group'),
			//(5,'guests','gue_grp','group');");

	_query("UPDATE ".$AIGAION2_DB_PREFIX."aigaiongeneral SET version='V2.0'");

	//---enlarge the 'series' field
	//---drop irrelevant legacy columns
	//---drop col 'users.u_rights'
	//---drop col 'users.csname'
	//---drop col 'notes.rights'
	//default add .ps, .z, .gz to allowed file types
	//drop release column from aigaiongeneral (keep database version column! possibly rename :) )
	//reinit all cleanfields!
	//set all correct rights for existing users, given new righst set...

	#
	# do utf8 / bibtex conversions
	#
	define('BASEPATH','.');
	include('./bibtexutf8_helper.php');

		//author->surname, von, firstname, institute
	$res = _query("SELECT * FROM ".$AIGAION2_DB_PREFIX."author");
	if ($res) {
		while ($row = mysql_fetch_array($res)) {
			_query("UPDATE ".$AIGAION2_DB_PREFIX."author SET ".
					"surname='".  	mysql_real_escape_string(bibCharsToUtf8FromString($row['surname'])).
					"', von='".		mysql_real_escape_string(bibCharsToUtf8FromString($row['von'])).
					"', firstname='".	mysql_real_escape_string(bibCharsToUtf8FromString($row['firstname'])).
					"', institute='". 	mysql_real_escape_string(bibCharsToUtf8FromString($row['institute'])).
					"' WHERE author_id='{$row['author_id']}' ");
		}
	}
		//keywords->keyword
	$res = _query("SELECT * FROM ".$AIGAION2_DB_PREFIX."keywords");
	if ($res) {
		while ($row = mysql_fetch_array($res)) {
			_query("UPDATE ".$AIGAION2_DB_PREFIX."keywords SET keyword='".mysql_real_escape_string(bibCharsToUtf8FromString($row['keyword'])).
					"' WHERE keyword_id='{$row['keyword_id']}' ");
		}
	}

	//notes->text
	$res = _query("SELECT * FROM ".$AIGAION2_DB_PREFIX."notes");
	if ($res) {
		while ($row = mysql_fetch_array($res)) {
			_query("UPDATE ".$AIGAION2_DB_PREFIX."notes SET text='".mysql_real_escape_string(bibCharsToUtf8FromString($row['text'])).
					"' WHERE note_id='{$row['note_id']}' ");
		}
	}

	//publication->title, series, publisher, location, journal, booktitle, institution, address, organisation, school, note, abstract,
	$res = _query("SELECT * FROM ".$AIGAION2_DB_PREFIX."publication");
	if ($res) {
		while ($row = mysql_fetch_array($res)) {
			_query("UPDATE ".$AIGAION2_DB_PREFIX."publication SET ".
					"   title='".		mysql_real_escape_string(bibCharsToUtf8FromString($row['title'])).
					"', series='".		mysql_real_escape_string(bibCharsToUtf8FromString($row['series'])).
					"', publisher='".	mysql_real_escape_string(bibCharsToUtf8FromString($row['publisher'])).
					"', location='". 	mysql_real_escape_string(bibCharsToUtf8FromString($row['location'])).
					"', journal='".   	mysql_real_escape_string(bibCharsToUtf8FromString($row['journal'])).
					"', booktitle='".	mysql_real_escape_string(bibCharsToUtf8FromString($row['booktitle'])).
					"', institution='".	mysql_real_escape_string(bibCharsToUtf8FromString($row['institution'])).
					"', address='".	mysql_real_escape_string(bibCharsToUtf8FromString($row['address'])).
					"', organization='".	mysql_real_escape_string(bibCharsToUtf8FromString($row['organization'])).
					"', school='".   	mysql_real_escape_string(bibCharsToUtf8FromString($row['school'])).
					"', note='".		mysql_real_escape_string(bibCharsToUtf8FromString($row['note'])).
					"', abstract='".	mysql_real_escape_string(bibCharsToUtf8FromString($row['abstract'])).
					"' WHERE pub_id='{$row['pub_id']}' ");
		}
	}

	#
	# rename tables, if prefix was specified
	#
	/* [WB - 080108] Not necessary anymore!
	if ($AIGAION2_DB_PREFIX!='') {
		_query("ALTER TABLE aigaiongeneral RENAME TO ".$AIGAION2_DB_PREFIX."aigaiongeneral;");
		_query("ALTER TABLE attachments RENAME TO ".$AIGAION2_DB_PREFIX."attachments;");
		_query("ALTER TABLE author RENAME TO ".$AIGAION2_DB_PREFIX."author;");
		_query("ALTER TABLE availablerights RENAME TO ".$AIGAION2_DB_PREFIX."availablerights;");
		_query("ALTER TABLE changehistory RENAME TO ".$AIGAION2_DB_PREFIX."changehistory;");
		_query("ALTER TABLE config RENAME TO ".$AIGAION2_DB_PREFIX."config;");
		_query("ALTER TABLE grouprightsprofilelink RENAME TO ".$AIGAION2_DB_PREFIX."grouprightsprofilelink;");
		_query("ALTER TABLE keywords RENAME TO ".$AIGAION2_DB_PREFIX."keywords;");
		_query("ALTER TABLE notecrossrefid RENAME TO ".$AIGAION2_DB_PREFIX."notecrossrefid;");
		_query("ALTER TABLE notes RENAME TO ".$AIGAION2_DB_PREFIX."notes;");
		_query("ALTER TABLE publication RENAME TO ".$AIGAION2_DB_PREFIX."publication;");
		_query("ALTER TABLE publicationauthorlink RENAME TO ".$AIGAION2_DB_PREFIX."publicationauthorlink;");
		_query("ALTER TABLE publicationkeywordlink RENAME TO ".$AIGAION2_DB_PREFIX."publicationkeywordlink;");
		_query("ALTER TABLE rightsprofilerightlink RENAME TO ".$AIGAION2_DB_PREFIX."rightsprofilerightlink;");
		_query("ALTER TABLE rightsprofiles RENAME TO ".$AIGAION2_DB_PREFIX."rightsprofiles;");
		_query("ALTER TABLE topicpublicationlink RENAME TO ".$AIGAION2_DB_PREFIX."topicpublicationlink;");
		_query("ALTER TABLE topics RENAME TO ".$AIGAION2_DB_PREFIX."topics;");
		_query("ALTER TABLE topictopiclink RENAME TO ".$AIGAION2_DB_PREFIX."topictopiclink;");
		_query("ALTER TABLE userbookmarklists RENAME TO ".$AIGAION2_DB_PREFIX."userbookmarklists;");
		_query("ALTER TABLE usergrouplink RENAME TO ".$AIGAION2_DB_PREFIX."usergrouplink;");
		_query("ALTER TABLE userpublicationmark RENAME TO ".$AIGAION2_DB_PREFIX."userpublicationmark;");
		_query("ALTER TABLE userrights RENAME TO ".$AIGAION2_DB_PREFIX."userrights;");
		_query("ALTER TABLE users RENAME TO ".$AIGAION2_DB_PREFIX."users;");
		_query("ALTER TABLE usertopiclink RENAME TO ".$AIGAION2_DB_PREFIX."usertopiclink;");
	}
	*/
	
	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` ADD COLUMN `cleanauthor` TEXT;");
    _query("ALTER TABLE `".$AIGAION2_DB_PREFIX."topics` ADD COLUMN `cleanname` VARCHAR(50) NOT NULL default '';");
    _query("INSERT INTO `".$AIGAION2_DB_PREFIX."config` (setting,value) 
                 VALUES ('ATT_DEFAULT_READ','intern'),
                        ('ATT_DEFAULT_EDIT','intern'),
                        ('PUB_DEFAULT_READ','intern'),
                        ('PUB_DEFAULT_EDIT','intern'),
                        ('NOTE_DEFAULT_READ','intern'),
                        ('NOTE_DEFAULT_EDIT','intern'),
                        ('TOPIC_DEFAULT_READ','intern'),
                        ('TOPIC_DEFAULT_EDIT','intern');");
    _query("ALTER TABLE `".$AIGAION2_DB_PREFIX."userpublicationmark` 
          MODIFY COLUMN `mark` 
                        ENUM('','1','2','3','4','5') NOT NULL DEFAULT 3;");

	_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."userpublicationmark`
			CHANGE COLUMN `read` `hasread` enum('y','n') NOT NULL default 'y';");
}

///////////////////////////////
function writeIndexFile ($AIGAION2_DB_HOST, $AIGAION2_DB_USER, $AIGAION2_DB_PWD, $AIGAION2_DB_NAME, $AIGAION2_DB_PREFIX, $AIGAION_SITEID, $AIGAION_ROOT_URL, $AIGAION_ROOT_DIR, $AIGAION_WEBCONTENT_URL, $AIGAION_WEBCONTENT_DIR, $AIGAION_ATTACHMENT_DIR) {
    
    #check existence of config script. If config.php already exists, kill this install script immediately and die.
    if (siteIsConfigured()) {
        kill_install_script();
        failInstall("A config.php file was encountered. You cannot use this install script because the aigaion installation seems to have already been configured.");
    }
    
    #build new config.php
    $fp = fopen($AIGAION_ROOT_DIR.'index.php', 'w');
    $index_php = "<?php";



$index_php .= "
/*==== MANDATORY SETTINGS */
#Root URL of this instance Aigaion, WITH trailing slash
define('AIGAION_ROOT_URL','".$AIGAION_ROOT_URL."');
#Unique ID of this site, to keep it separate from other installations that use same engine 
#NOTE: use only alphanumeric characters, no spaces, and at least one letter. Otherwise Aigaion won't work at all.
define('AIGAION_SITEID', '".$AIGAION_SITEID."');
# Host where database runs
define('AIGAION_DB_HOST', '".$AIGAION2_DB_HOST."');
# Database user
define('AIGAION_DB_USER', '".$AIGAION2_DB_USER."');
# Database password
define('AIGAION_DB_PWD', '".$AIGAION2_DB_PWD."');
# Name of the standard database
define('AIGAION_DB_NAME', '".$AIGAION2_DB_NAME."');

#We need to know where the web content of Aigaion (themes, icons, javascript) is located. WITH trailing slash.
#By default this is http://localhost/aigaion2root/webcontent/
define('AIGAION_WEBCONTENT_URL','".$AIGAION_WEBCONTENT_URL."');
define('AIGAION_WEBCONTENT_DIR','".$AIGAION_WEBCONTENT_DIR."');";

$index_php .= "

/*==== OPTIONAL SETTINGS */
";
$index_php .= "
# Directory where to store attachments. Default: this directory/attachments
# Only uncomment and fill this line if your directory for storing attachments on server 
# is different from the default
";
if ($AIGAION_ATTACHMENT_DIR != "")
  $index_php .= "define('AIGAION_ATTACHMENT_DIR', '".$AIGAION_ATTACHMENT_DIR."');\n";
else
  $index_php .= "//define('AIGAION_ATTACHMENT_DIR', '/Path/for/attachments');\n";

$index_php .= "
# Table prefix for database. 
# By default, no table prefix is defined. If your tables have been defined 
# with a table prefix, uncomment the following line and fill in the prefix:
";
if ($AIGAION2_DB_PREFIX != "")
  $index_php .= "define('AIGAION_DB_PREFIX', '".$AIGAION2_DB_PREFIX."');\n";
else
  $index_php .= "//define('AIGAION_DB_PREFIX', '');\n";

$index_php .= "
# Enable/disable clean URLs. 
# If set to true, you can use URLS like http://<server>/aigaion2root/topics instead of http://<server>/aigaion2root/index.php/topics
#
#This requires the webserver to rewrite URLs to /index.php
#see sample.htaccess for what you need to put in the .htaccess file to achieve these rewrite rules
# addition by Michael Gorven
define('CLEAN_URLS', FALSE);

/*
|---------------------------------------------------------------
| EMAIL EXPORT
|---------------------------------------------------------------
|
|
|
|
|
*/

# set this to the name of the email address you want to use as 'sender' when publications are exported by email
# 
define('EXPORT_REPLY_ADDRESS', '...@........');

# Defines the maximum size of email attachments
define('MAXIMUM_ATTACHMENT_SIZE', '10000');

/*
|---------------------------------------------------------------
| PHP ERROR REPORTING LEVEL
|---------------------------------------------------------------
|
| By default Aigaion runs with error reporting set to ALL.  For security
| reasons you are encouraged to change this when your site goes live.
| For more info visit:  http://www.php.net/error_reporting
|
*/
	error_reporting(E_ALL);

/*
|---------------------------------------------------------------
| SYSTEM FOLDER NAME
|---------------------------------------------------------------
|
| This variable must contain the name of your code igniter 'system' folder.
| Include the path if the folder is not in the same  directory
| as this file.
| This is normally only changed when you are sharing the same Aigaion 2 code base
| between several instances of Aigaion 2
|
| NO TRAILING SLASH!
|
*/
	\$system_folder = './system';

/*
|---------------------------------------------------------------
| APPLICATION FOLDER NAME
|---------------------------------------------------------------
|
| Points to the folder of the aigaion engine. If not relative from the directory
| in which this file is located, use a path.
| This is normally only changed when you are sharing the same Aigaion 2 code base
| between several instances of Aigaion 2
|
| If you want to use a relative path, always include ./ or ../
| E.g. like this: ./application
|
| This is normally only changed when you are sharing the same Aigaion 2 code base
| between several instances of Aigaion 2
|
| NO TRAILING SLASH!
|
*/
	\$application_folder = './application';


/*
|===============================================================
| END OF USER CONFIGURABLE SETTINGS
|===============================================================
*/


/*
|---------------------------------------------------------------
| DEFINE APPLICATION CONSTANTS
|---------------------------------------------------------------
|
| EXT		- The file extension.  Typically '.php'
| FCPATH	- The full server path to THIS file
| SELF		- The name of THIS file (typically 'index.php')
| BASEPATH	- The full server path to the 'system' folder
| APPPATH	- The full server path to the 'application' folder
|
*/
define('EXT', '.'.pathinfo(__FILE__, PATHINFO_EXTENSION));
define('FCPATH', __FILE__);
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('BASEPATH', \$system_folder.'/');

if (is_dir(\$application_folder))
{
	define('APPPATH', \$application_folder.'/');
}
else
{
	if (\$application_folder == '')
	{
		\$application_folder = 'application';
	}

	define('APPPATH', BASEPATH.\$application_folder.'/');
}

/*
|---------------------------------------------------------------
| DEFINE E_STRICT
|---------------------------------------------------------------
|
| Some older versions of PHP don't support the E_STRICT constant
| so we need to explicitly define it otherwise the Exception class 
| will generate errors.
|
*/
if ( ! defined('E_STRICT'))
{
	define('E_STRICT', 2048);
}

/*
|---------------------------------------------------------------
| LOAD THE FRONT CONTROLLER
|---------------------------------------------------------------
|
| And away we go...
|
*/
require_once BASEPATH.'core/CodeIgniter'.EXT;
?>";
        
    $success = fwrite($fp,$index_php);
    if ($success==FALSE) {
        echo "<div class='errormessage'>Install failed because the install.php file could not be saved on the server. <b>Please copy the text below and save it as index.php in the aigaion root directory!</b> You are then ready to start using Aigaion.</div>";
        echo "\n<textarea virtualcols=100 cols=80 rows=25>\n";
        echo $index_php;
        echo "\n</textarea>";
    }

}
?>