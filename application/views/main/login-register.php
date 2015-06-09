<?php $video_url = 'https://www.youtube.com/embed/mBBqb97IQMo'; ?>
<aside class="pure-hidden-phone pure-u-7-12 vertical-padding-xlarge">
  <div class="horizontal-padding-xlarge vertical-padding-xlarge">
    <p class="intro-text vertical-padding-xlarge">Collaborative News for Everyone.<br><br>allRise helps communities large and small connect in new ways to share real-time important information and easily build it into high-quality news articles.  It's democratic news by and for everyone.  Our goal is to become the most valuable source of real-time news in the world.</p>
    <div class="videoWrapper">
      <!-- Copy & Pasted from YouTube -->
      <iframe width="560" height="315" src="<?php echo $video_url; ?>" frameborder="0" allowfullscreen></iframe>
    </div>
  </div>
</aside>
<div class="pure-u-7-24 bottom-padding">
  <div class="horizontal-padding-huge vertical-padding-xlarge">
    <div class="videoWrapper pure-visible-phone">
      <!-- Copy & Pasted from YouTube -->
      <iframe width="560" height="315" src="<?php echo $video_url; ?>" frameborder="0" allowfullscreen></iframe>
    </div>
    <div class="margin-top pure-visible-phone">&nbsp;</div>
    <section id="login" class="pure-g vertical-padding-xlarge">
      <form class="pure-u-1 pure-form ajax" method="post" action="<?php echo site_url('login'); ?>" novalidate>
        <fieldset class="pure-group">
          <legend>Login</legend>
          <div class="errors">
            <?php if(isset($login_error)): ?>
              <p><?php echo $login_error; ?></p>
            <?php endif; ?>
            <?php if(isset($login_errors)): ?>
              <?php if(is_string($login_errors)): ?>
                <p><?php echo $login_errors; ?></p>
              <?php else: ?>
                <?php foreach($login_errors as $key => $val): ?>
                  <p><?php echo $val; ?></p>
                <?php endforeach; ?>
              <?php endif; ?>
            <?php endif; ?>
          </div>
          <input type="text" name="login" value="<?php echo set_value('login', $this->input->post('login')); ?>" id="login-login" class="pure-input-1" placeholder="Username / Email" required>
          <input type="password" name="password" value="<?php echo set_value('password', $this->input->post('password')); ?>" id="login-password" class="pure-input-1" placeholder="Password" required>
        </fieldset>
        <div class="pure-controls">
          <input type="hidden" name="redirect" value="<?php echo isset($_GET['r'])?$_GET['r']:'search'; ?>">
          <input type="hidden" name="ajax" value="<?php echo site_url('ajax/login'); ?>">
          <button type="submit" class="pure-button pure-input-1">Login <i class="fa fa-sign-in"></i></button>
        </div>
      </form>
    </section>
    <div class="margin-top pure-visible-phone">&nbsp;</div>
    <section id="register" class="pure-g vertical-padding-xlarge">
      <form class="pure-u-1 pure-form ajax" method="post" action="<?php echo site_url('register'); ?>" autocomplete="off" novalidate>
        <fieldset class="pure-group">
          <legend>Register</legend>
          <div class="errors">
            <?php if(isset($register_error)): ?>
              <p><?php echo $register_error; ?></p>
            <?php endif; ?>
            <?php if(isset($register_errors)): ?>
              <?php if(is_string($register_errors)): ?>
                <p><?php echo $register_errors; ?></p>
              <?php else: ?>
                <?php foreach($register_errors as $key => $val): ?>
                  <p><?php echo $val; ?></p>
                <?php endforeach; ?>
              <?php endif; ?>
            <?php endif; ?>
          </div>
          <input type="text" name="user" value="<?php echo set_value('user', $this->input->post('user')); ?>" id="register-user" class="pure-input-1" placeholder="Username" required autocomplete="off">
          <input type="text" name="email" value="<?php echo set_value('email', $this->input->post('email')); ?>" id="register-email" class="pure-input-1" placeholder="Email" required autocomplete="off">
          <input type="password" name="rgpassword" value="<?php echo set_value('rgpassword', $this->input->post('rgpassword')); ?>" id="register-password" class="pure-input-1" placeholder="Password" required autocomplete="off">
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
<div class="pure-hidden-phone pure-u-3-24"></div>