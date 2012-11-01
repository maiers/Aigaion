<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
$this->load->helper('form');
$this->load->helper('translation');
?>
	    
	    <tr>
	        <td align='left' colspan='2'><hr></td>
	    </tr>
      <tr><td>
<?php
    echo form_submit('submit',__('Store new settings'));
?>
        </td>
        </tr>
    </table>
<?php
echo form_close();
echo form_open('site/configure');
echo form_submit('cancel',__('Cancel'));
echo form_close();
?>
</div>