<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Blwords extends CI_Controller
{
  var $template = "admin-frame";

  function __construct()
  {
    parent::__construct();
    $this->load->model('utility_model');
  }

  public function index($current = 1, $limit = 40)
  {
    $this->form_validation->set_rules('pg', 'Page', 'trim|xss_clean');
    if($this->form_validation->run() && $_POST)
    {
      // Set Variables for Database Search
      $post = $this->input->post();
      if(isset($post['pg']) && $post['pg']) { $current = $post['pg']; }
    }
    $this->data['current'] = $current;
    $this->data['blwords'] = $this->database_model->get('blwords', array('deleted' => 0), $limit, $current-1);
    $count = $this->database_model->get_count('blwords', array('deleted' => 0));
    $this->data['pages'] = ceil($count / $limit);
    // Load View
    $this->data['title'] = "BL Words";
    if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'])
    {
      $this->load->view('includes/functions');
      $this->load->view('main/blwords', $this->data);
    }
    else
    {
      $this->data['page'] = "blwords";
      $this->load->view($this->template, $this->data);
    }
  }

  public function delete($blwordId = 0, $page = 1)
  {
    $update = array('deleted' => 1, 'editedBy' => $this->session->userdata('userId'));
    $this->database_model->edit('blwords', array('blwordId' => $blwordId), $update);
    redirect('admin/blwords?pg='.$page);
  }
}

/* End of file blwords.php */
/* Location: ./application/controllers/admin/blwords.php */