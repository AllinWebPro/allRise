<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Password extends CI_Controller
{
  var $data = array();
  var $template = "main-frame";

  function __construct()
  {
    parent::__construct();
    $this->load->model('utility_model');
  }

  /**
   * Forgot Password
   *
   * @access public
   * @return void
   */
  public function index()
  {
    $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean');
    if($this->form_validation->run('password'))
    {
      $post = $this->input->post();
      $where = array('email' => $this->db->escape_str($post['email']), 'deleted' => 0);
      $user = $this->database_model->get_single('users', $where);
      if($user)
      {
        $password = $this->utility_model->generate_password();
        $update = array('password' => $this->utility_model->password_encrypt($user->user, $password));
        $update['editedBy'] = null;
        $this->database_model->edit('users', array('userId' => $user->userId), $update);
        $this->utility_model->emails_password($user, $password);
        $this->data['error'] = "An email has been sent to the user.";
      }
      else { $this->data['error'] = "No user could be found."; }
    }
    // Load View
    $this->data['title'] = "Forgot Password";
    if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'])
    {
      $this->load->view('includes/functions');
      $this->load->view('main/password', $this->data);
    }
    else
    {
      $this->data['page'] = "password";
      $this->load->view($this->template, $this->data);
    }
  }
}

/* End of file password.php */
/* Location: ./application/controllers/password.php */