<footer id="bottom-bar" class="pure-g-r vertical-padding-xsmall">
  <div class="pure-u-1 center-text">
    <a href="<?php echo site_url('p/faq'); ?>" title="F.A.Q.">F.A.Q.</a>
    | <a href="javascript:void(0);" data-ajax="<?php echo site_url('ajax/page/tutorial'); ?>" title="Tutorial" data-modal='modal-page' id="tutorial-link">Tutorial</a>
    <?php if(!$this->session->userdata('isLoggedIn')): ?>
      | <a href="javascript:void(0);" data-ajax="<?php echo site_url('ajax/page/password'); ?>" title="Forgot Password" data-modal='modal-page'>Forgot Password</a>
    <?php endif; ?>
    | <a href="<?php echo site_url('p/bugs'); ?>" title="Report Bug">Report Bug</a>
    | <a href="<?php echo site_url('p/contact'); ?>" title="Contact Us">Contact Us</a>
    <?php if($this->session->userdata('level') == 'a'): ?>
      | <a href="<?php echo site_url('admin'); ?>" title="Admin Panel">Admin Panel</a>
    <?php endif; ?>
  </div>
  <div class="pure-u-1 center-text grey">
    <a href="<?php echo site_url(); ?>" title="<?php echo SITE_TITLE; ?>" class="branding">all<span class="orange">Rise</span></a>
    <span>&copy; <?php echo date("Y"); ?></span>
    | <a href="<?php echo site_url('p/terms'); ?>" title="Terms of Use">Terms of Use</a>
    | <a href="<?php echo site_url('p/policy'); ?>" title="Privacy Policy">Privacy Policy</a>
  </div>
</footer>
<div id="modal-page" title="" class="modal-content">
  <div class="message">Content is loading...</div>
  <div class="page hidden"></div>
</div>
<div class="modal-content" id="delete-confirm" title="Empty the recycle bin?">
  These items will be permanently deleted and cannot be recovered. Are you sure?
</div>
<div id="comingsoon" title="Coming Soon" class="modal-content">
  <p>This functionality is currently in development.</p>
</div>