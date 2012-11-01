<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
      <div style='clear:both;'></div>
      </div>
      <!-- End of content_holder -->

    	<div id="footer_holder">
    		<?php echo sprintf(__('processing time: %s seconds'), $this->benchmark->elapsed_time());?>.
    	</div>

    </div>
    <!-- End of main_holder -->
    
  </body>
</html>