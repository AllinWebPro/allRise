<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Preferences extends CI_Controller
{
  var $data = array();
  var $template = "page-frame";

  function __construct()
  {
    parent::__construct();
    $this->load->model('stream_model');
    $this->load->model('utility_model');
  }

  /**
   * Account Preferences
   *
   * @access public
   * @return void
   */
  public function index()
  {
    // Vars
    $where = array('userId' => $this->session->userdata('userId'));
    $this->data['user'] = $this->database_model->get_single('users', $where);
    // Validate
    $this->form_validation->set_rules('location', 'Location', 'trim|max_length[255]|xss_clean');
    $this->form_validation->set_rules('bio', 'Biography', 'trim|max_length[65535]|xss_clean');
    $this->form_validation->set_rules('notices', 'Email Notifications', 'trim|required|xss_clean');
    $this->form_validation->set_rules('password', 'Current Password', 'trim|required|xss_clean');
    $this->form_validation->set_rules('email', 'Email', 'trim|required|min_length[6]|max_length[255]|valid_email|xss_clean');
    $this->form_validation->set_rules('new_password', 'New Password', 'trim|min_length[8]|max_length[50]|xss_clean|password_check');
    $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|xss_clean|matches[new_password]');
    if($this->form_validation->run())
    {
      $post = $this->input->post();
      $update = array(
        'location' => $this->db->escape_str($post['location']),
        'bio' => $this->db->escape_str($post['bio']),
        'notices' => $this->db->escape_str($post['notices']),
        'editedBy' => $this->data['user']->userId
      );

      if(($post['email'] !== $this->data['user']->email || $post['new_password']))
      {
        if($this->utility_model->password_encrypt($this->data['user']->user, $post['password']) == $this->data['user']->password)
        {
          if($post['email'] !== $this->data['user']->email)
          {
            $email_where = array('email' => $this->db->escape_str($post['email']),'userId !=' => $this->session->userdata('userId'));
            if(!$this->database_model->get_single('users', $email_where))
            {
              $update['confirmed'] = 0;
              $update['email'] = $this->db->escape_str($post['email']);
            }
            else { $this->data['error'] = "Email already exists in our system."; }
            if(!$this->data['error'])
            {
              if($post['new_password']) { $update['password'] = $this->utility_model->password_encrypt($this->data['user']->user, $post['new_password']); }
            }
          }
        }
        else { $this->data['error'] = "Current Password does not match password in the system."; }
      }
      if(!$this->data['error'])
      {
        $this->database_model->edit('users', $where, $update);
        $this->data['user'] = $this->database_model->get_single('users', $where);
        if(isset($update['confirmed'])) { $this->utility_model->emails_confirmation($this->data['user']); }
        $s = array(
          'location' => $update['location'],
          'bio' => $update['bio'],
          'email' => $update['email'],
          'notices' => $update['notices']
        );
        $this->session->set_userdata($s);
        $this->data['error'] = "Account has been updated!";
      }
    }
    elseif($_POST)
    {
      $this->data['errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.';
    }
    // Load View
    $this->data['title'] = "Preferences";
    if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1)
    {
      $this->load->view('includes/functions');
      $this->load->view('main/preferences', $this->data);
    }
    else
    {
      $this->data['page'] = "preferences";
      $this->load->view($this->template, $this->data);
    }
  }

  public function photo($userId = 0)
  {
    $this->data['output'] = "";
    if($_FILES && !$_FILES['photo']['error'])
    {
      $ext = strtolower(end(explode('.', $_FILES['photo']['name'])));
      if(in_array($ext, array('jpeg', 'jpg', 'gif', 'png')))
      {
        $where = array('userId' => $this->session->userdata('userId'));
        $file = $this->session->userdata('user').'-'.time().".".$ext;
        $target = realpath('uploads/users').'/'.$file;
        if(move_uploaded_file($_FILES['photo']['tmp_name'], $target))
        {
          $update = array('photo' => $file);
          $this->database_model->edit('users', $where, $update);
          $this->data['output'] = "Profile photo updated!";
        }
        else
        {
          $this->data['output'] = "An error has occurred!";
        }
      }
      else
      {
        $this->data['output'] = "Selected file type is not supported!";
      }
    }
    elseif($_FILES && !$_FILES['photo']['error'])
    {
      $this->data['output'] = "Upload unsuccessful!";
    }
    $this->data['user'] = $this->database_model->get_single('users', array('userId' => $userId));
    $this->load->view('main/preferences-photo', $this->data);
  }

  public function destroy($encrypt = '')
  {
    if($encrypt && $encrypt == md5($this->session->userdata('userId')))
    {
      $where = array('userId' => $this->session->userdata('userId'));
      $update = array('deleted' => 1);
      $this->database_model->edit('users', $where, $update);
      redirect('logout');
    }
    else { redirect('/'); }
  }
}

/* End of file preferences.php */
/* Location: ./application/controllers/preferences.php */