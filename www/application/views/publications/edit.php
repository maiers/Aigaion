<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
  $publicationfields  = getPublicationFieldArray($publication->pub_type);
  $customfieldkeys    = $this->customfields_db->getCustomFieldKeys('publication');
  $formAttributes = array('id' => "publication_{$publication->pub_id}_edit", 'onsubmit' => "submitPublicationForm('publication_{$publication->pub_id}_edit');");
  $userlogin          = getUserLogin();
  $user               = $this->user_db->getByID($userlogin->userID());

$this->load->helper('translation');

echo "<script language='javascript'>";
include_once(AIGAION_WEBCONTENT_DIR.'javascript/authorselection.js');
echo "</script>";
  
?>
<div class='publication'>
<script language="javascript">
    function monthFieldSwitch(simpleMonth) {
        if (simpleMonth)
        {
          Element.replace('monthbox','<div id="monthbox" name="monthbox"><?php echo str_replace("\"","\\\"",str_replace("\n","",form_dropdown('month', getMonthsInternalNoQuotes(), formatMonthBibtexForEdit($publication->month))."&nbsp;".$this->ajax->button_to_function(__("Special"),"monthFieldSwitch(false);")));?></div>');
        }
        else 
        {
          Element.replace('monthbox','<?php echo "<div id=\"monthbox\" name=\"monthbox\">".__('In the input field below, you can enter a month using bibtex codes containing things such as the default month abbreviations. Do not forget to use outer braces or quotes for literal strings.')." <br/> ".__('Examples').": <ul><li>aug</li><li>nov#{~1st}</li><li>{".__('Between January and May')."}</li></ul> <br/><span title=\"".__('optional field')."\">".str_replace("\"","\\\"",str_replace("\n","",form_input(array('name' => 'month','id' => 'month','size' => '90','alt' => __('optional'),'autocomplete' => 'off','class' => 'optional'),formatMonthBibtexForEdit($publication->month))."&nbsp;".$this->ajax->button_to_function(__("Simple"),"monthFieldSwitch(true);")))."</span></div>"; ?>');
        }
    }
</script>
  <div class='header'><?php
    switch ($edit_type) {
      case 'add':
        echo __('New Publication');
        break;
      case 'edit':
      default:
        echo __('Edit Publication');
        break;
    }
    ?>
    </div>
<?php
  $isAddForm = $edit_type=='add';
  //open the edit form
  echo form_open('publications/commit', $formAttributes)."\n";
  echo "<div>\n";
  echo form_hidden('edit_type',   $edit_type)."\n";
  echo form_hidden('pub_id',      $publication->pub_id)."\n";
  echo form_hidden('user_id',     $publication->user_id)."\n";
  echo form_hidden('submit_type', 'submit')."\n";
  echo form_hidden('formname','publication')."\n";
  //form helper does not support ids, therefore by hand:
  echo "<input type='hidden' name='pubform_authors' id='pubform_authors' value=''/>\n";//into this field, the selectedauthors box will be parsed upon commit
  echo "<input type='hidden' name='pubform_editors' id='pubform_editors' value=''/>\n";//into this field, the selectededitors box will be parsed upon commit
  echo "</div>\n";
?>
  <table class='publication_edit_form' width='100%'>
    <tr>
      <td><?php echo __('Type of publication');?>:</td>
      <td><?php echo form_dropdown('pub_type', getPublicationTypes(), $publication->pub_type, "onchange=\"this.form.submit_type.value='type_change'; submitPublicationForm('publication_".$publication->pub_id."_edit');\""); ?></td>
    </tr>
    <tr>
      <td><?php echo __('Publication status');?>:</td>
      <td><?php echo form_dropdown('status', getPublicationStatusTypes(), $publication->status); ?></td>
    </tr>

    <tr>
      <td><?php echo __('Title');?>:</td>
      <td><?php echo form_input(array('name' => 'title', 
                                      'id'   => 'title', 
                                      'size' => '90',
                                      'class'=> 'required'), $publication->title); ?></td>
    </tr>
    <tr>
      <td><?php echo __('Citation');?>:</td>
      <td><?php echo form_input(array('name' => 'bibtex_id', 'id' => 'bibtex_id', 'size' => '90'), $publication->bibtex_id); ?></td>
    </tr>
