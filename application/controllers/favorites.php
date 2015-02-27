<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Favorites extends CI_Controller
{
  var $data = array();
  var $template = "main-frame";

  function __construct()
  {
    parent::__construct();
    $this->load->model('stream_model');
  }

  /**
   * Favorites
   *
   * @access public
   * @param string
   * @return void
   */
  public function index()
  {
    $this->data['items'] = $this->stream_model->get_favorites_by_user($this->session->userdata('userId'));
    // Load View
    $this->data['title'] = "Favorites";
    $this->data['page'] = "stream";
    $this->load->view($this->template, $this->data);
  }
}

/* End of file favorites.php */
/* Location: ./application/controllers/favorites.php */