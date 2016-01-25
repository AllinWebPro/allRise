<!doctype html>
<html>
  <body>
    <form method="post" enctype='multipart/form-data'>
      <?php if($user->photo): ?>
        <img src="<?php echo site_url('uploads/users/'.$user->photo); ?>" alt="<?php echo $user->user; ?>" height="42" style="float:left;margin-right:10px;">
      <?php else: ?>
        <img src="<?php echo site_url('media/img/no-image.gif'); ?>" alt="<?php echo $user->user; ?>" height="42" style="float:left;margin-right:10px;">
      <?php endif; ?>
      <?php if($output): ?>
        <label><?php echo $output; ?></label><br>
      <?php endif; ?>
      <input type="file" id="prefrences-photo" name="photo" placeholder="Test">
    </form>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="<?php echo site_url('media/js/vendor/jquery-1.10.2.min.js'); ?>"><\/script>')</script>
    <script>
    $(document).ready(function() {
      $("input").change(function() {
        $("form").submit();
      });
    });
    </script>
  </body>
</html>