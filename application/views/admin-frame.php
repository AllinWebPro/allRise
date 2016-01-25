<?php $this->load->view('includes/functions'); ?>
<!doctype html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" lang="en"><!--<![endif]-->
  <?php $this->load->view('includes/head'); ?>
  <body>
    <!--[if lt IE 7]><script>for (x in open);</script><![endif]-->
    <?php $this->load->view('includes/admin-header'); ?>
    <div id="content" class="pure-g-r">
      <nav id="sort" class="pure-menu pure-menu-open pure-menu-horizontal pure-u-1">
        <ul class="pure-g-r pure-u-1 center-text left-align">
          <li class="pure-u-1-6 left-align <?php echo ($page == 'users')?'pure-menu-selected':''; ?>">
            <a title="Users" href="<?php echo site_url('admin/users'); ?>">Users</a></li>
          <li class="pure-u-1-6 left-align <?php echo ($page == 'blwords')?'pure-menu-selected':''; ?>">
            <a title="BL Words" href="<?php echo site_url('admin/blwords'); ?>">BL Words</a></li>
        </ul>
      </nav>
      <?php $this->load->view('admin/'.$page); ?>
      <div class="modal-content" id="delete-confirm" title="Empty the recycle bin?">
        These items will be permanently deleted and cannot be recovered. Are you sure?
      </div>
    </div>
    <?php $this->load->view('includes/main-footer'); ?>
    <?php $this->load->view('includes/admin-scripts'); ?>
    <div class="ajax-loader-overlay"></div>
    <div class="ajax-loader"></div>
  </body>
</html>