<!-- insert text1 -->
<?php 
    //collect show data for all publication fields 
    //the HIDDEN fields are shown at the end of the form; the NOT HIDDEN ones are shown here.
    $hiddenFields = "";
    $capitalfields = getCapitalFieldArray();
    foreach ($publicationfields as $key => $class):
      //fields that are hidden but non empty are shown nevertheless
      if (($class == 'hidden') && ($publication->$key != '')) {
        $class = 'nonstandard';
      }
      $fieldCol = "";
      if ($key=='namekey') {
        $fieldCol = __('Key').' <span title="'.__('This is the bibtex `key` field, used to define sorting keys').'">(?)</span>'; //stored in the databse as namekey, it is actually the bibtex field 'key'
      } else { 
          if (in_array($key,$capitalfields)) {
              $fieldCol = utf8_strtoupper(translateField($key));
          } else  {
              $fieldCol = translateField($key,true);
          }

      }
      if ($class=='nonstandard') {
        $fieldCol .= ' <span title="'.__('This field might not be used by BibTeX for this publication type').'">(*)</span>';
      }
      $fieldCol .= ':';
      $valCol = "";
      if ($key == "month")
      {
        
        $month = $publication->month;          
        if (array_key_exists($month,getMonthsInternal())) 
        {
          $valCol .= "
            <div id='monthbox' name='monthbox'>
            </div>
            <script language='javascript'>monthFieldSwitch(true);</script>
            "; //note: the script must be placed outside the div. IE crashes on replacing the content of the div, when it includes the script, while the script is still running
        } 
        else 
        {
          $valCol .= "
            <div id='monthbox' name='monthbox'>
            </div>     
            <script language='javascript'>monthFieldSwitch(false);</script>
            "; //note: the script must be placed outside the div. IE crashes on replacing the content of the div, when it includes the script, while the script is still running
        }
      }
      else if ($key == "pages")
      {
        $valCol .= "<span title='".sprintf(__('%s field'), $class)."'>".form_input(array('name' => 'pages', 
                                                                'id' => 'pages', 
                                                                'size' => '90', 
                                                                'alt' => $class, 
                                                                'autocomplete' => 'off', 
                                                                'class' => $class), 
                                                          $publication->pages)."</span>\n";
      }
      elseif (($key == "abstract") || ($key == "userfields" ))
      {
          $valCol .= "<span title='".sprintf(__('%s field'), $class)."'>".form_textarea(array('name' => $key, 
                                                                     'id' => $key, 
                                                                     'cols' => '87', 
                                                                     'rows' => '3', 
                                                                     'alt' => $class, 
                                                                     'autocomplete' => 'off', 
                                                                     'class' => $class), 
                                                               $publication->$key)."</span>\n";
      }
      else 
      {
          $onelineval = $publication->$key;
          $valCol .= "<span title='".sprintf(__('%s field'), $class)."'>".form_input(array('name' => $key, 
                                                                     'id' => $key, 
                                                                     'size' => '90', 
                                                                     'alt' => $class, 
                                                                     'autocomplete' => 'off', 
                                                                     'class' => $class), 
                                                               $onelineval)."</span>\n";      
      }
    
      //at this point, $valcol and $fieldcol give the elements for the form. Now to decide:
      //show directly (non-hidden) or postpone to the dispreferred section?
      if ($class=='hidden') {
        $showdata = "<tr class='hidden'>";
      } else {
        $showdata = "<tr>";
      }   
      $showdata .= "
        <td valign='top'>
        ".$fieldCol."
        </td>
        <td valign='top'>
        ".$valCol."
        </td>
      </tr>
      ";
      if ($class=='hidden') {
        $hiddenFields .= $showdata;
      } else {
        echo $showdata;
      }   
    endforeach;
    
    //do the custom fields
    $customFields = $publication->getCustomFields();
    foreach ($customfieldkeys as $field_id => $field_name) {
      if (array_key_exists($field_id, $customFields)) {
        $value = $customFields[$field_id]['value'];
      }
      else {
        $value = '';
      }
      ?>
    <tr>
      <td><?php echo $field_name; ?>:</td>
      <td><?php echo form_input(array('name' => 'CUSTOM_FIELD_'.$field_id, 'id' => 'CUSTOM_FIELD_'.$field_id, 'size' => '90'), $value); ?></td>
    </tr>
      <?php
    }
    
    $keywords = $publication->getKeywords();
    if (is_array($keywords))
    {
      $keyword_string = "";
      foreach ($keywords as $keyword)
      {
        $keyword_string .= $keyword->keyword.", ";
      }
      $keywords = substr($keyword_string, 0, -2);
    }
    else
      $keywords = "";
      
      $key    = 'keywords';
      $class  = 'optional';

