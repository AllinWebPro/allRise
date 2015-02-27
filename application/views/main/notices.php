<?php if($notices): ?>
  <?php foreach($notices as $n): ?>
    <div class="vertical-padding-xsmall horizontal-padding-xsmall h-overflow <?php echo ($n->views < $n->instances)?'grey-light-bg':''; ?>">
      <?php if($n->photo): ?>
        <img src="<?php echo site_url('uploads/users/'.$n->photo); ?>" height="42" width="42" class="left-align right-padding-xsmall">
      <?php endif; ?>
      <a title="<?php echo stripslashes($n->headline); ?>" href="<?php echo site_url(substr($n->type, 0, 1).'/'.$n->hashId.'/'.get_url_string($n->headline)); ?>">
        <?php if($n->action == 'comments'): ?>
          <?php $people = explode(' ', $n->users); ?>
          <?php if(sizeof($people) > 3): ?>
            <strong><?php echo $people[0]; ?></strong>, <strong><?php echo $people[1]; ?></strong>, <strong><?php echo $people[2]; ?></strong> and <?php echo (sizeof($people) - 3); ?> others
          <?php elseif(sizeof($people) == 3): ?>
            <strong><?php echo $people[0]; ?></strong>, <strong><?php echo $people[1]; ?></strong>, and <strong><?php echo $people[2]; ?></strong>
          <?php elseif(sizeof($people) == 2): ?>
            <strong><?php echo $people[0]; ?></strong> and <strong><?php echo $people[1]; ?></strong>
          <?php elseif(sizeof($people) == 1): ?>
            <strong><?php echo $people[0]; ?></strong>
          <?php endif; ?>
          commented on <i class="fa fa-<?php echo ($n->type == 'headline')?'dot-circle-o':(($n->type == 'cluster')?'code-fork':'caret-square-o-up'); ?>"></i>
          <em>&ldquo;<?php echo stripslashes($n->headline); ?>&rdquo;</em>
        <?php elseif($n->action == 'edits'): ?>
          <?php $people = explode(' ', $n->users); ?>
          <?php if(sizeof($people) > 3): ?>
            <strong><?php echo $people[0]; ?></strong>, <strong><?php echo $people[1]; ?></strong>, <strong><?php echo $people[2]; ?></strong> and <?php echo (sizeof($people) - 3); ?> others
          <?php elseif(sizeof($people) == 3): ?>
            <strong><?php echo $people[0]; ?></strong>, <strong><?php echo $people[1]; ?></strong>, and <strong><?php echo $people[2]; ?></strong>
          <?php elseif(sizeof($people) == 2): ?>
            <strong><?php echo $people[0]; ?></strong> and <strong><?php echo $people[1]; ?></strong>
          <?php elseif(sizeof($people) == 1): ?>
            <strong><?php echo $people[0]; ?></strong>
          <?php endif; ?>
          edited <i class="fa fa-<?php echo ($n->type == 'headline')?'dot-circle-o':(($n->type == 'cluster')?'code-fork':'caret-square-o-up'); ?>"></i>
          <em>&ldquo;<?php echo stripslashes($n->headline); ?>&rdquo;</em>
        <?php elseif($n->action == 'joins'): ?>
          <i class="fa fa-<?php echo ($n->type == 'headline')?'dot-circle-o':(($n->type == 'cluster')?'code-fork':'caret-square-o-up'); ?>"></i>
          <em>&ldquo;<?php echo stripslashes($n->headline); ?>&rdquo;</em> has been upgraded to <?php echo ($n->type == 'headline')?'a Cluster':'an Article'; ?>
        <?php endif; ?>
      </a><br>
      <span class="grey">
        <span class="right-padding-tiny"><i class="fa fa-3-4 fa-clock-o horizontal-padding-tiny"></i>
          <time>
            <?php if($n->createdOn > strtotime(date("m/d/Y"))): ?>
              Today at <?php echo date("h:ia", $n->createdOn); ?>
            <?php elseif($n->createdOn > strtotime(date("m/d/Y", strtotime("-1 day")))): ?>
              Yesterday at <?php echo date("h:ia", $n->createdOn); ?>
            <?php elseif(date("Y", $n->createdOn) == date("Y")): ?>
              <?php echo date("M d", $n->createdOn); ?> @ <?php echo date("h:ia", $n->createdOn); ?>
            <?php else: ?>
              <?php echo date("M d Y", $n->createdOn); ?>
            <?php endif; ?>
          </time>
        </span>
      </span>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <div class="vertical-padding-small horizontal-padding-xsmall">
    <span class="text"><em>No notifications at this time.</em></span>
  </div>
<?php endif; ?>