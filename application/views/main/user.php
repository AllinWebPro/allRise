<nav id="sort" class="pure-menu pure-menu-open pure-menu-horizontal pure-u-1">
  <ul class="pure-g-r pure-u-1 center-text left-align">
    <li class="pure-u-1-6 left-align pure-menu-selected">
      <a title="Latest Contributions" class="no-underline">User</a></li>
  </ul>
</nav>
<div id="list" class="pure-u-1 vertical-padding-small">
  <section id="user" class="small">
    <div class="horizontal-margin-small vertical-margin-small">
      <div class="horizontal-padding-small vertical-padding-small">
        <h2><span class="left-align"><?php echo $user->user; ?></span>
          <?php echo ($user->level == 'm')?'<i class="fa fa-leaf green right-align horizontal-margin-xsmall vertical-margin-tiny" title="Organizer"></i>':''; ?>
          <?php echo ($user->level == 'a')?'<i class="fa fa-pagelines fa-9-10 green right-align horizontal-margin-xsmall vertical-margin-tiny" title="Director"></i>':''; ?>
          <?php echo ($user->score > 0)?'<i class="fa fa-plus fa-9-10 blue right-align horizontal-margin-xsmall vertical-margin-tiny" title="Positive Contributor"></i>':''; ?>
          <?php echo ($user->score < 0)?'<i class="fa fa-minus red right-align horizontal-margin-xsmall vertical-margin-tiny" title="Negative Contributor"></i>':''; ?>
          <?php echo $user->confirmed?'<i class="fa fa-check orange right-align horizontal-margin-xsmall vertical-margin-tiny" title="Confirmed Account"></i>':''; ?>
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
  <?php
  $url = "?s=".$sort."&u=".$user->userId;
  if(isset($uri) && $uri) { $url = ltrim($uri, '&'); }
  $headline_url = $url."&r=headlines";
  $latest_url = "?s=createdOn&u=".$user->userId;
  ?>
  <section id="items-list" class="large">
    <div class="horizontal-margin-small vertical-margin-small bottom-margin">
      <div class="vertical-padding-xsmall">
        <div class="horizontal-padding-small bottom-padding-xsmall">
          <strong><a name="headlines"><div class="icon-box"><?php echo file_get_contents('media/svg/headline.svg'); ?></div></a> Submitted Headlines</strong>
          <a title="Search User Headlines" href="<?php echo site_url('search/'.$headline_url); ?>" class="pure-button pure-button-tiny right-align vertical-margin-tiny grey-light-bg ajax" data-type="list">
          See More <i class="fa fa-chevron-right fa-3-4"></i></a>
        </div>
        <?php foreach($headlines as $i): ?>
          <div class="height-42 h-overflow pos-rel vertical-padding-xsmall horizontal-padding-small">
            <?php if($i->image): ?>
              <img src="<?php echo $i->image; ?>" height="42" width="42" class="height-inherit left-align right-padding-xsmall">
            <?php else: ?>
              <img src="<?php echo site_url('media/img/no-image.gif'); ?>" height="42" width="42" class="left-align right-padding-xsmall">
            <?php endif; ?>
            <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>" class="ajax" data-type="item">
              <span class="item truncate">
                <div class="icon-box"><?php echo file_get_contents('media/svg/'.$i->type.'.svg'); ?></div>
                <span><?php echo stripslashes($i->headline); ?></span>
              </span>
            </a>
            <span class="grey space-left">
              <span class="right-padding-tiny"><i class="fa fa-3-4 fa-clock-o horizontal-padding-tiny"></i>
                <time>
                  <?php if($i->editedOn > strtotime(date("m/d/Y"))): ?>
                    Today <span class="pure-hidden-phone">at <?php echo date("h:ia", $i->editedOn); ?></span>
                  <?php elseif($i->editedOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
                    Yesterday <span class="pure-hidden-phone">at <?php echo date("h:ia", $i->editedOn); ?></span>
                  <?php elseif(date("Y", $i->editedOn) == date("Y")): ?>
                    <?php echo date("M d", $i->editedOn); ?> <span class="pure-hidden-phone">@ <?php echo date("h:ia", $i->editedOn); ?></span>
                  <?php else: ?>
                    <span class="pure-hidden-phone"><?php echo date("M d Y", $i->editedOn); ?></span>
                    <span class="pure-visible-phone"><?php echo date("M Y", $i->editedOn); ?></span>
                  <?php endif; ?>
                </time>
              </span>
              <?php if($i->c_count): ?>
                <span class="horizontal-padding-tiny">
                  <div class="icon-box small grey"><?php echo file_get_contents('media/svg/cluster.svg'); ?></div>
                  <?php echo $i->c_count; ?></span>
              <?php endif; ?>
              <?php if($i->h_count): ?>
                <span class="horizontal-padding-tiny">
                  <div class="icon-box small grey"><?php echo file_get_contents('media/svg/headline.svg'); ?></div>
                  <?php echo $i->h_count; ?></span>
              <?php endif; ?>
              <?php if($i->comments): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-comment"></i>
                  <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>#comments" class="ajax" data-type="item">
                    <?php echo $i->comments; ?></a></span>
              <?php endif; ?>
              <?php if($this->session->userdata('isLoggedIn') && in_array($this->session->userdata('level'), array('a'))): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-eye"></i> <?php echo round($i->x_score * 10, 2); ?></span>
                <!--<span class="horizontal-padding-tiny">
                  K: <?php echo round($i->search_score * 10, 2); ?>
                  C: <?php echo round($i->cred_score * 10, 2); ?>
                  S: <?php echo round($i->sub_score * 10, 2); ?>
                  Q: <?php echo round($i->decay_score * 10, 2); ?>
                </span>-->
                <span class="horizontal-padding-tiny">
                  <input type="checkbox" name="<?php echo $i->type; ?>[]" id="<?php echo $i->type; ?>-<?php echo $i->id; ?>" value="<?php echo $i->id; ?>">
                  <label class="pure-hidden-phone" for="<?php echo $i->type; ?>-<?php echo $i->id; ?>">Group</label>
                  <label class="pure-visible-phone" for="<?php echo $i->type; ?>-<?php echo $i->id; ?>"><i class="fa fa-link"></i></label>
                </span>
              <?php endif; ?>
            </span>
            <div class="clear"></div>
          </div>
        <?php endforeach; ?>
        <div class="h-overflow vertical-padding-tiny horizontal-padding-small">
          <a title="Search User Headlines"  href="<?php echo site_url('search/'.$headline_url); ?>" class="right-align">See More &gt;</a>
        </div>
      </div>
    </div>
    <div class="horizontal-margin-small vertical-margin-small bottom-margin">
      <div class="vertical-padding-xsmall">
        <div class="horizontal-padding-small bottom-padding-xsmall">
          <strong><a name="contributions"><i class="fa fa-edit"></i></a> Top Contributed Items</strong>
          <a title="Search User Contributions" href="<?php echo site_url('search/'.$url); ?>" class="pure-button pure-button-tiny right-align vertical-margin-tiny grey-light-bg ajax" data-type="list">
          See More <i class="fa fa-chevron-right fa-3-4"></i></a>
        </div>
        <?php foreach($contributions as $i): ?>
          <div class="height-42 h-overflow pos-rel vertical-padding-xsmall horizontal-padding-small">
            <?php if($i->image): ?>
              <img src="<?php echo $i->image; ?>" height="42" width="42" class="height-inherit left-align right-padding-xsmall">
            <?php else: ?>
              <img src="<?php echo site_url('media/img/no-image.gif'); ?>" height="42" width="42" class="left-align right-padding-xsmall">
            <?php endif; ?>
            <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>" class="ajax" data-type="item">
              <span class="item truncate">
                <div class="icon-box"><?php echo file_get_contents('media/svg/'.$i->type.'.svg'); ?></div>
                <span><?php echo stripslashes($i->headline); ?></span>
              </span>
            </a>
            <span class="grey space-left">
              <span class="right-padding-tiny"><i class="fa fa-3-4 fa-clock-o horizontal-padding-tiny"></i>
                <time>
                  <?php if($i->editedOn > strtotime(date("m/d/Y"))): ?>
                    Today <span class="pure-hidden-phone">at <?php echo date("h:ia", $i->editedOn); ?></span>
                  <?php elseif($i->editedOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
                    Yesterday <span class="pure-hidden-phone">at <?php echo date("h:ia", $i->editedOn); ?></span>
                  <?php elseif(date("Y", $i->editedOn) == date("Y")): ?>
                    <?php echo date("M d", $i->editedOn); ?> <span class="pure-hidden-phone">@ <?php echo date("h:ia", $i->editedOn); ?></span>
                  <?php else: ?>
                    <span class="pure-hidden-phone"><?php echo date("M d Y", $i->editedOn); ?></span>
                    <span class="pure-visible-phone"><?php echo date("M Y", $i->editedOn); ?></span>
                  <?php endif; ?>
                </time>
              </span>
              <?php if($i->c_count): ?>
                <span class="horizontal-padding-tiny">
                  <div class="icon-box small grey"><?php echo file_get_contents('media/svg/cluster.svg'); ?></div>
                  <?php echo $i->c_count; ?></span>
              <?php endif; ?>
              <?php if($i->h_count): ?>
                <span class="horizontal-padding-tiny">
                  <div class="icon-box small grey"><?php echo file_get_contents('media/svg/headline.svg'); ?></div>
                  <?php echo $i->h_count; ?></span>
              <?php endif; ?>
              <?php if($i->comments): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-comment"></i>
                  <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>#comments" class="ajax" data-type="item">
                    <?php echo $i->comments; ?></a></span>
              <?php endif; ?>
              <?php if($this->session->userdata('isLoggedIn') && in_array($this->session->userdata('level'), array('a'))): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-eye"></i> <?php echo round($i->x_score * 10, 2); ?></span>
                <!--<span class="horizontal-padding-tiny">
                  K: <?php echo round($i->search_score * 10, 2); ?>
                  C: <?php echo round($i->cred_score * 10, 2); ?>
                  S: <?php echo round($i->sub_score * 10, 2); ?>
                  Q: <?php echo round($i->decay_score * 10, 2); ?>
                </span>-->
                <span class="horizontal-padding-tiny">
                  <input type="checkbox" name="<?php echo $i->type; ?>[]" id="<?php echo $i->type; ?>-<?php echo $i->id; ?>" value="<?php echo $i->id; ?>">
                  <label class="pure-hidden-phone" for="<?php echo $i->type; ?>-<?php echo $i->id; ?>">Group</label>
                  <label class="pure-visible-phone" for="<?php echo $i->type; ?>-<?php echo $i->id; ?>"><i class="fa fa-link"></i></label>
                </span>
              <?php endif; ?>
            </span>
            <div class="clear"></div>
          </div>
        <?php endforeach; ?>
        <div class="h-overflow vertical-padding-tiny horizontal-padding-small">
          <a title="Search User Contributions"  href="<?php echo site_url('search/'.$url); ?>" class="right-align">See More &gt;</a>
        </div>
      </div>
    </div>
    <div class="horizontal-margin-small vertical-margin-small">
      <div class="vertical-padding-xsmall">
        <div class="horizontal-padding-small bottom-padding-xsmall">
          <strong><a name="latest"><i class="fa fa-clock-o"></i></a> Recently Contributed Items</strong>
          <a title="Search User Latest" href="<?php echo site_url('search/'.$latest_url); ?>" class="pure-button pure-button-tiny right-align vertical-margin-tiny grey-light-bg ajax" data-type="list">
          See More <i class="fa fa-chevron-right fa-3-4"></i></a>
        </div>
        <?php foreach($recent as $i): ?>
          <div class="height-42 h-overflow pos-rel vertical-padding-xsmall horizontal-padding-small">
            <?php if($i->image): ?>
              <img src="<?php echo $i->image; ?>" height="42" width="42" class="height-inherit left-align right-padding-xsmall">
            <?php else: ?>
              <img src="<?php echo site_url('media/img/no-image.gif'); ?>" height="42" width="42" class="left-align right-padding-xsmall">
            <?php endif; ?>
            <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>" class="ajax" data-type="item">
              <span class="item truncate">
                <div class="icon-box"><?php echo file_get_contents('media/svg/'.$i->type.'.svg'); ?></div>
                <span><?php echo stripslashes($i->headline); ?></span>
              </span>
            </a>
            <span class="grey space-left">
              <span class="right-padding-tiny"><i class="fa fa-3-4 fa-clock-o horizontal-padding-tiny"></i>
                <time>
                  <?php if($i->editedOn > strtotime(date("m/d/Y"))): ?>
                    Today <span class="pure-hidden-phone">at <?php echo date("h:ia", $i->editedOn); ?></span>
                  <?php elseif($i->editedOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
                    Yesterday <span class="pure-hidden-phone">at <?php echo date("h:ia", $i->editedOn); ?></span>
                  <?php elseif(date("Y", $i->editedOn) == date("Y")): ?>
                    <?php echo date("M d", $i->editedOn); ?> <span class="pure-hidden-phone">@ <?php echo date("h:ia", $i->editedOn); ?></span>
                  <?php else: ?>
                    <span class="pure-hidden-phone"><?php echo date("M d Y", $i->editedOn); ?></span>
                    <span class="pure-visible-phone"><?php echo date("M Y", $i->editedOn); ?></span>
                  <?php endif; ?>
                </time>
              </span>
              <?php if($i->c_count): ?>
                <span class="horizontal-padding-tiny">
                  <div class="icon-box small grey"><?php echo file_get_contents('media/svg/cluster.svg'); ?></div>
                  <?php echo $i->c_count; ?></span>
              <?php endif; ?>
              <?php if($i->h_count): ?>
                <span class="horizontal-padding-tiny">
                  <div class="icon-box small grey"><?php echo file_get_contents('media/svg/headline.svg'); ?></div>
                  <?php echo $i->h_count; ?></span>
              <?php endif; ?>
              <?php if($i->comments): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-comment"></i>
                  <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>#comments" class="ajax" data-type="item">
                    <?php echo $i->comments; ?></a></span>
              <?php endif; ?>
              <?php if($this->session->userdata('isLoggedIn') && in_array($this->session->userdata('level'), array('a'))): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-eye"></i> <?php echo round($i->x_score * 10, 2); ?></span>
                <!--<span class="horizontal-padding-tiny">
                  K: <?php echo round($i->search_score * 10, 2); ?>
                  C: <?php echo round($i->cred_score * 10, 2); ?>
                  S: <?php echo round($i->sub_score * 10, 2); ?>
                  Q: <?php echo round($i->decay_score * 10, 2); ?>
                </span>-->
                <span class="horizontal-padding-tiny">
                  <input type="checkbox" name="<?php echo $i->type; ?>[]" id="<?php echo $i->type; ?>-<?php echo $i->id; ?>" value="<?php echo $i->id; ?>">
                  <label class="pure-hidden-phone" for="<?php echo $i->type; ?>-<?php echo $i->id; ?>">Group</label>
                  <label class="pure-visible-phone" for="<?php echo $i->type; ?>-<?php echo $i->id; ?>"><i class="fa fa-link"></i></label>
                </span>
              <?php endif; ?>
            </span>
            <div class="clear"></div>
          </div>
        <?php endforeach; ?>
        <div class="h-overflow vertical-padding-tiny horizontal-padding-small">
          <a title="Search User Latest"  href="<?php echo site_url('search/'.$latest_url); ?>" class="right-align">See More &gt;</a>
        </div>
      </div>
    </div>
  </section>
</div>
<div class="modal-content" id="stream-list" title="Stream List"></div>