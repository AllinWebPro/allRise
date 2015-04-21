<nav id="sort" class="pure-menu pure-menu-open pure-menu-horizontal pure-u-1">
  <ul class="pure-g-r pure-u-1 center-text left-align">
    <li class="pure-u-1-6 left-align pure-menu-selected"><a><?php echo ucfirst($type); ?></a></li>
    <?php if($this->session->userdata('isLoggedIn') && !isset($history)): ?>
      <?php if($subscription): ?>
        <li class="pure-u-1-6 right-align pure-menu-selected">
          <a id="subscribe" href="<?php echo site_url(substr($type, 0, 1).'/'.$item->hashId.'/'.get_url_string($item->headline)); ?>?subscribe=0" class="ajax" data-type="item">Unsubscribe</a></li>
      <?php else: ?>
        <li class="pure-u-1-6 right-align pure-menu-selected">
          <a id="subscribe" href="<?php echo site_url(substr($type, 0, 1).'/'.$item->hashId.'/'.get_url_string($item->headline)); ?>?subscribe=1" class="ajax" data-type="item">Subscribe</a></li>
      <?php endif; ?>
    <?php endif; ?>
  </ul>
</nav>
<?php if(isset($_REQUEST['n']) && $_REQUEST['n'] && isset($parent) && $parent): ?>
  <div id="alert" class="pure-u-1 top-padding">
    <div class="horizontal-margin-small">
      <div class="horizontal-padding-small vertical-padding-small">
        <a href="<?php echo site_url(substr($parent->type, 0, 1).'/'.$parent->hashId.'/'.get_url_string($parent->headline)); ?>">
          This Headline was Auto-Joined with a Cluster based on the title and tags submitted.
          Click here, to join the conversation and see what other people are saying about the topic.
        </a>
      </div>
    </div>
  </div>
