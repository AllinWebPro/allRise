<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Database_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
    $this->load->driver('cache', array('adapter' => 'file'));
  }
  
  /**
   * Add Item
   *
   * @access public
   * @param string
   * @param array
   * @param string
   * @return int
   */
  public function add($table = '', $insert = array(), $field_name = '')
  {
    $insert['editedOn'] = time();
    $this->db->insert($table, $insert);
    $insert_id = $this->db->insert_id();
    if($field_name) { $this->add_history($table, array($field_name => $insert_id)); }
    return $insert_id;
  }

  /**
   * Add Item History
   *
   * @access private
   * @param string
   * @param array
   * @return void
   */
  private function add_history($table = '', $where = array())
  {
    $data = $this->get_single($table, $where);
    $this->db->insert($table.'_history', $data);
  }

  /**
   * Edit Item
   *
   * @access public
   * @param string
   * @param array
   * @param array
   * @return void
   */
  public function edit($table = '', $where = array(), $update = array(), $history = true)
  {
    $update['editedOn'] = time();
    $this->db->where($where);
    $this->db->update($table, $update);
    if($history) { $this->add_history($table, $where); }
  }

  /**
   * Get Items
   *
   * @access public
   * @param string
   * @param array
   * @param int
   * @param int
   * @return objects
   */
  public function get($table = '', $where = array(), $limit = 0, $n = 0)
  {
    $cacheId = 'get-'.$table.'-'.implode('&', $where).'-'.$limit.'-'.$n;
    if(!$results = $this->cache->get($cacheId))
    {
      if($limit) { $this->db->limit($limit, ($limit*$n)); }
      $q = $this->db->get_where($table, $where);
      $results =  $q->result();
      
      // Save into the cache for 5 minutes
      $this->cache->save($cacheId, $results, 60*15);
    }
    
    return $results;
  }

  /**
   * Get Items
   *
   * @access public
   * @param string
   * @param array
   * @param int
   * @param int
   * @return objects
   */
  public function get_or($table = '', $where = array(), $or_where = array(), $limit = 0, $n = 0)
  {
    $cacheId = 'get_or-'.$table.'-'.implode('&', $where).'-'.implode('&', $or_where).'-'.$limit.'-'.$n;
    if(!$results = $this->cache->get($cacheId))
    {
      if($limit) { $this->db->limit($limit, ($limit*$n)); }
      $this->db->where($where);
      $this->db->or_where($or_where);
      $q = $this->db->get($table);
      $results =  $q->result();
      
      // Save into the cache for 5 minutes
      $this->cache->save($cacheId, $results, 60*15);
    }
    
    return $results;
  }

  /**
   * Get Items
   *
   * @access public
   * @param string
   * @param array
   * @param mixed
   * @param string
   * @return array
   */
  public function get_array($table = '', $where = array(), $value = '', $key = '')
  {
    $return = array();
    if(is_array($value))
    {
      foreach($value as $k => $v) { $return[$k] = array(); }
      foreach($this->get($table, $where) as $data)
      {
        foreach($value as $k => $v) { $return[$k][$data->$key] = $data->$v; }
      }
    }
    else
    {
      foreach($this->get($table, $where) as $data)
      {
        if($key) { $return[$data->$key] = $data->$value; }
        else { $return[] = $data->$value; }
      }
    }
    return $return;
  }

  /**
   * Count Items
   *
   * @access public
   * @param string
   * @param array
   * @return int
   */
  public function get_count($table = '', $where = array(), $group_by = '')
  {
    $cacheId = 'get_count-'.$table.'-'.implode('&', $where).'-'.$group_by;
    if(!$count = $this->cache->get($cacheId))
    {
      $this->db->where($where);
      if($group_by) { $this->db->group_by($group_by); }
      $count = $this->db->count_all_results($table);
      
      // Save into the cache for 5 minutes
      $this->cache->save($cacheId, $count, 60*15);
    }
    
    return $count;
  }

  /**
   * Get Items
   *
   * @access public
   * @param string
   * @param array
   * @return objects
   */
  public function get_join($table = '', $join = '', $on = '', $where = array(), $select = '*', $row = false, $order = '', $dir = '')
  {
    $cacheId = 'get_join-'.$table.'-'.$join.'-'.$on.'-'.implode('&', $where).'-'.$select.'-'.($row?1:0).'-'.$order.'-'.$dir;
    if(!$results = $this->cache->get($cacheId))
    {
      $this->db->select($select);
      if($join) { $this->db->join($join, $on, 'left'); }
      if($order) { $this->db->order_by($order, $dir); }
      $q = $this->db->get_where($table, $where);
      if($row) { return $q->row(); }
      $results =  $q->result();
      
      // Save into the cache for 5 minutes
      $this->cache->save($cacheId, $results, 60*15);
    }
    
    return $results;
  }

  /**
   * Get Item - Single
   *
   * @access public
   * @param string
   * @param array
   * @param string
   * @param string
   * @return object
   */
  public function get_single($table = '', $where = array(), $select = '*', $group_by = '', $order = '', $dir = '')
  {
    $cacheId = 'get_single-'.$table.'-'.implode('&', $where).'-'.$select.'-'.$group_by.'-'.$order.'-'.$dir;
    if(!$results = $this->cache->get($cacheId))
    {
    $this->db->select($select, false);
    if($group_by) { $this->db->group_by($group_by); }
    if($order) { $this->db->order_by($order, $dir); }
    $q = $this->db->get_where($table, $where);
      $results =  $q->row();
      
      // Save into the cache for 5 minutes
      $this->cache->save($cacheId, $results, 60*15);
    }
    
    return $results;
  }

  public function get_select($table = '', $where = array(), $select = '*', $order = '', $dir = '')
  {
    $cacheId = 'get_select-'.$table.'-'.implode('&', $where).'-'.$select.'-'.$order.'-'.$dir;
    if(!$results = $this->cache->get($cacheId))
    {
      $this->db->select($select, false);
      if($order) { $this->db->order_by($order, $dir); }
      $q = $this->db->get_where($table, $where);
      $results =  $q->result();
      
      // Save into the cache for 5 minutes
      $this->cache->save($cacheId, $results, 60*15);
    }
    
    return $results;
  }
}

/* End of file database_model.php */
/* Location: ./application/models/database_model.php */