?>      
    <tr>
      <td valign='top'><?php echo __('Keywords');?>:</td>
      <td valign='top'><?php echo "<span title='".sprintf(__('%s field'), $class)."'>".form_input(array('name' => $key, 'id' => $key, 'size' => '90', 'alt' => $class, 'autocomplete' => 'off', 'class' => $class), $keywords);?></span>
      <div name='keyword_autocomplete' id='keyword_autocomplete' class='autocomplete'>
      </div>
      <?php echo $this->ajax->auto_complete_field('keywords', $options = array('url' => base_url().'index.php/keywords/li_keywords/', 'update' => 'keyword_autocomplete', 'tokens' => array(",", ";"), 'frequency' => '0.01'))."\n";?>
      </td>
    </tr>
<?php
    //show dispreferred fields at the end
    echo $hiddenFields;
	/*a short note: the following long piece of code creates the author and editor boxes which can be 
	filled, emptied and reordered. When the main form is committed, these boxes should be processed into 
	two form fields using the 'getAUthors' and 'getEditors' javascript functions*/
?>
    <tr>
        <td colspan='2'>
    	<table width='100%'>
			<tr>
				<td  width='55%' valign='top'>
					<table width='100%'>
						<tr><td width='80%' align='left'><?php echo __('Authors');?></td>
							<td width='20%'></td></tr>
						<tr><td align='right'>
							<select name='selectedauthors' id='selectedauthors' style='width:100%;' size='5'>
<?php
                                if (is_array($publication->authors))
                                {
                                  foreach ($publication->authors as $author)
                                  {
                            		echo "<option value=".$author->author_id.">".$author->getName('vlf')."</option>\n";
                            	  }
                            	}
?>
							</select>
						</td>
						<td align='center'>
<?php
                            echo '['.$this->ajax->link_to_function('&lt;&lt;&nbsp;'.__('add'),'AddAuthor();').']<br/>';
                            echo '['.$this->ajax->link_to_function(__('rem').'&nbsp;&gt;&gt;','RemoveAuthor();').']<br/>';
?>
                        </td>
                        </tr>
						<tr><td align='right'>
<?php
                            echo '['.$this->ajax->link_to_function(__('up'),'AuthorUp();').']';
                            echo '['.$this->ajax->link_to_function(__('down'),'AuthorDown();').']';
?>
						</td><td></td></tr>
						<tr><td align='left'><?php echo __('Editors');?></td><td></td></tr>
						<tr>
						<td align='right'>
							<select name='selectededitors' id='selectededitors' style='width: 100%;' size='5'>
<?php
                                if (is_array($publication->editors))
                                {
                                  foreach ($publication->editors as $editor)
                                  {
                            		echo "<option value=".$editor->author_id.">".$editor->getName('vlf')."</option>\n";
                            	  }
                            	}
?>
							</select></td>
						<td align='center'>
<?php
                            echo '['.$this->ajax->link_to_function('&lt;&lt;&nbsp;'.__('add'),'AddEditor();').']<br/>';
                            echo '['.$this->ajax->link_to_function(__('rem').'&nbsp;&gt;&gt;','RemoveEditor();').']<br/>';
?>
                        </td>
					    </tr>
						<tr><td align='right'>
<?php
                            echo '['.$this->ajax->link_to_function(__('up'),'EditorUp();').']';
                            echo '['.$this->ajax->link_to_function(__('down'),'EditorDown();').']';
?>
						</td><td></td></tr>
					</table>
				</td>
				<td width='45%' valign='top'>
					<table width='100%'>
						<tr><td align='center'><div id='addnewauthorbutton'>[<a href="#" onclick="AddNewAuthor(); return false;"><?php echo __('Create as new name');?></a>]</div></td></tr>
						<tr><td><?php echo __('Search');?>: <input title=<?php echo "'".__('Type in name to quick search. Note: use unaccented letters!')."'";?> type='text' onkeyup='AuthorSearch();' name='authorinputtext' id='authorinputtext' size='31'></td></tr>
						<tr><td><select style='width:22em;' size='12' name='authorinputselect' id='authorinputselect'></select></td></tr>
						<tr><td align='right'></td></tr>
					</table>
				</td>
				
			</tr>
		</table>
	    <script language='JavaScript'>Init();</script>
	    </td>
    </tr>

  </table>
<?php
     

if ($edit_type=='edit') {
  echo $this->ajax->button_to_function(__('Change'),"submitPublicationForm('publication_".$publication->pub_id."_edit');")."\n";
} else {
  echo $this->ajax->button_to_function(__('Add'),"submitPublicationForm('publication_".$publication->pub_id."_edit');")."\n";
}
  echo form_close()."\n";

if ($edit_type=='edit') {
  echo form_open('publications/show/'.$publication->pub_id);
} else {
  echo form_open('');
}
  echo form_submit('cancel', __('Cancel'));
  echo form_close()."\n";

?>
</div>
