<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php 
/** Custom field settings and settings for using author synonyms */

echo form_hidden('configformname','content');

?>

<!-- CUSTOM FIELDS SETTINGS -->
	    <tr>
	        <td colspan='2'><hr>
	          <p class='header2'><?php echo __('Custom fields settings:');?></p>
	          <p><?php echo __('Aigaion allows adding installation-specific custom fields to publications, authors and topics. You can create these custom fields here.');?></p>
<?php	            
    echo form_hidden('customfield_count', sizeof($customFieldsInfo))."\n";
?>
	        </td>
	    </tr>
          </td>
      </tr>
      <tr>
        <td colspan = '2' align='center'>
          <table>
            <tr>
              <td><p class='header2'><?php echo __('Type');?></p></td>
              <td><p class='header2'><?php echo __('Name');?></p></td>
              <td><p class='header2'><?php echo __('Order');?></p></td>
              <td><p class='header2'><?php echo __('Keep');?></p></td>
            </tr>
            <?php
            $count = 0;
            //generate type count arrays
            $authorTypeCount = $publicationTypeCount = $topicTypeCount = 0;
            $authorTypeCountArray = $publicationTypeCountArray = $topicTypeCountArray = array();
            $authorTypeCountArray[] = 0;
            $publicationTypeCountArray[] = 0;
            $topicTypeCountArray[] = 0;
            
            foreach ($customFieldsInfo as $customField)
            {
              if ($customField['type'] == 'author') {
                $authorTypeCount++;
                $authorTypeCountArray[] = $authorTypeCount;
              }
              else if ($customField['type'] == 'publication') {
                $publicationTypeCount++;
                $publicationTypeCountArray[] = $publicationTypeCount;
              }
              else if ($customField['type'] == 'topic') {
                $topicTypeCount++;
                $topicTypeCountArray[] = $topicTypeCount;
              }
            }
            $countArrays = array('author' => $authorTypeCountArray, 'publication' => $publicationTypeCountArray, 'topic' => $topicTypeCountArray);
            foreach ($customFieldsInfo as $customField)
            {
              ?>
            <tr>
              <td><?php 
                echo form_hidden('CUSTOM_FIELD_ID_'.$count, $customField['type_id'])."\n";
                echo form_hidden('CUSTOM_FIELD_TYPE_'.$count, $customField['type'])."\n";
                echo translateCustomFieldsType($customField['type']); ?></td>
              <td><?php echo form_input(array('name'=>'CUSTOM_FIELD_NAME_'.$count,'id'=>'CUSTOM_FIELD_NAME_'.$count,'value'=>$customField['name'], 'size'=>30)); ?></td>
              <td><?php echo form_dropdown('CUSTOM_FIELD_ORDER_'.$count,$countArrays[$customField['type']],$customField['order']); ?></td>
              <td><?php echo form_checkbox('CUSTOM_FIELD_KEEP_'.$count,'CUSTOM_FIELD_KEEP_'.$count,true)."\n"; ?></td>
            </tr>
              <?php
              $count++;
            }
            //show empty row for adding new custom fields
            ?>
            <tr>
              <td><?php 
                echo form_hidden('CUSTOM_FIELD_ID_'.$count, '')."\n";
                echo form_dropdown('CUSTOM_FIELD_TYPE_'.$count,array(''=>'','publication' => __('Publication'),'author' => __('Author'),'topic' => __('Topic')),''); ?></td>
              <td><?php echo form_input(array('name'=>'CUSTOM_FIELD_NAME_'.$count,'id'=>'CUSTOM_FIELD_NAME_'.$count,'value'=>'', 'size'=>30)); ?></td>
              <td><?php echo form_dropdown('CUSTOM_FIELD_ORDER_'.$count,max($countArrays),''); ?></td>
              <td><?php echo form_checkbox('CUSTOM_FIELD_KEEP_'.$count,'CUSTOM_FIELD_KEEP_'.$count,false)."\n"; ?></td>
            </tr>
          </table>
        </td>
      </tr>

<!-- AUTHOR SYNONYMS SETTINGS -->
	    <tr>
	        <td colspan='2'><hr>
	          <p class='header2'><?php echo __('Author synonym settings:');?></p>
	          <p><?php echo __('Aigaion can be configured to allow the use of author synonyms, or aliases under which somebody has also published.');?></p>
	        </td>
	    </tr>

	    <tr>
	        <td colspan='2'><label><?php echo __('Allow the use of author synonyms');?></label>
	        <?php
            echo form_checkbox('USE_AUTHOR_SYNONYMS','USE_AUTHOR_SYNONYMS',$siteconfig->getConfigSetting("USE_AUTHOR_SYNONYMS") == "TRUE");
          ?>
          </td>
      </tr>
	    <tr>
	        <td align='left' colspan='2'>
          <?php	          
            //check whether author synonyms are enabled. If so, and synonyms exist in the database, warn that turning this of will remove existing synonyms.
            if ($siteconfig->getConfigSetting('USE_AUTHOR_SYNONYMS') == 'TRUE')
            {
              $this->db->where('synonym_of !=', '0');
              //$this->db->from('author');
              if ($this->db->count_all_results('author') > 0) //synonyms exist, report issue
              { 
    	          echo "<img class='icon' src='".getIconUrl("small_arrow.gif")."'>";
                echo "<div class='errormessage'>";
                echo __("Some authors have synonym names associated to them.")." ".__("When you disable the use of synonyms:")." ".__("These synonyms should been merged into the primary authors, and the synonym names themselves should be removed.")." ".__("Please go to the site maintenance page to do this.");
                echo "</div>";
              }
            }
	        ?>
	        </td>
	    </tr>


<!-- BOOK COVER SETTINGS -->
	    <tr>
	        <td colspan='2'><hr>
	          <p class='header2'><?php echo __('Book cover settings').':';?></p>
	          <p><?php echo __('Aigaion can be configured to allow images of book covers to be uploaded');?></p>
	        </td>
	    </tr>

	    <tr>
	        <td><label><?php echo __('Allow uploading of book cover images').':';?></label>
	        </td>
	        <td>
	        <?php
            echo form_checkbox('USE_BOOK_COVERS','USE_BOOK_COVERS',$siteconfig->getConfigSetting("USE_BOOK_COVERS") == "TRUE");
          ?>
          </td>
      </tr>
