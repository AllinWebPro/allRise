<div class="vertical-padding-small horizontal-padding-small">
  <div class="pure-u-1 bottom-padding-xsmall">
    <strong><a name="preferences"><i class="fa fa-cog"></i></a> Preferences</strong>
  </div>
  <form class="pure-u-1 pure-form ajax" method="post" action="<?php echo site_url('preferences'); ?>" novalidate enctype='multipart/form-data'>
    <div class="errors">
      <?php if(isset($error)): ?>
        <p><?php echo $error; ?></p>
      <?php endif; ?>
      <?php if(isset($errors)): ?>
        <?php if(is_string($errors)): ?>
          <p><?php echo $errors; ?></p>
        <?php else: ?>
          <?php foreach($errors as $key => $val): ?>
            <p><?php echo $val; ?></p>
          <?php endforeach; ?>
        <?php endif; ?>
      <?php endif; ?>
    </div>
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
    <div class="pure-controls">
      <input type="hidden" name="ajax" value="<?php echo site_url('ajax/preferences'); ?>">
      <button type="submit" class="pure-button pure-input-1"><i class="fa fa-cog"></i> Submit Preferences</button>
    </div>
    <div class="pure-u-1 center-text top-margin-small">
      <a class="delete" href="<?php echo site_url('preferences/destroy/'.md5($this->session->userdata('userId'))); ?>"><i class="fa fa-trash-o fa-9-10"></i> Destroy Account</a>
    </div>
  </form>
</div>