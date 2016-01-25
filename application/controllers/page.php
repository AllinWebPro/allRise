<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Page extends CI_Controller
{
  var $data = array();
  var $template = "page-frame";

  function __construct()
  {
    parent::__construct();
  }

  /**
   * Logout Page
   *
   * @access public
   * @return void
   */
  public function index($page = '404')
  {
    if($page == 'faq') { $this->data['title'] = "F.A.Q."; }
    elseif($page == 'bugs') { $this->data['title'] = "Report a Bug"; }
    elseif($page == 'contact') { $this->data['title'] = "Contact Us"; }
    elseif($page == 'terms') { $this->data['title'] = "Terms of Use"; }
    elseif($page == 'policy') { $this->data['title'] = "Privacy Policy"; }
    else { $this->data['title'] = "Page Not Found"; $page = '404'; }
    if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1)
    {
      $this->load->view('includes/functions');
      $this->load->view('main/'.$page, $this->data);
    }
    else
    {
      $this->data['page'] = $page;
      $this->load->view($this->template, $this->data);
    }
  }
}

/* End of file user.php */
/* Location: ./application/controllers/user.php */