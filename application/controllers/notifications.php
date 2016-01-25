<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notifications extends CI_Controller
{
  var $data = array();
  var $template = "main-frame";

  function __construct()
  {
    parent::__construct();
    $this->load->model('stream_model');
    $this->load->model('utility_model');
  }

  function index($limit = 20, $current = 1)
  {
    $this->form_validation->set_rules('pg', 'Page', 'trim|xss_clean');
    if($this->form_validation->run() && $_POST)
    {
      // Set Variables for Database Search
      $post = $this->input->post();
      if(isset($post['pg']) && $post['pg']) { $current = $post['pg']; }
    }
    $this->data['current'] = $current;
    $this->data['user'] = $this->database_model->get_single('users', array('userId' => $this->session->userdata('userId')));
    $display = array();
    if($this->data['user']->comments) { $display[] = "comments"; }
    if($this->data['user']->edits) { $display[] = "edits"; }
    if($this->data['user']->parents) { $display[] = "joins"; }
    $this->data['notices'] = $this->stream_model->notifications($this->data['user']->userId, 0, $display, $limit, $current);
    $count = $this->stream_model->notifications_count($this->data['user']->userId, 0, $display);
    $this->data['pages'] = ceil(($count) / $limit);
    $this->data['title'] = "Notifications";
    if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1)
    {
      $this->load->view('includes/functions');
      $this->load->view('main/notifications', $this->data);
    }
    else
    {
      $this->data['page'] = "notifications";
      $this->load->view($this->template, $this->data);
    }
  }

  function settings()
  {
    $this->form_validation->set_rules('edits', 'Item Updates', 'trim|required|xss_clean');
    $this->form_validation->set_rules('parents', 'Item Joins', 'trim|required|xss_clean');
    $this->form_validation->set_rules('comments', 'Item Comments', 'trim|required|xss_clean');
    if($this->form_validation->run() && $_POST)
    {
      // Set Variables for Database Search
      $post = $this->input->post();
      $userId = $this->session->userdata('userId');
      $this->database_model->edit('users', array('userId' => $userId), $post);
    }
    redirect('notifications');
  }
}

/* End of file notifications.php */
/* Location: ./application/controllers/notifications.php */