<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller
{
  var $data = array();
  var $template = "main-frame";

  function __construct()
  {
    parent::__construct();
    $this->load->model('utility_model');
  }

  /**
   * Account Login
   *
   * @access public
   * @return void
   */
  public function index()
  {
    $this->form_validation->set_rules('login', 'Username/Email', 'trim|required|xss_clean');
    $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
    $this->form_validation->set_rules('redirect', 'Redirect', 'trim|xss_clean');
    if($this->form_validation->run('login') && $_POST)
    {
      // Check Against User Table
      $post = $this->input->post();
      $where = array(
        'deleted' => 0,
        "(user = '".$this->db->escape_str($post['login'])."' 
          OR email = '".$this->db->escape_str($post['login'])."')" => null
      );
      if($user = $this->database_model->get_single('users', $where))
      {
        if($user->old_pass == $this->utility_model->old_password_encrypt($post['password']))
        {
          $update = array('old_pass' => '', 'password' => $this->utility_model->password_encrypt($user->user, $post['password']));
          $update['editedBy'] = $user->userId;
          $this->database_model->edit('users', array('userId' => $user->userId), $update);
          $user = $this->database_model->get_single('users', $where);
        }
        if($user->password == $this->utility_model->password_encrypt($user->user, $post['password']))
        {
          $this->database_model->edit('users', array('userId' => $user->userId), array('lastLogin' => time(), 'tutorial' => 0));
          // Save Session Info
          $s = array(
            'isLoggedIn' => true,
            'userId' => $user->userId,
            'user' => $user->user,
            'score' => $user->score,
            'level' => $user->level,
            'confirmed' => $user->confirmed,
            'location' => $user->location,
            'bio' => $user->bio,
            'email' => $user->email,
            'notices' => $user->notices
          );
          $this->session->set_userdata($s);
          $this->session->set_flashdata('tutorial', $user->tutorial);
          redirect($post['redirect']);
        }
        else { $this->data['login_error'] = 'Password did not match account.'; }
      }
      else { $this->data['login_error'] = 'Username/Email could not be found.'; }
    }
    elseif($_POST) { $this->data['login_errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
    // Load View
    $this->data['title'] = "";
    if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1)
    {
      $this->load->view('includes/functions');
      $this->load->view('main/login-register', $this->data);
    }
    else
    {
      $this->data['page'] = "login-register";
      $this->load->view($this->template, $this->data);
    }
  }
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */