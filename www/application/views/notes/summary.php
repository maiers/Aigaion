<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<!-- Single note display -->
<?php
/**
views/notes/summary

Shows a summary of a note: who entered it, what is the text, and some edit buttons etc

Parameters:
    $note=>the Note object that is to be shown
    
appropriate read rights are assumed. Edit block depends on other rights.
*/
//get text, replace links
$text = auto_link($note->text);

//replace bibtex cite_ids that appear in the text with a link to the publication
$link = "";
$bibtexidlinks = getBibtexIdLinks();
foreach ($note->xref_ids as $xref_id) {
    $link = $bibtexidlinks[$xref_id];
	//check whether the xref is present in the session var (should be). If not, try to correct the issue.
	if ($link == "") {
	    $this->db->select('bibtex_id');
		$Q = $this->db->get_where('publication',array('pub_id'=>$xref_id));
		if ($Q->num_rows() > 0) {
			$R = $Q->row();
			if (trim($R->bibtex_id) != "") {
				$bibtexidlinks[$xref_id ] = array($R->bibtex_id, "/\b(?<!\.)(".preg_quote($R->bibtex_id, "/").")\b/");
				$link = $bibtexidlinks[$xref_id ];
			}
		}
	}

	if ($link != "") {
		$text = preg_replace(
			$link[1],
			anchor('/publications/show/'.$xref_id,$link[0]),
			$text);
	}
}

echo "<div class='readernote'>
  <b>[".getAbbrevForUser($note->user_id)."]</b>: ";
  echo $text;

//the block of edit actions: dependent on user rights
$userlogin  = getUserLogin();
$user       = $this->user_db->getByID($userlogin->userID());

if (    ($userlogin->hasRights('note_edit'))
     && 
        $this->accesslevels_lib->canEditObject($note)      
    ) 
{
    echo "<br/>[".anchor('notes/delete/'.$note->note_id,__('delete'));
    echo "]&nbsp;[".anchor('notes/edit/'.$note->note_id,__('edit')).']';
    
    $read_icon = $this->accesslevels_lib->getReadAccessLevelIcon($note);
    $edit_icon = $this->accesslevels_lib->getEditAccessLevelIcon($note);
    
    $readrights = $this->ajax->link_to_remote($read_icon,
                  array('url'     => site_url('/accesslevels/toggle/note/'.$note->note_id.'/read'),
                        'update'  => 'note_rights_'.$note->note_id
                       )
                  );
    $editrights = $this->ajax->link_to_remote($edit_icon,
                  array('url'     => site_url('/accesslevels/toggle/note/'.$note->note_id.'/edit'),
                        'update'  => 'note_rights_'.$note->note_id
                       )
                  );
    echo"[<span id='note_rights_".$note->note_id."' title='".sprintf(__('%s read / edit rights'), __('note'))."'>r:".$readrights."e:".$editrights."</span>]";
}
?>
</div>
<!-- End of single note display -->
