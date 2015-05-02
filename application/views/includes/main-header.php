<header id="main-bar" class="pure-g-r vertical-padding-xsmall">
  <div id="logo" class="pure-u-1-6">
    <div class="horizontal-padding-xlarge center-text">
      <a href="<?php echo site_url('search'); ?>" title="<?php echo SITE_TITLE; ?>" class="branding">all<span class="orange">Rise</span><sup class="pure-hidden-tablet">BETA</sup></a>
    </div>
  </div>
  <div class="pure-u-1-24 pure-hidden-phone"></div>
  <div class="pure-u-1-2 pure-hidden-phone">
    <div class="horizontal-padding">
      <?php if($page !== 'login-register'): ?>
        <form action="<?php echo site_url('search'); ?>" class="pure-form pure-g-r vertical-padding-xsmall" novalidate>
          <div class="pure-u-5-6">
            <?php echo form_input('k', '', 'placeholder="Keywords, Tags (e.g. Dragons)" class="pure-input-1 no-round" id="k"'); ?>
          </div>
          <div class="pure-u-1-6">
            <?php if(isset($sort)): ?><input type="hidden" name="s" id="s" value="<?php echo $sort; ?>"><?php endif; ?>
            <button type="submit" class="pure-button pure-button-small pure-input-1"><i class="fa fa-search"></i></button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <div class="pure-u-1-24 pure-hidden-phone"></div>
  <div class="pure-u-1-4">
    <div class="horizontal-padding">
      <div class="pure-g center-text vertical-padding-xsmall">
        <?php if($this->session->userdata('isLoggedIn')): ?>
          <div class="pure-u-1-5">
            <a href="<?php echo site_url('h/create'); ?>" title="Create Headline">
              <div class="icon-box med"><?php echo file_get_contents('media/svg/create.svg'); ?></div>
            </a>
          </div>
          <div class="pure-u-1-5">
            <a href="<?php echo site_url('notifications'); ?>" title="Notifications" class="pos-rel" data-ajax="<?php echo site_url('ajax/page/notices'); ?>" data-modal='notice-page'>
              <i class="fa fa-book fa-7-4 ltgrey"></i> <strong id="notice-alert" class="notice white orange-bg horizontal-padding-xsmall"></strong>
            </a>
          </div>
          <div class="pure-u-1-5">
            <a href="<?php echo site_url('u/'.$this->session->userdata('user')); ?>" title="Profile"><i class="fa fa-user fa-7-4 ltgrey"></i></a>
          </div>
          <div class="pure-u-1-5">
            <a href="<?php echo site_url('preferences'); ?>" title="Preferences"><i class="fa fa-cog fa-7-4 ltgrey"></i></a>
          </div>
          <div class="pure-u-1-5">
            <a href="<?php echo site_url('logout'); ?>" title="Logout"><i class="fa fa-sign-out fa-7-4 ltgrey"></i></a>
          </div>
        <?php elseif($page !== 'login-register'): ?>
          <div class="pure-u-1">
            <a href="<?php echo site_url('?r='.$this->uri->uri_string()); ?>" title="Login / Register" class="pure-button pure-button-small pure-input-1-2 right-align pure-hidden-phone">Login / Register <i class="fa fa-sign-in fa-9-10"></i></a>
            <a href="<?php echo site_url('?r='.$this->uri->uri_string()); ?>" title="Login / Register" class="pure-button pure-button-small pure-input-1-2 center-align pure-visible-phone">Login / Register <i class="fa fa-sign-in fa-9-10"></i></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>