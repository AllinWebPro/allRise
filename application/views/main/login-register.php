<aside class="pure-u-7-12 vertical-padding-xlarge">
  <div class="horizontal-padding-xlarge vertical-padding-xlarge">
    <p class="intro-text vertical-padding-xlarge">Collaborative News for Everyone.<br><br>allRise helps communities large and small connect in new ways to share real-time important information and easily build it into high-quality news articles.  It's democratic news by and for everyone.  Our goal is to become the most valuable source of real-time news in the world.</p>
  </div>
</aside>
<div class="pure-u-7-24 bottom-padding">
  <div class="horizontal-padding-huge vertical-padding-xlarge">
    <section id="login" class="pure-g vertical-padding-xlarge">
      <form class="pure-u-1 pure-form ajax" method="post" action="<?php echo site_url('login'); ?>" novalidate>
        <fieldset class="pure-group">
          <legend>Login</legend>
          <div class="errors"></div>
          <input type="text" name="login" id="login-login" class="pure-input-1" placeholder="Username / Email" required>
          <input type="password" name="password" id="login-password" class="pure-input-1" placeholder="Password" required>
        </fieldset>
        <div class="pure-controls">
          <input type="hidden" name="redirect" value="<?php echo isset($_GET['r'])?$_GET['r']:'search'; ?>">
          <input type="hidden" name="ajax" value="<?php echo site_url('ajax/login'); ?>">
          <button type="submit" class="pure-button pure-input-1">Login <i class="fa fa-sign-in"></i></button>
        </div>
      </form>
    </section>
    <section id="register" class="pure-g vertical-padding-xlarge">
      <form class="pure-u-1 pure-form ajax" method="post" action="<?php echo site_url('register'); ?>" autocomplete="off" novalidate>
        <fieldset class="pure-group">
          <legend>Register</legend>
          <div class="errors"></div>
          <input type="text" name="user" id="register-user" class="pure-input-1" placeholder="Username" required autocomplete="off">
          <input type="text" name="email" id="register-email" class="pure-input-1" placeholder="Email" required autocomplete="off">
          <input type="password" name="password" id="register-password" class="pure-input-1" placeholder="Password" required autocomplete="off">
        </fieldset>
        <div class="pure-controls">
          <label for="register-confirm" class="pure-checkbox">
            <input id="register-confirm" type="checkbox"> I accept the
            <a href="<?php echo site_url('terms'); ?>" data-ajax="<?php echo site_url('ajax/page/terms'); ?>" title="Terms of Use" data-modal='modal-page'>Terms of Use</a> &amp;
            <a href="<?php echo site_url('policy'); ?>" data-ajax="<?php echo site_url('ajax/page/policy'); ?>" title="Privacy Policy" data-modal='modal-page'>Privacy Policy</a>
          </label>
          <input type="hidden" name="redirect" value="<?php echo isset($_GET['r'])?$_GET['r']:'search'; ?>">
          <input type="hidden" name="ajax" value="<?php echo site_url('ajax/register'); ?>">
          <button type="submit" class="pure-button pure-input-1">Register <i class="fa fa-arrow-right"></i></button>
        </div>
      </form>
    </section>
  </div>
</div>
<div class="pure-u-3-24"></div>