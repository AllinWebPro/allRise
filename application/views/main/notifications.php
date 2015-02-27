<nav id="sort" class="pure-menu pure-menu-open pure-menu-horizontal pure-u-1">
  <ul class="pure-g-r pure-u-1 center-text left-align">
    <li class="pure-u-1-6 left-align pure-menu-selected">
      <a title="Latest Notices" class="no-underline">Latest Notices</a></li>
  </ul>
</nav>
<div id="notices" class="pure-u-1 vertical-padding-small">
  <section id="notice-settings" class="small">
    <div class="horizontal-margin-small vertical-margin-small">
      <h3 class="horizontal-padding-xsmall">Settings</h3>
      <div class="vertical-padding-xsmall horizontal-padding-xsmall">
        <form class="pure-u-1 pure-form ajax-notice" method="post" action="<?php echo site_url('notifications/settings'); ?>">
          <div class="errors"></div>
          <div class="pure-g-r-x">
            <label class="pure-u-1-2 strong">Item Updates</label>
            <label for="edits-show" class="pure-u-1-4">
              <input type="radio" name="edits" id="edits-show" value="1" <?php echo ($user->edits)?'checked':''; ?>> Show
            </label>
            <label for="edits-hide" class="pure-u-1-4">
              <input type="radio" name="edits" id="edits-hide" value="0" <?php echo (!$user->edits)?'checked':''; ?>> Hide
            </label>
          </div>
          <div class="pure-g-r-x">
            <label class="pure-u-1-2 strong">Item Joins</label>
            <label for="parents-show" class="pure-u-1-4">
              <input type="radio" name="parents" id="parents-show" value="1" <?php echo ($user->parents)?'checked':''; ?>> Show
            </label>
            <label for="parents-hide" class="pure-u-1-4">
              <input type="radio" name="parents" id="parents-hide" value="0" <?php echo (!$user->parents)?'checked':''; ?>> Hide
            </label>
          </div>
          <div class="pure-g-r-x">
            <label class="pure-u-1-2 strong">Item Comments</label>
            <label for="comments-show" class="pure-u-1-4">
              <input type="radio" name="comments" id="comments-show" value="1" <?php echo ($user->comments)?'checked':''; ?>> Show
            </label>
            <label for="comments-hide" class="pure-u-1-4">
              <input type="radio" name="comments" id="comments-hide" value="0" <?php echo (!$user->comments)?'checked':''; ?>> Hide
            </label>
          </div>
          <fieldset>
            <input type="hidden" name="ajax" value="<?php echo site_url('ajax/notifications'); ?>">
            <button type="submit" class="pure-button pure-input-1"><i class="fa fa-search"></i> Update</button>
          </fieldset>
        </form>
      </div>
    </div>
  </section>
  <section id="notice-list" class="large">
    <div class="horizontal-margin-small vertical-margin-small">
      <h3 class="horizontal-padding-xsmall">Notices</h3>
      <div>
        <?php $this->load->view('main/notices'); ?>
        <?php if($pages > 1): ?>
          <?php $url = "search?s=".$sort."&".ltrim($uri."&pg=", '&'); ?>
          <div class="vertical-padding-small horizontal-padding-xsmall">
            <ul class="pure-paginator">
              <?php if($current > 1): ?>
                <li><a class="pure-button ajax prev" href="<?php echo site_url($url.($current-1)); ?>" data-type="list"><i class="fa fa-11-10 fa-arrow-circle-left"></i></a></li>
              <?php endif; ?>
              <?php for($p = 1; $p <= $pages; $p++): ?>
                <li><a class="pure-button ajax <?php echo ($p == $current)?'pure-button-active':''; ?>" href="<?php echo site_url($url.$p); ?>" data-type="list">
                  <?php echo $p; ?></a></li>
              <?php endfor; ?>
              <?php if($current < $pages): ?>
                <li><a class="pure-button ajax next" href="<?php echo site_url($url.($current+1)); ?>" data-type="list"><i class="fa fa-11-10 fa-arrow-circle-right"></i></a></li>
              <?php endif; ?>
            </ul>
          <div class="clear"></div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</div>