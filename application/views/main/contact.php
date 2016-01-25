<div class="vertical-padding-small horizontal-padding-small">
  <div class="pure-u-1 bottom-padding-xsmall">
    <strong><a name="contact"><i class="fa fa-life-ring"></i></a> Contact Us</strong>
  </div>
  <form class="pure-u-1 pure-form ajax" method="post" action="<?php echo site_url('contact'); ?>" novalidate>
    <fieldset class="pure-group">
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
      <input type="text" name="name" id="contact-name" class="pure-input-1" placeholder="Name" required>
      <input type="text" name="email" id="contact-email" class="pure-input-1" placeholder="Email" required>
      <input type="text" name="subject" id="contact-subject" class="pure-input-1" placeholder="Subject" required>
      <textarea name="message" id="contact-message" class="pure-input-1" placeholder="Message" required></textarea>
    </fieldset>
    <div class="pure-controls">
      <input type="hidden" name="ajax" value="<?php echo site_url('ajax/contact'); ?>">
      <button type="submit" class="pure-button pure-input-1"><i class="fa fa-envelope"></i> Send Email</button>
    </div>
  </form>
</div>