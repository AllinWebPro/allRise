<h3 class="horizontal-padding-xsmall">Contact Us</h3>
<div class="vertical-padding-xsmall horizontal-padding-xsmall">
  <form class="pure-u-1 pure-form ajax" method="post" action="<?php echo site_url('contact'); ?>" novalidate>
    <fieldset class="pure-group">
      <div class="errors"></div>
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