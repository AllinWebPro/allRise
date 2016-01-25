<h2>Forgot Password</h2>

<form class="pure-u-1 pure-form ajax" method="post" action="<?php echo site_url('password'); ?>" novalidate>
  <fieldset class="pure-group">
    <div class="errors"></div>
    <input type="text" name="email" id="password-email" class="pure-input-1" placeholder="Email" required>
  </fieldset>
  <div class="pure-controls">
    <input type="hidden" name="ajax" value="<?php echo site_url('ajax/password'); ?>">
    <button type="submit" class="pure-button pure-input-1"><i class="fa fa-envelope"></i> Send Email</button>
  </div>
</form>