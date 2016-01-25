<div id="admin" class="pure-u-1 vertical-padding-small">
  <section id="admin-users" class="xlarge">
    <div class="horizontal-margin-small vertical-margin-small">
      <div id="users" class="vertical-padding-small horizontal-padding-xsmall">
        <?php $i = 1; foreach($users as $u): ?>
          <div class="pure-g-r">
            <div class="phone-u-1-2 pure-u-1-6">
              <div class="horizontal-padding-xsmall vertical-padding-tiny truncate">
                <strong class="display-b <?php echo ($i > 1)?'pure-visible-phone':''; ?>">User</strong>
                <span><?php echo $u->user; ?></span>
              </div>
            </div>
            <div class="phone-u-1-2 pure-u-1-6">
              <div class="horizontal-padding-xsmall vertical-padding-tiny truncate">
                <strong class="display-b <?php echo ($i > 1)?'pure-visible-phone':''; ?>">Email</strong>
                <span><?php echo $u->email; ?></span>
              </div>
            </div>
            <div class="phone-u-1-2 pure-u-1-6">
              <div class="horizontal-padding-xsmall vertical-padding-tiny truncate">
                <strong class="display-b <?php echo ($i > 1)?'pure-visible-phone':''; ?>">Location</strong>
                <span><?php echo $u->location; ?></span>
              </div>
            </div>
            <div class="phone-u-1-2 pure-u-1-6">
              <div class="horizontal-padding-xsmall vertical-padding-tiny truncate">
                <strong class="display-b <?php echo ($i > 1)?'pure-visible-phone':''; ?>">Level</strong>
                <span><?php echo $levels[$u->level]; ?></span>
              </div>
            </div>
            <div class="phone-u-1-2 pure-u-1-6">
              <div class="horizontal-padding-xsmall vertical-padding-tiny truncate">
                <strong class="display-b <?php echo ($i > 1)?'pure-visible-phone':''; ?>">Last Login</strong>
                <span><?php echo date("m/d/Y", $u->lastLogin); ?></span>
              </div>
            </div>
            <div class="phone-u-1-2 pure-u-1-6">
              <div class="horizontal-padding-xsmall vertical-padding-tiny truncate">
                <strong class="display-b <?php echo ($i > 1)?'pure-visible-phone':''; ?>">Options</strong>
                <span><a class="delete" href="<?php echo site_url("admin/users/delete/".$u->userId."/".$current); ?>">
                    <i class="fa fa-trash-o fa-9-10"></i> Destroy</a></span>
              </div>
            </div>
            <div class="clear"></div>
          </div>
        <?php $i++; endforeach; ?>
      </div>
      <?php if($pages > 1): ?>
        <?php $url = "admin/users?pg="; ?>
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
  </section>
</div>