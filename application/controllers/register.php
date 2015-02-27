<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Register extends CI_Controller
{
  var $data = array();
  var $frame = "main-frame";

  function __construct()
  {
    parent::__construct();
    $this->load->model('utility_model');
  }

  /**
   * Account Registration
   *
   * @access public
   * @return void
   */
  public function index()
  {
    $this->form_validation->set_rules('user', 'Username', 'trim|required|min_length[3]|max_length[24]|alpha_dash|xss_clean|is_unique[users.user]');
    $this->form_validation->set_rules('email', 'Email', 'trim|required|min_length[6]|max_length[255]|valid_email|xss_clean|is_unique[users.email]');
    $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]|max_length[50]|xss_clean'); //password_check
    $this->form_validation->set_rules('redirect', 'Redirect', 'trim|xss_clean');
    if($this->form_validation->run() && $_POST)
    {
      // Create User
      $post = $this->input->post();
      $insert = array('user' => $this->db->escape_str($post['user']), 'email' => $this->db->escape_str($post['email']));
      $insert['createdOn'] = time();
      $insert['password'] = $this->utility_model->password_encrypt($post['user'], $post['password']);
      $userId = $this->database_model->add('users', $insert, 'userId');
      if($user = $this->database_model->get_single('users', array('userId' => $userId)))
      {
        @mail("owen@allinwebpro.com", "New User", "Username: ".$user->user);
        //
        $this->database_model->edit('users', array('userId' => $user->userId), array('lastLogin' => time()));
        $this->utility_model->emails_signup($user);
        $s = array(
          'isLoggedIn' => true,
          'userId' => $user->userId,
          'user' => $user->user,
          'score' => $user->score,
          'level' => $user->level,
          'confirmed' => $user->confirmed,
          'location' => $user->location,
          'bio' => $user->bio,
          'email' => $user->email
        );
        $this->session->set_userdata($s);
        redirect($post['redirect']);
      }
      else { $this->data['error'] = '<p>An error happened creating your account.</p>'; }
    }
    // Load View
    $this->data['title'] = "Login / Register";
    if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'])
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

/* End of file register.php */
/* Location: ./application/controllers/register.php */