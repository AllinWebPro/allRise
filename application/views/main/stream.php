<nav id="sort" class="pure-menu pure-menu-open pure-menu-horizontal pure-u-1">
  <ul class="pure-g-r pure-u-1 center-text left-align">
    <?php if(!isset($user)): ?>
      <li class="pure-u-1-6 left-align <?php echo ($sort == 'score')?'pure-menu-selected':''; ?>">
        <a title="Top Stories" href="<?php echo site_url('stream?s=score'.(isset($uri)?$uri:'')); ?>" class="ajax" data-type="stream">Top Stories</a></li>
      <li class="pure-u-1-6 left-align <?php echo ($sort == 'views')?'pure-menu-selected':''; ?>">
        <a title="Most Viewed" href="<?php echo site_url('stream?s=views'.(isset($uri)?$uri:'')); ?>" class="ajax" data-type="stream">Most Viewed</a></li>
      <li class="pure-u-1-6 left-align <?php echo ($sort == 'createdOn')?'pure-menu-selected':''; ?>">
        <a title="Latest Entries" href="<?php echo site_url('stream?s=createdOn'.(isset($uri)?$uri:'')); ?>" class="ajax" data-type="stream">Latest Entries</a></li>
    <?php else: ?>
      <li class="pure-u-1-6 left-align pure-menu-selected">
        <a title="Latest Contributions" class="no-underline">Latest Contributions</a></li>
    <?php endif; ?>
  </ul>
</nav>
<div id="stream" class="pure-u-1 vertical-padding-small">
  <?php if(isset($user)): ?>
    <section id="user" class="items">
      <div class="horizontal-margin-small vertical-margin-small">
        <div class="horizontal-padding-xsmall vertical-padding-small">
          <h2><span class="left-align"><?php echo $user->user; ?></span>
            <?php echo ($user->level == 'm')?'<i class="fa fa-leaf green right-align horizontal-margin-xsmall" title="Organizer"></i>':''; ?>
            <?php echo ($user->level == 'a')?'<i class="fa fa-pagelines fa-9-10 green right-align horizontal-margin-xsmall" title="Director"></i>':''; ?>
            <?php echo ($user->score > 0)?'<i class="fa fa-plus fa-9-10 blue right-align horizontal-margin-xsmall" title="Positive Contributor"></i>':''; ?>
            <?php echo ($user->score < 0)?'<i class="fa fa-minus red right-align horizontal-margin-xsmall" title="Negative Contributor"></i>':''; ?>
            <?php echo $user->confirmed?'<i class="fa fa-check orange right-align horizontal-margin-xsmall" title="Confirmed Account"></i>':''; ?>
          </h2>
          <div class="clear"></div>
          <div class="photo-frame horizontal-padding-xsmall vertical-padding-small left-align">
            <?php if($user->photo): ?>
              <img src="<?php echo site_url('uploads/users/'.$user->photo); ?>" alt="<?php echo $user->user; ?>" width="300" class="profile-photo">
            <?php else: ?>
              <img src="<?php echo site_url('media/img/no-image.gif'); ?>" alt="<?php echo $user->user; ?>" width="300" class="profile-photo">
            <?php endif; ?>
          </div>
          <div class="meta-frame horizontal-padding-xsmall vertical-padding-small right-align">
            <?php if($user->location): ?>
              <div class="bottom-margin">
                <em>Location</em><br>
                <?php echo stripslashes($user->location); ?>
              </div>
            <?php endif; ?>
              <div class="bottom-margin">
                <em>Last Login</em><br>
                <?php echo date("m/d/Y", $user->lastLogin); ?>
              </div>
              <div>
                <em>Member Since</em><br>
                <?php echo date("m/d/Y", $user->createdOn); ?>
              </div>
          </div>
          <div class="clear"></div>
          <?php if($user->bio): ?>
            <div class="horizontal-padding-xsmall bottom-margin">
              <em>Biography</em><br>
              <?php echo stripslashes($user->bio); ?>
            </div>
          <?php endif; ?>
          <table class="pure-table pure-table-bordered">
            <thead>
              <tr>
                <th>Created</th>
                <th>Edited</th>
                <th>Browsed</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?php echo $created; ?></td>
                <td><?php echo $edited-$created; ?></td>
                <td><?php echo $views; ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  <?php endif; ?>
  <?php foreach($scores as $key => $val): ?>
    <?php
    $url = "?s=".$sort;
    if(isset($uri) && $uri) { $url = ltrim($uri, '&'); }
    if(isset($user)) { $url .= "&u=".$user->userId; }
    ?>
    <section id="<?php echo ($key == 'all')?'all':$s_categories['slug'][$key]; ?>" class="items">
      <div class="horizontal-margin-small vertical-margin-small">
        <h3 class="horizontal-padding-xsmall">
          <a title="Search <?php echo ($key == 'all')?'':'['.$s_categories['category'][$key].']'; ?>"  href="<?php echo site_url('search/'.$url.(($key !== 'all')?("&c[]=".$s_categories['slug'][$key]):'')); ?>" class="white ajax" data-type="list">
            <?php echo ($key == 'all')?'Everything':$s_categories['category'][$key]; ?>
          </a>
          <a title="Search <?php echo ($key == 'all')?'':'['.$s_categories['category'][$key].']'; ?>" href="<?php echo site_url('search/'.$url.(($key !== 'all')?("&c[]=".$s_categories['slug'][$key]):'')); ?>" class="pure-button pure-button-tiny right-align vertical-margin-tiny grey-light-bg ajax" data-type="list">
            See More <i class="fa fa-chevron-right fa-3-4"></i></a>
        </h3>
        <div class="vertical-padding-xsmall">
          <?php foreach($items[$key] as $i): ?>
            <div class="item vertical-padding-small horizontal-padding-xsmall">
              <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>" class="ajax" data-type="item">
                <span class="item">
                  <div class="icon-box"><?php echo file_get_contents('media/svg/'.$i->type.'.svg'); ?></div>
                  <span><?php echo stripslashes($i->headline); ?></span>
                </span>
              </a>
              <span class="grey">
                <span class="right-padding-tiny"><i class="fa fa-3-4 fa-clock-o"></i>
                  <time>
                    <?php if($i->editedOn > strtotime(date("m/d/Y"))): ?>
                      Today
                    <?php elseif($i->editedOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
                      Yesterday
                    <?php elseif(date("Y", $i->editedOn) == date("Y")): ?>
                      <?php echo date("M d", $i->editedOn); ?>
                    <?php else: ?>
                      <?php echo date("M Y", $i->editedOn); ?>
                    <?php endif; ?>
                  </time>
                </span>
                <?php if($i->place): ?>
                  <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-globe"></i>
                    <a href="<?php echo site_url('search?p='.urlencode(stripslashes($i->place))); ?>"><?php echo stripslashes($i->place); ?></a></span>
                <?php endif; ?>
                <?php if(!isset($user)): ?>
                  <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-eye"></i> <?php echo round($i->score * 10, 2); ?></span>
                <?php endif; ?>
                <?php if($i->comments): ?>
                  <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-comment"></i>
                    <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>#comments" class="ajax" data-type="item">
                      <?php echo $i->comments; ?></a>
                  </span>
                <?php endif; ?>
              </span>
            </div>
          <?php endforeach; ?>
          <div class="vertical-padding-xsmall horizontal-padding-xsmall">
            <a title="Search <?php echo ($key == 'all')?'':'['.$s_categories['category'][$key].']'; ?>"  href="<?php echo site_url('search/'.$url.(($key !== 'all')?("&c[]=".$s_categories['slug'][$key]):'')); ?>" class="right-align">See More &gt;</a>
          </div>
        </div>
      </div>
    </section>
  <?php endforeach; ?>
</div>
<div class="modal-content" id="stream-list" title="Stream List"></div>