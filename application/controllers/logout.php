<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Logout extends CI_Controller
{
  /**
   * Logout Page
   *
   * @access public
   * @return void
   */
  public function index()
  {
    // Remove Session Info
    $s = array('isLoggedIn' => false, 'user' => '', 'score' => 0, 'level' => '', 'confirmed' => 0);
    $this->session->set_userdata($s);
    redirect('/');
  }
}

/* End of file logout.php */
/* Location: ./application/controllers/logout.php */