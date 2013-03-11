<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/site/stats

Shows a block of site stats

Parameters:
    none

*/
	//get number of authors
    $authorCount = $this->author_db->getAuthorCount();
    
	//get number of maintopics
    $topicCount = $this->topic_db->getMainTopicCount();

	$publicationCount = $this->topic_db->getVisiblePublicationCountForTopic(1);
	$publicationReadCount = $this->topic_db->getReadPublicationCountForTopic(1);

	echo "
<p class='header1'>".__('Aigaion statistics')."</p>
<ul>
<li>".sprintf(__('%s publications (%s read)'), $publicationCount, $publicationReadCount)."</li>
<li>".sprintf(__('%s authors'), $authorCount)."</li>
<li>".sprintf(__('%s main topics'), $topicCount)."</li>
</ul>
";
?>