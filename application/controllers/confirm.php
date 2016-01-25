<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Confirm extends CI_Controller
{
  var $data = array();
  var $template = "main-frame";

  /**
   * Account Confirmation
   *
   * @access public
   * @return void
   */
  public function index()
  {
    $this->form_validation->set_rules('e', '', 'trim|required|valid_email|xss_clean');
    $this->form_validation->set_rules('id', '', 'trim|required|exact_length[32]||alpha_dash|xss_clean');
    if($this->form_validation->run())
    {
      $post = $this->input->post();
      $where = array('email' => $post['e'], 'deleted' => 0);
      $user = $this->database_model->get_single('users', $where);
      if($user && $post['id'] == md5($user->userId))
      {
        $update = array('confirmed' => 1);
        $this->database_model->edit('users', array('userId' => $user->userId), $update);
        if($this->session->userdata('isLoggedIn')) { $this->session->set_userdata($update); }
        // Load View
        /*$this->data['title'] = "Email Confirmed";
        $this->data['page'] = "confirm/successful";
        $this->load->view($this->template, $this->data);*/
      }
    }
    redirect('search');
  }
}

/* End of file confirm.php */
/* Location: ./application/controllers/confirm.php */