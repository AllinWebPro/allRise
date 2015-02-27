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
      <div id="notices" class="pure-u-1 vertical-padding-small">
        <section id="notice-settings" class="small">
          <div class="horizontal-margin-small vertical-margin-small">
            <h3 class="horizontal-padding-xsmall">Site Support</h3>
            <nav class="vertical-padding-xsmall horizontal-padding-xsmall">
              <a href="<?php echo site_url('p/faq'); ?>" title="F.A.Q." class="vertical-padding-xsmall horizontal-padding-xsmall display-b">F.A.Q.</a>
              <a href="<?php echo site_url('p/bugs'); ?>" title="Report a Bug" class="vertical-padding-xsmall horizontal-padding-xsmall display-b">Report a Bug</a>
              <a href="<?php echo site_url('p/contact'); ?>" title="Contact Us" class="vertical-padding-xsmall horizontal-padding-xsmall display-b">Contact Us</a>
            </nav>
          </div>
          <div class="horizontal-margin-small vertical-margin-small">
            <h3 class="horizontal-padding-xsmall">Site Contracts</h3>
            <nav class="vertical-padding-xsmall horizontal-padding-xsmall">
              <a href="<?php echo site_url('p/terms'); ?>" title="Terms of Use" class="vertical-padding-xsmall horizontal-padding-xsmall display-b">Terms of Use</a>
              <a href="<?php echo site_url('p/policy'); ?>" title="Privacy Policy" class="vertical-padding-xsmall horizontal-padding-xsmall display-b">Privacy Policy</a>
            </nav>
          </div>
        </section>
        <section id="notice-list" class="large">
          <div class="horizontal-margin-small vertical-margin-small">
            <?php $this->load->view('main/'.$page); ?>
          </div>
        </section>
      </div>
    </div>
    <?php $this->load->view('includes/main-footer'); ?>
    <?php $this->load->view('includes/main-scripts'); ?>
    <div class="ajax-loader-overlay"></div>
    <div class="ajax-loader"></div>
  </body>
</html>