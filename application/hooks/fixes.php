<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fixes
{
  /**
   * Set Get to Post for Validation
   *
   * @access public
   * @return null
   */
  public function post_get() { $_POST = $_POST + $_GET; }
}

/* End of file fixes.php */
/* Location: ./application/hooks/fixes.php */