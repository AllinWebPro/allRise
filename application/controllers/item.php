<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Item extends CI_Controller
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

  /**
   * Item Details
   *
   * @access public
   * @param string
   * @param int
   * @return void
   */
  public function index($type = '', $id = 0, $sort = "score", $results = "all", $comments = true, $limit = 20, $current = 1, $userId = 0, $subscription = 0)
  {
    // Grab Data by Type
    $this->data['type'] = $type;
    $this->data['id'] = $id;
    $this->data['item'] = $this->database_model->get_single($type.'s', array($type.'Id' => $id, 'deleted' => 0), '*, OLD_PASSWORD('.$type.'Id) AS hashId');
    // Get Data
    if(isset($this->data['item']) && $this->data['item'])
    {
      // $this->data['related'] = $this->stream_model->search($this->data['item']->tags, '(id != '.$id.' AND type != "'.$type.'")', 'score', $type.'s', true, 3);
      // Views
      if($this->session->userdata('isLoggedIn'))
      {
        $views = array($type.'Id' => $id, 'userId' => $this->session->userdata('userId'));
        if($this->database_model->get_single('notices', $views)) { $this->database_model->edit('notices', $views, array('viewed' => 1), true); }
        if(!$this->database_model->get_single('views', $views)) { $this->database_model->add('views', $views); }
        if(isset($_REQUEST['subscribe']))
        {
          if($_REQUEST['subscribe'] && !$this->database_model->get_single('subscriptions', $views))
          {
            $subscriptionId = $this->database_model->add('subscriptions', $views+array('createdOn' => time()), 'subscriptionId');
          }
          elseif($_REQUEST['subscribe'] && $this->database_model->get_single('subscriptions', $views+array('deleted' => 1)))
          {
            $this->database_model->edit('subscriptions', $views, array('deleted' => 0));
          }
          elseif(!$_REQUEST['subscribe'] && $this->database_model->get_single('subscriptions', $views+array('deleted' => 0)))
          {
            $this->database_model->edit('subscriptions', $views, array('deleted' => 1));
          }
        }
        $this->data['subscription'] = $this->database_model->get_single('subscriptions', $views+array('deleted' => 0));
      }
      // Right Sidebar Content
      $assets_where = array($type.'Id' => $id);
      $this->data['favorite'] = $this->database_model->get_single('favorites', $assets_where+array('editedBy' => $this->session->userdata('userId'), 'deleted' => 0));
      if($this->data['item']->placeId)
      {
        //$this->data['place'] = $this->database_model->get_single('places', array('placeId' => $this->data['item']->placeId));
      }
      // $this->data['cats'] = $this->database_model->get_array('catlist', $assets_where+array('active' => 1, 'deleted' => 0), 'categoryId');
      $this->data['images'] = $this->database_model->get('images', $assets_where+array('active' => 1, 'deleted' => 0));
      $this->data['resources'] = $this->database_model->get('resources', $assets_where+array('active' => 1, 'deleted' => 0));
      $select = 'c.commentId, u.user, c.createdOn, c.editedOn, c.createdBy, c.comment, u.photo';
      $comment_where = array($type.'Id' => $id);
      if(isset($_GET['hId']))
      {
        $history_time = $this->data['item']->editedOn;
        $comment_where['c.createdOn <'] = $history_time;
        $comment_where['IF(c.deleted = 1, IF(c.editedOn > '.$history_time.', 1, 0), 1) ='] = 1;
      }
      $this->data['comments'] = $this->database_model->get_join('comments c', 'users u', 'c.createdBy = u.userId', $comment_where+array('c.deleted' => 0, 'u.deleted' => 0), $select, false, 'commentId', 'DESC');
      // Get Sub Content
      $this->data['contributors'] = $this->stream_model->get_contributors($type, $id);
      $this->data['ranking'] = $this->database_model->get_single('rankings', array($type.'Id' => $id, 'createdBy' => $this->session->userdata('userId')));
      $this->utility_model->metadata($type, $id);
      // Actions
      if(isset($_GET['comments']))
      {
        $this->comments($_GET['comments'], isset($_GET['commentId'])?$_GET['commentId']:0);
        $this->data['comments'] = $this->database_model->get_join('comments c', 'users u', 'c.createdBy = u.userId', $comment_where+array('c.deleted' => 0, 'u.deleted' => 0), $select, false, 'commentId', 'DESC');
      }
      if(isset($_GET['importance'])) { $this->importance($_GET['importance']); }
      if(isset($_GET['quality'])) { $this->quality($_GET['quality']); }
      if(isset($_GET['favorite'])) { $this->favorite($_GET['favorite']); }
      if(isset($_GET['importance']) || isset($_GET['quality']) || isset($_GET['favorite']))
      {
        $this->utility_model->metadata($type, $id);
        redirect($type.'/'.$id);
      }
      //
      if($type !== 'headline')
      {
        $this->data['headlines'] = array();
        $this->data['h_resources'] = array();
        $this->data['h_contributors'] = array();
        $this->data['h_comments'] = array();
        if($type == 'cluster')
        {
          $headlines_where = array('s.clusterId' => $id, 's.deleted' => 0);
          $this->data['headlines'] = $this->database_model->get_join('headlines s', '', '', $headlines_where, '*, OLD_PASSWORD(s.headlineId) AS hashId');
          foreach($this->data['headlines'] as $h)
          {
            $this->data['h_resources'][$h->headlineId] = $this->database_model->get('resources', array('headlineId' => $h->headlineId, 'active' => 1, 'deleted' => 0));
            $this->data['h_contributors'][$h->headlineId] = $this->stream_model->get_contributors('headline', $h->headlineId);
            $this->data['h_comments'][$h->headlineId] = $this->database_model->get_count('comments', array('headlineId' => $h->headlineId, 'deleted' => 0), 'headlineId');
          }
        }
        else
        {
          $clusters_where = array('s.articleId' => $id, 's.deleted' => 0);
          $this->data['clusters'] = $this->database_model->get_join('clusters s', '', '', $clusters_where, '*, OLD_PASSWORD(s.clusterId) AS hashId');
          $this->data['c_resources'] = array();
          $this->data['c_contributors'] = array();
          $this->data['c_comments'] = array();
          foreach($this->data['clusters'] as $c)
          {
            $this->data['c_resources'][$c->clusterId] = $this->database_model->get('resources', array('clusterId' => $c->clusterId, 'active' => 1, 'deleted' => 0));
            $this->data['c_contributors'][$c->clusterId] = $this->stream_model->get_contributors('cluster', $c->clusterId);
            $this->data['c_comments'][$c->clusterId] = $this->database_model->get_count('comments', array('clusterId' => $c->clusterId, 'deleted' => 0), 'headlineId');
          }
          foreach($this->data['clusters'] as $c)
          {
            $headlines_where = array('s.clusterId' => $c->clusterId, 's.deleted' => 0);
            $this->data['headlines'][$c->clusterId] = $this->database_model->get_join('headlines s', '', '', $headlines_where, '*, OLD_PASSWORD(s.headlineId) AS hashId');
            foreach($this->data['headlines'][$c->clusterId] as $h)
            {
              $this->data['h_resources'][$h->headlineId] = $this->database_model->get('resources', array('headlineId' => $h->headlineId, 'active' => 1, 'deleted' => 0));
              $this->data['h_contributors'][$h->headlineId] = $this->stream_model->get_contributors('headline', $h->headlineId);
              $this->data['h_comments'][$h->headlineId] = $this->database_model->get_count('comments', array('headlineId' => $h->headlineId, 'deleted' => 0), 'headlineId');
            }
          }
        }
      }
      if($type == 'headline')
      {
        $this->data['parent'] = $this->database_model->get_single('clusters', array('clusterId' => $this->data['item']->clusterId, 'deleted' => 0), '*, OLD_PASSWORD(clusterId) AS hashId, "cluster" as type');
      }
      elseif($type == 'cluster')
      {
        $this->data['parent'] = $this->database_model->get_single('articles', array('articleId' => $this->data['item']->articleId, 'deleted' => 0), '*, OLD_PASSWORD(articleId) AS hashId, "article" as type');
      }
      // Set Meta Data
      $this->data['author'] = '';
      foreach($this->data['contributors'] as $c) { $this->data['author'] .= (($this->data['author'])?', ':'').$c->user; }
      $this->data['description'] = $this->data['item']->headline;
      if($this->data['type'] == 'article') { $this->data['description'] = substr(strip_tags(stripslashes(str_replace("\r", ' ', str_replace("\n", ' ', $this->data['item']->article)))), 0, 500); }
      $this->data['keywords'] = $this->data['item']->tags;
      // Load View
      $this->data['title'] = stripslashes($this->data['item']->headline);
      if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1)
      {
        $this->load->view('includes/functions');
        $this->load->view('main/item', $this->data);
      }
      else
      {
        $this->data['page'] = "item";
        $this->load->view($this->template, $this->data);
      }
    }
    else { redirect('p/404'); }
  }

  /**
   * Item Details
   *
   * @access public
   * @param string
   * @param string
   * @return void
   */
  public function hashed($type = '', $hashId = '', $sort = "score", $results = "all", $comments = true, $limit = 20)
  {
    // Grab Data by Type
    $this->data['type'] = $type;
    $this->data['hashId'] = $hashId;
    $this->data['item'] = $this->database_model->get_single($type.'s', array('OLD_PASSWORD('.$type.'Id)' => $hashId, 'deleted' => 0), '*, OLD_PASSWORD('.$type.'Id) AS hashId');
    if(isset($_GET['hId']))
    {
      $this->data['item'] = $this->database_model->get_single($type.'s_history', array('OLD_PASSWORD(historyId)' => $_GET['hId'], 'deleted' => 0), '*, OLD_PASSWORD('.$type.'Id) AS hashId');
      $this->data['history'] = true;
      $this->data['history_prev'] = $this->database_model->get_single($type.'s_history', array('historyId <' => $this->data['item']->historyId, 'deleted' => 0), '*, OLD_PASSWORD(historyId) AS hashId', false, 'historyId', 'desc');
      $this->data['history_next'] = $this->database_model->get_single($type.'s_history', array('historyId >' => $this->data['item']->historyId, 'deleted' => 0), '*, OLD_PASSWORD(historyId) AS hashId', false, 'historyId', 'asc');
    }
    // Get Data
    if(isset($this->data['item']) && $this->data['item'])
    {
      $idType = $type.'Id';
      $this->data['id'] = $id = $this->data['item']->$idType;
      // $this->data['related'] = $this->stream_model->search($this->data['item']->tags, '(id != '.$id.' AND type != "'.$type.'")', 'score', $type.'s', true, 3);
      // Views
      if($this->session->userdata('isLoggedIn') && !isset($_GET['hId']))
      {
        $views = array($type.'Id' => $id, 'userId' => $this->session->userdata('userId'));
        if($this->database_model->get_single('notices', $views)) { $this->database_model->edit('notices', $views, array('viewed' => 1), true); }
        if(!$this->database_model->get_single('views', $views)) { $this->database_model->add('views', $views); }
        if(isset($_REQUEST['subscribe']))
        {
          if($_REQUEST['subscribe'] && !$this->database_model->get_single('subscriptions', $views))
          {
            $subscriptionId = $this->database_model->add('subscriptions', $views+array('createdOn' => time()), 'subscriptionId');
          }
          elseif($_REQUEST['subscribe'] && $this->database_model->get_single('subscriptions', $views+array('deleted' => 1)))
          {
            $this->database_model->edit('subscriptions', $views, array('deleted' => 0));
          }
          elseif(!$_REQUEST['subscribe'] && $this->database_model->get_single('subscriptions', $views+array('deleted' => 0)))
          {
            $this->database_model->edit('subscriptions', $views, array('deleted' => 1));
          }
        }
        $this->data['subscription'] = $this->database_model->get_single('subscriptions', $views+array('deleted' => 0));
      }
      // Right Sidebar Content
      $assets_where = array($type.'Id' => $id);
      if(isset($_GET['hId']))
      {
        $history_time = $this->data['item']->editedOn;
        $assets_where['createdOn <'] = $history_time;
        $assets_where['IF(deleted = 1, IF(editedOn > '.$history_time.', 1, 0), 1) ='] = 1;
      }
      $this->data['favorite'] = $this->database_model->get_single('favorites', $assets_where+array('editedBy' => $this->session->userdata('userId'), 'deleted' => 0));
      if($this->data['item']->placeId)
      {
        $this->data['place'] = $this->database_model->get_single('places', array('placeId' => $this->data['item']->placeId));
      }
      //$this->data['cats'] = $this->database_model->get_array('catlist', $assets_where+array('active' => 1, 'deleted' => 0), 'categoryId');
      $this->data['images'] = $this->database_model->get('images', $assets_where+array('active' => 1, 'deleted' => 0));
      $this->data['resources'] = $this->database_model->get('resources', $assets_where+array('active' => 1, 'deleted' => 0));
      $select = 'c.commentId, u.user, c.createdOn, c.editedOn, c.createdBy, c.comment, u.photo';
      $comment_where = array($type.'Id' => $id);
      if(isset($_GET['hId']))
      {
        $history_time = $this->data['item']->editedOn;
        $comment_where['c.createdOn <'] = $history_time;
        $comment_where['IF(c.deleted = 1, IF(c.editedOn > '.$history_time.', 1, 0), 1) ='] = 1;
      }
      $this->data['comments'] = $this->database_model->get_join('comments c', 'users u', 'c.createdBy = u.userId', $comment_where+array('c.deleted' => 0, 'u.deleted' => 0), $select, false, 'commentId', 'DESC');
      // Get Sub Content
      $this->data['contributors'] = $this->stream_model->get_contributors($type, $id);
      $this->data['ranking'] = $this->database_model->get_single('rankings', array($type.'Id' => $id, 'createdBy' => $this->session->userdata('userId')));
      $this->utility_model->metadata($type, $id);
      // Actions
      if(isset($_GET['comments']))
      {
        $this->comments($_GET['comments'], isset($_GET['commentId'])?$_GET['commentId']:0);
        $this->data['comments'] = $this->database_model->get_join('comments c', 'users u', 'c.createdBy = u.userId', $comment_where+array('c.deleted' => 0, 'u.deleted' => 0), $select, false, 'commentId', 'DESC');
      }
      if(isset($_GET['importance'])) { $this->importance($_GET['importance']); }
      if(isset($_GET['quality'])) { $this->quality($_GET['quality']); }
      if(isset($_GET['favorite'])) { $this->favorite($_GET['favorite']); }
      if(isset($_GET['importance']) || isset($_GET['quality']) || isset($_GET['favorite']))
      {
        $this->utility_model->metadata($type, $id);
        redirect($type.'/'.$id);
      }
      //
      if($type !== 'headline')
      {
        $this->data['headlines'] = array();
        $this->data['h_resources'] = array();
        $this->data['h_contributors'] = array();
        $this->data['h_comments'] = array();
        if($type == 'cluster')
        {
          $headlines_where = array('s.clusterId' => $id, 's.deleted' => 0);
          $this->data['headlines'] = $this->database_model->get_join('headlines s', '', '', $headlines_where, '*, OLD_PASSWORD(s.headlineId) AS hashId');
          foreach($this->data['headlines'] as $h)
          {
            $this->data['h_resources'][$h->headlineId] = $this->database_model->get('resources', array('headlineId' => $h->headlineId, 'active' => 1, 'deleted' => 0));
            $this->data['h_contributors'][$h->headlineId] = $this->stream_model->get_contributors('headline', $h->headlineId);
            $this->data['h_comments'][$h->headlineId] = $this->database_model->get_count('comments', array('headlineId' => $h->headlineId, 'deleted' => 0), 'headlineId');
          }
        }
        else
        {
          $clusters_where = array('s.articleId' => $id, 's.deleted' => 0);
          $this->data['clusters'] = $this->database_model->get_join('clusters s', '', '', $clusters_where, '*, OLD_PASSWORD(s.clusterId) AS hashId');
          $this->data['c_resources'] = array();
          $this->data['c_contributors'] = array();
          $this->data['c_comments'] = array();
          foreach($this->data['clusters'] as $c)
          {
            $this->data['c_resources'][$c->clusterId] = $this->database_model->get('resources', array('clusterId' => $c->clusterId, 'active' => 1, 'deleted' => 0));
            $this->data['c_contributors'][$c->clusterId] = $this->stream_model->get_contributors('cluster', $c->clusterId);
            $this->data['c_comments'][$c->clusterId] = $this->database_model->get_count('comments', array('clusterId' => $c->clusterId, 'deleted' => 0), 'headlineId');
          }
          foreach($this->data['clusters'] as $c)
          {
            $headlines_where = array('s.clusterId' => $c->clusterId, 's.deleted' => 0);
            $this->data['headlines'][$c->clusterId] = $this->database_model->get_join('headlines s', '', '', $headlines_where, '*, OLD_PASSWORD(s.headlineId) AS hashId');
            foreach($this->data['headlines'][$c->clusterId] as $h)
            {
              $this->data['h_resources'][$h->headlineId] = $this->database_model->get('resources', array('headlineId' => $h->headlineId, 'active' => 1, 'deleted' => 0));
              $this->data['h_contributors'][$h->headlineId] = $this->stream_model->get_contributors('headline', $h->headlineId);
              $this->data['h_comments'][$h->headlineId] = $this->database_model->get_count('comments', array('headlineId' => $h->headlineId, 'deleted' => 0), 'headlineId');
            }
          }
        }
      }
      if($type == 'headline')
      {
        $this->data['parent'] = $this->database_model->get_single('clusters', array('clusterId' => $this->data['item']->clusterId, 'deleted' => 0), "*, OLD_PASSWORD(clusterId) AS hashId, 'cluster' AS type");
        if($this->data['parent']) { $this->data['parent_parent'] = $this->database_model->get_single('articles', array('articleId' => $this->data['parent']->articleId, 'deleted' => 0), "*, OLD_PASSWORD(articleId) AS hashId, 'article' AS type"); }
      }
      elseif($type == 'cluster')
      {
        $this->data['parent'] = $this->database_model->get_single('articles', array('articleId' => $this->data['item']->articleId, 'deleted' => 0), "*, OLD_PASSWORD(articleId) AS hashId, 'article' AS type");
      }
      // Set Meta Data
      $this->data['author'] = '';
      foreach($this->data['contributors'] as $c) { $this->data['author'] .= (($this->data['author'])?', ':'').$c->user; }
      $this->data['description'] = $this->data['item']->headline;
      if($this->data['type'] == 'article') { $this->data['description'] = substr(strip_tags($this->data['item']->article), 0, 500); }
      $this->data['keywords'] = $this->data['item']->tags;
      // Load View
      $this->data['title'] = stripslashes($this->data['item']->headline);
      $this->data['page'] = "item";
      if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1)
      {
        $this->load->view('includes/functions');
        $this->load->view('main/item', $this->data);
      }
      else
      {
        $this->data['page'] = "item";
        $this->load->view($this->template, $this->data);
      }
    }
    else { redirect('p/404'); }
  }

  // --------------------------------------------------------------------

  /**
   * Manage Comments
   *
   * @access private
   * @param string
   * @param string
   * @return void
   */
  private function comments($action, $commentId)
  {
    unset($_POST['comments']);
    $userId = $this->session->userdata('userId');
    if($action == 'create')
    {
      $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
      $this->form_validation->set_rules('id', 'ID', 'trim|required|xss_clean');
      $this->form_validation->set_rules('comment', 'Comment', 'trim|required|xss_clean');
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
      }
      elseif($_POST) { $this->data['comment_errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
    }
    elseif($action == 'modify' && $commentId)
    {
      $this->data['comment'] = $this->database_model->get_single('comments', array('commentId' => $commentId, 'createdBy' => $userId, 'deleted' => 0));
      if($this->data['comment'])
      {
        $this->form_validation->set_rules('commentId', 'Comment ID', 'trim|required|xss_clean');
        $this->form_validation->set_rules('comment', 'Comment', 'trim|required|xss_clean');
        if($this->form_validation->run())
        {
          $update = array(
            'comment' => $this->db->escape_str($post['comment']),
            'editedBy' => $userId
          );
          $this->database_model->edit('comments', array('commentId' => $post['commentId']), $update);
          unset($this->data['comment']);
        }
        elseif($_POST) { $this->data['comment_errors'] = ($_POST)?$this->form_validation->error_array():'No data submitted.'; }
      }
      else { unset($this->data['comment']); }
    }
    elseif($action == 'destroy' && $commentId) { $this->database_model->edit('comments', array('commentId' => $commentId), array('deleted' => 1, 'editedBy' => $userId)); }
  }

  /**
   * Manage Favorites
   *
   * @access private
   * @param string
   * @return void
   */
  private function favorite($action)
  {
    $userId = $this->session->userdata('userId');
    if($action == 'add')
    {
      $insert[$this->data['type'].'Id'] = $this->data['id'];
      $insert['editedBy'] = $userId;
      $this->database_model->add('favorites', $insert, 'favoriteId');
    }
    elseif($action == 'remove')
    {
      $fav = $this->database_model->get_single('favorites', array($this->data['type'].'Id' => $this->data['id'], 'editedBy' => $userId));
      $this->database_model->edit('favorites', array('favoriteId' => $fav->favoriteId), array('deleted' => 1));
    }
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
      /*foreach($this->data['cats'] as $c)
      {
        $cat_score = $this->stream_model->get_score_by_user($user->userId, $c);
        $data = array('score' => (($cat_score->q_score_sum + $cat_score->i_score_sum) / $cat_score->total_items));
        $where = array('userId' => $user->userId, 'categoryId' => $c);
        if($item = $this->database_model->get_single('scores', $where)) { $this->database_model->edit('scores', array('scoreId' => $item->scoreId), $data); }
        else { $this->database_model->add('scores', $where+$data, 'scoreId'); }
      }*/
    }
  }
}

/* End of file item.php */
/* Location: ./application/controllers/item.php */