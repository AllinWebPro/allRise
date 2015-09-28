<form id="item_form" class="pure-u-1 pure-form ajax" method="post" action="<?php echo isset($id)?site_url($type.'/modify/'.$id):site_url($type.'/create'); ?>" novalidate>
  <div id="item" class="pure-u-1 vertical-padding-small">
    <section class="large ontop">
      <div class="horizontal-margin-small vertical-margin-small">
        <div class="horizontal-margin-small vertical-margin-small">
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
          <fieldset class="item pure-group pure-u-1">
            <input type="text" name="headline" id="item-headline" class="pure-input-1" placeholder="Headline*" value="<?php echo isset($_GET['headline'])?$_GET['headline']:(isset($item)?stripslashes($item->headline):''); ?>" required title="Max length 255 characters.">
            <?php if($type == 'headline'): ?>
              <textarea name="notes" id="item-notes" class="pure-input-1" placeholder="Description"><?php echo isset($item->notes)?stripcslashes($item->notes):''; ?></textarea>
            <?php elseif($type == 'article'): ?>
              <textarea name="article" id="item-article" class="pure-input-1" placeholder="Article"><?php echo isset($item->article)?stripslashes($item->article):''; ?></textarea>
            <?php endif; ?>
          </fieldset>
        </div>
        <?php if($type == 'cluster' && is_null($item->articleId) && $this->session->userdata('isLoggedIn')): ?>
          <div class="horizontal-padding-small vertical-padding-xsmall">
            <a title="Create Article" class="pure-button pure-button-small" href="<?php echo site_url('a/create/'.$hashId); ?>">
              Start Writing an Article</a>
          </div>
        <?php endif; ?>
        <hr>
        <div class="horizontal-padding-small">
          <strong><a name="images"><i class="fa fa-file-image-o"></i></a> Images</strong>
          <a href="javascript:void(0);" id="add_image" name="add_image" class="right-align">Add Image Link</a>
          <div class="clear"></div>
          <fieldset class="images pure-group pure-u-1">
            <?php if(isset($images_output) && $images_output): ?>
              <?php foreach($images_output as $i): ?>
                <div class="pure-g">
                  <div class="pure-u-1-24">
                    <div class="horizontal-margin-tiny vertical-margin-xsmall">
                      <img src="<?php echo stripslashes($i->image); ?>">
                    </div>
                  </div>
                  <div class="pure-u-11-12">
                    <input type="text" value="<?php echo stripslashes($i->image); ?>" name="image[<?php echo $i->imageId; ?>]" class="pure-input-1" placeholder="Image Link">
                  </div>
                  <div class="pure-u-1-24 center-text top-padding-xsmall"><a class="deleteLink" href="javascript:void(0);"><i class="fa fa-trash-o fa-7-5"></i></a></div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </fieldset>
        </div>
        <hr>
        <div class="horizontal-padding-small bottom-padding-small">
          <strong><a name="resources"><i class="fa fa-link"></i></a> Resources</strong>
          <a href="javascript:void(0);" id="add_resource" name="add_resource" class="right-align">Add Resource Link</a>
          <div class="clear"></div>
          <fieldset class="resources pure-group pure-u-1">
            <?php if(isset($resources_output) && $resources_output): ?>
              <?php foreach($resources_output as $r): ?>
                <div class="pure-g">
                  <div class="pure-u-23-24">
                    <input type="text" value="<?php echo stripslashes($r->resource); ?>" name="resource[<?php echo $r->resourceId; ?>]" class="pure-input-1" placeholder="Resource Link">
                  </div>
                  <div class="pure-u-1-24 center-text top-padding-xsmall"><a class="deleteLink" href="javascript:void(0);"><i class="fa fa-trash-o fa-7-5"></i></a></div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </fieldset>
        </div>
      </div>
    </section>
    <?php if($type !== 'headline' && isset($id) && $action !== 'add'): ?>
      <section id="components" class="large">
        <div class="horizontal-margin-small vertical-margin-small">
          <div class="horizontal-padding-small vertical-padding-xsmall pure-g">
            <?php if(isset($clusters)): ?>
              <div class="pure-u-1 bottom-padding-xsmall">
                <strong><a name="components"><div class="icon-box"><?php echo file_get_contents('media/svg/article.svg'); ?></div> Components</strong>
              </div>
              <?php foreach($clusters as $c): ?>
                <div class="pure-u-1 vertical-padding-tiny">
                  <a title="<?php echo stripslashes($c->headline); ?>" href="<?php echo site_url('c/'.$c->hashId.'/'.get_url_string($c->headline)); ?>" class="item ajax" data-type="item">
                    <div class="icon-box"><?php echo file_get_contents('media/svg/cluster.svg'); ?></div>
                    <?php echo stripslashes($c->headline); ?></a>
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
                    <span class="horizontal-padding-tiny"><i class="fa fa-3-4 fa-users"></i> <?php echo sizeof($c_contributors[$c->clusterId]); ?></span>
                  </span>
                </div>
                <?php foreach($headlines[$c->clusterId] as $h): ?>
                  <div class="pure-u-1 vertical-padding-tiny">
                    <a title="<?php echo stripslashes($h->headline); ?>" href="<?php echo site_url('h/'.$h->hashId.'/'.get_url_string($h->headline)); ?>" class="left-padding display-ib item ajax" data-type="item">
                      <div class="icon-box"><?php echo file_get_contents('media/svg/headline.svg'); ?></div>
                      <?php echo stripslashes($h->headline); ?></a>
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
                        <a href="<?php echo site_url('u/'.$h_contributors[$h->headlineId][0]->user); ?>"><?php echo $h_contributors[$h->headlineId][0]->user; ?></a></span>
                    </span>
                  </div>
                <?php endforeach; ?>
              <?php endforeach; ?>
            <?php elseif(isset($headlines)): ?>
              <div class="pure-u-1 bottom-padding-xsmall">
                <strong><a name="components"><div class="icon-box"><?php echo file_get_contents('media/svg/cluster.svg'); ?></div></a> Components</strong>
              </div>
              <?php foreach($headlines as $h): ?>
                <div class="pure-u-1 vertical-padding-tiny">
                  <a title="<?php echo stripslashes($h->headline); ?>" href="<?php echo site_url('h/'.$h->hashId.'/'.get_url_string($h->headline)); ?>" class="item ajax" data-type="item">
                    <div class="icon-box"><?php echo file_get_contents('media/svg/headline.svg'); ?></div>
                    <?php echo stripslashes($h->headline); ?></a>
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
                      <a href="<?php echo site_url('u/'.$h_contributors[$h->headlineId][0]->user); ?>"><?php echo $h_contributors[$h->headlineId][0]->user; ?></a></span>
                  </span>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </section>
    <?php endif; ?>
    <section class="small ontop">
      <div class="horizontal-margin-small vertical-margin-small">
        <div class="horizontal-padding-small vertical-padding-small">
          <div class="pure-u-1"><strong><a name="resources"><i class="fa fa-tags"></i></a> Tags</strong></div>
          <div class="clear"></div>
          <fieldset class="item pure-group pure-u-1">
            <textarea name="tags" id="item-tags" class="pure-input-1" placeholder="Tags (comma seperated)"><?php echo isset($item)?str_replace(',', ', ', stripslashes($item->tags)):''; ?></textarea>
          </fieldset>
        </div>
      </div>
    </section>
    <section class="small">
      <div class="horizontal-margin-small vertical-margin-small">
        <div class="horizontal-padding-small vertical-padding-small">
          <?php if($this->session->userdata('level') == 'a'): ?>
            <div class="pure-u-1 bottom-padding-xsmall">
              <div class="pure-u-1-3 em">
                <label for="adminOnly">Admin Only:</label>
              </div>
              <div class="pure-u-1-4">
                <label for="adminOnly-yes">
                  <input type="radio" name="adminOnly" id="adminOnly-yes" value="1" <?php echo set_radio('adminOnly', '1', (isset($item) && $item->adminOnly)?true:false); ?>> Yes
                </label>
              </div>
              <div class="pure-u-1-4">
                <label for="adminOnly-no">
                  <input type="radio" name="adminOnly" id="adminOnly-no" value="0" <?php echo set_radio('adminOnly', '0', (isset($item) && $item->adminOnly)?false:true); ?>> No
                </label>
              </div>
              <div class="pure-u-1-3 em">
                <label for="hidden">Hidden:</label>
              </div>
              <div class="pure-u-1-4">
                <label for="hidden-yes">
                  <input type="radio" name="hidden" id="hidden-yes" value="1" <?php echo set_radio('hidden', '1', (isset($item) && $item->hidden)?true:false); ?>> Yes
                </label>
              </div>
              <div class="pure-u-1-4">
                <label for="hidden-no">
                  <input type="radio" name="hidden" id="hidden-no" value="0" <?php echo set_radio('hidden', '0', (isset($item) && $item->hidden)?false:true); ?>> No
                </label>
              </div>
            </div>
          <?php endif; ?>
          <input type="hidden" name="place" id="headline-place" class="pure-input-1 place-ac">
          <input type="hidden" name="placeId" id="headline-placeId">
          <input type="hidden" name="categoryId[]" id="headline-categoryId-1" value="1">
          <?php if($type && isset($id) && $action !== 'add'): ?>
            <input type="hidden" name="type" value="<?php echo $type; ?>">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="ajax" value="<?php echo site_url('ajax/modify/'.$type.'/'.$id); ?>">
          <?php elseif($type !== 'headline'): ?>
            <input type="hidden" name="ajax" value="<?php echo site_url('ajax/'.$type.'/'.(isset($id)?$id:'')); ?>">
          <?php else: ?>
            <input type="hidden" name="ajax" value="<?php echo site_url('ajax/headline'); ?>">
          <?php endif; ?>
          <div class="pure-u-1 center-text bottom-margin-small">
            <button type="submit" class="pure-button pure-u-1 no-lr-padding">
              <i class="fa fa-pencil-square-o fa-9-10"></i> Publish <?php echo ucfirst($type); ?></button>
          </div>
          <?php if($type && isset($id) && $action !== 'add'): ?>
            <div class="pure-u-1 center-text">
              <a href="<?php echo site_url(substr($type, 0, 1).'/'.$hashId.'/'.get_url_string($item->headline)); ?>"><i class="fa fa-times fa-9-10"></i> Cancel Changes</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php if($type && isset($id) && $action !== 'add'): ?>
      <section class="small">
        <div class="horizontal-margin-small vertical-margin-small">
          <div class="horizontal-padding-small vertical-padding-small">
            <div class="pure-u-1 bottom-margin-xsmall"><strong><a name="resources"><i class="fa fa-clock-o"></i></a> Change History</strong></div>
            <div class="clear"></div>
            <ul class="no-margin">
              <?php foreach($history as $h): ?>
                <li><a href="<?php echo site_url(substr($type, 0, 1).'/'.$hashId.'/?hId='.$h->hashId); ?>" rel="nofollow"><?php echo date("F dS", $h->editedOn); ?> at <?php echo date("h:ia", $h->editedOn); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </section>
    <?php endif; ?>
  </div>
</form>