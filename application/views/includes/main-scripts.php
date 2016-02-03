<script>
var site_title = " | <?php echo SITE_TITLE; ?>";
var site_url = "<?php echo site_url(); ?>";
</script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<script src="<?php echo site_url('media/ckeditor/ckeditor.js'); ?>"></script>
<script src="<?php echo site_url('media/ckeditor/adapters/jquery.js'); ?>"></script>
<script src="<?php echo site_url('media/js/vendor/masonry.pkgd.min.js'); ?>"></script>
<script src="<?php echo site_url('media/js/vendor/jquery.prettyPhoto.js'); ?>"></script>
<script src="<?php echo site_url('media/js/vendor/triggered.autocomplete.js'); ?>"></script>
<script src="<?php echo site_url('media/js/fix.js'); ?>"></script>
<script src="<?php echo site_url('media/js/main-scripts.js'); ?>"></script>
<?php if($this->session->userdata('isLoggedIn') && $this->session->flashdata('tutorial')): ?>
  <script>setTimeout(function() { $("#tutorial-link").click(); }, 2000);</script>
<?php elseif(isset($_REQUEST['tutorial'])): ?>
  <script>setTimeout(function() { $("#tutorial-link").click(); }, 2000);</script>
<?php endif; ?>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  ga('create', 'UA-43024711-3', 'allrise.co');
  ga('send', 'pageview');
</script>