<h3 class="horizontal-padding-xsmall">Report Bug</h3>
<div class="vertical-padding-xsmall horizontal-padding-xsmall">
  <form class="pure-u-1 pure-form ajax" method="post" action="<?php echo site_url('bugs'); ?>" novalidate>
    <fieldset class="pure-group">
      <div class="errors"></div>
      <input type="text" name="url" id="contact-url" class="pure-input-1" placeholder="Bug URL" required>
      <textarea name="error" id="contact-error" class="pure-input-1" placeholder="Error" required></textarea>
      <textarea name="comments" id="contact-comments" class="pure-input-1" placeholder="Comments" required></textarea>
    </fieldset>
    <div class="pure-controls">
      <input type="hidden" name="ajax" value="<?php echo site_url('ajax/bugs'); ?>">
      <button type="submit" class="pure-button pure-input-1"><i class="fa fa-envelope"></i> Send Email</button>
    </div>
  </form>
</div>