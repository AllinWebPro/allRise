<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Manual extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('utility_model');
  }

  public function index() { exit('No direct script access allowed'); }
  
  public function headline()
  {
    $headlines = $this->database_model->get('headlines');
    foreach($headlines as $h)
    {
      $headline = $this->utility_model->blwords_strip($h->headline, 'regEx_spaces', ' ');
      $tags = $this->utility_model->blwords_strip($h->tags, 'regEx_commas', ' ');
      $keywords = explode(' ', preg_replace('/[^a-z\d\' ]/i', '', $headline))+explode(' ', preg_replace('/[^a-z\d\' ]/i', '', $tags));
      foreach($keywords as $k)
      {
        if(!$this->database_model->get_count('autocomplete', array("keyword" => $this->db->escape_str($k, true))))
        {
          $this->database_model->add('autocomplete', array('keyword' => strtolower($this->db->escape_str($k, true))));
        }
      }
    }
  }
  
  public function cluster()
  {
    $clusters = $this->database_model->get('clusters');
    foreach($clusters as $c)
    {
      $headline = $this->utility_model->blwords_strip($c->headline, 'regEx_spaces', ' ');
      $tags = $this->utility_model->blwords_strip($c->tags, 'regEx_commas', ' ');
      $keywords = explode(' ', preg_replace('/[^a-z\d\' ]/i', '', $headline))+explode(' ', preg_replace('/[^a-z\d\' ]/i', '', $tags));
      foreach($keywords as $k)
      {
        if(!$this->database_model->get_count('autocomplete', array("keyword" => $this->db->escape_str($k, true))))
        {
          $this->database_model->add('autocomplete', array('keyword' => strtolower($this->db->escape_str($k, true))));
        }
      }
    }
  }
  
  public function article()
  {
    $articles = $this->database_model->get('articles');
    foreach($articles as $a)
    {
      $headline = $this->utility_model->blwords_strip($a->headline, 'regEx_spaces', ' ');
      $tags = $this->utility_model->blwords_strip($a->tags, 'regEx_commas', ' ');
      $keywords = explode(' ', preg_replace('/[^a-z\d\' ]/i', '', $headline))+explode(' ', preg_replace('/[^a-z\d\' ]/i', '', $tags));
      foreach($keywords as $k)
      {
        if(!$this->database_model->get_count('autocomplete', array("keyword" => $this->db->escape_str($k, true))))
        {
          $this->database_model->add('autocomplete', array('keyword' => strtolower($this->db->escape_str($k, true))));
        }
      }
    }
  }
}

/* End of file manual.php */
/* Location: ./application/controllers/manual.php */