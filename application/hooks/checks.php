<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Checks extends CI_Controller
{
  private $CI;
  private $RTX;
  private $loginPages = array('actions', 'favorites', 'form', 'preferences', 'notifications');
  private $noLoginPages = array('login', 'register', 'password');

  public function __construct()
  {
    parent::__construct();
    $this->CI =& get_instance();
    $this->RTX =& load_class('Router', 'core');
  }

  /**
   * Check if has Admin Access
   *
   * @access public
   * @return null
   */
  public function is_admin()
  {
    if($this->RTX->directory == 'admin')
    {
      if(!$this->CI->session->userdata('level') !== 'a') { redirect('search'); }
    }
  }

  /**
   * Check if is Logged In
   *
   * @access public
   * @return null
   */
  public function is_logged_in()
  {
    if(in_array($this->RTX->class, $this->loginPages))
    {
      if(!$this->CI->session->userdata('isLoggedIn')) { redirect('/'); }
    }
    elseif(in_array($this->RTX->class, $this->noLoginPages))
    {
      if($this->CI->session->userdata('isLoggedIn')) { redirect('search'); }
    }
  }
}

/* End of file checks.php */
/* Location: ./application/hooks/checks.php */