<div id="admin" class="pure-u-1 vertical-padding-small">
  <section id="admin-blwords" class="xlarge">
    <div class="horizontal-margin-small vertical-margin-small">
      <div id="blwords" class="pure-g-r vertical-padding-small horizontal-padding-xsmall">
        <?php $i = 1; foreach($blwords as $b): ?>
          <div class="pure-g-r pure-u-1-2">
            <div class="phone-u-1-2 pure-u-1-4">
              <div class="horizontal-padding-xsmall vertical-padding-tiny truncate">
                <strong class="display-b <?php echo ($i > 2)?'pure-visible-phone':''; ?>">Words</strong>
                <span><?php echo $b->blword; ?></span>
              </div>
            </div>
            <div class="phone-u-1-2 pure-u-1-4">
              <div class="horizontal-padding-xsmall vertical-padding-tiny truncate">
                <strong class="display-b <?php echo ($i > 2)?'pure-visible-phone':''; ?>">Active</strong>
                <span><?php echo ($b->activated)?'Active':'Inactive'; ?></span>
              </div>
            </div>
            <div class="pure-u-1-2">
              <div class="horizontal-padding-xsmall vertical-padding-tiny truncate">
                <strong class="display-b <?php echo ($i > 2)?'pure-visible-phone':''; ?>">Options</strong>
                <span>
                  <a href="<?php echo site_url("admin/blwords/edit/".$b->blwordId."/".$current); ?>" data-modal="blword-modify" data-id="<?php echo $b->blwordId; ?>">
                    <i class="fa fa-pencil-square-o fa-9-10"></i> Modify</a>
                  | <a class="delete" href="<?php echo site_url("admin/blwords/delete/".$b->blwordId."/".$current); ?>">
                    <i class="fa fa-trash-o fa-9-10"></i> Destroy</a>
                </span>
              </div>
            </div>
            <div class="clear"></div>
          </div>
        <?php $i++; endforeach; ?>
      </div>
      <?php if($pages > 1): ?>
        <?php $url = "admin/blwords?pg="; ?>
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
<div class="modal-content" id="blword-modify" title="Modify BL Word">
  <div class="message">Content is loading...</div>
  <form class="pure-u-1 pure-form ajax hidden" method="post" action="<?php echo site_url("admin/blwords/edit?pg=".$current); ?>" novalidate>
    <div class="errors"></div>
    <fieldset class="pure-group">
      <input type="text" name="blword" id="blword" class="pure-input-1" placeholder="BL Word (e.g. them)" value="">
    </fieldset>
    <fieldset class="pure-group">
      <label for="activated-yes" class="pure-radio pure-u-1-3 left-align">
        <input id="activated-yes" type="radio" name="activated" value="1"> Activated
      </label>
      <label for="activated-no" class="pure-radio pure-u-1-3 left-align">
        <input id="activated-no" type="radio" name="activated" value="0"> Deactivated
      </label>
    </fieldset>
    <input type="hidden" name="blwordId" value="">
    <input type="hidden" name="ajax" value="<?php echo site_url('admin/ajax/blmodify'); ?>">
  </form>
</div>