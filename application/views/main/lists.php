<nav id="sort" class="pure-menu pure-menu-open pure-menu-horizontal pure-u-1">
  <ul class="pure-g-r pure-u-1 center-text left-align">
    <li class="pure-u-1-6 left-align <?php echo ($sort == 'score' && !$subscription)?'pure-menu-selected':''; ?>">
      <a title="Top Stories" href="<?php echo site_url('search?s=score'.(isset($uri)?$uri:'')); ?>">Top Stories</a></li>
    <li class="pure-u-1-6 left-align <?php echo ($sort == 'views' && !$subscription)?'pure-menu-selected':''; ?>">
      <a title="Most Viewed" href="<?php echo site_url('search?s=views'.(isset($uri)?$uri:'')); ?>">Most Viewed</a></li>
    <li class="pure-u-1-6 left-align <?php echo ($sort == 'createdOn' && !$subscription)?'pure-menu-selected':''; ?>">
      <a title="Latest Entries" href="<?php echo site_url('search?s=createdOn'.(isset($uri)?$uri:'')); ?>">Latest Entries</a></li>
    <?php if($this->session->userdata('isLoggedIn')): ?>
      <li class="pure-u-1-6 left-align <?php echo ($subscription)?'pure-menu-selected':''; ?>">
        <a title="Latest Entries" href="<?php echo site_url('search?b=1&s=editedOn'.(isset($uri)?$uri:'')); ?>">Subscriptions</a></li>
    <?php endif; ?>
  </ul>
</nav>
<div id="list" class="pure-u-1 vertical-padding-small">
  <section id="search-filters" class="small">
    <div class="horizontal-margin-small vertical-margin-small">
      <div class="vertical-padding-xsmall horizontal-padding-small">
        <div class="bottom-padding-xsmall">
          <strong><a name="search"><i class="fa fa-search"></i></a> Advanced Search</strong>
        </div>
        <form class="pure-u-1 pure-form ajax-list" method="get" action="<?php echo site_url('search'); ?>">
          <fieldset class="item pure-group pure-u-1 vertical-padding-tiny">
            <div class="pure-g">
              <input type="text" name="k" id="filter-k" class="pure-input-1" placeholder="Keywords, Tags (e.g. Dragons)" value="<?php echo $search; ?>">
            </div>
            <div class="pure-g">
              <select name="r" id="filter-r" class="pure-input-1">
                <option value="all" <?php echo ($results == 'all')?'selected':''; ?>>All Results</option>
                <option value="visited" <?php echo ($results == 'visited')?'selected':''; ?>>Already Visited</option>
                <option value="unvisited" <?php echo ($results == 'unvisited')?'selected':''; ?>>Not Yet Visited</option>
                <option value="headlines" <?php echo ($results == 'headlines')?'selected':''; ?>>Only Headlines</option>
                <option value="clusters" <?php echo ($results == 'clusters')?'selected':''; ?>>Only Clusters</option>
                <option value="articles" <?php echo ($results == 'articles')?'selected':''; ?>>Only Articles</option>
              </select>
            </div>
            <input type="hidden" name="s" id="filter-s" value="<?php echo $sort; ?>">
            <input type="hidden" name="subscription" id="filter-subscription" value="<?php echo $subscription; ?>">
            <input type="hidden" name="u" id="filter-u" value="<?php echo $userId; ?>">
            <input type="hidden" name="ajax" value="<?php echo site_url('ajax/lists'); ?>">
            <button type="submit" class="pure-button pure-input-1"><i class="fa fa-search"></i> Search</button>
          </fieldset>
        </form>
      </div>
    </div>
  </section>
  <section id="items-list" class="large">
    <div class="horizontal-margin-small vertical-margin-small">
      <form method="post" action="<?php echo site_url('search/join'); ?>" class="ajax-join">
        <div class="vertical-padding-xsmall">
          <div class="horizontal-padding-small bottom-padding-xsmall">
            <strong><a name="results"><i class="fa fa-align-justify"></i></a> Search Results</strong>
            <?php if($this->session->userdata('isLoggedIn') && in_array($this->session->userdata('level'), array('m', 'a'))): ?>
              <input type="hidden" name="ajax" value="<?php echo site_url('ajax/join'); ?>">
              <input type="hidden" name="uri" value="<?php echo ltrim($uri."&pg=".$current, '&'); ?>">
              <input type="submit" value="Combine Items" class="pure-button pure-button-tiny right-align vertical-margin-tiny grey-light-bg">
            <?php endif; ?>
          </div>
          <?php if($items): ?>
            <?php foreach($items as $i): ?>
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
                        <?php echo date("M d Y", $i->editedOn); ?>
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
                      <label for="<?php echo $i->type; ?>-<?php echo $i->id; ?>">Group</label>
                    </span>
                  <?php endif; ?>
                </span>
                <div class="clear"></div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="vertical-padding-xsmall horizontal-padding-xsmall">
              <span class="item">No results found.</span>
            </div>
          <?php endif; ?>
        </form>
      </div>
      <?php if($pages > 1): ?>
        <?php $url = "search?s=".$sort."&".ltrim($uri."&pg=", '&'); ?>
        <div class="vertical-padding-small horizontal-padding-small">
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
  <!--<section id="search-filters" class="small">
    <div class="horizontal-margin-small vertical-margin-small">
      <h3 class="horizontal-padding-xsmall">Other</h3>
      <div class="vertical-padding-xsmall">
      </div>
    </div>
  </section>-->
</div>