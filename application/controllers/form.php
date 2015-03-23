<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Form extends CI_Controller
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
    $this->data['action'] = $action;
    if(in_array($type, array('article', 'cluster', 'headline')))
    {
      $this->data['type'] = $type;
      $this->$action($id, false);
      // Load View
      // redirect(substr($type, 0, 1).'/'.$id);
      $this->data['title'] = ($action=='add'?'Create':'Modify')." ".ucfirst($this->data['type']);
      if(isset($_GET['ajax']) && $_GET['ajax'])
      {
        $this->load->view('includes/functions');
        $this->load->view('main/form', $this->data);
      }
      else
      {
        $this->data['page'] = "form";
        $this->load->view($this->template, $this->data);
      }
    }
    else { redirect('p/404'); }
  }

  public function hashed($action = '', $type = '', $id = 0)
  {
    $this->data['action'] = $action;
    if(in_array($type, array('article', 'cluster', 'headline')))
    {
      $this->data['type'] = $type;
      $this->$action($id, true);
      // Load View
      // redirect(substr($type, 0, 1).'/'.$id);
      $this->data['title'] = ($action=='add'?'Create':'Modify')." ".ucfirst($this->data['type']);
      if(isset($_GET['ajax']) && $_GET['ajax'])
      {
        $this->load->view('includes/functions');
        $this->load->view('main/form', $this->data);
      }
      else
      {
        $this->data['page'] = "form";
        $this->load->view($this->template, $this->data);
      }
    }
    else { redirect('p/404'); }
  }

  // --------------------------------------------------------------------

  /**
   * Create Item
   *
   * @access private
   * @param int
   * @return void
   */
  private function add($clusterId = 0, $hashed = false)
  {
    // Vars
    if($clusterId)
    {
      $this->data['id'] = $clusterId;
      if($hashed)
      {
        $this->data['hashId'] = $clusterId;
        $this->data['item'] = $this->database_model->get_single('clusters', array('OLD_PASSWORD(clusterId)' => $clusterId, 'deleted' => 0));
        $this->data['id'] = $this->data['item']->clusterId;
      }
      else
      {
        $this->data['item'] = $this->database_model->get_single('clusters', array('clusterId' => $clusterId, 'deleted' => 0));
      }
      $assets_where = array('clusterId' => $this->data['id']);
      $this->data['images_output'] = $this->database_model->get('images', $assets_where+array('active' => 1, 'deleted' => 0));
      $this->data['resources_output'] = $this->database_model->get('resources', $assets_where+array('active' => 1, 'deleted' => 0));
    }
    // Verify Data Exists
    if($post = $this->validate())
    {
      $userId = $this->session->userdata('userId');
      $insert = array(
        'headline' => $this->db->escape_str(preg_replace('/\s+/', ' ', $post['headline'])),
        'tags' => $this->db->escape_str($this->utility_model->clean_tag_list($post['tags'])),
        'createdBy' => $userId,
        'createdOn' => time(),
        'editedBy' => $userId
      );
      if($post['placeId']) { $insert['placeId'] = $post['placeId']; }
      elseif($post['place'])
      {
        if($place = $this->database_model->get_single('places', array('place' => $this->db->escape_str($post['place']), 'deleted' => 0))) { $insert['placeId'] = $place->placeId; }
        else { $insert['placeId'] = $this->database_model->add('places', array('place' => $this->db->escape_str($post['place']), 'editedBy' => $userId), 'placeId'); }
      }
      if($this->data['type'] == 'headline') { $insert['notes'] = $this->db->escape_str($post['notes']); }
      if($this->data['type'] == 'article') { $insert['article'] = $this->db->escape_str(str_replace('\r', '', str_replace('\n', '', $post['article']))); }
      $insert['active'] = 1;
      $id = $this->database_model->add($this->data['type']."s", $insert, $this->data['type']."Id");
      $sInsert = array($$this->data['type'].'Id' => $id, 'userId' => $userId);
      $subscriptionId = $this->database_model->add('subscriptions', $sInsert+array('createdOn' => time()), 'subscriptionId');
      if($this->data['type'] == 'headline')
      {
        $item = $this->database_model->get_select('headlines', array('headlineId' => $id), 'OLD_PASSWORD(headlineId) AS hashId');
        @mail("owen@allinwebpro.com", "New Headline", "Link: ".site_url('h/'.$item->hashId));
        $sInsert = array('headlineId' => $id, 'userId' => $userId);
      }
      foreach($post['categoryId'] as $c)
      {
        $this->database_model->add("catlist", array($this->data['type'].'Id' => $id, 'categoryId' => $c, 'editedBy' => $userId), 'catlistId');
      }
      if($clusterId) { $this->database_model->edit('clusters', array('clusterId' => $clusterId, 'editedBy' => $userId), array($this->data['type'].'Id' => $id)); }
      $this->utility_model->add_keywords($this->data['type'], $id, $post['headline'], $post['tags']);
      if(isset($post['image']) && $post['image'])
      {
        foreach($post['image'] as $i)
        {
          if($i !== '') { $this->database_model->add("images", array($this->data['type'].'Id' => $id, 'image' => $this->db->escape_str($i), 'editedBy' => $userId), 'imageId'); }
        }
      }
      if(isset($post['resource']) && $post['resource'])
      {
        foreach($post['resource'] as $r)
        {
          if($r !== '') { $this->database_model->add("resources", array($this->data['type'].'Id' => $id, 'resource' => $this->db->escape_str($r), 'editedBy' => $userId), 'resourceId'); }
        }
      }
      // Metadata
      $metadata = array($this->data['type'].'Id' => $id, 'quality' => 0, 'importance' => 0);
      $metadata['credibility'] = $this->utility_model->credibility($this->data['type'], $id);
      $this->database_model->add('metadata', $metadata, 'metadataId');
      $this->stream_model->autocompare($this->data['type'], $id);
      // Load View
      redirect($this->data['type'].'/'.$id);
    }
    else
    {
      if($clusterId)
      {
        $this->data['headlines'] = array();
        $this->data['h_resources'] = array();
        $this->data['h_contributors'] = array();
        $this->data['h_comments'] = array();
        $clusters_where = array('s.clusterId' => $this->data['id'], 's.deleted' => 0);
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
  }

  /**
   * Edit Item
   *
   * @access private
   * @param int
   * @return void
   */
  private function edit($id = 0, $hashed = false)
  {
    // Vars
    $this->data['id'] = $id;
    if($hashed)
    {
      $this->data['hashId'] = $id;
      $this->data['item'] = $this->database_model->get_single($this->data['type'].'s', array('OLD_PASSWORD('.$this->data['type'].'Id)' => $id, 'deleted' => 0));
      $typeId = $this->data['type']."Id";
      $this->data['id'] = $this->data['item']->$typeId;
    }
    else
    {
      $this->data['item'] = $this->database_model->get_single($this->data['type'].'s', array($this->data['type'].'Id' => $id, 'deleted' => 0));
    }
    $assets_where = array($this->data['type'].'Id' => $this->data['id']);
    $this->data['history'] = $this->database_model->get_select($this->data['type'].'s_history', $assets_where, "*, OLD_PASSWORD(historyId) AS hashId", 'editedOn', 'desc');
    $this->data['images_output'] = $this->database_model->get('images', $assets_where+array('active' => 1, 'deleted' => 0));
    $this->data['resources_output'] = $this->database_model->get('resources', $assets_where+array('active' => 1, 'deleted' => 0));
    // Verify Data Exists
    if(isset($this->data['item']) && $this->data['item'])
    {
      // Set Variables for Display
      $this->data['place'] = $this->database_model->get_single('places', array('placeId' => $this->data['item']->placeId, 'deleted' => 0));
      $this->data['cats'] = $this->database_model->get_array('catlist', array($this->data['type'].'Id' => $this->data['id'], 'active' => 1, 'deleted' => 0), 'categoryId');
      $this->data['images'] = $this->database_model->get_array('images', array($this->data['type'].'Id' => $this->data['id'], 'active' => 1, 'deleted' => 0), 'image');
      $this->data['resources'] = $this->database_model->get_array('resources', array($this->data['type'].'Id' => $this->data['id'], 'active' => 1, 'deleted' => 0), 'resource');
      // Validate
      if($post = $this->validate($this->data['type']))
      {
        $userId = $this->session->userdata('userId');
        if(!isset($post['resource'])) { $post['resource'] = array(); }
        $update = array(
          'headline' => $this->db->escape_str(preg_replace('/\s+/', ' ', $post['headline'])),
          'tags' => $this->db->escape_str($this->utility_model->clean_tag_list($post['tags'])),
          'editedBy' => $userId
        );
        if($this->data['place'] && $post['placeId'] == $this->data['place']->placeId)
        {
          if(empty($post['place'])) { $update['placeId'] = null; }
          elseif($post['place'] !== $this->data['place']->place)
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
        if($this->data['type'] == 'headline') { $update['notes'] = $this->db->escape_str($post['notes']); }
        if($this->data['type'] == 'article') { $update['article'] = $this->db->escape_str(str_replace('\r', '', str_replace('\n', '', $post['article']))); }
        $update['active'] = 1;

        $sInsert = array($$this->data['type'].'Id' => $this->data['id'], 'userId' => $userId);
        if(!$this->database_model->get_single('subscriptions', $sInsert))
        {
          $subscriptionId = $this->database_model->add('subscriptions', $sInsert+array('createdOn' => time()), 'subscriptionId');
        }
        $nInsert = array($this->data['type'].'Id' => $this->data['id'], 'edited' => 1, 'createdOn' => time(), 'editedBy' => $this->session->userdata('userId'));
        $subscribers = $this->database_model->get('subscriptions', array($post['type'].'Id' => $post['id'], 'deleted' => 0));
        foreach($subscribers as $s)
        {
          if($s->userId !== $userId)
          {
            $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId');
          }
        }

        $this->database_model->edit($this->data['type'].'s', array($this->data['type'].'Id' => $this->data['id']), $update);
        $this->utility_model->keywords($this->data['type'], $this->data['id'], $post['headline'], $post['tags']);
        if($this->data['type'] == 'cluster' && !$this->data['item']->articleId) { $this->stream_model->autocompare($this->data['type'], $id); }
        elseif($this->data['type'] == 'headline' && !$this->data['item']->clusterId) { $this->stream_model->autocompare($this->data['type'], $id); }
        // Edit Categories
        $delete = array('active' => 0, 'deleted' => 1, 'editedBy' => $userId);
        $undelete = array('active' => 1, 'deleted' => 0, 'editedBy' => $userId);
        foreach($this->data['cats'] as $cat)
        {
          if(!in_array($cat, $post['categoryId']))
          {
            $where = array($this->data['type'].'Id' => $this->data['id'], 'categoryId' => $cat);
            $cats = $this->database_model->get_single('catlist', $where);
            $this->database_model->edit('catlist', array('catlistId' => $cats->catlistId), $delete);
          }
        }
        foreach($post['categoryId'] as $cat)
        {
          if(!in_array($cat, $this->data['cats']))
          {
            $where = array($this->data['type'].'Id' => $this->data['id'], 'categoryId' => $cat);
            $cats = $this->database_model->get_single('catlist', $where);
            if($cats) { $this->database_model->edit('catlist', array('catlistId' => $cats->catlistId), $undelete); }
            else { $this->database_model->add('catlist', $where+array('editedBy' => $userId), 'catlistId'); }
          }
        }
        // Edit Images
        if($this->data['images'])
        {
          foreach($this->data['images'] as $image)
          {
            if(!in_array($image, $post['image']) || $image == '')
            {
              $where = array($this->data['type'].'Id' => $this->data['id'], 'image' => $image);
              $image_s = $this->database_model->get_single('images', $where);
              $this->database_model->edit('images', array('imageId' => $image_s->imageId), $delete);
            }
          }
        }
        if(isset($post['image']) && $post['image'])
        {
          foreach($post['image'] as $image)
          {
            if(!in_array($image, $this->data['images']) && $image !== '')
            {
              $where = array($this->data['type'].'Id' => $this->data['id'], 'image' => $image);
              $image_s = $this->database_model->get_single('images', $where);
              if($image_s) { $this->database_model->edit('images', array('imageId' => $image_s->imageId), $undelete); }
              else { $this->database_model->add('images', $where+array('editedBy' => $userId, 'active' => 1), 'imageId'); }
            }
          }
        }
        // Edit Resources
        if($this->data['resources'])
        {
          foreach($this->data['resources'] as $resource)
          {
            if(!in_array($resource, $post['resource']) || $resource == '')
            {
              $where = array($this->data['type'].'Id' => $this->data['id'], 'resource' => $this->db->escape_str($resource));
              $resources = $this->database_model->get_single('resources', $where);
              $this->database_model->edit('resources', array('resourceId' => $resources->resourceId), $delete);
            }
          }
        }
        if(isset($post['resource']) && $post['resource'])
        {
          foreach($post['resource'] as $resource)
          {
            if(!in_array($resource, $this->data['resources']) && $resource !== '')
            {
              $where = array($this->data['type'].'Id' => $this->data['id'], 'resource' => $this->db->escape_str($resource));
              $resources = $this->database_model->get_single('resources', $where);
              if($resources) { $this->database_model->edit('resources', array('resourceId' => $resources->resourceId), $undelete); }
              else { $this->database_model->add('resources', $where+array('editedBy' => $userId), 'resourceId'); }
            }
          }
        }
        $this->utility_model->metadata($this->data['type'], $this->data['id']);
        redirect($this->data['type'].'/'.$id);
      }
      else
      {
        if($this->data['type'] !== 'headline')
        {
          $this->data['headlines'] = array();
          $this->data['h_resources'] = array();
          $this->data['h_contributors'] = array();
          $this->data['h_comments'] = array();
          if($this->data['type'] == 'cluster')
          {
            $headlines_where = array('s.clusterId' => $this->data['id'], 's.deleted' => 0);
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
            $clusters_where = array('s.articleId' => $this->data['id'], 's.deleted' => 0);
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
      }
    }
    else { redirect('search'); }
  }

  // --------------------------------------------------------------------

  /**
   * Manage Notes
   *
   * @access private
   * @return void
   */
  private function notes()
  {
    $userId = $this->session->userdata('userId');
    if($action == 'add')
    {
      $this->form_validation->set_rules('note', 'Note', 'trim|required|xss_clean');
      if($this->form_validation->run())
      {
        $insert = $this->input->post();
        $insert[$this->data['type'].'Id'] = $this->data['id'];
        $insert['editedBy'] = $userId;
        $this->database_model->add('notes', $insert, 'noteId');
      }
    }
    elseif($action == 'edit' && $target)
    {
      // Edit Note
      if($section == 'note')
      {
        $this->data['note'] = $this->database_model->get_single('notes', array('noteId' => $target, 'editedBy' => $userId, 'deleted' => 0));
        if($this->data['note'])
        {
          $this->form_validation->set_rules('note', 'Note', 'trim|required|xss_clean');
          if($this->form_validation->run())
          {
            $update = $this->input->post();
            $update['editedBy'] = $userId;
            $this->database_model->edit('notes', array('noteId' => $target), $update);
          }
        }
        unset($this->data['note']);
      }
    }
    elseif($action == 'delete' && $target) { $this->database_model->edit('notes', array('noteId' => $target), array('deleted' => 1, 'editedBy' => $userId)); }
  }

  /**
   * Validate Form Items
   *
   * @access private
   * @return void
   */
  private function validate()
  {
    $this->form_validation->set_rules('headline', 'Headline', 'trim|required|xss_clean|callback_clean_url|max_length[255]');
    if($this->data['type'] == 'headline')
    {
      $this->form_validation->set_rules('notes', 'Author Notes', 'trim|xss_clean|strip_tags');
    }
    if($this->data['type'] == 'article')
    {
      $this->form_validation->set_rules('article', 'Article', 'trim|xss_clean');
    }
    $this->form_validation->set_rules('place', 'Location', 'trim|xss_clean');
    $this->form_validation->set_rules('placeId', 'Place ID', 'trim|xss_clean');
    $this->form_validation->set_rules('categoryId[]', 'Category', 'trim|required|xss_clean');
    $this->form_validation->set_rules('tags', 'Tags', 'trim|xss_clean');
    $this->form_validation->set_rules('image[]', 'Links', 'trim|prep_url|xss_clean');
    $this->form_validation->set_rules('resource[]', 'Links', 'trim|prep_url|xss_clean');
    if($this->form_validation->run()) { return $this->input->post(); }
    return false;
  }
}

/* End of file form.php */
/* Location: ./application/controllers/form.php */