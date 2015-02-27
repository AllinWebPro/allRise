<nav id="sort" class="pure-menu pure-menu-open pure-menu-horizontal pure-u-1">
  <ul class="pure-g-r pure-u-1 center-text left-align">
    <li class="pure-u-1-6 left-align pure-menu-selected">
      <a title="Latest Contributions" class="no-underline">User</a></li>
  </ul>
</nav>
<div id="stream" class="pure-u-1 vertical-padding-small">
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
  <?php
  $url = "?s=".$sort."&u=".$user->userId;
  if(isset($uri) && $uri) { $url = ltrim($uri, '&'); }
  $headline_url = $url."&r=headlines";
  $latest_url = "?s=createdOn&u=".$user->userId;
  ?>
  <section id="headlines" class="items">
    <div class="horizontal-margin-small vertical-margin-small">
      <h3 class="horizontal-padding-xsmall">
        <a title="Search User Headlines"  href="<?php echo site_url('search/'.$headline_url); ?>" class="white ajax" data-type="list">Headlines</a>
        <a title="Search User Headlines" href="<?php echo site_url('search/'.$headline_url); ?>" class="pure-button pure-button-tiny right-align vertical-margin-tiny grey-light-bg ajax" data-type="list">
          See More <i class="fa fa-chevron-right fa-3-4"></i></a>
      </h3>
      <div class="vertical-padding-xsmall">
        <?php foreach($headlines as $i): ?>
          <div class="vertical-padding-small horizontal-padding-xsmall">
            <?php if($i->image): ?>
              <img src="<?php echo $i->image; ?>" height="42" width="42" class="left-align right-padding-xsmall">
            <?php endif; ?>
            <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>" class="ajax" data-type="item">
              <span class="item">
                <i class="fa fa-<?php echo ($i->type == 'headline')?'dot-circle-o':(($i->type == 'cluster')?'code-fork':'caret-square-o-up'); ?>"></i>
                <span><?php echo stripslashes($i->headline); ?></span>
              </span>
            </a>
            <span class="grey">
              <span class="right-padding-tiny"><i class="fa fa-3-4 fa-clock-o horizontal-padding-tiny"></i>
                <time>
                  <?php if($i->editedOn > strtotime(date("m/d/Y"))): ?>
                    Today at <?php echo date("h:ia", $i->editedOn); ?>
                  <?php elseif($i->editedOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
                    Yesterday at <?php echo date("h:ia", $i->editedOn); ?>
                  <?php elseif(date("Y", $i->editedOn) == date("Y")): ?>
                    <?php echo date("M d", $i->editedOn); ?> @ <?php echo date("h:ia", $i->editedOn); ?>
                  <?php else: ?>
                    <?php echo date("M d Y", $i->editedOn); ?>
                  <?php endif; ?>
                </time>
              </span>
              <?php if($i->c_count): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-code-fork"></i>
                  <?php echo $i->c_count; ?></span>
              <?php endif; ?>
              <?php if($i->h_count): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-dot-circle-o"></i>
                  <?php echo $i->h_count; ?></span>
              <?php endif; ?>
              <?php if($i->comments): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-comment"></i>
                  <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>#comments" class="ajax" data-type="item">
                    <?php echo $i->comments; ?></a></span>
              <?php endif; ?>
              <?php if($this->session->userdata('isLoggedIn') && in_array($this->session->userdata('level'), array('m', 'a'))): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-eye"></i> <?php echo round($i->x_score * 10, 2); ?></span>
              <?php endif; ?>
            </span><!--<br>
            <span>
              K: <?php echo $i->search_score; ?>
              C: <?php echo $i->cred_score; ?>
              S: <?php echo $i->sub_score; ?>
              Q: <?php echo $i->decay_score; ?>
            </span>-->
          </div>
        <?php endforeach; ?>
        <div class="vertical-padding-xsmall horizontal-padding-xsmall">
          <a title="Search User Headlines"  href="<?php echo site_url('search/'.$headline_url); ?>" class="right-align">See More &gt;</a>
        </div>
      </div>
    </div>
  </section>
  <section id="contributions" class="items">
    <div class="horizontal-margin-small vertical-margin-small">
      <h3 class="horizontal-padding-xsmall">
        <a title="Search User Contributions"  href="<?php echo site_url('search/'.$url); ?>" class="white ajax" data-type="list">Contributions</a>
        <a title="Search User Contributions" href="<?php echo site_url('search/'.$url); ?>" class="pure-button pure-button-tiny right-align vertical-margin-tiny grey-light-bg ajax" data-type="list">
          See More <i class="fa fa-chevron-right fa-3-4"></i></a>
      </h3>
      <div class="vertical-padding-xsmall">
        <?php foreach($contributions as $i): ?>
          <div class="vertical-padding-small horizontal-padding-xsmall">
            <?php if($i->image): ?>
              <img src="<?php echo $i->image; ?>" height="42" width="42" class="left-align right-padding-xsmall">
            <?php endif; ?>
            <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>" class="ajax" data-type="item">
              <span class="item">
                <i class="fa fa-<?php echo ($i->type == 'headline')?'dot-circle-o':(($i->type == 'cluster')?'code-fork':'caret-square-o-up'); ?>"></i>
                <span><?php echo stripslashes($i->headline); ?></span>
              </span>
            </a>
            <span class="grey">
              <span class="right-padding-tiny"><i class="fa fa-3-4 fa-clock-o horizontal-padding-tiny"></i>
                <time>
                  <?php if($i->editedOn > strtotime(date("m/d/Y"))): ?>
                    Today at <?php echo date("h:ia", $i->editedOn); ?>
                  <?php elseif($i->editedOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
                    Yesterday at <?php echo date("h:ia", $i->editedOn); ?>
                  <?php elseif(date("Y", $i->editedOn) == date("Y")): ?>
                    <?php echo date("M d", $i->editedOn); ?> @ <?php echo date("h:ia", $i->editedOn); ?>
                  <?php else: ?>
                    <?php echo date("M d Y", $i->editedOn); ?>
                  <?php endif; ?>
                </time>
              </span>
              <?php if($i->c_count): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-code-fork"></i>
                  <?php echo $i->c_count; ?></span>
              <?php endif; ?>
              <?php if($i->h_count): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-dot-circle-o"></i>
                  <?php echo $i->h_count; ?></span>
              <?php endif; ?>
              <?php if($i->comments): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-comment"></i>
                  <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>#comments" class="ajax" data-type="item">
                    <?php echo $i->comments; ?></a></span>
              <?php endif; ?>
              <?php if($this->session->userdata('isLoggedIn') && in_array($this->session->userdata('level'), array('m', 'a'))): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-eye"></i> <?php echo round($i->x_score * 10, 2); ?></span>
              <?php endif; ?>
            </span><!--<br>
            <span>
              K: <?php echo $i->search_score; ?>
              C: <?php echo $i->cred_score; ?>
              S: <?php echo $i->sub_score; ?>
              Q: <?php echo $i->decay_score; ?>
            </span>-->
          </div>
        <?php endforeach; ?>
        <div class="vertical-padding-xsmall horizontal-padding-xsmall">
          <a title="Search User Contributions"  href="<?php echo site_url('search/'.$url); ?>" class="right-align">See More &gt;</a>
        </div>
      </div>
    </div>
  </section>
  <section id="latest" class="items">
    <div class="horizontal-margin-small vertical-margin-small">
      <h3 class="horizontal-padding-xsmall">
        <a title="Search User Latest"  href="<?php echo site_url('search/'.$latest_url); ?>" class="white ajax" data-type="list">Latest</a>
        <a title="Search User Latest" href="<?php echo site_url('search/'.$latest_url); ?>" class="pure-button pure-button-tiny right-align vertical-margin-tiny grey-light-bg ajax" data-type="list">
          See More <i class="fa fa-chevron-right fa-3-4"></i></a>
      </h3>
      <div class="vertical-padding-xsmall">
        <?php foreach($recent as $i): ?>
          <div class="vertical-padding-small horizontal-padding-xsmall">
            <?php if($i->image): ?>
              <img src="<?php echo $i->image; ?>" height="42" width="42" class="left-align right-padding-xsmall">
            <?php endif; ?>
            <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>" class="ajax" data-type="item">
              <span class="item">
                <i class="fa fa-<?php echo ($i->type == 'headline')?'dot-circle-o':(($i->type == 'cluster')?'code-fork':'caret-square-o-up'); ?>"></i>
                <span><?php echo stripslashes($i->headline); ?></span>
              </span>
            </a>
            <span class="grey">
              <span class="right-padding-tiny"><i class="fa fa-3-4 fa-clock-o horizontal-padding-tiny"></i>
                <time>
                  <?php if($i->editedOn > strtotime(date("m/d/Y"))): ?>
                    Today at <?php echo date("h:ia", $i->editedOn); ?>
                  <?php elseif($i->editedOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
                    Yesterday at <?php echo date("h:ia", $i->editedOn); ?>
                  <?php elseif(date("Y", $i->editedOn) == date("Y")): ?>
                    <?php echo date("M d", $i->editedOn); ?> @ <?php echo date("h:ia", $i->editedOn); ?>
                  <?php else: ?>
                    <?php echo date("M d Y", $i->editedOn); ?>
                  <?php endif; ?>
                </time>
              </span>
              <?php if($i->c_count): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-code-fork"></i>
                  <?php echo $i->c_count; ?></span>
              <?php endif; ?>
              <?php if($i->h_count): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-dot-circle-o"></i>
                  <?php echo $i->h_count; ?></span>
              <?php endif; ?>
              <?php if($i->comments): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-comment"></i>
                  <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>#comments" class="ajax" data-type="item">
                    <?php echo $i->comments; ?></a></span>
              <?php endif; ?>
              <?php if($this->session->userdata('isLoggedIn') && in_array($this->session->userdata('level'), array('m', 'a'))): ?>
                <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-eye"></i> <?php echo round($i->x_score * 10, 2); ?></span>
              <?php endif; ?>
            </span><!--<br>
            <span>
              K: <?php echo $i->search_score; ?>
              C: <?php echo $i->cred_score; ?>
              S: <?php echo $i->sub_score; ?>
              Q: <?php echo $i->decay_score; ?>
            </span>-->
          </div>
        <?php endforeach; ?>
        <div class="vertical-padding-xsmall horizontal-padding-xsmall">
          <a title="Search User Latest"  href="<?php echo site_url('search/'.$latest_url); ?>" class="right-align">See More &gt;</a>
        </div>
      </div>
    </div>
  </section>
</div>
<div class="modal-content" id="stream-list" title="Stream List"></div>