<?php endif; ?>
<div id="item" class="pure-u-1 vertical-padding-small">
  <section class="large">
    <div class="horizontal-margin-small vertical-margin-small">
      <div class="horizontal-padding-small vertical-padding-small">
        <h2><?php echo stripslashes($item->headline); ?></h2>
        <div class="horizontal-padding-tiny vertical-padding-tiny top-padding-xsmall grey">
          <span class="right-padding-tiny"><i class="fa fa-3-4 fa-edit horizontal-padding-tiny"></i>
            <time>
              <?php if($item->editedOn > strtotime(date("m/d/Y"))): ?>
                Today at <?php echo date("h:ia", $item->editedOn); ?>
              <?php elseif($item->editedOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
                Yesterday at <?php echo date("h:ia", $item->editedOn); ?>d
              <?php elseif(date("Y", $item->editedOn) == date("Y")): ?>
                <?php echo date("F dS", $item->editedOn); ?> at <?php echo date("h:ia", $item->editedOn); ?>
              <?php else: ?>
                <?php echo date("F dS Y", $item->editedOn); ?>
              <?php endif; ?>
            </time>
          </span>
          <?php if($item->createdOn !== $item->editedOn): ?>
            <span class="right-padding-tiny"><i class="fa fa-3-4 fa-clock-o horizontal-padding-tiny"></i>
              <time>
                <?php if($item->createdOn > strtotime(date("m/d/Y"))): ?>
                  Today at <?php echo date("h:ia", $item->createdOn); ?>
                <?php elseif($item->createdOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
                  Yesterday at <?php echo date("h:ia", $item->createdOn); ?>
                <?php elseif(date("Y", $item->createdOn) == date("Y")): ?>
                  <?php echo date("F dS", $item->createdOn); ?> at <?php echo date("h:ia", $item->createdOn); ?>
                <?php else: ?>
                  <?php echo date("F dS Y", $item->createdOn); ?>
                <?php endif; ?>
              </time>
            </span>
          <?php endif; ?>
          <?php if($type == 'headline'): ?>
            <span><i class="fa fa-3-4 fa-user horizontal-padding-tiny"></i>
              <a href="<?php echo site_url('user/'.$contributors[0]->user); ?>"><?php echo $contributors[0]->user; ?></a></span>
          <?php endif; ?>
          <?php if(isset($parent) && $parent): ?>
            <span><i class="fa fa-<?php echo ($parent->type == 'cluster')?'code-fork':'caret-square-o-up'; ?> horizontal-padding-tiny"></i>
              <a href="<?php echo site_url(substr($parent->type, 0, 1).'/'.$parent->hashId.'/'.get_url_string($parent->headline)); ?>">View Parent <?php echo ucfirst($parent->type); ?></a></span>
            <?php if($parent->type == 'cluster' && isset($parent_parent) && $parent_parent): ?>
              <span><i class="fa fa-caret-square-o-up horizontal-padding-tiny"></i>
                <a href="<?php echo site_url('a/'.$parent_parent->hashId.'/'.get_url_string($parent_parent->headline)); ?>">View Parent Article</a></span>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
      <?php if($type == 'article'): ?>
        <div class="horizontal-padding-small vertical-padding-small">
          <?php if($type == 'article' && $item->article): ?>
            <article class="content">
              <?php echo stripslashes(str_replace('\r', '', str_replace('\n', '', $item->article))); ?>
            </article>
            <?php if($this->session->userdata('isLoggedIn') && !isset($history) && (!$item->adminOnly || ($item->adminOnly && $this->session->userdata('level') == 'a')) && ($type !== 'headline' || ($item->createdBy == $this->session->userdata('userId') || in_array($this->session->userdata('level'), array('m', 'a'))))): ?>
              <a href="<?php echo site_url(substr($type, 0, 1).'/modify/'.$item->hashId); ?>" class="right-align">Edit this Article</a>
            <?php endif; ?>
            <div class="clear"></div>
          <?php elseif($this->session->userdata('isLoggedIn')): ?>
            <a class="pure-button pure-button-small" href="<?php echo site_url('article/modify/'.$id); ?>" >
              Start Writing an Article</a>
          <?php endif; ?>
        </div>
      <?php elseif($type == 'cluster' && is_null($item->articleId) && $this->session->userdata('isLoggedIn')): ?>
          <div class="horizontal-padding-small vertical-padding-xsmall">
            <a title="Create Article" class="pure-button pure-button-small" href="<?php echo site_url('article/create/'.$id); ?>">
              Start Writing an Article</a>
          </div>
      <?php elseif($type == 'headline' && $item->notes): ?>
        <div class="horizontal-padding-small vertical-padding-xsmall">
          <article class="content">
            <p class="em"><?php echo stripslashes(str_replace('\r', '', str_replace('\n', '<br>', $item->notes))); ?></p>
          </article>
        </div>
      <?php endif; ?>
      <hr>
      <div class="horizontal-padding-small vertical-padding-xsmall pure-g-r">
        <div class="pure-u-1 bottom-padding-small">
          <strong><a name="resources"><i class="fa fa-camera"></i></a> Images</strong>
        </div>
        <?php if($images): ?>
          <div class="clear"></div>
          <?php foreach($images as $i): ?>
            <div class="pure-u-1-4">
              <div class="horizontal-padding-small vertical-padding-small">
                <a href="<?php echo stripslashes($i->image); ?>" target="_blank" rel="nofollow">
                  <img src="<?php echo stripslashes($i->image); ?>" class="border"></a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="pure-u-1 truncate">
            <em>No images at this time.</em>
          </div>
        <?php endif; ?>
      </div>
      <hr>
      <div class="horizontal-padding-small vertical-padding-small top-padding-xsmall">
        <div class="pure-u-1 bottom-padding-small">
          <strong><a name="resources"><i class="fa fa-link"></i></a> Resources</strong>
        </div>
        <?php if($resources): ?>
          <?php foreach($resources as $r): ?>
            <div class="pure-u-1 truncate">
              <a href="<?php echo stripslashes($r->resource); ?>" target="_blank" rel="nofollow">
                 <?php echo $r->resourceId; ?>. <?php echo stripslashes($r->resource); ?></a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="pure-u-1 truncate">
            <em>No links at this time.</em>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php if($type == 'cluster' && is_null($item->articleId)): ?>
    <div class="modal-content" id="article-create" title="Create Article">
      <div class="message">Content is loading...</div>
      <form class="pure-u-1 pure-form ajax hidden" method="post" action="<?php echo site_url('article/create/'.$id); ?>" novalidate>
        <div class="errors"></div>
        <fieldset class="pure-group">
          <input type="text" name="headline" id="article-headline" class="pure-input-1" placeholder="Headline*" value="" required title="Max length 255 characters.">
          <textarea name="article" id="article-article" class="pure-input-1" placeholder="Article"></textarea>
          <input type="hidden" name="place" id="article-place" class="pure-input-1 place-ac" placeholder="Location" value="">
          <input type="hidden" name="placeId" id="article-placeId" value="">
          <input type="text" name="tags" id="article-tags" class="pure-input-1" placeholder="Tags (comma seperated)" value="">
        </fieldset>
        <fieldset class="resources pure-group"></fieldset>
        <input type="hidden" name="categoryId[]" id="item-categoryId-1" value="1">
        <input type="hidden" name="ajax" value="<?php echo site_url('ajax/article/'.$id); ?>">
      </form>
    </div>
  <?php endif; ?>
  <?php if($type !== 'headline'): ?>
    <section id="components" class="large">
      <div class="horizontal-margin-small vertical-margin-small">
        <div class="horizontal-padding-small vertical-padding-small pure-g">
          <?php if(isset($clusters)): ?>
            <div class="pure-u-1 bottom-padding-small">
              <strong><a name="social"><i class="fa fa-caret-square-o-up"></i></a> Components</strong>
            </div>
            <?php foreach($clusters as $c): ?>
              <div class="pure-u-1 vertical-padding-tiny">
                <a title="<?php echo stripslashes($c->headline); ?>" href="<?php echo site_url('c/'.$c->hashId.'/'.get_url_string($c->headline)); ?>" class="item ajax" data-type="item">
                  <i class="fa fa-code-fork"></i> <?php echo stripslashes($c->headline); ?></a>
                <span class="grey">
                  <span class="right-padding-tiny"><i class="fa fa-3-4 fa-clock-o horizontal-padding-tiny"></i>
                    <time>
                      <?php if($c->editedOn > strtotime(date("m/d/Y"))): ?>
                        Today at <?php echo date("h:ia", $c->editedOn); ?>
                      <?php elseif($c->editedOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
                        Yesterday at <?php echo date("h:ia", $c->editedOn); ?>
                      <?php elseif(date("Y", $c->editedOn) == date("Y")): ?>
                        <?php echo date("M d", $c->editedOn); ?> @ <?php echo date("h:ia", $c->editedOn); ?>
                      <?php else: ?>
                        <?php echo date("M d Y", $c->editedOn); ?>
                      <?php endif; ?>
                    </time>
                  </span>
                  <?php if($c_resources[$c->clusterId]): ?>
                    <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-link"></i> <?php echo sizeof($c_resources[$c->clusterId]); ?></span>
                  <?php endif; ?>
                  <?php if($c_comments[$c->clusterId]): ?>
                    <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-comment"></i>
                      <a title="<?php echo stripslashes($c->headline); ?>" href="<?php echo site_url('h/'.$c->hashId.'/'.get_url_string($c->headline)); ?>#comments" data-type="item" class="ajax">
                        <?php echo $c_comments[$c->clusterId]; ?></a>
                    </span>
                  <?php endif; ?>
                  <?php if(sizeof($c_contributors[$c->clusterId])): ?>
                    <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-users"></i> <?php echo sizeof($c_contributors[$c->clusterId]); ?></span>
                  <?php endif; ?>
                </span>
              </div>
              <?php foreach($headlines[$c->clusterId] as $h): ?>
                <div class="pure-u-1 vertical-padding-tiny">
                  <a title="<?php echo stripslashes($h->headline); ?>" href="<?php echo site_url('h/'.$h->hashId.'/'.get_url_string($h->headline)); ?>" class="left-padding display-ib item ajax" data-type="item">
                    <i class="fa fa-dot-circle-o"></i> <?php echo stripslashes($h->headline); ?></a>
                  <span class="grey left-padding display-ib">
                    <span class="right-padding-tiny"><i class="fa fa-3-4 fa-clock-o horizontal-padding-tiny"></i>
                      <time>
                        <?php if($h->editedOn > strtotime(date("m/d/Y"))): ?>
                          Today at <?php echo date("h:ia", $h->editedOn); ?>
                        <?php elseif($h->editedOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
                          Yesterday at <?php echo date("h:ia", $h->editedOn); ?>
                        <?php elseif(date("Y", $h->editedOn) == date("Y")): ?>
                          <?php echo date("M d", $h->editedOn); ?> @ <?php echo date("h:ia", $h->editedOn); ?>
                        <?php else: ?>
                          <?php echo date("M d Y", $h->editedOn); ?>
                        <?php endif; ?>
                      </time>
                    </span>
                    <?php if($h_resources[$h->headlineId]): ?>
                      <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-link"></i> <?php echo sizeof($h_resources[$h->headlineId]); ?></span>
                    <?php endif; ?>
                    <?php if($h_comments[$h->headlineId]): ?>
                      <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-comment"></i>
                        <a title="<?php echo stripslashes($h->headline); ?>" href="<?php echo site_url('h/'.$h->hashId.'/'.get_url_string($h->headline)); ?>#comments" data-type="item" class="ajax">
                          <?php echo $h_comments[$h->headlineId]; ?></a>
                      </span>
                    <?php endif; ?>
                    <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-user"></i>
                      <a href="<?php echo site_url('user/'.$h_contributors[$h->headlineId][0]->user); ?>"><?php echo $h_contributors[$h->headlineId][0]->user; ?></a></span>
                    <?php if($h->notes): ?><span class="horizontal-padding-tiny note"><i class="fa fa-3-4 fa-file-text-o"></i> View Note</span><?php endif; ?>
                  </span>
                  <?php if($h->notes): ?><div class="note-text hidden"><?php echo stripslashes(str_replace('\r', '', str_replace('\n', '<br>', $h->notes))); ?></div><?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endforeach; ?>
          <?php elseif(isset($headlines)): ?>
            <div class="pure-u-1 bottom-padding-small">
              <strong><a name="social"><i class="fa fa-code-fork"></i></a> Components</strong>
            </div>
            <?php foreach($headlines as $h): ?>
              <div class="pure-u-1 vertical-padding-tiny">
                <a title="<?php echo stripslashes($h->headline); ?>" href="<?php echo site_url('h/'.$h->hashId.'/'.get_url_string($h->headline)); ?>" class="item ajax" data-type="item">
                  <i class="fa fa-dot-circle-o"></i> <?php echo stripslashes($h->headline); ?></a>
                <span class="grey">
                  <span class="right-padding-tiny"><i class="fa fa-3-4 fa-clock-o horizontal-padding-tiny"></i>
                    <time>
                      <?php if($h->editedOn > strtotime(date("m/d/Y"))): ?>
                        Today at <?php echo date("h:ia", $h->editedOn); ?>
                      <?php elseif($h->editedOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
                        Yesterday at <?php echo date("h:ia", $h->editedOn); ?>
                      <?php elseif(date("Y", $h->editedOn) == date("Y")): ?>
                        <?php echo date("M d", $h->editedOn); ?> @ <?php echo date("h:ia", $h->editedOn); ?>
                      <?php else: ?>
                        <?php echo date("M d Y", $h->editedOn); ?>
                      <?php endif; ?>
                    </time>
                  </span>
                  <?php if($h_resources[$h->headlineId]): ?>
                    <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-link"></i> <?php echo sizeof($h_resources[$h->headlineId]); ?></span>
                  <?php endif; ?>
                  <?php if($h_comments[$h->headlineId]): ?>
                    <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-comment"></i>
                      <a title="<?php echo stripslashes($h->headline); ?>" href="<?php echo site_url('h/'.$h->hashId.'/'.get_url_string($h->headline)); ?>#comments" class="ajax" data-type="item">
                        <?php echo $h_comments[$h->headlineId]; ?></a>
                    </span>
                  <?php endif; ?>
                  <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-user"></i>
                    <a href="<?php echo site_url('user/'.$h_contributors[$h->headlineId][0]->user); ?>"><?php echo $h_contributors[$h->headlineId][0]->user; ?></a></span>
                  <?php if($h->notes): ?><span class="horizontal-padding-tiny note-toggle pointer"><i class="fa fa-3-4 fa-file-text-o"></i> View Note</span><?php endif; ?>
                </span>
                <?php if($h->notes): ?><div class="note-text show-none em"><?php echo stripslashes(str_replace('\r', '', str_replace('\n', '<br>', $h->notes))); ?></div><?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>
  <?php if($this->session->userdata('isLoggedIn') && !isset($history) && (!$item->adminOnly || ($item->adminOnly && $this->session->userdata('level') == 'a'))): ?>
    <section class="small">
      <div class="horizontal-margin-small vertical-margin-small">
        <div class="horizontal-padding-small vertical-padding-small pure-g">
          <div class="pure-u-1-2 center-text bottom-margin-small">
            <strong class="bottom-margin-xsmall display-b">Importance</strong>
            <div class="pure-u-1-2 left-align">
              <?php if($ranking && $ranking->iPositive): ?>
                <span href="<?php echo site_url(substr($type, 0, 1).'/'.$item->hashId.'?importance=up'); ?>" class="pure-button pure-button-xsmall pure-button-active ajax" data-history="false" data-type="vote" data-ajax="<?php echo site_url('ajax/vote/importance/up/'.$type.'/'.$id); ?>" id="importance-up">
                  <i class="fa fa-plus fa-7-5 fa-lh-110"></i></span>
              <?php else: ?>
                <a href="<?php echo site_url(substr($type, 0, 1).'/'.$item->hashId.'?importance=up'); ?>" class="pure-button pure-button-xsmall ajax" data-history="false" data-type="vote" data-ajax="<?php echo site_url('ajax/vote/importance/up/'.$type.'/'.$id); ?>" id="importance-up">
                  <i class="fa fa-plus fa-7-5 fa-lh-110"></i></a>
              <?php endif; ?>
            </div>
            <div class="pure-u-1-2 left-align">
              <?php if($ranking && $ranking->iNegative): ?>
                <span href="<?php echo site_url(substr($type, 0, 1).'/'.$item->hashId.'?importance=down'); ?>" class="pure-button pure-button-xsmall pure-button-active ajax" data-history="false" data-type="vote" data-ajax="<?php echo site_url('ajax/vote/importance/down/'.$type.'/'.$id); ?>" id="importance-down">
                  <i class="fa fa-minus fa-7-5 fa-lh-110"></i></span>
              <?php else: ?>
                <a href="<?php echo site_url(substr($type, 0, 1).'/'.$item->hashId.'?importance=down'); ?>" class="pure-button pure-button-xsmall ajax" data-history="false" data-type="vote" data-ajax="<?php echo site_url('ajax/vote/importance/down/'.$type.'/'.$id); ?>" id="importance-down">
                  <i class="fa fa-minus fa-7-5 fa-lh-110"></i></a>
              <?php endif; ?>
            </div>
          </div>
          <div class="pure-u-1-2 center-text bottom-margin-small">
            <strong class="bottom-margin-xsmall display-b">Quality</strong>
            <div class="pure-u-1-2 left-align">
              <?php if($ranking && $ranking->qPositive): ?>
                <span href="<?php echo site_url(substr($type, 0, 1).'/'.$item->hashId.'?quality=up'); ?>" class="pure-button pure-button-xsmall pure-button-active ajax" data-type="vote" data-ajax="<?php echo site_url('ajax/vote/quality/up/'.$type.'/'.$id); ?>" id="quality-up">
                  <i class="fa fa-plus fa-7-5"></i></span>
              <?php else: ?>
                <a href="<?php echo site_url(substr($type, 0, 1).'/'.$item->hashId.'?quality=up'); ?>" class="pure-button pure-button-xsmall ajax" data-type="vote" data-ajax="<?php echo site_url('ajax/vote/quality/up/'.$type.'/'.$id); ?>" id="quality-up">
                  <i class="fa fa-plus fa-7-5"></i></a>
              <?php endif; ?>
            </div>
            <div class="pure-u-1-2 left-align">
              <?php if($ranking && $ranking->qNegative): ?>
                <span href="<?php echo site_url(substr($type, 0, 1).'/'.$item->hashId.'?quality=down'); ?>" class="pure-button pure-button-xsmall pure-button-active ajax" data-type="vote" data-ajax="<?php echo site_url('ajax/vote/quality/down/'.$type.'/'.$id); ?>" id="quality-down">
                  <i class="fa fa-minus fa-7-5"></i></span>
              <?php else: ?>
                <a href="<?php echo site_url(substr($type, 0, 1).'/'.$item->hashId.'?quality=down'); ?>" class="pure-button pure-button-xsmall ajax" data-type="vote" data-ajax="<?php echo site_url('ajax/vote/quality/down/'.$type.'/'.$id); ?>" id="quality-down">
                  <i class="fa fa-minus fa-7-5"></i></a>
              <?php endif; ?>
            </div>
          </div>
          <?php if($type !== 'headline' || ($item->createdBy == $this->session->userdata('userId') || in_array($this->session->userdata('level'), array('m', 'a')))): ?>
            <div class="pure-u-1 center-text vertical-margin-small">
              <a class="pure-button pure-u-1 no-lr-padding" href="<?php echo site_url(substr($type, 0, 1).'/modify/'.$item->hashId); ?>">
                <i class="fa fa-pencil-square-o fa-9-10"></i> Modify <?php echo ucfirst($type); ?></a>
            </div>
          <?php endif; ?>
          <?php if((($type == 'cluster' && $item->articleId) || ($type == 'headline' && $item->clusterId)) && in_array($this->session->userdata('level'), array('m', 'a'))): ?>
            <div class="pure-u-1-2 center-text">
              <a href="<?php echo site_url(substr($type, 0, 1).'/unlink/'.$item->hashId); ?>"><i class="fa fa-chain-broken fa-9-10"></i> Unlink <?php echo ucfirst($type); ?></a>
            </div>
          <?php $unlink = true; endif; ?>
          <?php if(($type == 'headline' && $item->createdBy == $this->session->userdata('userId')) || in_array($this->session->userdata('level'), array('m', 'a'))): ?>
            <div class="pure-u-1<?php echo isset($unlink)?'-2':''; ?> center-text">
              <a class="delete" href="<?php echo site_url(substr($type, 0, 1).'/destroy/'.$item->hashId); ?>"><i class="fa fa-trash-o fa-9-10"></i> Destroy <?php echo ucfirst($type); ?></a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  <?php elseif(isset($history) && ($history_prev || $history_next)): ?>
    <section class="small">
      <div class="horizontal-margin-small vertical-margin-small">
        <div class="pure-g">
          <?php if($history_prev): ?>
            <div class="pure-u-1-2 left-align">
              <div class="horizontal-padding-small vertical-padding-small">
                <a class="pure-button pure-u-1 no-lr-padding" href="<?php echo site_url(substr($type, 0, 1).'/'.$item->hashId.'?hId='.$history_prev->hashId); ?>">
                  <i class="fa fa-chevron-circle-left fa-9-10"></i> Older Version</a>
              </div>
            </div>
          <?php endif; ?>
          <?php if($history_next): ?>
            <div class="pure-u-1-2 left-align">
              <div class="horizontal-padding-small vertical-padding-small">
                <a class="pure-button pure-u-1 no-lr-padding" href="<?php echo site_url(substr($type, 0, 1).'/'.$item->hashId.'?hId='.$history_next->hashId); ?>">
                  Newer Version <i class="fa fa-chevron-circle-right fa-9-10"></i></a>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <div class="horizontal-padding-small vertical-padding-small pure-g">
          <a class="pure-button pure-u-1 no-lr-padding" href="<?php echo site_url(substr($type, 0, 1).'/modify/'.$item->hashId); ?>">
            <i class="fa fa-pencil-square-o fa-9-10"></i> Modify Current Version</a>
        </div>
      </div>
    </section>
  <?php endif; ?>
  <section class="small">
    <div class="horizontal-margin-small vertical-margin-small">
      <div class="horizontal-padding-small vertical-padding-small">
        <a name="tags"><i class="fa fa-tags"></i></a>
        <?php
        $tags = "";
        foreach(explode(',', $item->tags) as $tag)
        {
          if($tags) { $tags .= ", "; }
          $tags .= '<a href="'.site_url('search?k='.urlencode(stripslashes($tag))).'">'.stripslashes($tag).'</a>';
        }
        echo $tags;
        ?>
      </div>
      <?php if($type !== 'headline' && (isset($contributors) && $contributors)): ?>
        <div class="horizontal-padding-small vertical-padding-small">
          <a name="contributors"><i class="fa fa-users"></i></a>
          <?php
          $output = "";
          foreach($contributors as $c)
          {
            if($output) { $output .= ", "; }
            $output .= '<a href="'.site_url('user/'.strtolower($c->user)).'">'.$c->user.'</a>';
          }
          echo $output;
          ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
  <section class="small">
    <div class="horizontal-margin-small vertical-margin-small">
      <div class="horizontal-padding-small vertical-padding-small">
        <div class="pure-u-1  bottom-padding-small">
          <strong><a name="social"><i class="fa fa-share"></i></a> Social</strong>
        </div>
        <form class="pure-u-1 pure-form pure-g-r">
          <?php $tinyurl = file_get_contents('http://tinyurl.com/api-create.php?url=http:'.site_url(substr($type, 0, 1).'/'.$item->hashId.'/'.get_url_string($item->headline))); ?>
          <fieldset class="pure-group pure-u-1">
            <input type="text" id="copy" value="<?php echo $tinyurl; ?>" class="pure-input-1" readonly>
          </fieldset>
          <div class="pure-u-1-2 center-text">
            <a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo $tinyurl; ?>" data-text="<?php echo get_100_char($item->headline); ?>">Tweet</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
          </div>
          <div class="pure-u-1-2 center-text">
            <div class="fb-share-button" data-href="<?php echo $tinyurl; ?>" data-layout="button_count"></div>
          </div>
        </form>
      </div>
    </div>
  </section>
  <section class="large">
    <div class="horizontal-margin-small vertical-margin-small">
      <div class="horizontal-padding-small top-padding-small">
        <strong><a name="comments"><i class="fa fa-comment"></i></a> Comments (<?php echo sizeof($comments); ?>)</strong>
      </div>
      <?php if($this->session->userdata('isLoggedIn') && !isset($history)): ?>
        <div class="horizontal-padding-small vertical-padding-small">
          <form method="post" action="?comments=create" class="pure-form pure-u-1 vertical-padding-xsmall ajax" novalidate>
            <div class="errors"></div>
            <input type="text" name="comment" id="add-comment" class="pure-input-1" placeholder="Share Your Thoughts" value="<?php echo set_value('comment'); ?>" required>
            <div class="pure-controls right-text top-padding">
              <input type="hidden" name="type" value="<?php echo $type; ?>">
              <input type="hidden" name="id" value="<?php echo $id; ?>">
              <input type="hidden" name="ajax" value="<?php echo site_url('ajax/comments/create'); ?>">
              <?php if(!$subscription): ?>
                <label for="subscribe" class="right-padding-xsmall">
                  <input type="checkbox" name="subscribe" id="subscribe" value="1" checked> Subscribe to <?php echo ucfirst($type); ?></label>
              <?php endif; ?>
              <button type="submit" class="pure-button pure-button-small">Comment</button>
            </div>
          </form>
        </div>
        <hr>
      <?php endif; ?>
      <div class="horizontal-padding-small vertical-padding-small">
        <div id="comments">
          <?php if($comments): ?>
            <?php foreach($comments as $c): ?>
              <article id="comment<?php echo $c->commentId; ?>" class="horizontal-padding-xsmall vertical-padding-xsmall">
                <?php if($c->photo): ?>
                  <img src="<?php echo site_url('uploads/users/'.$c->photo); ?>" alt="<?php echo $c->user; ?>" width="40" class="left-align right-padding-xsmall">
                <?php else: ?>
                  <img src="<?php echo site_url('media/img/no-image.gif'); ?>" alt="<?php echo $c->user; ?>" width="40" class="left-align right-padding-xsmall">
                <?php endif; ?>
                <header>
                  <a href="<?php echo site_url('user/'.$c->user); ?>" class="grey"><?php echo $c->user; ?></a> |
                  <time class="grey">
                    <?php if($c->editedOn > strtotime(date("m/d/Y"))): ?>
                      Today at <?php echo date("h:ia", $c->editedOn); ?>
                    <?php elseif($c->editedOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
                      Yesterday at <?php echo date("h:ia", $c->editedOn); ?>
                    <?php elseif(date("Y", $c->editedOn) == date("Y")): ?>
                      <?php echo date("F dS", $c->editedOn); ?> at <?php echo date("h:ia", $c->editedOn); ?>
                    <?php else: ?>
                      <?php echo date("F dS Y", $c->editedOn); ?>
                    <?php endif; ?>
                  </time>
                  <span class="right-align">
                    <?php if($c->createdBy == $this->session->userdata('userId')): ?>
                      <a href="<?php echo site_url($type.'/'.$id.'?comments=modify&commentId='.$c->commentId); ?>" data-modal='comment-edit' data-id="<?php echo $c->commentId; ?>">
                        <i class="fa fa-pencil-square-o"></i> Modify</a>
                    <?php endif; ?>
                    <?php if(($c->createdBy == $this->session->userdata('userId')) || in_array($this->session->userdata('level'), array('m', 'a'))): ?>
                      <?php if($c->createdBy == $this->session->userdata('userId')): ?>|<?php endif; ?>
                      <a href="<?php echo site_url($type.'/'.$id.'?comments=destroy&commentId='.$c->commentId); ?>" class="delete" data-type="comment"
                        data-ajax="<?php echo site_url('ajax/comments/destroy/'.$c->commentId); ?>" data-target="comment<?php echo $c->commentId; ?>">
                        <i class="fa fa-trash-o"></i> Destroy</a>
                    <?php endif; ?>
                  </span>
                </header>
                <span class="text">
                  <?php
                  $reg_exUrl = "/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                  $text = stripslashes($c->comment);
                  if(preg_match_all($reg_exUrl, $text, $url))
                  {
                    foreach($url[0] as $u) { $text = str_replace($u, "<a href='$u' target='_blank' rel='nofollow'>$u</a>", $text); }
                  }
                  echo $text;
                  ?>
                </span>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <article id="blank-comment">
              <span class="text"><em>
                Be the first to add a comment.
                <?php if(!$this->session->userdata('isLoggedIn')): ?>
                  <a href="<?php echo site_url('?r='.$this->uri->uri_string()); ?>" title="Login / Register"><i>Login / Register</i></a>
                <?php endif; ?>
              </em></span>
            </article>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
  <?php if(isset($related)): ?>
    <section class="small">
      <div class="horizontal-margin-small vertical-margin-small">
        <div class="horizontal-padding-small vertical-padding-small">
          <strong><a name="related"><i class="fa fa-search"></i></a> Related</strong>
          <?php if($related): ?>
            <?php foreach($related as $i): ?>
              <div class="vertical-padding-small horizontal-padding-xsmall">
                <?php if($i->image && 1 == 0): ?>
                  <img src="<?php echo $i->image; ?>" height="42" width="42" class="left-align right-padding-xsmall">
                <?php endif; ?>
                <a title="<?php echo stripslashes($i->headline); ?>" href="<?php echo site_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.get_url_string($i->headline)); ?>" class="ajax" data-type="item">
                  <span class="item">
                    <i class="fa fa-<?php echo ($i->type == 'headline')?'dot-circle-o':(($i->type == 'cluster')?'code-fork':'caret-square-o-up'); ?>"></i>
                    <span><?php echo stripslashes($i->headline); ?></span>
                  </span>
                </a><br>
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
                  <?php if($this->session->userdata('isLoggedIn') && in_array($this->session->userdata('level'), array('a'))): ?>
                    <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-eye"></i> <?php echo round($i->x_score * 10, 2); ?></span>
                    <span class="horizontal-padding-tiny">
                      K: <?php echo round($i->search_score * 10, 2); ?>
                      C: <?php echo round($i->cred_score * 10, 2); ?>
                      S: <?php echo round($i->sub_score * 10, 2); ?>
                      Q: <?php echo round($i->decay_score * 10, 2); ?>
                    </span>
                  <?php endif; ?>
                </span>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="vertical-padding-xsmall horizontal-padding-xsmall">
              <span class="item">No results found.</span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>
</div>
<?php if($this->session->userdata('isLoggedIn')): ?>
  <div class="modal-content" id="comment-edit" title="Modify Comment">
    <form class="pure-u-1 pure-form ajax" method="post" action="<?php echo site_url($type.'/'.$id.'?comments=modify&commentId='); ?>" novalidate>
      <div class="errors"></div>
      <fieldset>
        <input type="text" name="comment" id="edit-comment" class="pure-input-1" placeholder="Comment" value="" required>
      </fieldset>
      <input type="hidden" name="commentId" id="edit-commentId" value="">
      <input type="hidden" name="ajax" value="<?php echo site_url('ajax/comments/modify'); ?>">
    </form>
  </div>
<?php endif; ?>