<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

/*$hook['pre_system'][] = array(
  'class'    => 'Fixes',
  'function' => 'post_get',
  'filename' => 'fixes.php',
  'filepath' => 'hooks'
);*/
$hook['post_controller_constructor'][] = array(
  'class'    => 'Checks',
  'function' => 'is_logged_in',
  'filename' => 'checks.php',
  'filepath' => 'hooks'
);
$hook['post_controller_constructor'][] = array(
  'class'    => 'Checks',
  'function' => 'is_admin',
  'filename' => 'checks.php',
  'filepath' => 'hooks'
);
$hook['post_controller_constructor'][] = array(
  'class'    => 'Load',
  'function' => 'set_arrays',
  'filename' => 'load.php',
  'filepath' => 'hooks'
);

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */