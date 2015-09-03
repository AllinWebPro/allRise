<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax extends CI_Controller
{
  function __construct()
  {
    $_POST = $_POST + $_GET;
    parent::__construct();
    $this->load->model('utility_model');
    if(!$this->session->userdata('isLoggedIn'))
    {
      $loginPages = array('article','comments', 'headline', 'join', 'preferences', 'modify', 'vote');
      if(in_array($this->uri->segment(2), $loginPages)) { $this->_output_json(array('success' => 0, 'errors' => "Access Denied!")); }
    }
    elseif($this->uri->segment(2) == 'join' && !in_array($this->session->userdata('level'), array('m', 'a')))
    {
      $this->_output_json(array('success' => 0, 'errors' => "Access Denied!"));
    }
    if(in_array($this->uri->segment(2), array('bugs', 'contact'))) { $this->load->library('email'); }
    $this->load->model('stream_model');
  }

  public function index() { exit('No direct script access allowed'); }

  public function alerts()
  {
    $user = $this->database_model->get_single('users', array('userId' => $this->session->userdata('userId')));
    if($user)
    {
      $display = array();
      if($user->comments) { $display[] = "comments"; }
      if($user->edits) { $display[] = "edits"; }
      if($user->parents) { $display[] = "joins"; }
      $count = $this->stream_model->notifications_count($user->userId, 0, $display, "not");
    }
    echo (isset($count) && $count) ? $count : '' ;
  }

  /**
   * Create Headline
   *
   * @access public
   * @param int
   * @return void
   */
  public function article($clusterId = 0)
  {
    $this->form_validation->set_rules('headline', 'Headline', 'trim|required|xss_clean|callback_clean_url|max_length[255]');
    $this->form_validation->set_rules('article', 'Article', 'trim|xss_clean');
    
    if($this->session->userdata('level') == 'a')
    {
      $this->form_validation->set_rules('adminOnly', 'Admin Only', 'trim|xss_clean');
      $this->form_validation->set_rules('hidden', 'Hidden', 'trim|xss_clean');
    }
    
    $this->form_validation->set_rules('place', 'Location', 'trim|xss_clean');
    $this->form_validation->set_rules('placeId', 'Place ID', 'trim|xss_clean');
    $this->form_validation->set_rules('tags', 'Tags', 'trim|xss_clean');
    $this->form_validation->set_rules('image[]', 'Images', 'trim|prep_url|xss_clean');
    $this->form_validation->set_rules('resource[]', 'Links', 'trim|prep_url|xss_clean');
    $this->form_validation->set_rules('categoryId[]', 'Category', 'trim|required|xss_clean');
    if($this->form_validation->run())
    {
      $post = $this->input->post();
      $userId = $this->session->userdata('userId');
      $insert = array(
        'headline' => $this->db->escape_str(preg_replace('/\s+/', ' ', $post['headline'])),
        'article' => $this->db->escape_str(str_replace('\r', '', str_replace('\n', '', $post['article']))),
        'tags' => $this->db->escape_str($this->utility_model->clean_tag_list($post['tags'])),
        'createdBy' => $userId,
        'createdOn' => time(),
        'editedBy' => $userId
      );
        
      if($this->session->userdata('level') == 'a')
      {
        $insert['adminOnly'] = $post['adminOnly'];
        $insert['hidden'] = $post['hidden'];
      }
      
      if($post['placeId']) { $insert['placeId'] = $post['placeId']; }
      elseif($post['place'])
      {
        if($place = $this->database_model->get_single('places', array('place' => $this->db->escape_str($post['place']), 'deleted' => 0))) { $insert['placeId'] = $place->placeId; }
        else { $insert['placeId'] = $this->database_model->add('places', array('place' => $this->db->escape_str($post['place']), 'editedBy' => $userId), 'placeId'); }
      }
      $insert['active'] = 1;
      $id = $this->database_model->add("articles", $insert, "articleId");
      $sInsert = array('articleId' => $id, 'userId' => $userId);
      $subscriptionId = $this->database_model->add('subscriptions', $sInsert+array('createdOn' => time()), 'subscriptionId');
      $this->database_model->add('subscriptions', array('userId' => 3, 'articleId' => $id, 'createdOn' => time()), 'subscriptionId');
      $this->database_model->edit('clusters', array('clusterId' => $clusterId), array('articleId' => $id));
      foreach($post['categoryId'] as $c)
      {
        $this->database_model->add("catlist", array('articleId' => $id, 'categoryId' => $c, 'editedBy' => $userId), 'catlistId');
      }
      $this->utility_model->add_keywords('article', $id, $post['headline'], $post['tags']);
      if(isset($post['image']) && $post['image'])
      {
        foreach($post['image'] as $i)
        {
          if($i !== '') { $this->database_model->add("images", array('articleId' => $id, 'image' => $this->db->escape_str($i), 'editedBy' => $userId), 'imageId'); }
        }
      }
      if(isset($post['resource']) && $post['resource'])
      {
        foreach($post['resource'] as $r)
        {
          if($r !== '') { $this->database_model->add("resources", array('articleId' => $id, 'resource' => $this->db->escape_str($r), 'editedBy' => $userId), 'resourceId'); }
        }
      }
      // Metadata
      $metadata = array('articleId' => $id, 'quality' => 0, 'importance' => 0);
      $metadata['credibility'] = $this->utility_model->credibility('article', $id);
      $this->database_model->add('metadata', $metadata, 'metadataId');
      $this->stream_model->autocompare('article', $id);
      $response['success'] = 1;
      $item_temp = $this->database_model->get_single('articles', array('articleId' => $id, 'deleted' => 0), '*, OLD_PASSWORD(articleId) AS hashId');
      $response['redirect'] = site_url('a/'.$item_temp->hashId);
    }
    elseif($_POST) { $response['errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
    $this->_output_json($response);
  }

  /**
   * Bugs
   *
   * @access public
   * @return void
   */
  function bugs()
  {
    $response = array('success' => 0);
    $this->form_validation->set_rules('url', 'Bug URL', 'trim|required|xss_clean');
    $this->form_validation->set_rules('error', 'Error', 'trim|required|xss_clean');
    $this->form_validation->set_rules('comments', 'Comments', 'trim|required|xss_clean');
    if($this->form_validation->run())
    {
      $post = $this->input->post();

      $this->email->from('no-repy@allrise.co', 'allRise');
      $this->email->to($this->utility_model->get_admin_emails());
      $this->email->subject("Bug: ".time());
      $msg = "URL: ".$post['url']."\n";
      $msg .= "\n";
      $msg .= "Error:\n";
      $msg .= $post['error']."\n";
      $msg .= "\n";
      $msg .= "Comments:\n";
      $msg .= $post['comments']."\n";
      $this->email->message($msg);
      $this->email->send();
      $response['success'] = 1;
      $response['errors'] = "Email sent successfully!";
    }
    else { $response['errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
    $this->_output_json($response);
  }

  /**
   * Comments
   *
   * @access public
   * @param string
   * @return void
   */
  public function comments($action, $commentId = 0)
  {
    $response = array();
    $userId = $this->session->userdata('userId');
    if($action == 'create')
    {
      $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
      $this->form_validation->set_rules('id', 'ID', 'trim|required|xss_clean');
      $this->form_validation->set_rules('comment', 'Comment', 'trim|required|callback_text_clean');
      $this->form_validation->set_rules('subscribe', 'Subscribe', 'trim|xss_clean');
      if($this->form_validation->run())
      {
        $post = $this->input->post();
        $insert = array(
          'comment' => $this->db->escape_str($post['comment']),
          $post['type'].'Id' => $post['id'],
          'createdOn' => time(),
          'createdBy' => $userId,
          'editedBy' => $userId
        );
        $commentId = $this->database_model->add('comments', $insert, 'commentId');
        if(isset($post['subscribe']) && $post['subscribe'])
        {
          $sInsert = array($post['type'].'Id' => $post['id'], 'userId' => $userId);
          if(!$this->database_model->get_single('subscriptions', $sInsert+array('deleted' => 0)))
          {
            $subscriptionId = $this->database_model->add('subscriptions', $sInsert+array('createdOn' => time()), 'subscriptionId');
          }
          elseif($this->database_model->get_single('subscriptions', $sInsert+array('deleted' => 1)))
          {
            $this->database_model->edit('subscriptions', $sInsert, array('deleted' => 0));
          }
        }
        $nInsert = array($post['type'].'Id' => $post['id'], 'commentId' => $commentId, 'createdOn' => time(), 'editedBy' => $this->session->userdata('userId'));
        $subscribers = $this->database_model->get('subscriptions', array($post['type'].'Id' => $post['id'], 'deleted' => 0));
        foreach($subscribers as $s)
        {
          if($s->userId !== $userId)
          {
            $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId');
          }
        }
        $response['prepend'] = array();
        $select = 'c.commentId, u.user, c.createdOn, c.editedOn, c.createdBy, c.comment, u.photo';
        $comment = $this->database_model->get_join('comments c', 'users u', 'c.createdBy = u.userId', array('commentId' => $commentId), $select);
        $response['prepend']['html'] = $this->_comment_html($post['type'], $post['id'], $comment[0]);

        $comment = $this->database_model->get_single('comments', array('commentId' => $commentId));
      }
      else { $response['errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
    }
    elseif($action == 'modify')
    {
      $this->form_validation->set_rules('commentId', 'Comment ID', 'trim|required|xss_clean');
      $this->form_validation->set_rules('comment', 'Comment', 'trim|required|callback_text_clean');
      if($this->form_validation->run())
      {
        $post = $this->input->post();
        $this->data['comment'] = $this->database_model->get_single('comments', array('commentId' => $post['commentId'], 'createdBy' => $userId, 'deleted' => 0));
        if($this->data['comment'])
        {
          $update = array(
            'comment' => $this->db->escape_str($post['comment']),
            'editedBy' => $userId
          );
          $this->database_model->edit('comments', array('commentId' => $post['commentId']), $update);
          $response['replace'] = array();
          $response['replace']['itemid'] = "comment".$post['commentId'];
          $select = 'c.commentId, u.user, c.createdOn, c.editedOn, c.createdBy, c.comment, c.headlineId, c.clusterId, c.articleId, u.photo';
          $comment = $this->database_model->get_join('comments c', 'users u', 'c.createdBy = u.userId', array('commentId' => $post['commentId']), $select);
          if($comment[0]->headlineId) { $type = 'headline'; $id = $comment[0]->headlineId; }
          if($comment[0]->clusterId) { $type = 'cluster'; $id = $comment[0]->clusterId; }
          else { $type = 'article'; $id = $comment[0]->articleId; }
          $response['replace']['html'] = $this->_comment_html($type, $id, $comment[0]);
        }
        else
        {
          $response['errors'] = "No comment found.";
          unset($this->data['comment']);
        }
      }
      else { $response['errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
    }
    elseif($action == 'destroy' && $commentId) { $this->database_model->edit('comments', array('commentId' => $commentId), array('deleted' => 1, 'editedBy' => $userId)); }
    $this->_output_json($response);
  }

  /**
   * Contact
   *
   * @access public
   * @return void
   */
  function contact()
  {
    $response = array('success' => 0);
    $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
    $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean');
    $this->form_validation->set_rules('subject', 'Subject', 'trim|required|xss_clean');
    $this->form_validation->set_rules('message', 'Message', 'trim|required|xss_clean');
    if($this->form_validation->run())
    {
      $post = $this->input->post();
      $this->email->from($post['email'], $post['name']);
      $this->email->to($this->utility_model->get_admin_emails());
      $this->email->subject("Contact: ".$post['subject']);
      $this->email->message($post['message']);
      $this->email->send();
      $response['success'] = 1;
      $response['errors'] = "Email sent successfully!";
    }
    else { $response['errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
    $this->_output_json($response);
  }

  /**
   * Create Headline
   *
   * @access public
   * @param int
   * @return void
   */
  public function headline()
  {
    $this->form_validation->set_rules('headline', 'Headline', 'trim|required|xss_clean|callback_clean_url|max_length[255]');
    $this->form_validation->set_rules('notes', 'Author Notes', 'trim|xss_clean|strip_tags');
    
    if($this->session->userdata('level') == 'a')
    {
      $this->form_validation->set_rules('adminOnly', 'Admin Only', 'trim|xss_clean');
      $this->form_validation->set_rules('hidden', 'Hidden', 'trim|xss_clean');
    }
    
    $this->form_validation->set_rules('place', 'Location', 'trim|xss_clean');
    $this->form_validation->set_rules('placeId', 'Place ID', 'trim|xss_clean');
    $this->form_validation->set_rules('tags', 'Tags', 'trim|xss_clean');
    $this->form_validation->set_rules('image[]', 'Links', 'trim|prep_url|xss_clean');
    $this->form_validation->set_rules('resource[]', 'Links', 'trim|prep_url|xss_clean');
    $this->form_validation->set_rules('categoryId[]', 'Category', 'trim|required|xss_clean');
    if($this->form_validation->run())
    {
      $post = $this->input->post();
      $userId = $this->session->userdata('userId');
      $insert = array(
        'headline' => $this->db->escape_str(preg_replace('/\s+/', ' ', $post['headline'])),
        'notes' => $this->db->escape_str($post['notes']),
        'tags' => $this->db->escape_str($this->utility_model->clean_tag_list($post['tags'])),
        'createdBy' => $userId,
        'createdOn' => time(),
        'editedBy' => $userId
      );
        
      if($this->session->userdata('level') == 'a')
      {
        $update['adminOnly'] = $post['adminOnly'];
        $update['hidden'] = $post['hidden'];
      }
        
      if($post['placeId']) { $insert['placeId'] = $post['placeId']; }
      elseif($post['place'])
      {
        if($place = $this->database_model->get_single('places', array('place' => $this->db->escape_str($post['place']), 'deleted' => 0))) { $insert['placeId'] = $place->placeId; }
        else { $insert['placeId'] = $this->database_model->add('places', array('place' => $this->db->escape_str($post['place']), 'editedBy' => $userId), 'placeId'); }
      }
      $insert['active'] = 1;
      $id = $this->database_model->add("headlines", $insert, "headlineId");
      $item = $this->database_model->get_select('headlines', array('headlineId' => $id), 'OLD_PASSWORD(headlineId) AS hashId');
      $this->utility_model->email_owen("New Headline", "Link: http:".site_url('h/'.$item[0]->hashId));
      $sInsert = array('headlineId' => $id, 'userId' => $userId);
      $subscriptionId = $this->database_model->add('subscriptions', $sInsert+array('createdOn' => time()), 'subscriptionId');
      $this->database_model->add('subscriptions', array('userId' => 3, 'headlineId' => $id, 'createdOn' => time()), 'subscriptionId');
      foreach($post['categoryId'] as $c)
      {
        $this->database_model->add("catlist", array('headlineId' => $id, 'categoryId' => $c, 'editedBy' => $userId), 'catlistId');
      }
      $this->utility_model->add_keywords('headline', $id, $post['headline'], $post['tags']);
      if(isset($post['image']) && $post['image'])
      {
        foreach($post['image'] as $i)
        {
          if($i !== '') { $this->database_model->add("images", array('headlineId' => $id, 'image' => $this->db->escape_str($i), 'editedBy' => $userId), 'imageId'); }
        }
      }
      if(isset($post['resource']) && $post['resource'])
      {
        foreach($post['resource'] as $r)
        {
          if($r !== '') { $this->database_model->add("resources", array('headlineId' => $id, 'resource' => $this->db->escape_str($r), 'editedBy' => $userId), 'resourceId'); }
        }
      }
      // Metadata
      $metadata = array('headlineId' => $id, 'quality' => 0, 'importance' => 0);
      $metadata['credibility'] = $this->utility_model->credibility('headline', $id);
      $this->database_model->add('metadata', $metadata, 'metadataId');
      $this->stream_model->autocompare('headline', $id);
      //
      $response['success'] = 1;
      $h = $this->database_model->get_single('headlines', array('headlineId' => $id, 'deleted' => 0), '*, OLD_PASSWORD(headlineId) AS hashId');
      $response['redirect'] = site_url('h/'.$h->hashId."?n=1");
    }
    elseif($_POST) { $response['errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
    $this->_output_json($response);
  }

  /**
   * Item History
   *
   * @access public
   * @param string
   * @param int
   * @param int
   * @return void
   */
  function history($type = '', $id = 0, $skip = 0)
  {
    $userId = $this->session->userdata('userId');
    $response = array('success' => 0, 'time' => time());
    $where = array($type.'Id' => $id, 'deleted' => 0);
    if(!$skip) { $where['IF((editable = 1), 1, IF(editedOn < '.($response['time']-(1*60)).', 1, IF(editedBy = '.$userId.', 1, 0))) ='] = 1; }
    $item = $this->database_model->get_single($type.'s', $where);
    if($item)
    {
      $where = array($type.'Id' => $id, 'deleted' => 0);
      $this->database_model->edit($type.'s', $where, array('editable' => 0, 'editedBy' => $userId));
      $item->headline = stripslashes($item->headline);
      if($type == 'article') { $item->article = stripslashes(str_replace('\n', '', str_replace('\r', '', $item->article))); }
      $item->tags = stripslashes($item->tags);
      $response['item'] = $item;
      if($item->placeId && $place = $this->database_model->get_single('places', array('placeId' => $item->placeId)))
      {
        //$place->place = stripslashes($place->place);
        //$response['place'] = $place;
      }
      //if($cats = $this->database_model->get_array('catlist', $where, 'categoryId')) { $response['cats'] = $cats; }
      if($images = $this->database_model->get('images', $where))
      {
        foreach($images as $key => $val)
        {
          $images[$key]->image = stripslashes($val->image);
        }
        $response['image'] = $images;
      }
      if($resources = $this->database_model->get('resources', $where))
      {
        foreach($resources as $key => $val)
        {
          $resources[$key]->resource = stripslashes($val->resource);
        }
        $response['resources'] = $resources;
      }
      $response['success'] = 1;
    }
    $this->_output_json($response);
  }

  /**
   * Make Editable Item
   *
   * @access public
   * @param string
   * @param int
   * @return void
   */
  function editable($type = '', $id = 0)
  {
    $where = array($type.'Id' => $id, 'deleted' => 0);
    $this->database_model->edit($type.'s', $where, array('editable' => 1));
  }



  /**
   * Search List
   *
   * @access public
   * @param string
   * @param string
   * @param array
   * @param string
   * @param string
   * @param string
   * @param string
   * @param string
   * @param bool
   * @param int
   * @return void
   */
  function lists($search = "", $where = "", $sort = "score", $results = "all", $comments = true, $limit = 0, $current = 1, $userId = 0, $subscription = 0)
  {
    $this->data['uri'] = '';
    //
    $this->form_validation->set_rules('r', 'Results', 'trim|xss_clean');
    $this->form_validation->set_rules('s', 'Sort', 'trim|xss_clean');
    $this->form_validation->set_rules('b', 'Type', 'trim|xss_clean');
    $this->form_validation->set_rules('k', 'Keywords', 'trim|xss_clean');
    $this->form_validation->set_rules('u', 'user', 'trim|xss_clean');
    $this->form_validation->set_rules('pg', 'Page', 'trim|xss_clean');
    if($this->form_validation->run() && $_POST)
    {
      // Set Variables for Database Search
      $post = $this->input->post();
      if(isset($post['pg']) && $post['pg']) { $current = $post['pg']; }
      if(isset($post['s']) && $post['s']) { $sort = $post['s']; }
      if(isset($post['b']) && $post['b']) { $subscription = $post['b']; $this->data['uri'] .= "&b=".$subscription; }
      if(isset($post['r']) && $post['r']) { $results = $post['r']; $this->data['uri'] .= "&r=".$results; }
      if(isset($post['k']) && $post['k']) { $search = $post['k']; $this->data['uri'] .= "&k=".$search; }
      if(isset($post['u']) && $post['u']) { $userId = $post['u']; $this->data['uri'] .= "&u=".$userId; }
    }
    $this->data['results'] = $results;
    $this->data['sort'] = $sort;
    $this->data['subscription'] = $subscription;
    $this->data['search'] = $search;
    $this->data['userId'] = $userId;
    $this->data['current'] = $current;
    $limit = 20;
    if($subscription) { $userId = $this->session->userdata('userId'); }
    $this->data['items'] = $this->stream_model->search($search, $where, $sort, $results, $comments, $limit, $current, $userId, $subscription);
    $count = $this->stream_model->search_count($search, $where, $sort, $results, $userId, $subscription);
    $this->data['pages'] = ceil(($count->items) / $limit);
    $this->load->view('includes/functions');
    $this->load->view("main/lists", $this->data);
  }

  /**
   * Account Login
   *
   * @access public
   * @return void
   */
  public function login()
  {
    $response = array('success' => 0);
    $this->form_validation->set_rules('login', 'Username / Email', 'trim|required|xss_clean');
    $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
    $this->form_validation->set_rules('redirect', 'Redirect', 'trim|xss_clean');
    if($this->form_validation->run('login'))
    {
      // Check Against User Table
      $post = $this->input->post();
      $where = array(
        'deleted' => 0,
        "(user = '".$this->db->escape_str($post['login'])."' OR email = '".$this->db->escape_str($post['login'])."')" => null
      );
      if($user = $this->database_model->get_single('users', $where))
      {
        if($user->old_pass == $this->utility_model->old_password_encrypt($post['password']))
        {
          $update = array('old_pass' => '', 'password' => $this->utility_model->password_encrypt($user->user, $post['password']));
          $update['editedBy'] = $user->userId;
          $this->database_model->edit('users', array('userId' => $user->userId), $update);
          $user = $this->database_model->get_single('users', $where);
        }
        if($user->password == $this->utility_model->password_encrypt($user->user, $post['password']))
        {
          $this->database_model->edit('users', array('userId' => $user->userId), array('lastLogin' => time(), 'tutorial' => 0));
          // Save Session Info
          $s = array(
            'isLoggedIn' => true,
            'userId' => $user->userId,
            'user' => $user->user,
            'score' => $user->score,
            'level' => $user->level,
            'confirmed' => $user->confirmed,
            'location' => $user->location,
            'bio' => $user->bio,
            'email' => $user->email,
            'notices' => $user->notices
          );
          $this->session->set_userdata($s);
          $this->session->set_flashdata('tutorial', $user->tutorial);
          $response['success'] = 1;
          $response['redirect'] = site_url($post['redirect']);
        }
        else { $response['errors'] = 'Password did not match account.'; }
      }
      else { $response['errors'] = 'Username/Email could not be found.'; }
    }
    else { $response['errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
    $this->_output_json($response);
  }

  /**
   * Join Items
   *
   * @access public
   * @param array
   * @return void
   */
  public function join($uri = array())
  {
    $this->form_validation->set_rules('headline[]', 'Headlines', 'trim|xss_clean');
    $this->form_validation->set_rules('cluster[]', 'Clusters', 'trim|xss_clean');
    $this->form_validation->set_rules('article[]', 'Articles', 'trim|xss_clean');
    $this->form_validation->set_rules('uri', 'URI', 'trim|xss_clean');
    if($this->form_validation->run())
    {
      $post = $this->input->post();
      $items = array(
        'headline' => isset($post['headline'])?$post['headline']:array(),
        'cluster' => isset($post['cluster'])?$post['cluster']:array(),
        'article' => isset($post['article'])?$post['article']:array()
      );
      $this->stream_model->manual_join($items);
      if(isset($post['uri'])) { parse_str($post['uri'], $uri); }
    }
    if(!isset($uri['r'])) { $uri['r'] = 'all'; }
    if(!isset($uri['s'])) { $uri['s'] = 'score'; }
    if(!isset($uri['k'])) { $uri['k'] = ''; }
    $this->lists($uri['k'], array(), $uri['s'], $uri['r']);
  }

  /**
   * Get Places from String
   *
   * @access public
   * @param string
   * @param string
   * @return json
   */
  public function _modify($type = '', $id = '')
  {
    if(in_array($type, array('article', 'cluster', 'headline')) && $item = $this->database_model->get_single($type.'s', array($type.'Id' => $id, 'deleted' => 0)))
    {
      $this->form_validation->set_rules('headline', 'Headline', 'trim|required|xss_clean|callback_clean_url');
      if($type == 'article')
      {
        $this->form_validation->set_rules('article', 'Article', 'trim|xss_clean');
      }
      $this->form_validation->set_rules('place', 'Location', 'trim|xss_clean');
      $this->form_validation->set_rules('placeId', 'Place ID', 'trim|xss_clean');
      $this->form_validation->set_rules('categoryId[]', 'Category', 'trim|required|xss_clean');
      $this->form_validation->set_rules('tags', 'Tags', 'trim|xss_clean');
      $this->form_validation->set_rules('resource[]', 'Links', 'trim|prep_url|xss_clean');
      if($this->form_validation->run())
      {
        // Set Variables for Display
        $org_place = $this->database_model->get_single('places', array('placeId' => $item->placeId, 'deleted' => 0));
        $org_cats = $this->database_model->get_array('catlist', array($type.'Id' => $id, 'deleted' => 0), 'categoryId');
        $org_resources = $this->database_model->get_array('resources', array($type.'Id' => $id, 'deleted' => 0), 'resource');
        $userId = $this->session->userdata('userId');
        if(!isset($post['resource'])) { $post['resource'] = array(); }
        $update = array(
          'headline' => $this->db->escape_str($post['headline']),
          'tags' => $this->db->escape_str($this->utility_model->clean_tag_list($post['tags'])),
          'editedBy' => $userId
        );
        if($org_place && $post['placeId'] == $org_place->placeId)
        {
          if(empty($post['place'])) { $update['placeId'] = null; }
          elseif($post['place'] !== $org_place->place)
          {
            if($place = $this->database_model->get_single('places', array('place' => $this->db->escape_str($post['place']), 'deleted' => 0))) { $update['placeId'] = $place->placeId; }
            else { $update['placeId'] = $this->database_model->add('places', array('place' => $this->db->escape_str($post['place']), 'editedBy' => $userId), 'placeId'); }
          }
        }
        else
        {
          if($post['placeId']) { $update['placeId'] = $post['placeId']; }
          elseif(!empty($post['place']))
          {
            if($place = $this->database_model->get_single('places', array('place' => $this->db->escape_str($post['place']), 'deleted' => 0))) { $update['placeId'] = $place->placeId; }
            else { $update['placeId'] = $this->database_model->add('places', array('place' => $this->db->escape_str($post['place']), 'editedBy' => $userId), 'placeId'); }
          }
        }
        // Update Proper Table
        if($type == 'article') { $update['article'] = $post['article']; }
        $update['active'] = 1;
        $this->database_model->edit($type.'s', array($type.'Id' => $id), $update);
        $this->utility_model->keywords($type, $id, $post['headline'], $post['tags']);
        if($type == 'cluster' && !$item->articleId) { $this->stream_model->autocompare($type, $id); }
        elseif($type == 'headline' && !$item->clusterId) { $this->stream_model->autocompare($type, $id); }
        // Edit Categories
        $delete = array('active' => 0, 'deleted' => 1, 'editedBy' => $userId);
        $undelete = array('active' => 1, 'deleted' => 0, 'editedBy' => $userId);
        foreach($org_cats as $cat)
        {
          if(!in_array($cat, $post['categoryId']))
          {
            $where = array($type.'Id' => $id, 'categoryId' => $cat);
            $cats = $this->database_model->get_single('catlist', $where);
            $this->database_model->edit('catlist', array('catlistId' => $cats->catlistId), $delete);
          }
        }
        foreach($post['categoryId'] as $cat)
        {
          if(!in_array($cat, $org_cats))
          {
            $where = array($type.'Id' => $id, 'categoryId' => $cat);
            $cats = $this->database_model->get_single('catlist', $where);
            if($cats) { $this->database_model->edit('catlist', array('catlistId' => $cats->catlistId), $undelete); }
            else { $this->database_model->add('catlist', $where+array('editedBy' => $userId), 'catlistId'); }
          }
        }
        // Edit Resources
        if($org_resources)
        {
          foreach($org_resources as $resource)
          {
            if(!in_array($resource, $post['resource']))
            {
              $where = array($type.'Id' => $id, 'resource' => $this->db->escape_str($resource));
              $resources = $this->database_model->get_single('resources', $where);
              $this->database_model->edit('resources', array('resourceId' => $resources->resourceId), $delete);
            }
          }
        }
        if(isset($post['resource']) && $post['resource'])
        {
          foreach($post['resource'] as $resource)
          {
            if(!in_array($resource, $org_resources))
            {
              $where = array($type.'Id' => $id, 'resource' => $this->db->escape_str($resource));
              $resources = $this->database_model->get_single('resources', $where);
              if($resources) { $this->database_model->edit('resources', array('resourceId' => $resources->resourceId), $undelete); }
              else { $this->database_model->add('resources', $where+array('editedBy' => $userId), 'resourceId'); }
            }
          }
        }
        $this->utility_model->metadata($type, $id);
        $response['errors'] = ucfirst($type).' has been updated!';
      }
      else { $response['errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
    }
    else { $response['errors'] = 'An error has occurred. Please try again.'; }
    $this->_output_json($response);
  }

  /**
   * Get Places from String
   *
   * @access public
   * @return json
   */
  public function places()
  {
    $this->form_validation->set_rules('place', 'Location', 'trim|required|xss_clean');
    $response = array();
    if($this->form_validation->run())
    {
      // Loop Through Matching Locations
      $places = $this->database_model->get('places', array('place LIKE' => '%'.$this->input->post('place').'%', 'deleted' => 0));
      foreach($places as $l) { $response[] = array('value' => $l->placeId, 'label' => $l->place); }
      if(!$response) { $response[] = array('value' => 0, 'label' => 'No Locations Found...'); }
    }
    else { $response[] = array('value' => 0, 'label' => 'An error has occured...'); }
    $this->_output_json($response);
  }

  public function notifications($limit = 20, $current = 1)
  {
    $userId = $this->session->userdata('userId');
    $this->form_validation->set_rules('edits', 'Item Updates', 'trim|required|xss_clean');
    $this->form_validation->set_rules('parents', 'Item Joins', 'trim|required|xss_clean');
    $this->form_validation->set_rules('comments', 'Item Comments', 'trim|required|xss_clean');
    if($this->form_validation->run() && $_POST)
    {
      // Set Variables for Database Search
      $post = $this->input->post();
      $this->database_model->edit('users', array('userId' => $userId), $post);
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
    $this->load->view('includes/functions');
    $this->load->view('main/notifications', $this->data);
  }

  /**
   * Account Preferences
   *
   * @access public
   * @return void
   */
  public function preferences()
  {
    // Vars
    $where = array('userId' => $this->session->userdata('userId'));
    $this->data['user'] = $this->database_model->get_single('users', $where);
    // Validate
    $this->form_validation->set_rules('location', 'Location', 'trim|max_length[255]|xss_clean');
    $this->form_validation->set_rules('bio', 'Biography', 'trim|max_length[65535]|xss_clean');
    $this->form_validation->set_rules('notices', 'Email Notifications', 'trim|required|xss_clean');
    $this->form_validation->set_rules('password', 'Current Password', 'trim|xss_clean');
    $this->form_validation->set_rules('email', 'Email', 'trim|required|min_length[6]|max_length[255]|valid_email|xss_clean');
    $this->form_validation->set_rules('new_password', 'New Password', 'trim|min_length[8]|max_length[50]|xss_clean|password_check');
    $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|xss_clean|matches[new_password]');
    if($this->form_validation->run())
    {
      $post = $this->input->post();
      $update = array('location' => $post['location'], 'bio' => $post['bio'], 'editedBy' => $this->data['user']->userId, 'notices' => $post['notices']);

      if(($post['email'] !== $this->data['user']->email || $post['new_password']))
      {
        if($this->utility_model->password_encrypt($this->data['user']->user, $post['password']) == $this->data['user']->password)
        {
          if($post['email'] !== $this->data['user']->email)
          {
            $email_where = array('email' => $post['email'],'userId !=' => $this->session->userdata('userId'));
            if(!$this->database_model->get_single('users', $email_where))
            {
              $update['confirmed'] = 0;
              $update['email'] = $post['email'];
            }
            else { $response['errors'] = "Email already exists in our system."; }
          }
          if(!isset($response['errors']) && $post['new_password'])
          {
            $update['password'] = $this->utility_model->password_encrypt($this->data['user']->user, $post['new_password']);
          }
        }
        else { $response['errors'] = "Current Password does not match password in the system."; }
      }
      if(!isset($response['errors']))
      {
        $this->database_model->edit('users', $where, $update);
        $this->data['user'] = $this->database_model->get_single('users', $where);
        if(isset($update['confirmed'])) { $this->utility_model->emails_confirmation($this->data['user']); }
        $s = array(
          'location' => $this->data['user']->location,
          'bio' => $this->data['user']->bio,
          'email' => $this->data['user']->email,
          'notices' => $this->data['user']->notices
        );
        $this->session->set_userdata($s);
        $response['errors'] = "Account has been updated!";
      }
    }
    elseif($_POST) { $response['errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
    $this->_output_json($response);
  }

  /**
   * Account Registration
   *
   * @access public
   * @return void
   */
  public function register()
  {
    $response = array('success' => 0);
    $this->form_validation->set_rules('user', 'Username', 'trim|required|min_length[3]|max_length[24]|alpha_dash|xss_clean|is_unique[users.user]');
    $this->form_validation->set_rules('email', 'Email', 'trim|required|min_length[6]|max_length[255]|valid_email|xss_clean|is_unique[users.email]');
    $this->form_validation->set_rules('rgpassword', 'Password', 'trim|required|min_length[8]|max_length[50]|xss_clean'); //password_check
    $this->form_validation->set_rules('redirect', 'Redirect', 'trim|xss_clean');
    if($this->form_validation->run())
    {
      // Create User
      $post = $this->input->post();
      $insert = array('user' => $this->db->escape_str($post['user']), 'email' => $this->db->escape_str($post['email']));
      $insert['createdOn'] = time();
      $insert['password'] = $this->utility_model->password_encrypt($post['user'], $post['rgpassword']);
      $userId = $this->database_model->add('users', $insert, 'userId');
      if($user = $this->database_model->get_single('users', array('userId' => $userId)))
      {
        $this->utility_model->email_owen("New User", "Username: ".$user->user);
        //
        $this->database_model->edit('users', array('userId' => $user->userId), array('lastLogin' => time()));
        $this->utility_model->emails_signup($user);
        $s = array(
          'isLoggedIn' => true,
          'userId' => $user->userId,
          'user' => $user->user,
          'score' => $user->score,
          'level' => $user->level,
          'confirmed' => $user->confirmed,
          'location' => $user->location,
          'bio' => $user->bio,
          'email' => $user->email
        );
        $this->session->set_userdata($s);
        $response['success'] = 1;
        $response['redirect'] = site_url($post['redirect']);
      }
      else { $response['errors'] = '<p>An error happened creating your account.</p>'; }
    }
    else { $response['errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
    $this->_output_json($response);
  }

  /**
   * Item Update
   *
   * @access public
   * @param string
   * @param int
   * @return void
   */
  function modify($type = '', $id = 0)
  {
    $response = array('success' => 0);
    $item = (array) $this->database_model->get_single($type.'s', array($type.'Id' => $id, 'deleted' => 0));
    $place = (array) $this->database_model->get_single('places', array('placeId' => $item['placeId'], 'deleted' => 0));
    $cats = (array) $this->database_model->get_array('catlist', array($type.'Id' => $id, 'active' => 1, 'deleted' => 0), 'categoryId');
    $images = (array) $this->database_model->get_array('images', array($type.'Id' => $id, 'active' => 1, 'deleted' => 0), 'image');
    $resources = (array) $this->database_model->get_array('resources', array($type.'Id' => $id, 'active' => 1, 'deleted' => 0), 'resource');

    $this->form_validation->set_rules('headline', 'Headline', 'trim|required|xss_clean|callback_clean_url|max_length[255]');
    if($type == 'headline')
    {
      $this->form_validation->set_rules('notes', 'Author Notes', 'trim|xss_clean|strip_tags');
    }
    if($type == 'article')
    {
      $this->form_validation->set_rules('article', 'Article', 'trim|xss_clean');
    }
    if($this->session->userdata('level') == 'a')
    {
      $this->form_validation->set_rules('adminOnly', 'Admin Only', 'trim|xss_clean');
      $this->form_validation->set_rules('hidden', 'Hidden', 'trim|xss_clean');
    }
    $this->form_validation->set_rules('place', 'Location', 'trim|xss_clean');
    $this->form_validation->set_rules('placeId', 'Place ID', 'trim|xss_clean');
    $this->form_validation->set_rules('tags', 'Tags', 'trim|xss_clean');
    $this->form_validation->set_rules('image[]', 'Links', 'trim  |prep_url|xss_clean');
    $this->form_validation->set_rules('remove-image[]', 'Links', 'trim  |prep_url|xss_clean');
    $this->form_validation->set_rules('resource[]', 'Links', 'trim  |prep_url|xss_clean');
    $this->form_validation->set_rules('remove-resource[]', 'Links', 'trim  |prep_url|xss_clean');
    $this->form_validation->set_rules('categoryId[]', 'Category', 'trim|required|xss_clean');
    if($this->form_validation->run())
    {
      $post = $this->input->post();
      $userId = $this->session->userdata('userId');
      $item['headline'] = $this->db->escape_str(preg_replace('/\s+/', ' ', $post['headline']));
      $item['tags'] = $this->db->escape_str($this->utility_model->clean_tag_list($post['tags']));
      $item['editedBy'] = $userId;
      $item['active'] = 1;
      if($this->session->userdata('level') == 'a')
      {
        $item['adminOnly'] = $post['adminOnly'];
        $item['hidden'] = $post['hidden'];
      }
      if($type == 'headline') { $item['notes'] = $this->db->escape_str($post['notes']); }
      if($type == 'article') { $item['article'] = $this->db->escape_str(str_replace("\r", '', str_replace("\n", '', $post['article']))); }
      if($place && $post['placeId'] == $place['placeId'])
      {
        if(empty($post['place'])) { $update['placeId'] = null; }
        elseif($post['place'] !== $place['place'])
        {
          if($place_temp = $this->database_model->get_single('places', array('place' => $post['place'], 'deleted' => 0))) { $update['placeId'] = $place_temp->placeId; }
          else { $update['placeId'] = $this->database_model->add('places', array('place' => $post['place'], 'editedBy' => $userId), 'placeId'); }
        }
      }
      else
      {
        if($post['placeId']) { $update['placeId'] = $post['placeId']; }
        elseif(!empty($post['place']))
        {
          if($place_temp = $this->database_model->get_single('places', array('place' => $post['place'], 'deleted' => 0))) { $update['placeId'] = $place_temp->placeId; }
          else { $update['placeId'] = $this->database_model->add('places', array('place' => $post['place'], 'editedBy' => $userId), 'placeId'); }
        }
      }
      $this->database_model->edit($type.'s', array($type.'Id' => $id), $item);
      $this->utility_model->keywords($type, $id, $post['headline'], $post['tags']);

      $sInsert = array($type.'Id' => $id, 'userId' => $userId);
      if(!$this->database_model->get_single('subscriptions', $sInsert))
      {
        $subscriptionId = $this->database_model->add('subscriptions', $sInsert+array('createdOn' => time()), 'subscriptionId');
      }
      $nInsert = array($type.'Id' => $id, 'edited' => 1, 'createdOn' => time(), 'editedBy' => $this->session->userdata('userId'));
      $subscribers = $this->database_model->get('subscriptions', array($type.'Id' => $id, 'deleted' => 0));
      foreach($subscribers as $s)
      {
        if($s->userId !== $userId)
        {
          $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId');
        }
      }
      // Edit Categories
      $delete = array('deleted' => 1, 'editedBy' => $userId, 'active' => 0);
      $undelete = array('deleted' => 0, 'editedBy' => $userId, 'active' => 1);
      foreach($cats as $cat)
      {
        if(!in_array($cat, $post['categoryId']))
        {
          $where = array($type.'Id' => $id, 'categoryId' => $cat);
          $cat_s = $this->database_model->get_single('catlist', $where);
          $this->database_model->edit('catlist', array('catlistId' => $cat_s->catlistId), $delete);
        }
      }
      foreach($post['categoryId'] as $cat)
      {
        if(!in_array($cat, $cats))
        {
          $where = array($type.'Id' => $id, 'categoryId' => $cat);
          $cat_s = $this->database_model->get_single('catlist', $where);
          if($cat_s) { $this->database_model->edit('catlist', array('catlistId' => $cat_s->catlistId), $undelete); }
          else { $this->database_model->add('catlist', $where+array('editedBy' => $userId, 'active' => 1), 'catlistId'); }
        }
      }
      // Edit Images
      if(isset($post['image']) && $post['image'])
      {
        foreach($post['image'] as $imageId => $image)
        {
          $this->database_model->edit('images', array('imageId' => $imageId), array('image' => $this->db->escape_str($image), 'editedBy' => $userId));
        }
      }
      if(isset($post['add-image']) && $post['add-image'])
      {
        foreach($post['add-image'] as $image)
        {
          $this->database_model->add('images', array($type.'Id' => $id, 'image' => $this->db->escape_str($image), 'editedBy' => $userId, 'active' => 1), 'imageId');
        }
      }
      if(isset($post['remove-image']) && $post['remove-image'])
      {
        foreach($post['remove-image'] as $imageId => $image)
        {
          $this->database_model->edit('images', array('imageId' => $imageId), $delete);
        }
      }
      // Edit Resources
      if(isset($post['resource']) && $post['resource'])
      {
        foreach($post['resource'] as $resourceId => $resource)
        {
          $this->database_model->edit('images', array('resourceId' => $resourceId), array('resource' => $this->db->escape_str($resource), 'editedBy' => $userId));
        }
      }
      if(isset($post['add-resource']) && $post['add-resource'])
      {
        foreach($post['add-resource'] as $resource)
        {
          $this->database_model->add('images', array($type.'Id' => $id, 'resource' => $this->db->escape_str($resource), 'editedBy' => $userId, 'active' => 1), 'resourceId');
        }
      }
      if(isset($post['remove-resource']) && $post['remove-resource'])
      {
        foreach($post['remove-resource'] as $resourceId => $resource)
        {
          $this->database_model->edit('resources', array('resourceId' => $resourceId), $delete);
        }
      }
      //
      $this->utility_model->metadata($type, $id);
    }
    elseif($_POST)
    {
      $this->data['errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.';
    }
    $item_temp = $this->database_model->get_select($type.'s', array($type.'Id' => $id), 'OLD_PASSWORD('.$type.'Id) AS hashId');
    $response['redirect'] = site_url(substr($type, 0, 1).'/'.$item_temp[0]->hashId);
    //$response['errors'] = 'Update complete!';
    $this->_output_json($response);
  }

  /**
   * Load Page
   *
   * @access public
   * @param string
   * @return void
   */
  public function page($target = '', $limit = 20, $current = 1)
  {
    $response = array();
    if($target == 'notices')
    {
      $user = $this->database_model->get_single('users', array('userId' => $this->session->userdata('userId')));
      if($user->comments) { $display[] = "comments"; }
      if($user->edits) { $display[] = "edits"; }
      if($user->parents) { $display[] = "joins"; }
      $response['notices'] = $this->stream_model->notifications($user->userId, 0, $display, $limit, $current);
    }
    $this->load->view('includes/functions');
    $this->load->view('main/'.$target, $response);
  }

  /**
   * Reset Password
   *
   * @access public
   * @return void
   */
  public function password()
  {
    $response = array('success' => 0);
    $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean');
    if($this->form_validation->run('password'))
    {
      $post = $this->input->post();
      $where = array('email' => $this->db->escape_str($post['email']), 'deleted' => 0);
      $user = $this->database_model->get_single('users', $where);
      if($user)
      {
        $password = $this->utility_model->generate_password();
        $update = array('password' => $this->utility_model->password_encrypt($user->user, $password));
        $update['editedBy'] = null;
        $this->database_model->edit('users', array('userId' => $user->userId), $update);
        if($this->utility_model->emails_password($user, $password))
        {
          $response['errors'] = "An email has been sent to the user.";
        }
        else { $response['errors'] = "An error has occurred."; }
      }
      else { $response['errors'] = "No user could be found."; }
    }
    else { $response['errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
    $this->_output_json($response);
  }

  public function viewed()
  {
    $this->database_model->edit('notices', array('userId' => $this->session->userdata('userId')), array('viewed' => 1), true);
  }

  /**
   * Item Vote
   *
   * @access public
   * @param string
   * @param string
   * @param string
   * @param int
   * @return void
   */
  public function vote($target = '', $dir = '', $type = '', $id = 0)
  {
    $userId = $this->session->userdata('userId');
    $this->data['id'] = $id;
    $this->data['type'] = $type;
    $this->data['ranking'] = $this->database_model->get_single('rankings', array($type.'Id' => $id, 'createdBy' => $userId));
    $this->data['cats'] = $this->database_model->get_array('catlist', array($type.'Id' => $id, 'deleted' => 0), 'categoryId');
    $this->data['contributors'] = $this->stream_model->get_contributors($type, $id);
    $this->$target($dir);
  }

  /**
   * Manage Importance
   *
   * @access private
   * @param string
   * @return void
   */
  private function importance($action)
  {
    $userId = $this->session->userdata('userId');
    if($this->data['ranking'])
    {
      if($action == 'up')
      {
        $update['iPositive'] = 1;
        $update['iNegative'] = 0;
      }
      else
      {
        $update['iPositive'] = 0;
        $update['iNegative'] = 1;
      }
      $update['editedBy'] = $userId;
      $this->database_model->edit('rankings', array('rankingId' => $this->data['ranking']->rankingId), $update);
    }
    else
    {
      $insert = array($this->data['type'].'Id' => $this->data['id']);
      if($action == 'up') { $insert['iPositive'] = 1; }
      else { $insert['iNegative'] = 1; }
      $insert['createdBy'] = $userId;
      $insert['editedBy'] = $userId;
      $this->database_model->add('rankings', $insert, 'rankingId');
    }
    $this->data['ranking'] = $this->database_model->get_single('rankings', array($this->data['type'].'Id' => $this->data['id'], 'createdBy' => $userId));
    $this->score();
  }

  /**
   * Manage Quality
   *
   * @access private
   * @param string
   * @return void
   */
  private function quality($action)
  {
    $userId = $this->session->userdata('userId');
    if($this->data['ranking'])
    {
      if($action == 'up')
      {
        $update['qPositive'] = 1;
        $update['qNegative'] = 0;
      }
      else
      {
        $update['qPositive'] = 0;
        $update['qNegative'] = 1;
      }
      $update['editedBy'] = $userId;
      $this->database_model->edit('rankings', array('rankingId' => $this->data['ranking']->rankingId), $update);
    }
    else
    {
      $insert = array($this->data['type'].'Id' => $this->data['id']);
      if($action == 'up') { $insert['qPositive'] = 1; }
      else { $insert['qNegative'] = 1; }
      $insert['createdBy'] = $userId;
      $insert['editedBy'] = $userId;
      $this->database_model->add('rankings', $insert, 'rankingId');
    }
    $this->data['ranking'] = $this->database_model->get_single('rankings', array($this->data['type'].'Id' => $this->data['id'], 'createdBy' => $userId));
    $this->score();
  }

  /**
   * User Score Update
   *
   * @access private
   * @return void
   */
  private function score()
  {
    foreach($this->data['contributors'] as $user)
    {
      $score = $this->stream_model->get_score_by_user($user->userId);
      $update = array('score' => (($score->q_score_sum + $score->i_score_sum) / $score->total_items));
      $this->database_model->edit('users', array('userId' => $user->userId), $update);
      foreach($this->data['cats'] as $c)
      {
        $cat_score = $this->stream_model->get_score_by_user($user->userId, $c);
        $data = array('score' => (($cat_score->q_score_sum + $cat_score->i_score_sum) / $cat_score->total_items));
        $where = array('userId' => $user->userId, 'categoryId' => $c);
        if($item = $this->database_model->get_single('scores', $where)) { $this->database_model->edit('scores', array('scoreId' => $item->scoreId), $data); }
        else { $this->database_model->add('scores', $where+$data, 'scoreId'); }
      }
    }
  }

  /**
   * Comment HTML
   *
   * @access private
   * @param string
   * @param string
   * @param string
   * @return void
   */
  private function _comment_html($type = "", $id = "", $comment = "")
  {
    $return = '<article class="horizontal-padding-xsmall vertical-padding-xsmall" id="comment'.$comment->commentId.'">';
      if($comment->photo) { $return .= '<img src="'.site_url('uploads/users/'.$comment->photo).'" alt="'.$comment->user.'" width="40" class="left-align right-padding-xsmall">'; }
      else { $return .= '<img src="'.site_url('media/img/no-image.gif').'" alt="'.$comment->user.'" width="40" class="left-align right-padding-xsmall">'; }
      $return .= '<header>';
        $return .= '<a href="'.site_url('user/'.$comment->user).'" class="grey">'.$comment->user.'</a> | ';
        $return .= '<time class="grey">';
        if($comment->editedOn > strtotime(date("m/d/Y"))) { $return .= "Today at ".date("h:ia", $comment->editedOn); }
        elseif($comment->editedOn > strtotime(date("m/d/Y", strtotime("-1 day")))) { $return .= "Yesterday at ".date("h:ia", $comment->editedOn); }
        elseif(date("Y", $comment->editedOn) == date("Y")) { $return .= date("F dS", $comment->editedOn)." at ".date("h:ia", $comment->editedOn); }
        else { $return .= date("F dS Y", $comment->editedOn); }
        $return .= '</time>';
        $return .= '<span class="right-align">';
          if($comment->createdBy == $this->session->userdata('userId'))
          {
            $return .= '<a href="'.site_url($type.'/'.$id.'?comments=modify&commentId='.$comment->commentId).'"><i class="fa fa-pencil-square-o"></i> Modify</a>';
          }
          if(($comment->createdBy == $this->session->userdata('userId')) || in_array($this->session->userdata('level'), array('m', 'a')))
          {
            if($comment->createdBy == $this->session->userdata('userId')) { $return .= ' | '; }
            $return .= '<a href="'.site_url($type.'/'.$id.'?comments=destroy&commentId='.$comment->commentId).'" class="delete"><i class="fa fa-trash-o"></i> Destroy</a>';
          }
        $return .= '</span>';
      $return .= '</header>';
      $reg_exUrl = "/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
      $text = stripslashes($comment->comment);
      if(preg_match_all($reg_exUrl, $text, $url))
      {
        foreach($url[0] as $u) { $text = str_replace($u, "<a href='$u' target='_blank' rel='nofollow'>$u</a>", $text); }
      }
      $return .= '<span class="text">'.$text.'</span>';
    $return .= '</article>';
    return $return;
  }

  /**
   * Output JSON
   *
   * @access private
   * @param array
   * @return void
   */
  private function _output_json($data = array())
  {
    // Output JSON
    $this->output->set_content_type('application/json');
    die(json_encode($data));
  }

  function text_clean($string)
  {
    return filter_var($string, FILTER_SANITIZE_STRING);
  }
}

/* End of file ajax.php */
/* Location: ./application/controllers/ajax.php */