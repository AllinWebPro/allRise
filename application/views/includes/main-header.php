<header id="main-bar" class="pure-g-r vertical-padding-xsmall">
  <div id="logo" class="pure-u-1-6">
    <div class="horizontal-padding-xlarge center-text">
      <a href="<?php echo site_url('search'); ?>" title="<?php echo SITE_TITLE; ?>" class="branding">all<span class="orange">Rise</span><sup class="pure-hidden-tablet">BETA</sup></a>
    </div>
  </div>
  <div class="pure-u-1-24"></div>
  <div class="pure-u-1-2">
    <div class="horizontal-padding">
      <?php if($page !== 'login-register'): ?>
        <form action="<?php echo site_url('search'); ?>" class="pure-form pure-g-r vertical-padding-xsmall" novalidate>
          <div class="pure-u-5-6">
            <?php echo form_input('k', '', 'placeholder="Keywords, Tags (e.g. Dragons)" class="pure-input-1" id="k"'); ?>
          </div>
          <div class="pure-u-1-6">
            <?php if(isset($sort)): ?><input type="hidden" name="s" id="s" value="<?php echo $sort; ?>"><?php endif; ?>
            <button type="submit" class="pure-button pure-button-small pure-input-1"><i class="fa fa-search"></i></button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <div class="pure-u-1-24"></div>
  <div class="pure-u-1-4">
    <div class="horizontal-padding">
      <div class="pure-g center-text vertical-padding-xsmall">
        <?php if($this->session->userdata('isLoggedIn')): ?>
          <div class="pure-u-1-5">
            <a href="<?php echo site_url('h/create'); ?>" title="Create Headline"><i class="fa fa-dot-circle-o fa-7-4"></i></a>
          </div>
          <div class="pure-u-1-5">
            <a href="<?php echo site_url('notifications'); ?>" title="Notifications" class="pos-rel" data-ajax="<?php echo site_url('ajax/page/notices'); ?>" data-modal='notice-page'>
              <i class="fa fa-book fa-7-4"></i> <strong id="notice-alert" class="notice grey-light-bg orange horizontal-padding-xsmall"></strong>
            </a>
          </div>
          <div class="pure-u-1-5">
            <a href="<?php echo site_url('user/'.$this->session->userdata('user')); ?>" title="Profile"><i class="fa fa-user fa-7-4"></i></a>
          </div>
          <div class="pure-u-1-5">
            <a href="<?php echo site_url('preferences'); ?>" title="Preferences" data-modal="account-preferences"><i class="fa fa-cog fa-7-4"></i></a>
          </div>
          <div class="pure-u-1-5">
            <a href="<?php echo site_url('logout'); ?>" title="Logout"><i class="fa fa-sign-out fa-7-4"></i></a>
          </div>
        <?php elseif($page !== 'login-register'): ?>
          <div class="pure-u-1">
            <a href="<?php echo site_url('?r='.$this->uri->uri_string()); ?>" title="Login / Register" class="pure-button pure-button-small pure-input-1-2 right-align">Login / Register <i class="fa fa-sign-in fa-9-10"></i></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>
<?php if($this->session->userdata('isLoggedIn')): ?>
  <div class="modal-content" id="headline-create" title="Create Headline">
    <form class="pure-u-1 pure-form ajax" method="post" action="<?php echo site_url('headline/create'); ?>" novalidate autocomplete="off">
      <div class="errors"></div>
      <fieldset class="pure-group">
        <input type="text" name="headline" id="headline-headline" class="pure-input-1" placeholder="Headline* (Max length 255 characters)" required>
        <input type="hidden" name="place" id="headline-place" class="pure-input-1 place-ac" placeholder="Location">
        <input type="hidden" name="placeId" id="headline-placeId">
        <input type="text" name="tags" id="headline-tags" class="pure-input-1" placeholder="Tags (comma seperated)">
      </fieldset>
      <fieldset class="images pure-group"></fieldset>
      <fieldset class="resources pure-group"></fieldset>
      <input type="hidden" name="categoryId[]" id="headline-categoryId-1" value="1">
      <input type="hidden" name="ajax" value="<?php echo site_url('ajax/headline'); ?>">
    </form>
  </div>
  <div class="modal-content" id="account-preferences" title="Account Preferences">
    <form class="pure-u-1 pure-form ajax" method="post" action="<?php echo site_url('preferences'); ?>" novalidate enctype='multipart/form-data'>
      <div class="errors"></div>
      <fieldset class="pure-group">
        <input type="text" name="location" id="preferences-location" class="pure-input-1" placeholder="Location" value="<?php echo $this->session->userdata('location'); ?>">
        <textarea name="bio" id="preferences-bio" class="pure-input-1" placeholder="Biography"><?php echo $this->session->userdata('bio'); ?></textarea>
        <div class="pure-g-r">
          <label for="notices" class="pure-u-1-5">Email Notifications:</label>
          <label for="notices-w" class="pure-u-1-5">
            <input name="notices" id="notices-w" type="radio" value="w" class="left-align" <?php echo ($this->session->userdata('notices') == 'w')?'checked':''; ?>> <span class="left-padding-xsmall">Weekly</span>
          </label>
          <label for="notices-d" class="pure-u-1-5">
            <input name="notices" id="notices-d" type="radio" value="d" class="left-align" <?php echo ($this->session->userdata('notices') == 'd')?'checked':''; ?>> <span class="left-padding-xsmall">Daily</span>
          </label>
          <label for="notices-h" class="pure-u-1-5">
            <input name="notices" id="notices-h" type="radio" value="h" class="left-align" <?php echo ($this->session->userdata('notices') == 'h')?'checked':''; ?>> <span class="left-padding-xsmall">Individually</span>
          </label>
        </div>
      </fieldset>
      <iframe src="<?php echo site_url('preferences/photo/'.$this->session->userdata('userId')); ?>" width="100%" height="62"></iframe>
      <fieldset class="pure-group">
        <input type="password" name="password" id="preferences-password" class="pure-input-1" placeholder="Current Password">
        <input type="text" name="email" id="preferences-email" class="pure-input-1" placeholder="Email" value="<?php echo $this->session->userdata('email'); ?>" required>
        <input type="password" name="new_password" id="preferences-new_password" class="pure-input-1" placeholder="New Password">
        <input type="password" name="confirm_password" id="preferences-confirm_password" class="pure-input-1" placeholder="Confirm Password">
      </fieldset>
      <input type="hidden" name="ajax" value="<?php echo site_url('ajax/preferences'); ?>">
    </form>
  </div>
<?php endif; ?>