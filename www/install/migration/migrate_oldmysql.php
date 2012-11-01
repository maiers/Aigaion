<?php
include ('migrate_config.php');
include ('UTF8.php');
//split script on POST user/paswd var.
$pwd='';
if (isset($_POST['aigaion2_pwd']))
	$pwd = $_POST['aigaion2_pwd'];
$user='';
if (isset($_POST['aigaion2_user']))
	$user = $_POST['aigaion2_user'];
if ( 		$pwd !=''
		&&	$user != ''
		&&	defined('AIGAION_INSTALL_USERNAME')
		&&	(AIGAION_INSTALL_USERNAME!='')
		&&	defined('AIGAION_INSTALL_PWD')
		&&	(AIGAION_INSTALL_PWD!='')
		&&	$pwd == AIGAION_INSTALL_PWD
		&&	$user == AIGAION_INSTALL_USERNAME
) {
	// correct password was provided - do migration
	if (		!defined('AIGAION1_DB_HOST')
			||	(AIGAION1_DB_HOST=='')
			||	!defined('AIGAION1_DB_USER')
			||	(AIGAION1_DB_USER=='')
			||	!defined('AIGAION1_DB_PWD')
			||	!defined('AIGAION1_DB_NAME')
			||	(AIGAION1_DB_NAME=='')

			||	!defined('AIGAION2_DB_HOST')
			||	(AIGAION2_DB_HOST=='')
			||	!defined('AIGAION2_DB_USER')
			||	(AIGAION2_DB_USER=='')
			||	!defined('AIGAION2_DB_PWD')
			||	!defined('AIGAION2_DB_NAME')
			||	(AIGAION2_DB_NAME=='')
			||	!defined('AIGAION2_DB_PREFIX')
	) {
		die('Please define all appropriate parameters for the migration.');
	}

	#
	# connect to aigaion 1 database, construct migration query
	#

	//Connect to the database, feedback html when an error occurs.
	$theDatabase = mysql_connect(
			AIGAION1_DB_HOST,
			AIGAION1_DB_USER,
			AIGAION1_DB_PWD);
	if ($theDatabase) {
		if (!mysql_select_db(AIGAION1_DB_NAME)) {
				die("Aigaion 2.0 migration script: database connection to version 1 database failed<br>
				Error: Aigaion did not succeed in selecting the correct
				database. Please check the database settings in your migration script.");
		}
	} else {
		die("Aigaion: database connection to version 1 database failed<br>
		Error: Aigaion did not succeed in connecting to the database
		server. Please check the database settings in config.php.");
	}

	//construct migration queries
	$migrate_queries = array();

	$databaseTables = array();
	$Q = mysql_query("SHOW TABLES FROM ".AIGAION1_DB_NAME);
	if (mysql_num_rows($Q) > 0) {
		while ($R = mysql_fetch_array($Q)) {
			$databaseTables[] = $R['Tables_in_'.AIGAION1_DB_NAME];
		}
	}

	foreach ($databaseTables as $table) {
		$Q = mysql_query("SHOW CREATE TABLE ".AIGAION1_DB_NAME.".".$table);
		if (mysql_num_rows($Q) > 0) {
			$R = mysql_fetch_row($Q);
			$migrate_queries[] = $R[1].";";
		}

		$Q = mysql_query("SELECT * FROM ".AIGAION1_DB_NAME.".".$table);
		if (mysql_num_rows($Q) == 0)
			continue;

		$fields 		= array();
		$num_fields	= mysql_num_fields($Q);
		for ($i = 0; $i < $num_fields; $i++) {
			$fields[] = mysql_fetch_field($Q, $i);
		}

		$values = array();
		$insertTo = "INSERT INTO {$table} VALUES ";
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
			AIGAION2_DB_HOST,
			AIGAION2_DB_USER,
			AIGAION2_DB_PWD);
	if ($theDatabase)
	{
			if (!mysql_select_db(AIGAION2_DB_NAME)) {
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
			mysql_query($utf8->smartUtf8_encode($query)); //DR: I use the utf8 smart encode here because some Aigaion 1.x installations did not convert latin1 chars on import; in those cases that data should be converted to utf8...
			if (mysql_error()) {
					$success = false;
					echo mysql_error().'<br/>';
			}
	}

	//execute the checkschema upgrades of Aigaion 1.x on the new database
	include('checkschema.php');
	if (!checkDatabase() ) {
			die('Could not upgrade old database to the latest Aigaion 1.x version, preparatory to migrating to version 2');
	}

	#
	# do all database changes for version 2.0, originally stored in database_changes.txt
	#
	_query("ALTER TABLE `topic` RENAME TO `topics`;");
	_query("ALTER TABLE `topics` CHANGE COLUMN `ID` `topic_id` INT(10) NOT NULL AUTO_INCREMENT;");

	_query("ALTER TABLE `person` RENAME TO `users`;");
	_query("ALTER TABLE `users` CHANGE COLUMN `ID` `user_id` INT(10) NOT NULL AUTO_INCREMENT;");
	_query("ALTER TABLE `users` ADD COLUMN `type` ENUM('group','anon','normal') NOT NULL DEFAULT 'normal';");

	_query("CREATE TABLE `rightsprofiles`
		(`rightsprofile_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`name` VARCHAR( 20 ) NOT NULL);");
	_query("CREATE TABLE `rightsprofilerightlink`
		(`rightsprofile_id` INT( 10 ) NOT NULL,
		`right_name` VARCHAR( 20 ) NOT NULL,
		PRIMARY KEY (`rightsprofile_id`, `right_name` ));");
	_query("CREATE TABLE `usergrouplink` (`user_id` INT( 10 ) NOT NULL ,`group_id` INT( 10 ) NOT NULL ,PRIMARY KEY ( `user_id` ,`group_id`));");
	_query("CREATE TABLE `grouprightsprofilelink`
		(`group_id` INT( 10 ) NOT NULL,
		`rightsprofile_id` INT( 10 ) NOT NULL,
		PRIMARY KEY ( `group_id` , `rightsprofile_id`));");
	_query("INSERT INTO  `grouprightsprofilelink` (`group_id`,`rightsprofile_id`) VALUES  (2,1),(2,2),(2,3),(2,4),(3,3),(3,4),(4,2),(4,3),(4,4),(5,4);");

	_query("ALTER TABLE `topicpublication` RENAME TO `topicpublicationlink`;");

	_query("ALTER TABLE `persontopic` RENAME TO `usertopiclink`;");
	_query("ALTER TABLE `usertopiclink` CHANGE COLUMN `person_id` `user_id` INT(10) NOT NULL DEFAULT 0;");
	_query("ALTER TABLE `publication` CHANGE COLUMN `pub_type` `report_type` VARCHAR(255) NOT NULL DEFAULT '';");
	_query("ALTER TABLE `author` CHANGE COLUMN `ID` `author_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;");
	_query("ALTER TABLE `publicationfile` DROP PRIMARY KEY;");
	_query("ALTER TABLE `publicationfile`
		ADD COLUMN `att_id` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `isremote`;");
	_query("ALTER TABLE `publicationfile`
		MODIFY COLUMN `att_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
		ADD PRIMARY KEY(`att_id`);");
	_query("ALTER TABLE `publicationfile` RENAME TO `attachments`;");
	_query("ALTER TABLE `attachments` CHANGE COLUMN `person_id` `user_id` INTEGER NOT NULL DEFAULT 0;");

	_query("ALTER TABLE `publication` CHANGE COLUMN `entered_by` `user_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;");
	_query("ALTER TABLE `personpublicationnote` CHANGE COLUMN `person_id` `user_id` INTEGER NOT NULL DEFAULT 0;");
	_query("ALTER TABLE `topics`
		ADD COLUMN `user_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;");

	_query("ALTER TABLE `attachments`
		ADD COLUMN `read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `publication`
		ADD COLUMN `read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `topics`
		ADD COLUMN `read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `personpublicationnote`
		ADD COLUMN `read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `attachments`
		ADD COLUMN `edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `publication`
		ADD COLUMN `edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `topics`
		ADD COLUMN `edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `personpublicationnote`
		ADD COLUMN `edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");

	_query("UPDATE topics SET read_access_level = 'public' WHERE topic_id = 1;");

	_query("INSERT INTO `availablerights` (`name`,`description`) VALUES
		('attachment_read_all', 'read all attachments, overriding access levels'),
		('topic_read_all','read all topics, overriding access levels'),
		('note_read_all','read all notes, overriding access levels');");

	_query("ALTER TABLE `personpublicationnote` RENAME TO `notes`;");

	_query("UPDATE notes SET read_access_level='private',edit_access_level='private' WHERE rights='private';");

	_query("ALTER TABLE `publication`
		CHANGE `type` `pub_type` ENUM( 'Article', 'Book', 'Booklet', 'Inbook', 'Incollection', 'Inproceedings',
		'Manual', 'Mastersthesis', 'Misc', 'Phdthesis', 'Proceedings', 'Techreport', 'Unpublished' ) NULL DEFAULT NULL ;");

	_query("INSERT INTO `availablerights` (`name`,`description`) VALUES
		('attachment_edit_all','edit all attachments, overriding access levels'),
		('topic_edit_all','edit all topics, overriding access levels');");

	_query("ALTER TABLE `publicationauthor` CHANGE `author` `author_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';");

	_query("ALTER TABLE `publicationauthor` RENAME TO `publicationauthorlink`;");

	_query("CREATE TABLE `keywords` (`keyword_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`keyword` text NOT NULL);");

	_query("CREATE TABLE `publicationkeywordlink`
		(`pub_id` INT( 10 ) NOT NULL,
		`keyword_id` INT( 10 ) NOT NULL,
		PRIMARY KEY (`pub_id`, `keyword_id` ));");

	//copy keyword values to new keyword table
	$res = _query("SELECT pub_id, keywords FROM publication");

	$keyword_array = array();
	if ($res) {
			while ($row = mysql_fetch_array($res) ) {
				$keywords = preg_replace(
							'/ *([^,]+)/',
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
				$res = _query("SELECT keyword_id FROM keywords WHERE keyword='".mysql_real_escape_string($entry['keyword'])."'");
				if (mysql_num_rows($res) > 0) {
					while ($row = mysql_fetch_array($res)) {
						$keyword_id = $row['keyword_id'];
					}
				} else {
					$res = _query("INSERT IGNORE INTO keywords (keyword) VALUES ('".mysql_real_escape_string($entry['keyword'])."')");
					$keyword_id = mysql_insert_id();
				}

				$res = _query("INSERT IGNORE INTO publicationkeywordlink (pub_id, keyword_id) VALUES (".$entry['pub_id'].", ".$keyword_id.");");

				if (mysql_affected_rows() == 1) {
					//echo "Insert pub_id ".$entry['pub_id'].": keyword_id ".$keyword_id."<br/>";
				}
			}
		}

	_query("ALTER TABLE `publication` DROP `keywords`;");

	_query("CREATE TABLE `userbookmarklists` (`user_id` INT( 10 ) NOT NULL ,`pub_id` INT( 10 ) NOT NULL ,PRIMARY KEY (`user_id`, `pub_id` ));");

	_query("INSERT INTO `availablerights` (`name`,`description`) VALUES  ('bookmarklist','use a persistent bookmarklist');");

	_query("ALTER TABLE `topics`		ADD COLUMN `group_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;");
	_query("ALTER TABLE `publication`	ADD COLUMN `group_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;");
	_query("ALTER TABLE `notes`		ADD COLUMN `group_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;");
	_query("ALTER TABLE `attachments`	ADD COLUMN `group_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;");


	_query("ALTER TABLE `topics`		ADD COLUMN `derived_read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `publication`	ADD COLUMN `derived_read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `notes`		ADD COLUMN `derived_read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `attachments`	ADD COLUMN `derived_read_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `topics`		ADD COLUMN `derived_edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `publication`	ADD COLUMN `derived_edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `notes`		ADD COLUMN `derived_edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");
	_query("ALTER TABLE `attachments`	ADD COLUMN `derived_edit_access_level` ENUM('private','public','intern','group') NOT NULL DEFAULT 'intern';");

	_query("INSERT INTO `availablerights` (`name`,`description`) VALUES
			('read_all_override',	'read all attachments, publications, topics and notes, overriding access levels'),
			('edit_all_override',	'edit all attachments, publications, topics and notes, overriding access levels');");
	// _query("DELETE FROM availablerights WHERE
			// name='attachment_edit_all'
			// OR name='attachment_read_all'
			// OR name='note_read_all'
			// OR name='note_edit_all'
			// OR name='topic_read_all'
			// OR name='topic_edit_all';");
	_query("DELETE FROM availablerights
			WHERE name IN
			('attachment_edit_all',
			'attachment_read_all',
			'note_read_all',
			'note_edit_all',
			'topic_read_all',
			'topic_edit_all')");	// SMS 070922: equivalent, is it not?

	_query("UPDATE availablerights SET name='note_edit' WHERE name='note_edit_self';");

	_query("UPDATE topics SET read_access_level='public',derived_read_access_level='public' where topic_id=1;");

	_query("INSERT INTO rightsprofiles VALUES
			('1', 'admin_rights'),
			('2', 'editor_rights'),
			('3', 'reader_rights'),
			('4', 'guest_rights')");
	_query("INSERT INTO rightsprofilerightlink VALUES
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

	_query("ALTER TABLE `users` MODIFY COLUMN `surname` VARCHAR(100) DEFAULT NULL;");

	_query("CREATE TABLE `changehistory`
			(`version` varchar(20) NOT NULL,
			`type` varchar(50) NOT NULL,
			`description` text NOT NULL,
			PRIMARY KEY  (`version`));");
	_query("INSERT INTO `changehistory` (`version`,`type`,`description`) VALUES
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

	_query("ALTER TABLE `users`	ADD COLUMN `lastupdatecheck` INTEGER UNSIGNED NOT NULL DEFAULT 0;");

	_query("ALTER TABLE `personpublicationmark`
			CHANGE COLUMN `person_id` `user_id` INTEGER NOT NULL DEFAULT 0,
			DROP PRIMARY KEY,
			ADD PRIMARY KEY  (`pub_id`, `user_id`);");

	_query("ALTER TABLE `personpublicationmark` RENAME TO `userpublicationmark`;");

	_query("ALTER TABLE `users`	ADD COLUMN `exportinbrowser` ENUM('TRUE','FALSE') NOT NULL DEFAULT 'TRUE';");

	_query("ALTER TABLE `users`	ADD COLUMN `utf8bibtex` ENUM('TRUE','FALSE') NOT NULL DEFAULT 'FALSE';");

	//DR: following query needs to be dependent on current maximum user_id
	//_query("INSERT INTO users (`user_id`,`surname`,`abbreviation`,`type`) VALUES
			//(2,'admins','adm_grp','group'),
			//(3,'readers','read_grp','group'),
			//(4,'editors','ed_grp','group'),
			//(5,'guests','gue_grp','group');");

	_query("UPDATE aigaiongeneral SET version='V2.0'");

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
	include('../aigaionengine/helpers/bibtexutf8_helper.php');

		//author->surname, von, firstname, institute
	$res = _query("SELECT * FROM author");
	if ($res) {
		while ($row = mysql_fetch_array($res)) {
			_query("UPDATE author SET ".
					"surname='".  	mysql_real_escape_string(bibCharsToUtf8FromString($row['surname'])).
					"', von='".		mysql_real_escape_string(bibCharsToUtf8FromString($row['von'])).
					"', firstname='".	mysql_real_escape_string(bibCharsToUtf8FromString($row['firstname'])).
					"', institute='". 	mysql_real_escape_string(bibCharsToUtf8FromString($row['institute'])).
					"' WHERE author_id='{$row['author_id']}' ");
		}
	}
		//keywords->keyword
	$res = _query("SELECT * FROM keywords");
	if ($res) {
		while ($row = mysql_fetch_array($res)) {
			_query("UPDATE keywords SET keyword='".mysql_real_escape_string(bibCharsToUtf8FromString($row['keyword'])).
					"' WHERE keyword_id='{$row['keyword_id']}' ");
		}
	}

	//notes->text
	$res = _query("SELECT * FROM notes");
	if ($res) {
		while ($row = mysql_fetch_array($res)) {
			_query("UPDATE notes SET text='".mysql_real_escape_string(bibCharsToUtf8FromString($row['text'])).
					"' WHERE note_id='{$row['note_id']}' ");
		}
	}

	//publication->title, series, publisher, location, journal, booktitle, institution, address, organisation, school, note, abstract,
	$res = _query("SELECT * FROM publication");
	if ($res) {
		while ($row = mysql_fetch_array($res)) {
			_query("UPDATE publication SET ".
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
	if (AIGAION2_DB_PREFIX!='') {
		_query("ALTER TABLE aigaiongeneral RENAME TO ".AIGAION2_DB_PREFIX."aigaiongeneral;");
		_query("ALTER TABLE attachments RENAME TO ".AIGAION2_DB_PREFIX."attachments;");
		_query("ALTER TABLE author RENAME TO ".AIGAION2_DB_PREFIX."author;");
		_query("ALTER TABLE availablerights RENAME TO ".AIGAION2_DB_PREFIX."availablerights;");
		_query("ALTER TABLE changehistory RENAME TO ".AIGAION2_DB_PREFIX."changehistory;");
		_query("ALTER TABLE config RENAME TO ".AIGAION2_DB_PREFIX."config;");
		_query("ALTER TABLE grouprightsprofilelink RENAME TO ".AIGAION2_DB_PREFIX."grouprightsprofilelink;");
		_query("ALTER TABLE keywords RENAME TO ".AIGAION2_DB_PREFIX."keywords;");
		_query("ALTER TABLE notecrossrefid RENAME TO ".AIGAION2_DB_PREFIX."notecrossrefid;");
		_query("ALTER TABLE notes RENAME TO ".AIGAION2_DB_PREFIX."notes;");
		_query("ALTER TABLE publication RENAME TO ".AIGAION2_DB_PREFIX."publication;");
		_query("ALTER TABLE publicationauthorlink RENAME TO ".AIGAION2_DB_PREFIX."publicationauthorlink;");
		_query("ALTER TABLE publicationkeywordlink RENAME TO ".AIGAION2_DB_PREFIX."publicationkeywordlink;");
		_query("ALTER TABLE rightsprofilerightlink RENAME TO ".AIGAION2_DB_PREFIX."rightsprofilerightlink;");
		_query("ALTER TABLE rightsprofiles RENAME TO ".AIGAION2_DB_PREFIX."rightsprofiles;");
		_query("ALTER TABLE topicpublicationlink RENAME TO ".AIGAION2_DB_PREFIX."topicpublicationlink;");
		_query("ALTER TABLE topics RENAME TO ".AIGAION2_DB_PREFIX."topics;");
		_query("ALTER TABLE topictopiclink RENAME TO ".AIGAION2_DB_PREFIX."topictopiclink;");
		_query("ALTER TABLE userbookmarklists RENAME TO ".AIGAION2_DB_PREFIX."userbookmarklists;");
		_query("ALTER TABLE usergrouplink RENAME TO ".AIGAION2_DB_PREFIX."usergrouplink;");
		_query("ALTER TABLE userpublicationmark RENAME TO ".AIGAION2_DB_PREFIX."userpublicationmark;");
		_query("ALTER TABLE userrights RENAME TO ".AIGAION2_DB_PREFIX."userrights;");
		_query("ALTER TABLE users RENAME TO ".AIGAION2_DB_PREFIX."users;");
		_query("ALTER TABLE usertopiclink RENAME TO ".AIGAION2_DB_PREFIX."usertopiclink;");
	}
	
	_query("ALTER TABLE `".AIGAION2_DB_PREFIX."publication` ADD COLUMN `cleanauthor` TEXT;");
    _query("ALTER TABLE `".AIGAION2_DB_PREFIX."topics` ADD COLUMN `cleanname` VARCHAR(50) NOT NULL default '';");
    _query("INSERT INTO `".AIGAION2_DB_PREFIX."config` (setting,value) 
                 VALUES ('ATT_DEFAULT_READ','intern'),
                        ('ATT_DEFAULT_EDIT','intern'),
                        ('PUB_DEFAULT_READ','intern'),
                        ('PUB_DEFAULT_EDIT','intern'),
                        ('NOTE_DEFAULT_READ','intern'),
                        ('NOTE_DEFAULT_EDIT','intern'),
                        ('TOPIC_DEFAULT_READ','intern'),
                        ('TOPIC_DEFAULT_EDIT','intern');");
    _query("ALTER TABLE `".AIGAION2_DB_PREFIX."userpublicationmark` 
          MODIFY COLUMN `mark` 
                        ENUM('','1','2','3','4','5') NOT NULL DEFAULT 3;");

	_query("ALTER TABLE `".AIGAION2_DB_PREFIX."userpublicationmark`
			CHANGE COLUMN `read` `hasread` enum('y','n') NOT NULL default 'y';");

    echo 'If no errors occurred, you can now start to fill in index.php and then proceed to login into Aigaion 2. Please close your browser first. It is advisable to run a maintenance check upon the first login.<br/>';
} else {
	//no or incorrect pwd - show form
	?>
<form action='migrate_oldmysql.php' method='post'>
	<table bgcolor="F7F7F7" cellspacing="3" cellpadding="3" style="border:1px solid black" width="400"  style='width:395px;'>
		<TR>
		<TD>Name:</TD>
		<TD><input type=text name=aigaion2_user size=50></TD>
		</TR>

		<TR>
		<TD>Password:</TD>
		<TD><input type=password name=aigaion2_pwd size=50></TD>
		</TR>

		<TR>
		<TD></TD>
		<TD><input type=submit name=Submit value='Migrate' size=50></TD>
		</TR>
	</table>
</form>
<?php
}


function _query($q) {
    if (!ini_get('safe_mode'))set_time_limit(5);
	$res = mysql_query($q);
	if (mysql_error())
		echo "{$q}: ".mysql_error()."<br/>";
	return $res;
}
?>