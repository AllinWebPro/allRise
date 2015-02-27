<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Actions extends CI_Controller
{
  var $data = array();
  var $template = "main-frame";

  function __construct()
  {
    parent::__construct();
    $this->load->model('stream_model');
    $this->load->model('utility_model');
  }

  /**
   * Create Item
   *
   * @access public
   * @param string
   * @param string
   * @param int
   * @return void
   */
  public function index($action = '', $type = '', $id = 0)
  {
    $this->$action($type, $id);
  }

  /**
   * Create Item
   *
   * @access public
   * @param string
   * @param string
   * @param int
   * @return void
   */
  public function hashed($action = '', $type = '', $id = 0)
  {
    $this->$action($type, $id, true);
  }

  // --------------------------------------------------------------------

  /**
   * Delete Item
   *
   * @access private
   * @param string
   * @param int
   * @return void
   */
  private function delete($type = '', $id = 0, $hashed = false)
  {
    $update = array('deleted' => 1, 'editedBy' => $this->session->userdata('userId'));
    if($type == 'article')
    {
      if($hashed) { $article = array('OLD_PASSWORD(articleId)' => $id); }
      else { $article = array('articleId' => $id); }
      $clusters = $this->database_model->get('clusters', $article);
      $c_update = array('articleId' => null, 'editedBy' => $this->session->userdata('userId'));
      foreach($clusters as $c) { $this->database_model->edit('clusters', array('clusterId' => $c->clusterId), $c_update); }
      $this->database_model->edit('articles', $article, $update);
    }
    elseif($type == 'cluster')
    {
      if($hashed) { $cluster = array('OLD_PASSWORD(clusterId)' => $id); }
      else { $cluster = array('clusterId' => $id); }
      $headlines = $this->database_model->get('headlines', $cluster);
      $h_update = array('clusterId' => null, 'editedBy' => $this->session->userdata('userId'));
      foreach($headlines as $h) { $this->database_model->edit('headlines', array('headlineId' => $h->headlineId), $h_update); }
      $this->database_model->edit('clusters', $cluster, $update);
    }
    else
    {
      if($hashed) { $headline = array('OLD_PASSWORD(headlineId)' => $id); }
      else { $headline = array('headlineId' => $id); }
      $this->database_model->edit('headlines', $headline, $update);
    }
    redirect('search');
  }

  /**
   * Un-merge Item
   *
   * @access public
   * @param string
   * @param int
   * @return void
   */
  public function remove($type = '', $id = 0, $hashed = false)
  {
    $userId = $this->session->userdata('userId');
    if($hashed)
    {
      if($type == "headline") { $this->database_model->edit('headlines', array('OLD_PASSWORD(headlineId)' => $id), array('clusterId' => null, 'editedBy' => $userId)); }
      elseif($type == "cluster") { $this->database_model->edit('clusters', array('OLD_PASSWORD(clusterId)' => $id), array('articleId' => null, 'editedBy' => $userId)); }
      redirect(substr($type, 0, 1).'/'.$id);
    }
    else
    {
      if($type == "headline") { $this->database_model->edit('headlines', array('headlineId' => $id), array('clusterId' => null, 'editedBy' => $userId)); }
      elseif($type == "cluster") { $this->database_model->edit('clusters', array('clusterId' => $id), array('articleId' => null, 'editedBy' => $userId)); }
      redirect($type.'/'.$id);
    }
  }
}

/* End of file actions.php */
/* Location: ./application/controllers/actions.php */