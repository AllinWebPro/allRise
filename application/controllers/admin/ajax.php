<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax extends CI_Controller
{
  function __construct()
  {
    parent::__construct();
    $this->load->model('utility_model');
  }

  public function index() { exit('No direct script access allowed'); }

  public function blword($blwordId)
  {
    $response = array('success' => 0, 'time' => time());
    $where = array('blwordId' => $blwordId, 'deleted' => 0);
    $item = $this->database_model->get_single('blwords', $where);
    if($item)
    {
      $response['item'] = $item;
      $response['success'] = 1;
    }
    $this->_output_json($response);
  }

  public function blmodify()
  {
    $response = array('success' => 0);
    $this->form_validation->set_rules('blword', 'BL Word', 'trim|required|xss_clean');
    $this->form_validation->set_rules('activated', 'Active', 'trim|required|xss_clean');
    $this->form_validation->set_rules('blwordId', 'BL Word ID', 'trim|required|xss_clean');
    if($this->form_validation->run())
    {
      $post = $this->input->post();
      $userId = $this->session->userdata('userId');
      $update = array('blword' => $post['blword'], 'activated' => $post['activated']);
      $this->database_model->edit('blwords', array('blwordId' => $post['blwordId']), $update);
    }
    $response['errors'] = 'Update complete!';
    $this->_output_json($response);
  }

  /**
   * Output JSON
   *
   * @access private
   * @param array
   * @return void
   */
  private function _output_json($data)
  {
    // Output JSON
    $this->output->set_content_type('application/json');
    $this->output->set_output(json_encode($data));
  }
}

/* End of file ajax.php */
/* Location: ./application/controllers/admin/ajax.php */