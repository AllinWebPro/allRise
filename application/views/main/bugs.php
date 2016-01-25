<div class="vertical-padding-small horizontal-padding-small">
  <div class="pure-u-1 bottom-padding-xsmall">
    <strong><a name="bug"><i class="fa fa-life-ring"></i></a> Report a Bug</strong>
  </div>
  <form class="pure-u-1 pure-form ajax" method="post" action="<?php echo site_url('bugs'); ?>" novalidate>
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