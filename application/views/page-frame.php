<?php $this->load->view('includes/functions'); ?>
<!doctype html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" lang="en"><!--<![endif]-->
  <?php $this->load->view('includes/head'); ?>
  <body>
    <?php $this->load->view('includes/main-header'); ?>
    <div id="content" class="pure-g-r" data-page="<?php echo $page; ?>">
      <div class="pure-u-1-8"></div>
      <div id="notices" class="pure-u-3-4 vertical-padding-small">
        <section class="small">
          <?php if($this->session->userdata('isLoggedIn')): ?>
            <div class="horizontal-margin-small vertical-margin-small bottom-margin-large">
              <div class="pure-u-1 top-padding-small horizontal-padding-small">
                <strong><a name="support"><i class="fa fa-cog"></i></a> User Settings</strong>
              </div>
              <nav class="vertical-padding-xsmall horizontal-padding-small">
                <a href="<?php echo site_url('preferences'); ?>" title="Preferences" class="vertical-padding-xsmall horizontal-padding-xsmall display-b">Preferences</a>
              </nav>
            </div>
          <?php endif; ?>
          <div class="horizontal-margin-small vertical-margin-small bottom-margin-large">
            <div class="pure-u-1 top-padding-small horizontal-padding-small">
              <strong><a name="support"><i class="fa fa-life-ring"></i></a> Site Support</strong>
            </div>
            <nav class="vertical-padding-xsmall horizontal-padding-small">
              <a href="<?php echo site_url('p/faq'); ?>" title="F.A.Q." class="vertical-padding-xsmall horizontal-padding-xsmall display-b">F.A.Q.</a>
              <a href="<?php echo site_url('p/bugs'); ?>" title="Report a Bug" class="vertical-padding-xsmall horizontal-padding-xsmall display-b">Report a Bug</a>
              <a href="<?php echo site_url('p/contact'); ?>" title="Contact Us" class="vertical-padding-xsmall horizontal-padding-xsmall display-b">Contact Us</a>
            </nav>
          </div>
          <div class="horizontal-margin-small vertical-margin-small">
            <div class="pure-u-1 top-padding-small horizontal-padding-small">
              <strong><a name="contracts"><i class="fa fa-file-text-o"></i></a> Site Contracts</strong>
            </div>
            <nav class="vertical-padding-xsmall horizontal-padding-small">
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
      <div class="pure-u-1-8"></div>
    </div>
    <?php $this->load->view('includes/main-footer'); ?>
    <?php $this->load->view('includes/main-scripts'); ?>
    <div class="ajax-loader-overlay"></div>
    <div class="ajax-loader"></div>
  </body>
</html>