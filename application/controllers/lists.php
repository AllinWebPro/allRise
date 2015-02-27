<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lists extends CI_Controller
{
  var $data = array();
  var $template = "main-frame";

  function __construct()
  {
    parent::__construct();
    $this->load->model('stream_model');
    $this->load->model('utility_model');
    $where = array('activated' => 1, 'deleted' => 0);
    $this->data['categories'] = $this->database_model->get_array('categories', $where, array('category' => 'category', 'slug' => 'slug'), 'categoryId');
    $this->data['flags'] = $this->database_model->get_array('flags', $where, array('flag' => 'flag', 'slug' => 'slug'), 'flagId');
  }

  public function index($search = "", $where = "", $sort = "score", $results = "all", $comments = true, $limit = 20, $current = 1, $userId = 0, $subscription = 0)
  {
    $this->data['uri'] = '';
    $this->form_validation->set_rules('r', 'Results', 'trim|xss_clean');
    $this->form_validation->set_rules('s', 'Sort', 'trim|xss_clean');
    $this->form_validation->set_rules('k', 'Keywords', 'trim|xss_clean');
    $this->form_validation->set_rules('u', 'user', 'trim|xss_clean');
    $this->form_validation->set_rules('b', 'subscription', 'trim|xss_clean');
    $this->form_validation->set_rules('pg', 'Page', 'trim|xss_clean');
    if($this->form_validation->run() && $_POST)
    {
      // Set Variables for Database Search
      $post = $this->input->post();
      if(isset($post['pg']) && $post['pg']) { $current = $post['pg']; }
      if(isset($post['s']) && $post['s']) { $sort = $post['s']; }
      if(isset($post['r']) && $post['r']) { $results = $post['r']; $this->data['uri'] .= "&r=".$results; }
      if(isset($post['k']) && $post['k']) { $search = $post['k']; $this->data['uri'] .= "&k=".$search; }
      if(isset($post['u']) && $post['u']) { $userId = $post['u']; $this->data['uri'] .= "&u=".$userId; }
      if(isset($post['b']) && $post['b']) { $subscription = $post['b']; $this->data['uri'] .= "&b=".$subscription; }
    }
    $this->data['results'] = $results;
    $this->data['sort'] = $sort;
    $this->data['search'] = $search;
    $this->data['userId'] = $userId;
    $this->data['subscription'] = $subscription;
    $this->data['current'] = $current;
    $this->data['sort'] = $sort;
    if($subscription) { $userId = $this->session->userdata('userId'); }
    $this->data['items'] = $this->stream_model->search($search, $where, $sort, $results, $comments, $limit, $current, $userId, $subscription);
    $count = $this->stream_model->search_count($search, $where, $sort, $results, $userId, $subscription);
    $this->data['pages'] = ceil(($count->items) / $limit);
    $this->data['title'] = "Search";
    if($search) { $this->data['title'] .= " [".$search."]"; }
    //print_r($this->data); die();
    if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'])
    {
      $this->load->view('includes/functions');
      $this->load->view('main/lists', $this->data);
    }
    else
    {
      $this->data['page'] = "lists";
      $this->load->view($this->template, $this->data);
    }
  }

  /**
   * Merge Items
   *
   * @access public
   * @return void
   */
  public function join()
  {
    $this->form_validation->set_rules('headline[]', 'Headlines', 'trim|xss_clean');
    $this->form_validation->set_rules('cluster[]', 'Clusters', 'trim|xss_clean');
    $this->form_validation->set_rules('article[]', 'Articles', 'trim|xss_clean');
    if($this->form_validation->run())
    {
      $post = $this->input->post();
      $items = array(
        'headline' => isset($post['headline'])?$post['headline']:array(),
        'cluster' => isset($post['cluster'])?$post['cluster']:array(),
        'article' => isset($post['article'])?$post['article']:array()
      );
      $this->stream_model->manual_join($items);
    }
    redirect('search');
  }
}

/* End of file lists.php */
/* Location: ./application/controllers/lists.php */