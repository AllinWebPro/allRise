<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller
{
  var $data = array();
  var $template = "main-frame";

  function __construct()
  {
    parent::__construct();
    $this->load->model('stream_model');
  }

  /**
   * Logout Page
   *
   * @access public
   * @return void
   */
  public function index($user, $search = "", $sort = "score", $results = "all", $comments = true, $limit = 5, $current = 1)
  {
    $this->data['user'] = $this->database_model->get_single('users', array('user' => $this->db->escape_str($user)));
    if($this->data['user'])
    {
      $where = array('activated' => 1, 'deleted' => 0);
      $this->data['categories'] = $this->database_model->get_array('categories', $where, array('category' => 'category', 'slug' => 'slug'), 'categoryId');
      $this->data['created'] = $this->database_model->get_count('headlines', array('createdBy' => $this->data['user']->userId), 'createdBy');
      $this->data['created'] += $this->database_model->get_count('clusters', array('createdBy' => $this->data['user']->userId), 'createdBy');
      $this->data['created'] += $this->database_model->get_count('articles', array('createdBy' => $this->data['user']->userId), 'createdBy');
      $this->data['edited'] = $this->database_model->get_count('headlines_history', array('editedBy' => $this->data['user']->userId), 'editedBy');
      $this->data['edited'] += $this->database_model->get_count('clusters_history', array('editedBy' => $this->data['user']->userId), 'editedBy');
      $this->data['edited'] += $this->database_model->get_count('articles_history', array('editedBy' => $this->data['user']->userId), 'editedBy');
      $this->data['views'] = $this->database_model->get_count('views', array('userId' => $this->data['user']->userId));

      $this->data['sort'] = 'createdOn';
      $this->data['headlines'] = $this->stream_model->search($search, 'createdBy = '.$this->data['user']->userId, $sort, 'headlines', $comments, $limit, $current, $this->data['user']->userId, false, false);
      $this->data['contributions'] = $this->stream_model->search($search, '', $sort, $results, $comments, $limit, $current, $this->data['user']->userId, false, false);
      $this->data['recent'] = $this->stream_model->search($search, 'IF((type = "headline"), IF((createdBy = '.$this->data['user']->userId.'), 1, 0), 1) = 1', 'createdOn', $results, $comments, $limit, $current, $this->data['user']->userId, false, false);
      // Load View
      $this->data['title'] = $this->data['user']->user;
      if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1)
      {
        $this->load->view('includes/functions');
        $this->load->view('main/user', $this->data);
      }
      else
      {
        $this->data['page'] = "user";
        $this->load->view($this->template, $this->data);
      }
    }
    else { redirect('p/404'); }
  }
}

/* End of file user.php */
/* Location: ./application/controllers/user.php */