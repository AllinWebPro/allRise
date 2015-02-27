<?php $this->load->view('includes/functions'); ?>
<!doctype html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" lang="en"><!--<![endif]-->
  <?php $this->load->view('includes/head'); ?>
  <body>
    <!--[if lt IE 7]><script>for (x in open);</script><![endif]-->
    <?php $this->load->view('includes/main-header'); ?>
    <div id="content" class="pure-g-r" data-page="<?php echo $page; ?>">
      <?php $this->load->view('main/'.$page); ?>
    </div>
    <?php $this->load->view('includes/main-footer'); ?>
    <?php $this->load->view('includes/main-scripts'); ?>
    <div class="ajax-loader-overlay"></div>
    <div class="ajax-loader"></div>
  </body>
</html>