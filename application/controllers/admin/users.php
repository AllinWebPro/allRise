<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends CI_Controller
{
  var $template = "admin-frame";

  function __construct()
  {
    parent::__construct();
    $this->load->model('utility_model');
  }

  public function index($current = 1, $limit = 20)
  {
    $this->form_validation->set_rules('pg', 'Page', 'trim|xss_clean');
    if($this->form_validation->run() && $_POST)
    {
      // Set Variables for Database Search
      $post = $this->input->post();
      if(isset($post['pg']) && $post['pg']) { $current = $post['pg']; }
    }
    $this->data['current'] = $current;
    $this->data['users'] = $this->database_model->get('users', array('deleted' => 0), $limit, $current-1);
    $count = $this->database_model->get_count('users', array('deleted' => 0));
    $this->data['pages'] = ceil($count / $limit);
    // Load View
    $this->data['title'] = "Users";
    if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'])
    {
      $this->load->view('includes/functions');
      $this->load->view('main/users', $this->data);
    }
    else
    {
      $this->data['page'] = "users";
      $this->load->view($this->template, $this->data);
    }
  }

  public function delete($userId = 0, $page = 1)
  {
    $update = array('deleted' => 1, 'editedBy' => $this->session->userdata('userId'));
    $this->database_model->edit('users', array('userId' => $userId), $update);
    redirect('admin/users?pg='.$page);
  }
}

/* End of file users.php */
/* Location: ./application/controllers/admin/users.php */