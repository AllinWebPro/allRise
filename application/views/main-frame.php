<?php $this->load->view('includes/functions'); ?>
<!doctype html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" lang="en"><!--<![endif]-->
  <?php $this->load->view('includes/head'); ?>
  <body>
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
    <!--load page-->
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