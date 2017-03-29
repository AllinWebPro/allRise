<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stream_model extends CI_Model
{
  private $ci;
  private $generic_select = "s.headline, s.tags, s.createdBy, s.createdOn, s.editedBy, s.editedOn, p.place, s.placeId, s.flagId, s.deleted ";
  private $places_join = "LEFT JOIN places p ON p.placeId = s.placeId ";
  private $decay_score = "s.decay ";

  public function __construct()
  {
    parent::__construct();
  }

  // -- here --

  /**
   * Get Stream Items by User ID
   *
   * @access public
   * @param int
   * @return object
   */
  public function get_by_user($userId = 0, $categories_list = array(), $limit = 5)
  {
    $global_select = $this->generic_select.", ";
    $global_select .= "IF(h.editedBy = ".$userId.", 1, 0) AS edited , ";
    $global_select .= "0 AS score, 0 AS h_count, 0 AS c_count, ";
    $global_select .= "0 AS keyword_score, 0 AS place_score, ";
    $global_select .= "0 AS quality_score, 0 AS importance_score, 0 AS credibility_score ";
    if(!$categories_list) { $global_select .= ", 0 AS category_score "; }

    if($categories_list)
    {
      $catlist_include = "COUNT(catlistId) AS cat_count ";
      $catlist_include .= "FROM catlist ";
      $catlist_include .= "WHERE categoryId IN (".implode(',', $categories_list).") ";
      $catlist_include .= "AND deleted = 0 ";
    }

    $comments_include = "COUNT(commentId) as comments ";
    $comments_include .= "FROM comments ";
    $comments_include .= "WHERE deleted = 0 ";

    $where = "WHERE s.deleted = 0 ";

    $sql = "SELECT *, OLD_PASSWORD(id) AS hashId ";
    $sql .= "FROM ( ";
      $sql .= "SELECT s.headlineId AS id, 'headline' AS type, comments, ".$global_select;
      if($categories_list) { $sql .= ", (cat_count / ".sizeof($categories_list).") as category_score "; }
      $sql .= "FROM headlines s ";
      $sql .= "LEFT JOIN headlines_history h ON h.headlineId = s.headlineId ";
      if($categories_list)
      {
        $sql .= "LEFT JOIN ( ";
          $sql .= "SELECT headlineId, ";
          $sql .= $catlist_include;
          $sql .= "GROUP BY headlineId ";
        $sql .= ") l ON l.headlineId = s.headlineId ";
      }
      $sql .= "LEFT JOIN ( ";
        $sql .= "SELECT headlineId, ";
        $sql .= $comments_include;
        $sql .= "GROUP BY headlineId ";
      $sql .= ") x ON x.headlineId = s.headlineId ";
      $sql .= $this->places_join;
      $sql .= $where;
      $sql .= "GROUP BY s.headlineId ";
      $sql .= "UNION ALL ";
      $sql .= "SELECT s.clusterId AS id, 'cluster' AS type, comments, ".$global_select;
      if($categories_list) { $sql .= ", (cat_count / ".sizeof($categories_list).") as category_score "; }
      $sql .= "FROM clusters s ";
      $sql .= "LEFT JOIN clusters_history h ON h.clusterId = s.clusterId ";
      if($categories_list)
      {
        $sql .= "LEFT JOIN ( ";
          $sql .= "SELECT clusterId, ";
          $sql .= $catlist_include;
          $sql .= "GROUP BY clusterId ";
        $sql .= ") l ON l.clusterId = s.clusterId ";
      }
      $sql .= "LEFT JOIN ( ";
        $sql .= "SELECT clusterId, ";
        $sql .= $comments_include;
        $sql .= "GROUP BY clusterId ";
      $sql .= ") x ON x.clusterId = s.clusterId ";
      $sql .= $this->places_join;
      $sql .= $where;
      $sql .= "GROUP BY s.clusterId ";
      $sql .= "UNION ALL ";
      $sql .= "SELECT s.articleId AS id, 'article' AS type, comments, ".$global_select;
      if($categories_list) { $sql .= ", (cat_count / ".sizeof($categories_list).") as category_score "; }
      $sql .= "FROM articles s ";
      $sql .= "LEFT JOIN articles_history h ON h.articleId = s.articleId ";
      if($categories_list)
      {
        $sql .= "LEFT JOIN ( ";
          $sql .= "SELECT articleId, ";
          $sql .= $catlist_include;
          $sql .= "GROUP BY articleId ";
        $sql .= ") l ON l.articleId = s.articleId ";
      }
      $sql .= "LEFT JOIN ( ";
        $sql .= "SELECT articleId, ";
        $sql .= $comments_include;
        $sql .= "GROUP BY articleId ";
      $sql .= ") x ON x.articleId = s.articleId ";
      $sql .= $this->places_join;
      $sql .= $where;
      $sql .= "GROUP BY s.articleId ";
    $sql .= ") items ";
    $sql .= "WHERE edited = 1 ";
    if($categories_list) { $sql .= "AND category_score > 0 "; }
    $sql .= "ORDER BY editedOn DESC ";
    $sql .= "LIMIT ".$limit." ";

    $q = $this->db->query($sql);
    $results = $q->result();
    
    return $results;
  }

  /**
   * Get Stream Contributors
   *
   * @access public
   * @param string
   * @param int
   * @return object
   */
  public function get_contributors($type = '', $id = 0)
  {
    $sql = "SELECT DISTINCT userId, user, score ";
    $sql .= "FROM ( ";
      $sql .= "SELECT u.userid, u.user, u.score, u.deleted ";
      if($type == 'article')
      {
        $sql .= "FROM articles s ";
        $sql .= "JOIN articles_history h ";
        $sql .= "ON h.articleId = s.articleId ";
        $sql .= "JOIN users u ";
        $sql .= "ON u.userId = h.editedBy ";
        $sql .= "WHERE s.articleId = ".$id." ";
      }
      elseif($type == 'cluster')
      {
        $sql .= "FROM clusters s ";
        $sql .= "JOIN clusters_history h ";
        $sql .= "ON h.clusterId = s.clusterId ";
        $sql .= "JOIN users u ";
        $sql .= "ON u.userId = h.editedBy ";
        $sql .= "WHERE s.clusterId = ".$id." ";
      }
      else
      {
        $sql .= "FROM headlines s ";
        $sql .= "JOIN headlines_history h ";
        $sql .= "ON h.headlineId = s.headlineId ";
        $sql .= "JOIN users u ";
        $sql .= "ON u.userId = h.createdBy ";
        $sql .= "WHERE s.headlineId = ".$id." ";
      }
      $sql .= "GROUP BY u.userid ";
    $sql .= ") contributors ";

    $q = $this->db->query($sql);
    $results = $q->result();
    
    return $results;
  }

  /**
   * Get Stream Contributors
   *
   * @access public
   * @param string
   * @param int
   * @return object
   */
  public function get_contributors_full($type = '', $id = 0)
  {
    $sql = "SELECT DISTINCT userId, user, score ";
    $sql .= "FROM ( ";
      if($type !== 'headline')
      {
        if($type == 'article')
        {
          $sql .= "SELECT u.userid, u.user, u.score, u.deleted ";
          $sql .= "FROM articles s ";
          $sql .= "JOIN articles_history h ";
          $sql .= "ON h.articleId = s.articleId ";
          $sql .= "JOIN users u ";
          $sql .= "ON u.userId = h.editedBy ";
          $sql .= "WHERE s.articleId = ".$id." ";
          $sql .= "GROUP BY u.userid ";
          $sql .= "UNION ALL ";
        }
        $sql .= "SELECT u.userid, u.user, u.score, u.deleted ";
        $sql .= "FROM clusters s ";
        $sql .= "JOIN clusters_history h ";
        $sql .= "ON h.clusterId = s.clusterId ";
        $sql .= "JOIN users u ";
        $sql .= "ON u.userId = h.editedBy ";
        if($type == 'article')
        {
          $sql .= "WHERE s.articleId = ".$id." ";
          $sql .= "AND s.deleted = 0 ";
        }
        else { $sql .= "WHERE s.clusterId = ".$id." "; }
        $sql .= "GROUP BY u.userid ";
        $sql .= "UNION ALL ";
      }
      $sql .= "SELECT u.userid, u.user, u.score, u.deleted ";
      $sql .= "FROM headlines s ";
      $sql .= "JOIN headlines_history h ";
      $sql .= "ON h.headlineId = s.headlineId ";
      $sql .= "JOIN users u ";
      $sql .= "ON u.userId = h.editedBy ";
      if($type == 'article')
      {
        $sql .= "JOIN clusters_history c ";
        $sql .= "ON c.clusterId = s.clusterId ";
        $sql .= "WHERE c.articleId = ".$id." ";
        $sql .= "AND c.deleted = 0 ";
        $sql .= "AND s.deleted = 0 ";
      }
      elseif($type == 'cluster')
      {
        $sql .= "WHERE s.clusterId = ".$id." ";
        $sql .= "AND s.deleted = 0 ";
      }
      else { $sql .= "WHERE s.headlineId = ".$id." "; }
      $sql .= "GROUP BY u.userid ";
    $sql .= ") contributors ";
    $sql .= "WHERE deleted = 0 ";

    $q = $this->db->query($sql);
    $results = $q->result();
    
    return $results;
  }

  /**
   * Get Favorite Items by User ID
   *
   * @access public
   * @param int
   * @return object
   */
  public function get_favorites_by_user($userId = 0)
  {
    $global_select = $this->generic_select.", ";
    $global_select .= "IF(f.editedBy = ".$userId.", 1, 0) AS edited , ";
    $global_select .= "0 AS score, 0 AS h_count, 0 AS c_count, ";
    $global_select .= "0 AS keyword_score, 0 AS place_score, 0 AS category_score, ";
    $global_select .= "0 AS quality_score, 0 AS importance_score, 0 AS credibility_score ";

    $where = "WHERE s.deleted = 0 AND f.deleted = 0 ";

    $sql = "SELECT * ";
    $sql .= "FROM ( ";
      $sql .= "SELECT s.headlineId AS id, 'headline' AS type, ".$global_select;
      $sql .= "FROM headlines_history s ";
      $sql .= $this->places_join;
      $sql .= "LEFT JOIN favorites f ON f.headlineId = s.headlineId ";
      $sql .= $where;
      $sql .= "GROUP BY s.headlineId ";
      $sql .= "UNION ALL ";
      $sql .= "SELECT s.clusterId AS id, 'cluster' AS type, ".$global_select;
      $sql .= "FROM clusters_history s ";
      $sql .= $this->places_join;
      $sql .= "LEFT JOIN favorites f ON f.clusterId = s.clusterId ";
      $sql .= $where;
      $sql .= "GROUP BY s.clusterId ";
      $sql .= "UNION ALL ";
      $sql .= "SELECT s.articleId AS id, 'article' AS type, ".$global_select;
      $sql .= "FROM articles_history s ";
      $sql .= $this->places_join;
      $sql .= "LEFT JOIN favorites f ON f.articleId = s.articleId ";
      $sql .= $where;
      $sql .= "GROUP BY s.articleId ";
    $sql .= ") items ";
    $sql .= "WHERE edited = 1 ";
    $sql .= "ORDER BY editedOn DESC ";

    $q = $this->db->query($sql);
    $results = $q->result();
    
    return $results;
  }

  public function get_notices($userId = 0, $comments = 1, $edits = 1, $parents = 1, $limit = 0, $page = 1)
  {
    $select = "n.noticeId, n.commentId, IF(n.parentId IS NOT NULL, OLD_PASSWORD(n.parentId), null) AS parent, n.edited, u.user, n.viewed, n.createdOn, s.headline, ";

    $where = "WHERE s.deleted = 0 AND n.deleted = 0 AND n.userId = ".$userId." ";
    if(!$comments) { $where .= "AND commentId IS null "; }
    if(!$edits) { $where .= "AND editedBy IS null "; }
    if(!$parents) { $where .= "AND parentId IS null "; }

    $user_join = "LEFT OUTER JOIN users u ";
    $user_join .= "ON u.userId = n.editedBy ";

    $sql = "SELECT * ";
    $sql .= "FROM ( ";
      $sql .= "SELECT ".$select;
      $sql .= "OLD_PASSWORD(s.headlineId) AS hashId, 'headline' AS type ";
      $sql .= "FROM notices n ";
      $sql .= "LEFT JOIN headlines s ";
      $sql .= "ON s.headlineId = n.headlineId ";
      $sql .= $user_join;
      $sql .= $where;

      $sql .= "UNION ALL ";

      $sql .= "SELECT ".$select;
      $sql .= "OLD_PASSWORD(s.clusterId) AS hashId, 'cluster' AS type ";
      $sql .= "FROM notices n ";
      $sql .= "LEFT JOIN clusters s ";
      $sql .= "ON s.clusterId = n.clusterId ";
      $sql .= $user_join;
      $sql .= $where;

      $sql .= "UNION ALL ";

      $sql .= "SELECT ".$select;
      $sql .= "OLD_PASSWORD(s.articleId) AS hashId, 'article' AS type ";
      $sql .= "FROM notices n ";
      $sql .= "LEFT JOIN articles s ";
      $sql .= "ON s.articleId = n.articleId ";
      $sql .= $user_join;
      $sql .= $where;
    $sql .= ") items ";
    $sql .= "ORDER BY createdOn DESC, noticeId DESC ";
    $sql .= "LIMIT ".(($page-1) * $limit).", ".$limit;

    $q = $this->db->query($sql);
    $results = $q->result();
    
    return $results;
  }

  /**
   * Get Stream Score by User ID
   *
   * @access public
   * @param int
   * @param int
   * @return object
   */
  public function get_score_by_user($userId = 0, $category = 0)
  {
    $user_count_query = "SELECT COUNT(userId) FROM users WHERE deleted = 0 ";

    $global_select = "SELECT 1 AS items, ";
    $global_select .= "CASE ";
      $global_select .= "WHEN (qpc IS NOT NULL AND qnc IS NOT NULL) = true ";
        $global_select .= "THEN ";
          $global_select .= "CASE ";
            $global_select .= "WHEN (qpc > 0 AND qnc > 0) ";
              $global_select .= "THEN ((qpc + (qnc * (-1.1))) / (".$user_count_query.")) ";
            $global_select .= "WHEN (qpc > 0) ";
              $global_select .= "THEN (qpc / (".$user_count_query.")) ";
            $global_select .= "ELSE ((qnc * (-1.1)) / (".$user_count_query.")) ";
          $global_select .= "END ";
      $global_select .= "ELSE 0 ";
    $global_select .= "END AS q_score, ";
    $global_select .= "CASE ";
      $global_select .= "WHEN (ipc IS NOT NULL AND inc IS NOT NULL) = true ";
        $global_select .= "THEN ";
          $global_select .= "CASE ";
            $global_select .= "WHEN (ipc > 0 AND inc > 0) ";
              $global_select .= "THEN ((ipc + (inc * (-1.1))) / (".$user_count_query.")) ";
            $global_select .= "WHEN (ipc > 0) ";
              $global_select .= "THEN (ipc / (".$user_count_query.")) ";
            $global_select .= "ELSE ((inc * (-1.1)) / (".$user_count_query.")) ";
          $global_select .= "END ";
      $global_select .= "ELSE 0 ";
    $global_select .= "END AS i_score ";

    $rankings_select = "SUM(qPositive) AS qpc, SUM(qNegative) AS qnc, SUM(iPositive) AS ipc, SUM(iNegative) AS inc";

    $where = "WHERE s.deleted = 0 ";
    $where .= "AND IF(s.editedBy = ".$userId.", 1, 0) = 1 ";
    if($category) { $where .= "AND c.categoryId = ".$category." AND c.deleted = 0 "; }

    $sql = "SELECT IF(items IS NOT NULL, SUM(items), 1) as total_items, IF(q_score IS NOT NULL, SUM(q_score), 0) AS q_score_sum, IF(i_score IS NOT NULL, SUM(i_score), 0) AS i_score_sum ";
    $sql .= "FROM ( ";
      $sql .= $global_select;
      $sql .= "FROM headlines s ";
      $sql .= "LEFT JOIN ( ";
        $sql .= "SELECT headlineId, ".$rankings_select." ";
        $sql .= "FROM rankings WHERE headlineId IS NOT NULL GROUP BY headlineId ";
      $sql .= ") r ";
      $sql .= "ON r.headlineId = s.headlineId ";
      if($category) { $sql .= "LEFT JOIN catlist c ON c.headlineId = s.headlineId "; }
      $sql .= $where;
      $sql .= "UNION ALL ";
      $sql .= $global_select;
      $sql .= "FROM clusters s ";
      $sql .= "LEFT JOIN ( ";
        $sql .= "SELECT clusterId, ".$rankings_select." ";
        $sql .= "FROM rankings WHERE clusterId IS NOT NULL GROUP BY clusterId ";
      $sql .= ") r ";
      $sql .= "ON r.clusterId = s.clusterId ";
      if($category) { $sql .= "LEFT JOIN catlist c ON c.clusterId = s.clusterId "; }
      $sql .= $where;
      $sql .= "UNION ALL ";
      $sql .= $global_select;
      $sql .= "FROM articles s ";
      $sql .= "LEFT JOIN ( ";
        $sql .= "SELECT articleId, ".$rankings_select." ";
        $sql .= "FROM rankings WHERE articleId IS NOT NULL GROUP BY articleId ";
      $sql .= ") r ";
      $sql .= "ON r.articleId = s.articleId ";
      if($category) { $sql .= "LEFT JOIN catlist c ON c.articleId = s.articleId "; }
      $sql .= $where;
    $sql .= ") scores ";
    $sql .= "WHERE items = 1 ";

    $q = $this->db->query($sql);
    $results = $q->row();
    
    return $results;
  }

  function notifications($userId, $time, $display = array('edits', 'joins', 'comments', 'mentions'), $limit = 10, $page = 1)
  {
    $select_one = "headline, instances, users, action, n.createdOn, photo, views ";

    $select_two = "GROUP_CONCAT(DISTINCT user SEPARATOR ' ') AS users, n.createdOn, u.photo, SUM(viewed) AS views ";

    $select_headline = "SELECT DISTINCT n.headlineId, COUNT(n.headlineId) AS instances, ";

    $select_cluster = "SELECT DISTINCT n.clusterId, COUNT(n.clusterId) AS instances, ";

    $select_article = "SELECT DISTINCT n.articleId, COUNT(n.articleId) AS instances, ";

    $notice_join = "SELECT * FROM notices ORDER BY createdOn ";

    $h_notice_join = "FROM ( ";
      $h_notice_join .= $notice_join;
    $h_notice_join .= ") n ";
    $h_notice_join .= "LEFT JOIN users u ";
    $h_notice_join .= "ON u.userId = n.editedBy ";

    $c_notice_join = "FROM ( ";
      $c_notice_join .= $notice_join;
    $c_notice_join .= ") n ";
    $c_notice_join .= "LEFT JOIN users u ";
    $c_notice_join .= "ON u.userId = n.editedBy ";

    $a_notice_join = "FROM ( ";
      $a_notice_join .= $notice_join;
    $a_notice_join .= ") n ";
    $a_notice_join .= "LEFT JOIN users u ";
    $a_notice_join .= "ON u.userId = n.editedBy ";

    $notice_where =  "AND n.createdOn >= $time ";
    $notice_where .= "AND n.userId = $userId ";
    $notice_where .= "AND n.deleted = 0 ";
    $notice_where .= "AND u.deleted = 0 ";

    $main_where =  "WHERE s.deleted = 0 ";
    $main_where .= "AND instances > 0 ";

    $start = ($page-1) * $limit;

    $sql = "SELECT * ";
    $sql .= "FROM ( ";
      $sql .= "SELECT s.headlineId AS id, OLD_PASSWORD(s.headlineId) AS hashId, 'headline' AS type, $select_one";
      $sql .= "FROM headlines s ";
      $sql .= "LEFT JOIN ( ";
        $sections = 0;
    
        if(in_array('edits', $display))
        {
          $sql .= "$select_headline 'edits' AS action, $select_two";
          $sql .= $h_notice_join;
          $sql .= "WHERE n.edited = 1 $notice_where";
          $sql .= "GROUP BY headlineId ";
          
          $sections++;
        }

        if(in_array('joins', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_headline 'joins' AS action, $select_two";
          $sql .= $h_notice_join;
          $sql .= "WHERE parentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY headlineId ";
          
          $sections++;
        }

        if(in_array('comments', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_headline 'comments' AS action, $select_two";
          $sql .= $h_notice_join;
          $sql .= "WHERE commentId IS NOT NULL AND mention = 0 $notice_where";
          $sql .= "GROUP BY headlineId ";
          
          $sections++;
        }

        if(in_array('mentions', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_headline 'mentions' AS action, $select_two";
          $sql .= $h_notice_join;
          $sql .= "WHERE commentId IS NOT NULL AND mention = 1 $notice_where";
          $sql .= "GROUP BY headlineId ";
          
          $sections++;
        }
      $sql .= ") n ";
      $sql .= "ON n.headlineId = s.headlineId ";
      $sql .= $main_where;

      $sql .= "UNION ALL ";

      $sql .= "SELECT s.clusterId AS id, OLD_PASSWORD(s.clusterId) AS hashId, 'cluster' AS type, $select_one";
      $sql .= "FROM clusters s ";
      $sql .= "LEFT JOIN ( ";
        $sections = 0;
    
        if(in_array('edits', $display))
        {
          $sql .= "$select_cluster 'edits' AS action, $select_two";
          $sql .= $c_notice_join;
          $sql .= "WHERE n.edited = 1 $notice_where";
          $sql .= "GROUP BY clusterId ";
          
          $sections++;
        }

        if(in_array('joins', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_cluster 'joins' AS action, $select_two";
          $sql .= $c_notice_join;
          $sql .= "WHERE parentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY clusterId ";
          
          $sections++;
        }

        if(in_array('comments', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_cluster 'comments' AS action, $select_two";
          $sql .= $c_notice_join;
          $sql .= "WHERE commentId IS NOT NULL AND mention = 0 $notice_where";
          $sql .= "GROUP BY clusterId ";
          
          $sections++;
        }

        if(in_array('mentions', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_cluster 'mentions' AS action, $select_two";
          $sql .= $c_notice_join;
          $sql .= "WHERE commentId IS NOT NULL AND mention = 1 $notice_where";
          $sql .= "GROUP BY clusterId ";
          
          $sections++;
        }
      $sql .= ") n ";
      $sql .= "ON n.clusterId = s.clusterId ";
      $sql .= $main_where;

      $sql .= "UNION ALL ";

      $sql .= "SELECT s.articleId AS id, OLD_PASSWORD(s.articleId) AS hashId, 'article' AS type, $select_one";
      $sql .= "FROM articles s ";
      $sql .= "LEFT JOIN ( ";
        $sections = 0;
    
        if(in_array('edits', $display))
        {
          $sql .= "$select_article 'edits' AS action, $select_two";
          $sql .= $a_notice_join;
          $sql .= "WHERE n.edited = 1 $notice_where";
          $sql .= "GROUP BY articleId ";
          
          $sections++;
        }

        if(in_array('joins', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_article 'joins' AS action, $select_two";
          $sql .= $a_notice_join;
          $sql .= "WHERE parentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY articleId ";
          
          $sections++;
        }

        if(in_array('comments', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_article 'comments' AS action, $select_two";
          $sql .= $a_notice_join;
          $sql .= "WHERE commentId IS NOT NULL AND mention = 0 $notice_where";
          $sql .= "GROUP BY articleId ";
          
          $sections++;
        }

        if(in_array('mentions', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_article 'mentions' AS action, $select_two";
          $sql .= $a_notice_join;
          $sql .= "WHERE commentId IS NOT NULL AND mention = 1 $notice_where";
          $sql .= "GROUP BY articleId ";
          
          $sections++;
        }
      $sql .= ") n ";
      $sql .= "ON n.articleId = s.articleId ";
      $sql .= $main_where;
    $sql .= ") items ";
    $sql .= "ORDER BY createdOn DESC ";
    $sql .= "LIMIT $start, $limit";

    $q = $this->db->query($sql);
    return $q->result();
  }

  function notifications_count($userId, $time, $display = array('edits', 'joins', 'comments', 'mentions'), $views = "all")
  {
    $select_headline = "SELECT DISTINCT n.headlineId, COUNT(n.headlineId) AS instances, ";

    $select_cluster = "SELECT DISTINCT n.clusterId, COUNT(n.clusterId) AS instances, ";

    $select_article = "SELECT DISTINCT n.articleId, COUNT(n.articleId) AS instances, ";

    $notice_join =  "FROM notices n ";
    $notice_join .= "LEFT JOIN users u ";
    $notice_join .= "ON n.editedBy = u.userId ";

    $notice_where =  "AND n.createdOn >= $time ";
    $notice_where .= "AND n.userId = $userId ";
    $notice_where .= "AND n.deleted = 0 ";
    if($views == "not") { $notice_where .= "AND n.viewed = 0 "; }
    $notice_where .= "AND u.deleted = 0 ";

    $main_where =  "WHERE s.deleted = 0 ";
    $main_where .= "AND instances > 0 ";

    $sql = "SELECT COUNT(id) AS items ";
    $sql .= "FROM ( ";
      $sql .= "SELECT s.headlineId AS id, OLD_PASSWORD(s.headlineId) AS hashId, 'headline' AS type ";
      $sql .= "FROM headlines s ";
      $sql .= "LEFT JOIN ( ";
        $sections = 0;
    
        if(in_array('edits', $display))
        {
          $sql .= "$select_headline 'edits' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE n.edited = 1 $notice_where";
          $sql .= "GROUP BY headlineId ";
          
          $sections++;
        }

        if(in_array('joins', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_headline 'joins' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE parentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY headlineId ";
          
          $sections++;
        }

        if(in_array('comments', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_headline 'comments' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE commentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY headlineId ";
          
          $sections++;
        }

        if(in_array('mentions', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_headline 'mentions' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE commentId IS NOT NULL AND mention = 1 $notice_where";
          $sql .= "GROUP BY headlineId ";
          
          $sections++;
        }
      $sql .= ") n ";
      $sql .= "ON n.headlineId = s.headlineId ";
      $sql .= $main_where;

      $sql .= "UNION ALL ";

      $sql .= "SELECT s.clusterId AS id, OLD_PASSWORD(s.clusterId) AS hashId, 'cluster' AS type ";
      $sql .= "FROM clusters s ";
      $sql .= "LEFT JOIN ( ";
        $sections = 0;
    
        if(in_array('edits', $display))
        {
          $sql .= "$select_cluster 'edits' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE n.edited = 1 $notice_where";
          $sql .= "GROUP BY clusterId ";
          
          $sections++;
        }

        if(in_array('joins', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_cluster 'joins' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE parentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY clusterId ";
          
          $sections++;
        }

        if(in_array('comments', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_cluster 'comments' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE commentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY clusterId ";
          
          $sections++;
        }

        if(in_array('mentions', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_cluster 'mentions' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE commentId IS NOT NULL AND mention = 1 $notice_where";
          $sql .= "GROUP BY clusterId ";
          
          $sections++;
        }
      $sql .= ") n ";
      $sql .= "ON n.clusterId = s.clusterId ";
      $sql .= $main_where;

      $sql .= "UNION ALL ";

      $sql .= "SELECT s.articleId AS id, OLD_PASSWORD(s.articleId) AS hashId, 'article' AS type ";
      $sql .= "FROM articles s ";
      $sql .= "LEFT JOIN ( ";
        $sections = 0;
    
        if(in_array('edits', $display))
        {
          $sql .= "$select_article 'edits' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE n.edited = 1 $notice_where";
          $sql .= "GROUP BY articleId ";
          
          $sections++;
        }

        if(in_array('joins', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_article 'joins' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE parentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY articleId ";
          
          $sections++;
        }

        if(in_array('comments', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_article 'comments' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE commentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY articleId ";
          
          $sections++;
        }

        if(in_array('mentions', $display))
        {
          if($sections) { $sql .= "UNION ALL "; }
        
          $sql .= "$select_article 'mentions' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE commentId IS NOT NULL AND mention = 1 $notice_where";
          $sql .= "GROUP BY articleId ";
          
          $sections++;
        }
      $sql .= ") n ";
      $sql .= "ON n.articleId = s.articleId ";
      $sql .= $main_where;
    $sql .= ") items ";

    $q = $this->db->query($sql);
    return $q->row()->items;
  }

  /**
   * Join Items by Array
   *
   * @access public
   * @param array
   * @return object
   */
  public function manual_join($selected = array())
  {
    // Load Separate Model
    $this->ci =& get_instance();
    $this->ci->load->model('database_model', 'database', true);
    $this->ci->load->model('utility_model', 'utility', true);
    // Var
    $userId = $this->session->userdata('userId');
    $where_score = '((keyword_score >= .75 AND place_score >= .6 AND category_score > 0) OR (keyword_score >= .85 AND (place_score > .3 OR placeId IS NULL) AND category_score > 0))';
      // Process
    if(sizeof($selected['article']) == 1)
    {
      $article = $this->database_model->get_single('articles', array('articleId' => $selected['article'][0]), 'adminOnly');
      if(!$article->adminOnly || $this->session->userdata('level') == 'a')
      {
        // Update Cluster Matches
        $clusters_update = array('articleId' => $selected['article'][0], 'editedBy' => $userId);
        foreach($selected['cluster'] as $c)
        {
          $cluster = $this->database_model->get_single('clusters', array('clusterId' => $c), 'adminOnly');
          if(!$cluster->adminOnly || $this->session->userdata('level') == 'a')
          {
            $nInsert = array('clusterId' => $c, 'parentId' => $selected['article'][0], 'createdOn' => time());
            $subscribers = $this->database_model->get('subscriptions', array('clusterId' => $c, 'deleted' => 0));
            foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
            $this->ci->database->edit('clusters', array('clusterId' => $c), $clusters_update);
          }
        }
        // Update Headline Matches
        $newCluster = array();
        foreach($selected['headline'] as $h)
        {
          $headline = $this->database_model->get_single('headlines', array('headlineId' => $h), 'adminOnly');
          if(!$headline->adminOnly || $this->session->userdata('level') == 'a')
          {
            $hl = $this->ci->database->get_single('headlines', array('headlineId' => $h, 'deleted' => 0));
            $compared = array();
            foreach($selected['cluster'] as $c)
            {
              $cluster = $this->database_model->get_single('clusters', array('clusterId' => $c), 'adminOnly');
              if(!$cluster->adminOnly || $this->session->userdata('level') == 'a')
              {
                $cl = $this->ci->database->get_single('clusters', array('clusterId' => $c, 'deleted' => 0));
                if($score = $this->item_compare($h1, $c->headline, $c->tags)) { $compared[$c] = $score; }
              }
            }
            if($compared)
            {
              asort($compared);
              $clusterId = key($compared);
              //
              $nInsert = array('headlineId' => $c, 'parentId' => $clusterId, 'createdOn' => time());
              $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $h, 'deleted' => 0));
              foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
              $this->ci->database->edit('headlines', array('headlineId' => $h), array('clusterId' => $clusterId, 'editedBy' => $userId));
            }
            else
            {
              $newCluster[] = array('id' => $h);
            }
          }
        }
        if($newCluster) { $this->new_cluster($newCluster, $userId, $selected['article'][0]); }
      }
    }
    elseif(sizeof($selected['cluster']) == 1)
    {
      $cluster = $this->database_model->get_single('clusters', array('clusterId' => $selected['cluster'][0]), 'adminOnly');
      if(!$cluster->adminOnly || $this->session->userdata('level') == 'a')
      {
        $headlines_update = array('clusterId' => $selected['cluster'][0], 'editedBy' => $userId);
        foreach($selected['headline'] as $h)
        {
          $headline = $this->database_model->get_single('headlines', array('headlineId' => $h), 'adminOnly');
          if(!$headline->adminOnly || $this->session->userdata('level') == 'a')
          {
            $nInsert = array('headlineId' => $h, 'parentId' => $selected['cluster'][0], 'createdOn' => time());
            $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $h, 'deleted' => 0));
            foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
            $this->ci->database->edit('headlines', array('headlineId' => $h), $headlines_update);
          }
        }
      }
    }
    elseif($selected['cluster'])
    {
      // Update Cluster Matches
      $articleId = 0;
      $newArticle = array();
      foreach($selected['cluster'] as $c)
      {
        $cluster = $this->database_model->get_single('clusters', array('clusterId' => $c));
        if(!$cluster->adminOnly || $this->session->userdata('level') == 'a')
        {
          $newArticle[] = array('id' => $c);
        }
      }
      if($newArticle) { $this->new_article($newArticle, $userId); }
      // Update Headline Matches
      $newCluster = array();
      foreach($selected['headline'] as $h)
      {
        $headline = $this->database_model->get_single('headlines', array('headlineId' => $h), 'adminOnly');
        if(!$headline->adminOnly || $this->session->userdata('level') == 'a')
        {
          $hl = $this->ci->database->get_single('headlines', array('headlineId' => $h, 'deleted' => 0));
          $compared = array();
          foreach($selected['cluster'] as $c)
          {
            $cl = $this->ci->database->get_single('clusters', array('clusterId' => $c, 'deleted' => 0));
            if($score = $this->item_compare($h1, $c->headline, $c->tags)) { $compared[$c] = $score; }
          }
          if($compared)
          {
            asort($compared);
            $clusterId = key($compared);
            //
            $nInsert = array('headlineId' => $c, 'parentId' => $clusterId, 'createdOn' => time());
            $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $h, 'deleted' => 0));
            foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
            $this->ci->database->edit('headlines', array('headlineId' => $h), array('clusterId' => $clusterId, 'editedBy' => $userId));
          }
          else
          {
            $newCluster[] = array('id' => $h);
          }
        }
      }
      if($newCluster) { $this->new_cluster($newCluster, $userId, $articleId); }
    }
    elseif(sizeof($selected['headline']) > 1)
    {
      // Update Headline Matches
      $newCluster = array();
      $i = 0;
      foreach($selected['headline'] as $h)
      {
        $headline = $this->database_model->get_single('headlines', array('headlineId' => $h), 'adminOnly');
        if(!$headline->adminOnly || $this->session->userdata('level') == 'a')
        {
          $hl = $this->ci->database->get_single('headlines', array('headlineId' => $h, 'deleted' => 0));
          $compared = array();
          foreach($selected['cluster'] as $c)
          {
            $cl = $this->ci->database->get_single('clusters', array('clusterId' => $c, 'deleted' => 0));
            if($score = $this->item_compare($h1, $c->headline, $c->tags)) { $compared[$c] = $score; }
          }
          if($compared)
          {
            asort($compared);
            $clusterId = key($compared);
            //
            $nInsert = array('headlineId' => $c, 'parentId' => $clusterId, 'createdOn' => time());
            $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $h, 'deleted' => 0));
            foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
            $this->ci->database->edit('headlines', array('headlineId' => $h), array('clusterId' => $clusterId, 'editedBy' => $userId));
          }
          else
          {
            $newCluster[] = array('id' => $h);
          }
        }
      }
      if($newCluster) { $this->new_cluster($newCluster, $userId); }
    }
  }

  function search($terms = "", $where = "", $order = "score", $results = "all", $comments = false, $limit = 0, $page = 1, $userId = null, $subscriptions = false, $exclusive = true)
  {
    $filters = json_encode(array(
      'terms' => $terms,
      'where' => $where,
      'order' => $order,
      'results' => $results,
      'comments' => $comments,
      'limit' => ($order !== "score")?$limit:0,
      'page' => ($order !== "score")?$page:0,
      'userId' => $userId,
      'subscriptions' => $subscriptions,
      'exclusive' => $exclusive
    ));
    
    $this->ci =& get_instance();
    $this->ci->load->model('database_model', 'database', true);
    $this->ci->load->model('utility_model', 'utility', true);
    
    $query = $this->ci->database->get_single('search', array('filters' => $filters));
    
    if($query && $query->editedOn > time()-(60*60))
    {
      $results = json_decode($query->data);
      
      if($order == 'score') { $results = array_slice($results, ($page-1) * $limit, $limit); }
    }
    else
    {
      if($terms)
      {
        $terms = str_replace(",", " ", $terms);
        $terms = preg_replace('/[^A-Za-z0-9 ]/', '', $terms);
        $terms = $this->ci->utility->blwords_strip($terms, 'regEx_spaces', ' ');
        
        $w = 0;
        $h_search = "(";
        $c_search = "(";
        $a_search = "(";
        $terms_array = explode(' ', $terms);
        $arrayKeys = array_keys($terms_array);
        $lastArrayKey = array_pop($arrayKeys);
        $ext_terms_array = array();
        foreach($terms_array as $k => $t)
        {
          $t = $this->db->escape_str($t, true);
          
          $h_temp = "s.headline LIKE '%".$t."%' ";
          $h_temp .= "OR s.tags LIKE '%".$t."%' ";
          $c_temp = "h_headline LIKE '%".$t."%' ";
          $c_temp .= "OR h_tags LIKE '%".$t."%' ";
          $a_temp = "s.article LIKE '%".$t."%' ";
          $a_temp .= "OR c_headline LIKE '%".$t."%' ";
          $a_temp .= "OR c_tags LIKE '%".$t."%' ";
          
          $h_search .= ($w?"OR ":"").$h_temp;
          $c_search .= ($w?"OR ":"").$h_temp."OR ".$c_temp;
          $a_search .= ($w?"OR ":"").$h_temp."OR ".$c_temp."OR ".$a_temp;
          
          if($k !== $lastArrayKey)
          {
            $ext_terms_array[] = $t.' '.$terms_array[$k+1];
          }
          
          $w++;
        }
        $h_search .= ") ";
        $c_search .= ") ";
        $a_search .= ") ";
      }
  
      $article_scores = $scores = "m.score AS cred_score, ";
      $article_scores = $scores .= "s.decay AS decay_score ";
  
      if($comments)
      {
        $comments_include = "COUNT(commentId) as comments ";
        $comments_include .= "FROM comments ";
        $comments_include .= "WHERE deleted = 0 ";
      }
  
      $view_include = "COUNT(viewId) AS views ";
      $view_include .= "FROM views ";
  
      $viewed_include = "IF(viewId IS NULL, 0, 1) AS viewed ";
      $viewed_include .= "FROM views ";
      $viewed_include .= "WHERE userId = ".$this->session->userdata('userId')." ";
  
      $image_include = "imageId FROM images ";
      $image_include .= "WHERE deleted = 0 AND active = 1 ";
      $image_include .= "LIMIT 1 ";
  
      $sub_score = "SUM(m.score) AS cred_score, ";
      $sub_score .= "SUM(s.decay) AS decay_score ";
  
      if($results !== 'headlines')
      {
        $headline_include = "LEFT JOIN ( ";
          $headline_include .= "SELECT s.clusterId, COUNT(s.headlineId) AS h_count, ";
          $headline_include .= "s.headline AS h_headline, s.tags AS h_tags, ";
          $headline_include .= $sub_score;
          $headline_include .= "FROM headlines s ";
          $headline_include .= "LEFT JOIN metadata m ON m.headlineId = s.headlineId ";
          $headline_include .= "WHERE s.clusterId IS NOT NULL ";
          $headline_include .= "AND s.deleted = 0 ";
          $headline_include .= "GROUP BY clusterId ";
        $headline_include .= ") h ON h.clusterId = s.clusterId ";
      }
  
      $global_select = "s.headline, s.tags, i2.image, s.createdOn, s.editedOn, ";
      if($userId && !$subscriptions) { $global_select .= "s.createdBy, s.editedBy, "; }
  
      $sql = "SELECT ";
        $sql .= "*, OLD_PASSWORD(id) AS hashId, ((cred_score + sub_score) / decay_score) AS x_score ";
        //$sql .= "*, OLD_PASSWORD(id) AS hashId, (((cred_score / 2) + (sub_score / 2)) * decay_score) AS score, ((cred_score / 2) * decay_score) AS x_score ";
      $sql .= "FROM ( ";
        if(!in_array($results, array('clusters', 'articles')))
        {
          $sql .= "SELECT ";
            $sql .= "'headline' AS type, s.headlineId AS id, IF(views IS NULL, 0, views) AS views, 0 AS h_count, 0 AS c_count, 0 AS sub_score, ";
            $sql .= "s.notes AS article, '' AS c_headline, '' AS c_tags, '' AS h_headline, '' AS h_tags, ";
            $sql .= $global_select;
            if($comments) { $sql .= "comments, "; }
            if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
            $sql .= $scores;
          $sql .= "FROM headlines s ";
          $sql .= "LEFT JOIN headlines_history h ON h.headlineId = s.headlineId ";
          $sql .= "LEFT JOIN metadata m ON m.headlineId = s.headlineId ";
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT headlineId, ";
            $sql .= $view_include;
            $sql .= "GROUP BY headlineId ";
          $sql .= ") v ON v.headlineId = s.headlineId ";
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT headlineId, ";
            $sql .= $image_include;
          $sql .= ") i1 ON i1.headlineId = s.headlineId ";
          $sql .= "LEFT JOIN images i2 ON (i2.imageId = i2.imageId AND i2.headlineId = s.headlineId) ";
          if($comments)
          {
            $sql .= "LEFT JOIN ( ";
              $sql .= "SELECT headlineId, ";
              $sql .= $comments_include;
              $sql .= "GROUP BY headlineId ";
            $sql .= ") x ON x.headlineId = s.headlineId ";
          }
          if($subscriptions) { $sql .= "JOIN subscriptions b ON b.headlineId = s.headlineId "; }
          if(in_array($results, array('visited', 'unvisited')))
          {
            $sql .= "LEFT JOIN (";
              $sql .= "SELECT headlineId, ";
              $sql .= $viewed_include;
              $sql .= "GROUP BY headlineId ";
            $sql .= ") d ON d.headlineId = s.headlineId ";
          }
          $sql .= "WHERE s.deleted = 0 AND s.hidden = 0 ";
            $sql .= "AND ((i2.active = 1 && i2.deleted = 0) || i2.imageId IS null) ";
            if($terms) { $sql .= "AND ".$h_search; }
            if($exclusive && !$subscriptions) { $sql .= "AND s.clusterId IS null "; }
            if($userId && !$subscriptions) { $sql .= "AND (s.createdBy = ".$userId." OR h.editedBy = ".$userId.") "; }
            if($subscriptions) { $sql .= "AND b.userId = ".$userId." AND b.deleted = 0 "; }
          $sql .= "GROUP BY s.headlineId ";
        }
  
        if(!in_array($results, array('headlines', 'clusters', 'articles'))) { $sql .= "UNION ALL "; }
  
        if(!in_array($results, array('headlines', 'articles')))
        {
          $sql .= "SELECT ";
            $sql .= "'cluster' AS type, s.clusterId AS id, IF(views IS NULL, 0, views) AS views, h_count, 0 AS c_count, ";
            $sql .= "((((h.cred_score / 2)) * h.decay_score) / h_count) AS sub_score, ";
            $sql .= "'' AS article, '' AS c_headline, '' AS c_tags, GROUP_CONCAT(h_headline SEPARATOR ' '), GROUP_CONCAT(h_tags SEPARATOR ' '), ";
            $sql .= $global_select;
            if($comments) { $sql .= "comments, "; }
            if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
            $sql .= $scores;
          $sql .= "FROM clusters s ";
          $sql .= "LEFT JOIN clusters_history c ON c.clusterId = s.clusterId ";
          $sql .= "LEFT JOIN metadata m ON m.clusterId = s.clusterId ";
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT clusterId, ";
            $sql .= $view_include;
            $sql .= "GROUP BY clusterId ";
          $sql .= ") v ON v.clusterId = s.clusterId ";
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT clusterId, ";
            $sql .= $image_include;
          $sql .= ") i1 ON i1.clusterId = s.clusterId ";
          $sql .= "LEFT JOIN images i2 ON (i2.imageId = i2.imageId AND i2.clusterId = s.clusterId) ";
          $sql .= $headline_include;
          if($comments)
          {
            $sql .= "LEFT JOIN ( ";
              $sql .= "SELECT clusterId, ";
              $sql .= $comments_include;
              $sql .= "GROUP BY clusterId ";
            $sql .= ") x ON x.clusterId = s.clusterId ";
          }
          if($subscriptions) { $sql .= "JOIN subscriptions b ON b.clusterId = s.clusterId "; }
          if(in_array($results, array('visited', 'unvisited')))
          {
            $sql .= "LEFT JOIN (";
              $sql .= "SELECT clusterId, ";
              $sql .= $viewed_include;
            $sql .= ") d ON d.clusterId = s.clusterId ";
          }
          $sql .= "WHERE s.deleted = 0 AND s.hidden = 0 ";
            $sql .= "AND ((i2.active = 1 && i2.deleted = 0) || i2.imageId IS null) ";
            if($terms) { $sql .= "AND ".$c_search; }
            if($exclusive && !$subscriptions) { $sql .= "AND s.articleId IS null "; }
            if($userId && !$subscriptions) { $sql .= "AND (s.createdBy = ".$userId." OR c.editedBy = ".$userId.") "; }
            if($subscriptions) { $sql .= "AND b.userId = ".$userId." AND b.deleted = 0 "; }
          $sql .= "GROUP BY s.clusterId ";
        }
  
        if(!in_array($results, array('headlines', 'clusters', 'articles'))) { $sql .= "UNION ALL "; }
  
        if(!in_array($results, array('headlines', 'clusters')))
        {
          $sql .= "SELECT ";
            $sql .= "'article' AS type, s.articleId AS id, IF(views IS NULL, 0, views) AS views, h_count, c_count, ";
            $sql .= "((((c.cred_score / 2) + c.sub_score) * c.decay_score) / c_count) AS sub_score, ";
            $sql .= "s.article, GROUP_CONCAT(c_headline SEPARATOR ' '), GROUP_CONCAT(c_tags SEPARATOR ' '), h_headline, h_tags, ";
            $sql .= $global_select;
            if($comments) { $sql .= "comments, "; }
            if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
            $sql .= $article_scores;
          $sql .= "FROM articles s ";
          $sql .= "LEFT JOIN articles_history a ON a.articleId = s.articleId ";
          $sql .= "LEFT JOIN metadata m ON m.articleId = s.articleId ";
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT articleId, ";
            $sql .= $view_include;
            $sql .= "GROUP BY articleId ";
          $sql .= ") v ON v.articleId = s.articleId ";
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT articleId, ";
            $sql .= $image_include;
          $sql .= ") i1 ON i1.articleId = s.articleId ";
          $sql .= "LEFT JOIN images i2 ON (i2.imageId = i2.imageId AND i2.articleId = s.articleId) ";
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT s.articleId, COUNT(s.clusterId) AS c_count, SUM(h.h_count) AS h_count, ";
            $sql .= "((((h.cred_score / 2)) * h.decay_score) / h_count) AS sub_score, ";
            $sql .= "s.headline AS c_headline, s.tags AS c_tags, h_headline, h_tags, ";
            $sql .= $sub_score;
            $sql .= "FROM clusters s ";
            $sql .= "LEFT JOIN metadata m ON m.clusterId = s.clusterId ";
            $sql .= $headline_include;
            $sql .= "WHERE s.articleId IS NOT NULL ";
            $sql .= "AND s.deleted = 0 ";
            $sql .= "GROUP BY articleId ";
          $sql .= ") c ON c.articleId = s.articleId ";
          if($comments)
          {
            $sql .= "LEFT JOIN ( ";
              $sql .= "SELECT articleId, ";
              $sql .= $comments_include;
              $sql .= "GROUP BY articleId ";
            $sql .= ") x ON x.articleId = s.articleId ";
          }
          if($subscriptions) { $sql .= "JOIN subscriptions b ON b.articleId = s.articleId "; }
          if(in_array($results, array('visited', 'unvisited')))
          {
            $sql .= "LEFT JOIN (";
              $sql .= "SELECT articleId, ";
              $sql .= $viewed_include;
            $sql .= ") d ON d.articleId = s.articleId ";
          }
          $sql .= "WHERE s.deleted = 0 AND s.hidden = 0 ";
            $sql .= "AND ((i2.active = 1 && i2.deleted = 0) || i2.imageId IS null) ";
            if($terms) { $sql .= "AND ".$a_search; }
            if($userId && !$subscriptions) { $sql .= "AND (s.createdBy = ".$userId." OR a.editedBy = ".$userId.") "; }
            if($subscriptions) { $sql .= "AND b.userId = ".$userId." AND b.deleted = 0 "; }
          $sql .= "GROUP BY s.articleId ";
        }
      $sql .= ") items ";
      $sql .= "WHERE 1 = 1 ";
      if($where) { $sql .= "AND ".$where." "; }
      if($results == 'visited') { $sql .= "AND visited = 1 "; }
      if($results == 'unvisited') { $sql .= "AND visited = 0 "; }
      if($order !== 'score') { $sql .= "ORDER BY ".$order." DESC "; }
      else { $sql .= "ORDER BY x_score DESC "; }
      if($limit && $order !== 'score') { $sql .= "LIMIT ".(($page-1) * $limit).", ".$limit; }
  
      $q = $this->db->query($sql);
      $results = $q->result();
      
      if($terms && $order == "score")
      {
        $total = array();
        $search = array();
        $sub = array();
        $cred = array();
        $decay = array();
        foreach($results as $key => $r)
        {
          $score = 0;
          foreach(array_merge($terms_array, $ext_terms_array) as $t)
          {
            if(preg_match('/(\b'.$t.'\b|\B'.$t.'\b|\b'.$t.'\B|\B'.$t.'\B)/i', $r->headline))
            {
              if(preg_match('/(\b'.$t.'\b)/i', $r->headline, $matches)) { $score += 1.00 * sizeof($matches);  }
              elseif(preg_match('/(\B'.$t.'\b|\b'.$t.'\B)/i', $r->headline, $matches)) { $score += 0.85 * sizeof($matches);  }
              elseif(preg_match('/(\B'.$t.'\B)/i', $r->headline, $matches)) { $score += 0.70 * sizeof($matches);  }
            }
            if(preg_match('/(\b'.$t.'\b|\B'.$t.'\b|\b'.$t.'\B|\B'.$t.'\B)/i', $r->tags))
            {
              if(preg_match('/(\b'.$t.'\b)/i', $r->tags, $matches)) { $score += 0.90 * sizeof($matches);  }
              elseif(preg_match('/(\B'.$t.'\b|\b'.$t.'\B)/i', $r->tags, $matches)) { $score += 0.75 * sizeof($matches);  }
              elseif(preg_match('/(\B'.$t.'\B)/i', $r->tags, $matches)) { $score += 0.60 * sizeof($matches);  }
            }
            if($r->type == "article")
            {
              if($r->article && preg_match('/(\b'.$t.'\b|\B'.$t.'\b|\b'.$t.'\B|\B'.$t.'\B)/i', $r->article))
              {
                if(preg_match('/(\b'.$t.'\b)/i', $r->article, $matches)) { $score += 0.90 * sizeof($matches);  }
                elseif(preg_match('/(\B'.$t.'\b|\b'.$t.'\B)/i', $r->article, $matches)) { $score += 0.75 * sizeof($matches);  }
                elseif(preg_match('/(\B'.$t.'\B)/i', $r->article, $matches)) { $score += 0.60 * sizeof($matches);  }
              }
              if($r->c_headline && preg_match('/(\b'.$t.'\b|\B'.$t.'\b|\b'.$t.'\B|\B'.$t.'\B)/i', $r->c_headline))
              {
                if(preg_match('/(\b'.$t.'\b)/i', $r->c_headline, $matches)) { $score += 0.80 * sizeof($matches) / $r->c_count;  }
                elseif(preg_match('/(\B'.$t.'\b|\b'.$t.'\B)/i', $r->c_headline, $matches)) { $score += 0.65 * sizeof($matches) / $r->c_count;  }
                elseif(preg_match('/(\B'.$t.'\B)/i', $r->c_headline, $matches)) { $score += 0.50 * sizeof($matches) / $r->c_count;  }
              }
              if($r->c_tags && preg_match('/(\b'.$t.'\b|\B'.$t.'\b|\b'.$t.'\B|\B'.$t.'\B)/i', $r->c_tags))
              {
                if(preg_match('/(\b'.$t.'\b)/i', $r->c_tags, $matches)) { $score += 0.70 * sizeof($matches) / $r->c_count;  }
                elseif(preg_match('/(\B'.$t.'\b|\b'.$t.'\B)/i', $r->c_tags, $matches)) { $score += 0.55 * sizeof($matches) / $r->c_count;  }
                elseif(preg_match('/(\B'.$t.'\B)/i', $r->c_tags, $matches)) { $score += 0.40 * sizeof($matches) / $r->c_count;  }
              }
              if($r->h_headline && preg_match('/(\b'.$t.'\b|\B'.$t.'\b|\b'.$t.'\B|\B'.$t.'\B)/i', $r->h_headline))
              {
                if(preg_match('/(\b'.$t.'\b)/i', $r->h_headline, $matches)) { $score += 0.60 * sizeof($matches) / $r->h_count;  }
                elseif(preg_match('/(\B'.$t.'\b|\b'.$t.'\B)/i', $r->h_headline, $matches)) { $score += 0.45 * sizeof($matches) / $r->h_count;  }
                elseif(preg_match('/(\B'.$t.'\B)/i', $r->h_headline, $matches)) { $score += 0.30 * sizeof($matches) / $r->h_count;  }
              }
              if($r->h_tags && preg_match('/(\b'.$t.'\b|\B'.$t.'\b|\b'.$t.'\B|\B'.$t.'\B)/i', $r->h_tags))
              {
                if(preg_match('/(\b'.$t.'\b)/i', $r->h_tags, $matches)) { $score += 0.50 * sizeof($matches) / $r->h_count;  }
                elseif(preg_match('/(\B'.$t.'\b|\b'.$t.'\B)/i', $r->h_tags, $matches)) { $score += 0.35 * sizeof($matches) / $r->h_count;  }
                elseif(preg_match('/(\B'.$t.'\B)/i', $r->h_tags, $matches)) { $score += 0.20 * sizeof($matches) / $r->h_count;  }
              }
            }
            elseif($r->type == "cluster")
            {
              if($r->h_headline && preg_match('/(\b'.$t.'\b|\B'.$t.'\b|\b'.$t.'\B|\B'.$t.'\B)/i', $r->h_headline))
              {
                if(preg_match('/(\b'.$t.'\b)/i', $r->h_headline, $matches)) { $score += 0.80 * sizeof($matches) / $r->h_count;  }
                elseif(preg_match('/(\B'.$t.'\b|\b'.$t.'\B)/i', $r->h_headline, $matches)) { $score += 0.65 * sizeof($matches) / $r->h_count;  }
                elseif(preg_match('/(\B'.$t.'\B)/i', $r->h_headline, $matches)) { $score += 0.50 * sizeof($matches) / $r->h_count;  }
              }
              if($r->h_tags && preg_match('/(\b'.$t.'\b|\B'.$t.'\b|\b'.$t.'\B|\B'.$t.'\B)/i', $r->h_tags))
              {
                if(preg_match('/(\b'.$t.'\b)/i', $r->h_tags, $matches)) { $score += 0.70 * sizeof($matches) / $r->h_count;  }
                elseif(preg_match('/(\B'.$t.'\b|\b'.$t.'\B)/i', $r->h_tags, $matches)) { $score += 0.55 * sizeof($matches) / $r->h_count;  }
                elseif(preg_match('/(\B'.$t.'\B)/i', $r->h_tags, $matches)) { $score += 0.40 * sizeof($matches) / $r->h_count;  }
              }
            }
            else
            {  
              if($r->article && preg_match('/(\b'.$t.'\b|\B'.$t.'\b|\b'.$t.'\B|\B'.$t.'\B)/i', $r->article))
              {
                if(preg_match('/(\b'.$t.'\b)/i', $r->article, $matches)) { $score += 0.90 * sizeof($matches);  }
                elseif(preg_match('/(\B'.$t.'\b|\b'.$t.'\B)/i', $r->article, $matches)) { $score += 0.75 * sizeof($matches);  }
                elseif(preg_match('/(\B'.$t.'\B)/i', $r->article, $matches)) { $score += 0.60 * sizeof($matches);  }
              }
            }
          }
          
          $search[$key] = $score / (sizeof($terms_array) + sizeof($ext_terms_array));
          $total[$key]  = ($search[$key] + $r->cred_score + $r->sub_score) / $r->decay_score;
          $sub[$key] = $r->sub_score;
          $cred[$key] = $r->cred_score;
          $decay[$key] = $r->decay_score;
        }
        array_multisort($total, SORT_DESC, $search, SORT_DESC, $sub, SORT_DESC, $cred, SORT_DESC, $decay, SORT_DESC, $results);
      }
      
      if($order !== 'createdOn')
      {
        if($query)
        {
          $this->ci->database->edit('search', array('searchId' => $query->searchId), array('data' => json_encode($results)), false);
        }
        else
        {
          $this->ci->database->add('search', array('filters' => $filters, 'data' => json_encode($results)));
        }
      }
      
      if($order == 'score') { $results = array_slice($results, ($page-1) * $limit, $limit); }
    }
    
    return $results;
  }

  function search_count($terms = "", $where = "", $order = "score", $results = "all", $comments = false, $limit = 0, $page = 1, $userId = null, $subscriptions = false, $exclusive = true)
  {
    
    $this->ci =& get_instance();
    $this->ci->load->model('database_model', 'database', true);
    $this->ci->load->model('utility_model', 'utility', true);
    
    if($order == "score")
    {
      $filters = json_encode(array(
        'terms' => $terms,
        'where' => $where,
        'order' => $order,
        'results' => $results,
        'comments' => $comments,
        'limit' => ($order !== "score")?$limit:0,
        'page' => ($order !== "score")?$page:0,
        'userId' => $userId,
        'subscriptions' => $subscriptions,
        'exclusive' => $exclusive
      ));
    
      $query = $this->ci->database->get_single('search', array('filters' => $filters));
    
      if($query && $query->editedOn > time()-(60*60))
      {
        $results = json_decode($query->data);
        
        $count = sizeof($results);
      }
    }
    
    if(!isset($count))
    {

      if($terms)
      {
        $terms = str_replace(",", " ", $terms);
        $terms = preg_replace('/[^A-Za-z0-9 ]/', '', $terms);
        $terms = $this->ci->utility->blwords_strip($terms, 'regEx_spaces', ' ');
        
        $w = 0;
        $h_search = "(";
        $c_search = "(";
        $a_search = "(";
        $t_list = explode(' ', $terms);
        foreach($t_list as $t)
        {
          $t = $this->db->escape_str($t, true);
          
          $h_temp = "s.headline LIKE '%".$t."%' ";
          $h_temp .= "OR s.tags LIKE '%".$t."%' ";
          $c_temp = "h_headline LIKE '%".$t."%' ";
          $c_temp .= "OR h_tags LIKE '%".$t."%' ";
          $a_temp = "s.article LIKE '%".$t."%' ";
          $a_temp .= "OR c_headline LIKE '%".$t."%' ";
          $a_temp .= "OR c_tags LIKE '%".$t."%' ";
          
          $h_search .= ($w?"OR ":"").$h_temp;
          $c_search .= ($w?"OR ":"").$h_temp."OR ".$c_temp;
          $a_search .= ($w?"OR ":"").$h_temp."OR ".$c_temp."OR ".$a_temp;
        
          $w++;
        }
        $h_search .= ") ";
        $c_search .= ") ";
        $a_search .= ") ";
      }
  
      $article_scores = $scores = "(m.score) AS cred_score, ";
      $article_scores = $scores .= "s.decay AS decay_score ";
  
      $view_include = "COUNT(viewId) AS views ";
      $view_include .= "FROM views ";
  
      $viewed_include = "IF(viewId IS NULL, 0, 1) AS viewed ";
      $viewed_include .= "FROM views ";
      $viewed_include .= "WHERE userId = ".$this->session->userdata('userId')." ";
  
      $sub_score = "SUM(m.score) AS cred_score, ";
      $sub_score .= "SUM(s.decay) AS decay_score ";
  
      if($results !== 'headline')
      {
        $headline_include = "LEFT JOIN ( ";
          $headline_include .= "SELECT s.clusterId, COUNT(s.headlineId) AS h_count, ";
          $headline_include .= "s.headline AS h_headline, s.tags AS h_tags, ";
          $headline_include .= $sub_score;
          $headline_include .= "FROM headlines s ";
          $headline_include .= "LEFT JOIN metadata m ON m.headlineId = s.headlineId ";
          $headline_include .= "WHERE s.clusterId IS NOT NULL ";
          $headline_include .= "AND s.deleted = 0 ";
          $headline_include .= "GROUP BY clusterId ";
        $headline_include .= ") h ON h.clusterId = s.clusterId ";
      }
  
      $sql = "SELECT ";
        $sql .= "COUNT(id) AS items ";
      $sql .= "FROM ( ";
        if(!in_array($results, array('clusters', 'articles')))
        {
          $sql .= "SELECT ";
            $sql .= "'headline' AS type, s.headlineId AS id, s.createdBy, s.editedBy, IF(views IS NULL, 0, views) AS views, 0 AS h_count, 0 AS c_count, 0 AS sub_score, ";
            if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
            $sql .= $scores;
          $sql .= "FROM headlines s ";
          $sql .= "LEFT JOIN metadata m ON m.headlineId = s.headlineId ";
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT headlineId, ";
            $sql .= $view_include;
            $sql .= "GROUP BY headlineId ";
          $sql .= ") v ON v.headlineId = s.headlineId ";
          if($subscriptions) { $sql .= "JOIN subscriptions b ON b.headlineId = s.headlineId "; }
          if(in_array($results, array('visited', 'unvisited')))
          {
            $sql .= "LEFT JOIN (";
              $sql .= "SELECT headlineId, ";
              $sql .= $viewed_include;
              $sql .= "GROUP BY headlineId ";
            $sql .= ") d ON d.headlineId = s.headlineId ";
          }
          $sql .= "WHERE s.deleted = 0 AND s.hidden = 0 ";
            if($terms) { $sql .= "AND ".$h_search; }
            if($exclusive && !$subscriptions) { $sql .= "AND s.clusterId IS null "; }
            if($userId && !$subscriptions) { $sql .= "AND (createdBy = ".$userId." OR editedBy = ".$userId.") "; }
            if($subscriptions) { $sql .= "AND b.userId = ".$userId." AND b.deleted = 0 "; }
        }
  
        if(!in_array($results, array('headlines', 'clusters', 'articles'))) { $sql .= "UNION ALL "; }
  
        if(!in_array($results, array('headlines', 'articles')))
        {
          $sql .= "SELECT ";
            $sql .= "'cluster' AS type, s.clusterId AS id, s.createdBy, s.editedBy, IF(views IS NULL, 0, views) AS views, h_count, 0 AS c_count, ";
            $sql .= "((((h.cred_score / 2)) * h.decay_score) / h_count) AS sub_score, ";
            if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
            $sql .= $scores;
          $sql .= "FROM clusters s ";
          $sql .= "LEFT JOIN metadata m ON m.clusterId = s.clusterId ";
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT clusterId, ";
            $sql .= $view_include;
            $sql .= "GROUP BY clusterId ";
          $sql .= ") v ON v.clusterId = s.clusterId ";
          $sql .= $headline_include;
          if($exclusive && $subscriptions) { $sql .= "JOIN subscriptions b ON b.clusterId = s.clusterId "; }
          if(in_array($results, array('visited', 'unvisited')))
          {
            $sql .= "LEFT JOIN (";
              $sql .= "SELECT clusterId, ";
              $sql .= $viewed_include;
            $sql .= ") d ON d.clusterId = s.clusterId ";
          }
          $sql .= "WHERE s.deleted = 0 AND s.hidden = 0 ";
            if($terms) { $sql .= "AND ".$c_search; }
            if(!$subscriptions) { $sql .= "AND s.articleId IS null "; }
            if($userId && !$subscriptions) { $sql .= "AND (createdBy = ".$userId." OR editedBy = ".$userId.") "; }
            if($subscriptions) { $sql .= "AND b.userId = ".$userId." AND b.deleted = 0 "; }
        }
  
        if(!in_array($results, array('headlines', 'clusters', 'articles'))) { $sql .= "UNION ALL "; }
  
        if(!in_array($results, array('headlines', 'clusters')))
        {
          $sql .= "SELECT ";
            $sql .= "'article' AS type, s.articleId AS id, s.createdBy, s.editedBy, IF(views IS NULL, 0, views) AS views, h_count, c_count, ";
            $sql .= "((((c.cred_score / 2) + c.sub_score) * c.decay_score) / c_count) AS sub_score, ";
            if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
            $sql .= $article_scores;
          $sql .= "FROM articles s ";
          $sql .= "LEFT JOIN metadata m ON m.articleId = s.articleId ";
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT articleId, ";
            $sql .= $view_include;
            $sql .= "GROUP BY articleId ";
          $sql .= ") v ON v.articleId = s.articleId ";
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT s.articleId, COUNT(s.clusterId) AS c_count, SUM(h.h_count) AS h_count, ";
            $sql .= "((((h.cred_score / 2)) * h.decay_score) / h_count) AS sub_score, ";
            $sql .= "s.headline AS c_headline, s.tags AS c_tags, h_headline, h_tags, ";
            $sql .= $sub_score;
            $sql .= "FROM clusters s ";
            $sql .= "LEFT JOIN metadata m ON m.clusterId = s.clusterId ";
            $sql .= $headline_include;
            $sql .= "WHERE s.articleId IS NOT NULL ";
            $sql .= "AND s.deleted = 0 ";
            $sql .= "GROUP BY articleId ";
          $sql .= ") c ON c.articleId = s.articleId ";
          if($subscriptions) { $sql .= "JOIN subscriptions b ON b.articleId = s.articleId "; }
          if(in_array($results, array('visited', 'unvisited')))
          {
            $sql .= "LEFT JOIN (";
              $sql .= "SELECT articleId, ";
              $sql .= $viewed_include;
            $sql .= ") d ON d.articleId = s.articleId ";
          }
          $sql .= "WHERE s.deleted = 0 AND s.hidden = 0 ";
            if($terms) { $sql .= "AND ".$a_search; }
            if($userId && !$subscriptions) { $sql .= "AND (createdBy = ".$userId." OR editedBy = ".$userId.") "; }
            if($subscriptions) { $sql .= "AND b.userId = ".$userId." AND b.deleted = 0 "; }
        }
      $sql .= ") items ";
      $sql .= "WHERE 1 = 1 ";
      if($where) { $sql .= "AND ".$where." "; }
      if($results == 'visited') { $sql .= "AND visited = 1 "; }
      if($results == 'unvisited') { $sql .= "AND visited = 0 "; }
  
      $q = $this->db->query($sql);
      $results = $q->row();
      
      $count = $results->items;
    
    }
    
    return $count;
  }

  function autocompare($type, $id)
  {
    $this->ci =& get_instance();
    $this->ci->load->model('database_model', 'database', true);
    $this->ci->load->model('utility_model', 'utility', true);
    $original = $this->ci->database->get_single($type."s", array($type."Id" => $id));
    $o_headline = $this->ci->utility->blwords_strip($original->headline, 'regEx_spaces', ' ');
    $o_tags = $this->ci->utility->blwords_strip($original->tags, 'regEx_commas', ' ');

    $keywords = trim($o_headline.' '.$o_tags);
    $if = "IF(headline REGEXP '".str_replace(' ', '|', $keywords)."', 1, IF(tags REGEXP '".str_replace(' ', '|', $keywords)."', 1, 0)) =";
    $where = array($if => 1, 'deleted' => 0, 'hidden' => 0, 'adminOnly' => 0);
    $o_success = array(
      'headline' => array(),
      'cluster' => array(),
      'article' => array()
    );
    
    if($type == 'cluster')
    {
      // Get Matching Articles
      $a_where = $where;
      if($type == 'article') { $a_where['articleId !='] = $id; }
      $a_list = $this->ci->database->get('articles', $a_where);
      foreach($a_list as $i)
      {
        if($score = $this->item_compare($i, $o_headline, $o_tags))
        {
          $o_success['article'][] = array('id' => $i->articleId, 'ovn' => $score, 'item' => $i);
        }
      }

      foreach($o_success['article'] as $key => $o)
      {
        $n_headline = $this->ci->utility->blwords_strip($o['item']->headline, 'regEx_spaces', ' ');
        $n_tags = $this->ci->utility->blwords_strip($o['item']->tags, 'regEx_commas', ' ');
  
        if($score = $this->item_compare($original, $n_headline, $n_tags))
        {
          $o_success['article'][$key]['nvo'] = $score;
        }
        else { unset($o_success['article'][$key]); }
      }
      $o_success['article'] = array_values($o_success['article']);
    }
    
    if(($type == 'cluster' && !sizeof($o_success['article'])) || $type !== 'cluster')
    {
      // Get Matching Clusters
      $c_where = $where;
      if($type == 'cluster') { $c_where['clusterId !='] = $id; }
      $c_list = $this->ci->database->get('clusters', $c_where+array('articleId' => null));
      foreach($c_list as $i)
      {
        if($score = $this->item_compare($i, $o_headline, $o_tags))
        {
          $o_success['cluster'][] = array('id' => $i->clusterId, 'ovn' => $score, 'item' => $i);
        }
      }
  
      foreach($o_success['cluster'] as $key => $o)
      {
        $n_headline = $this->ci->utility->blwords_strip($o['item']->headline, 'regEx_spaces', ' ');
        $n_tags = $this->ci->utility->blwords_strip($o['item']->tags, 'regEx_commas', ' ');
  
        if($score = $this->item_compare($original, $n_headline, $n_tags))
        {
          $o_success['cluster'][$key]['nvo'] = $score;
        }
        else { unset($o_success['cluster'][$key]); }
      }
      $o_success['cluster'] = array_values($o_success['cluster']);
    }

    if($type !== 'article')
    {
      // Get Matching Headlines
      $h_where = $where;
      if($type == 'headline') { $h_where['headlineId !='] = $id; }
      $h_list = $this->ci->database->get('headlines', $h_where+array('clusterId' => null));
      foreach($h_list as $i)
      {
        if($score = $this->item_compare($i, $o_headline, $o_tags))
        {
          $o_success['headline'][] = array('id' => $i->headlineId, 'ovn' => $score, 'item' => $i);
        }
      }
  
      foreach($o_success['headline'] as $key => $o)
      {
        $n_headline = $this->ci->utility->blwords_strip($o['item']->headline, 'regEx_spaces', ' ');
        $n_tags = $this->ci->utility->blwords_strip($o['item']->tags, 'regEx_commas', ' ');
  
        if($score = $this->item_compare($original, $n_headline, $n_tags))
        {
          $o_success['headline'][$key]['nvo'] = $score;
        }
        else { unset($o_success['headline'][$key]); }
      }
      $o_success['headline'] = array_values($o_success['headline']);
    }
    
    // Add Items to Items
    if($type == 'article')
    {
      if(sizeof($o_success['cluster']))
      {
        foreach($o_success['cluster'] as $i)
        {
          if(!$i['item']->parentId)
          {
            // Add Clusters to Article
            $nInsert = array('clusterId' => $i['id'], 'parentId' => $id, 'createdOn' => time());
            $subscribers = $this->ci->database->get('subscriptions', array('clusterId' => $i['id'], 'deleted' => 0));
            foreach($subscribers as $s) { $noticeId = $this->ci->database->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
            $this->ci->database->edit('clusters', array('clusterId' => $i['id']), array('articleId' => $id));
          } 
        }
      }
    }
    elseif($type == 'cluster')
    {
      if(sizeof($o_success['article']) > 1)
      {
        // Find Best Matching Article for Cluster
        $temp_score = 0;
        $temp_item = null;
        foreach($o_success['article'] as $key => $n)
        {
          $n_headline = $this->ci->utility->blwords_strip($n['item']->headline, 'regEx_spaces', ' ');
          $n_tags = $this->ci->utility->blwords_strip($n['item']->tags, 'regEx_commas', ' ');
          
          $score = $n['ovn'] + $n['nvo'];
          if($score > $temp_score)
          {
            $temp_score = $score;
            $temp_item = $n;
          }
        }
        
        // Add Cluster to Best Matching Article
        $nInsert = array('clusterId' => $id, 'parentId' => $temp_item['id'], 'createdOn' => time());
        $subscribers = $this->ci->database->get('subscriptions', array('clusterId' => $id, 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->ci->database->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('clusters', array('clusterId' => $id), array('articleId' => $temp_item['id']));
      }
      elseif(sizeof($o_success['article']) == 1)
      {
        // Add Cluster to Article
        $articleId = $o_success['article'][key($o_success['article'])]['id'];
        $nInsert = array('clusterId' => $id, 'parentId' => $articleId, 'createdOn' => time());
        $subscribers = $this->ci->database->get('subscriptions', array('clusterId' => $id, 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->ci->database->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('clusters', array('clusterId' => $id), array('articleId' => $articleId));
      }
      elseif(sizeof($o_success['cluster']))
      {
        // Find Available Clusters and Sort by Highest Rated Cluster
        $clusters = array($id);
        $meta_scores = array(0);
        foreach($o_success['cluster'] as $i)
        {
          if(!$i['item']->parentId && !$i['item']->adminOnly)
          {
            $clusters[] = $i['id'];
            $meta = $this->ci->database->get_single('metadata', array('clusterId' => $i['id']), $select = 'score');
            $meta_scores[] = $meta->score;
          }
        }
        array_multisort($meta_scores, SORT_DESC, $clusters);
        
        // Get Data from Clusters
        $top_item = null;
        $tags = array();
        $resources = array();
        $images = array();
        foreach($clusters as $c)
        {
          $temp_item = $this->ci->database->get_single('clusters', array('clusterId' => $c), 'headline, tags');
          if(!$top_item) { $top_item = $temp_item; }
          
          foreach(explode(',', $temp_item->tags) as $t)
          {
            if(!preg_grep("/\b".$t."\b/i", $tags)) { $tags[] = $t; }
          }
          
          $temp_resources = $this->ci->database->get_array('resources', array('clusterId' => $c, 'deleted' => 0), 'resource');
          foreach($temp_resources as $r)
          {
            if(!preg_grep("/\b".$r."/\bi", $resources)) { $resources[] = $r; }
          }
          
          $temp_images = $this->ci->database->get_array('images', array('clusterId' => $c, 'deleted' => 0), 'image');
          foreach($temp_images as $i)
          {
            if(!preg_grep("/\b".$i."\b/i", $images)) { $images[] = $i; }
          }
        }
        
        // Create Article
        $insert = array('headline' => $top_item->headline, 'tags' => implode(',', $tags), 'createdOn' => time());
        $articleId = $this->ci->database->add('articles', $insert, 'articleId');
        $this->database_model->add('subscriptions', array('userId' => 3, 'articleId' => $articleId, 'createdOn' => time()), 'subscriptionId');
        
        // Add Metadata
        $metadata = array('articleId' => $articleId, 'quality' => 0, 'importance' => 0);
        $metadata['credibility'] = $this->ci->utility->credibility('article', $articleId);
        $this->ci->database->add('metadata', $metadata, 'metadataId');
        
        // Add Images/Resources/etc.
        $clusters_update = array('articleId' => $articleId);
        $this->ci->database->add('catlist', array('categoryId' => 1)+$clusters_update, 'catlistId');
        foreach($images as $image)
        {
          $this->ci->database->add('images', array('image' => $image)+$clusters_update, 'imageId');
        }
        foreach($resources as $resource)
        {
          $this->ci->database->add('resources', array('resource' => $resource)+$clusters_update, 'resourceId');
        }
        
        // Add Clusters to Article
        foreach($clusters as $c)
        {
          $nInsert = array('clusterId' => $c, 'parentId' => $articleId, 'createdOn' => time());
          $subscribers = $this->ci->database->get('subscriptions', array('clusterId' => $c, 'deleted' => 0));
          foreach($subscribers as $s) { $noticeId = $this->ci->database->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
          $this->ci->database->edit('clusters', array('clusterId' => $c), $clusters_update);
        }
      }
      
      if(sizeof($o_success['headline']))
      {
        foreach($o_success['headline'] as $i)
        {
          if(!$i['item']->parentId)
          {
            // Add Headlines to Cluster
            $nInsert = array('headlineId' => $i['id'], 'parentId' => $id, 'createdOn' => time());
            $subscribers = $this->ci->database->get('subscriptions', array('headlineId' => $i['id'], 'deleted' => 0));
            foreach($subscribers as $s) { $noticeId = $this->ci->database->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
            $this->ci->database->edit('clusters', array('headlineId' => $i['id']), array('clusterId' => $id));
          }
        }
      }
    }
    else
    {
      if(sizeof($o_success['cluster']) > 1)
      {
        // Find Best Matching Cluster for Headline
        $temp_score = 0;
        $temp_item = null;
        foreach($o_success['cluster'] as $key => $n)
        {
          $n_headline = $this->ci->utility->blwords_strip($n['item']->headline, 'regEx_spaces', ' ');
          $n_tags = $this->ci->utility->blwords_strip($n['item']->tags, 'regEx_commas', ' ');
          
          $score = $n['ovn'] + $n['nvo'];
          if($score > $temp_score)
          {
            $temp_score = $score;
            $temp_item = $n;
          }
        }
        
        // Add Headline to Best Matching Cluster
        $nInsert = array('headlineId' => $id, 'parentId' => $temp_item['id'], 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $id, 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('clusters', array('headlineId' => $id), array('clusterId' => $temp_item['id']));
      }
      if(sizeof($o_success['cluster']) == 1)
      {
        // Add Headline to Cluster
        $clusterId = $o_success['article'][key($o_success['cluster'])]['id'];
        $nInsert = array('headlineId' => $id, 'parentId' => $clusterId, 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $id, 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('clusters', array('headlineId' => $id), array('clusterId' => $clusterId));
      }
      elseif(sizeof($o_success['headline']))
      {
        // Find Available Headlines and Sort by Highest Rated Headline
        $headlines = array($id);
        $meta_scores = array(0);
        foreach($o_success['headline'] as $i)
        {
          if(!$i['item']->parentId && !$i['item']->adminOnly)
          {
            $headlines[] = $i['id'];
            $meta = $this->ci->database->get_single('metadata', array('headlineId' => $i['id']), $select = 'score');
            $meta_scores[] = $meta->score;
          }
        }
        array_multisort($meta_scores, SORT_DESC, $headlines);
        
        // Get Data from Headlines
        $top_item = null;
        $tags = array();
        $resources = array();
        $images = array();
        foreach($headlines as $h)
        {
          $temp_item = $this->ci->database->get_single('headlines', array('headlineId' => $h), 'headline, tags');
          if(!$top_item) { $top_item = $temp_item; }
          
          foreach(explode(',', $temp_item->tags) as $t)
          {
            if(!preg_grep("/\b".$t."\b/i", $tags)) { $tags[] = $t; }
          }
          
          $temp_resources = $this->ci->database->get_array('resources', array('headlineId' => $h, 'deleted' => 0), 'resource');
          foreach($temp_resources as $r)
          {
            if(!preg_grep("/\b".$r."/\bi", $resources)) { $resources[] = $r; }
          }
          
          $temp_images = $this->ci->database->get_array('images', array('headlineId' => $h, 'deleted' => 0), 'image');
          foreach($temp_images as $i)
          {
            if(!preg_grep("/\b".$i."\b/i", $images)) { $images[] = $i; }
          }
        }
        
        // Create Cluster
        $insert = array('headline' => $top_item->headline, 'tags' => implode(',', $tags), 'createdOn' => time());
        $clusterId = $this->ci->database->add('clusters', $insert, 'clusterId');
        $this->database_model->add('subscriptions', array('userId' => 3, 'clusterId' => $clusterId, 'createdOn' => time()), 'subscriptionId');
        
        // Add Metadata
        $metadata = array('clusterId' => $clusterId, 'quality' => 0, 'importance' => 0);
        $metadata['credibility'] = $this->ci->utility->credibility('cluster', $clusterId);
        $this->ci->database->add('metadata', $metadata, 'metadataId');
        
        // Add Images/Resources/etc.
        $headlines_update = array('clusterId' => $clusterId);
        $this->ci->database->add('catlist', array('categoryId' => 1)+$headlines_update, 'catlistId');
        foreach($images as $image)
        {
          $this->ci->database->add('images', array('image' => $image)+$headlines_update, 'imageId');
        }
        foreach($resources as $resource)
        {
          $this->ci->database->add('resources', array('resource' => $resource)+$headlines_update, 'resourceId');
        }
        
        // Add Headlines to Cluster
        foreach($headlines as $h)
        {
          $nInsert = array('headlineId' => $h, 'parentId' => $clusterId, 'createdOn' => time());
          $subscribers = $this->ci->database->get('subscriptions', array('headlineId' => $h, 'deleted' => 0));
          foreach($subscribers as $s) { $noticeId = $this->ci->database->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
          $this->ci->database->edit('headlines', array('headlineId' => $h), $headlines_update);
        }
      }
    }
  }

  private function item_compare($item, $headline, $tags)
  {
    $i_headline = $this->ci->utility->blwords_strip($item->headline, 'regEx_spaces', ' ');
    $i_tags = $this->ci->utility->blwords_strip($item->tags, 'regEx_commas', ' ');

    $h_score = 0;
    $h_array = explode(' ', $headline);
    foreach($h_array as $h)
    {
      if(preg_match('/\b'.$h.'\b/i', $i_headline)) { $h_score += 1; }
      elseif(preg_match('/'.$h.'/i', $i_headline)) { $h_score += 0.75; }

      if(preg_match('/\b'.$h.'\b/i', $i_tags)) { $h_score += 0.5; }
      elseif(preg_match('/'.$h.'/i', $i_tags)) { $h_score += 0.25; }
    }

    $h_array_pair = array();
    $h_temp = $h_array;
    for($j = 0; $j < sizeof($h_array); $j++)
    {
      $imp = implode(' ', array_splice($h_temp, $j, 2));
      if(strpos($imp, ' ')) { $h_array_pair[] = $imp; }
      $h_temp = $h_array;
    }
    foreach($h_array_pair as $h)
    {
      if(preg_match('/\b'.$h.'\b/i', $i_headline)) { $h_score += 1; }
      elseif(preg_match('/'.$h.'/i', $i_headline)) { $h_score += 0.75; }

      if(preg_match('/\b'.$h.'\b/i', $i_tags)) { $h_score += 0.5; }
      elseif(preg_match('/'.$h.'/i', $i_tags)) { $h_score += 0.25; }
    }

    $h_size = sizeof($h_array + $h_array_pair);
    $h_score_avg = $h_score / $h_size;
    if($h_size < 8) { $h_score_avg = $h_score_avg * 0.75; }

    if($h_score_avg >= 0.5)
    {
      $t_score = 0;
      $t_array = explode(' ', $tags);
      foreach($t_array as $t)
      {
        if(preg_match('/\b'.$t.'\b/i', $i_tags)) { $t_score += 1; }
        elseif(preg_match('/'.$t.'/i', $i_tags)) { $t_score += 0.75; }

        if(preg_match('/\b'.$t.'\b/i', $i_headline)) { $t_score += 0.5; }
        elseif(preg_match('/'.$t.'/i', $i_headline)) { $t_score += 0.25; }
      }

      $t_size = sizeof($t_array);
      $t_score_avg = $t_score / sizeof($t_array);
      if($t_size < 5) { $t_score_avg = $t_score_avg * 0.75; }

      if($t_score_avg >= 0.25 || $h_score_avg >= 1)
      {
        return $h_score_avg + ($t_score_avg * 0.5);
      }
    }
    return false;
  }

  private function new_article($data = array(), $userId = 0)
  {
    // Find Available Clusters and Sort by Highest Rated Cluster
    $clusters = array();
    $meta_scores = array(0);
    foreach($data as $i)
    {
      $clusters[] = $i['id'];
      $meta = $this->ci->database->get_single('metadata', array('clusterId' => $i['id']), $select = 'score');
      $meta_scores[] = $meta->score;
    }
    array_multisort($meta_scores, SORT_DESC, $clusters);
    
    // Get Data from Clusters
    $top_item = null;
    $tags = array();
    $resources = array();
    $images = array();
    foreach($clusters as $c)
    {
      $temp_item = $this->ci->database->get_single('clusters', array('clusterId' => $c), 'headline, tags');
      if(!$top_item) { $top_item = $temp_item; }
      
      foreach(explode(',', $temp_item->tags) as $t)
      {
        if(!preg_grep("/\b".$t."\b/i", $tags)) { $tags[] = $t; }
      }
      
      $temp_resources = $this->ci->database->get_array('resources', array('clusterId' => $c, 'deleted' => 0), 'resource');
      foreach($temp_resources as $r)
      {
        if(!preg_grep("/\b".$r."/\bi", $resources)) { $resources[] = $r; }
      }
      
      $temp_images = $this->ci->database->get_array('images', array('clusterId' => $c, 'deleted' => 0), 'image');
      foreach($temp_images as $i)
      {
        if(!preg_grep("/\b".$i."\b/i", $images)) { $images[] = $i; }
      }
    }
    // Create Article
    $insert = array('headline' => $top_item->headline, 'tags' => implode(',', $tags), 'createdOn' => time(), 'createdBy' => $userId);
    if($userId) { $insert['editedBy'] = $userId; }
    $articleId = $this->ci->database->add('articles', $insert, 'articleId');
    $this->database_model->add('subscriptions', array('userId' => 3, 'articleId' => $articleId, 'createdOn' => time()), 'subscriptionId');
    
    // Add Metadata
    $metadata = array('articleId' => $articleId, 'quality' => 0, 'importance' => 0);
    $metadata['credibility'] = $this->ci->utility->credibility('article', $articleId);
    $this->ci->database->add('metadata', $metadata, 'metadataId');
    
    // Add Images/Resources/etc.
    $clusters_update = array('articleId' => $articleId);
    $this->ci->database->add('catlist', array('categoryId' => 1)+$clusters_update, 'catlistId');
    foreach($images as $image)
    {
      $this->ci->database->add('images', array('image' => $image)+$clusters_update, 'imageId');
    }
    foreach($resources as $resource)
    {
      $this->ci->database->add('resources', array('resource' => $resource)+$clusters_update, 'resourceId');
    }
    
    // Add Clusters to Article
    foreach($clusters as $c)
    {
      $nInsert = array('clusterId' => $c, 'parentId' => $articleId, 'createdOn' => time());
      $subscribers = $this->ci->database->get('subscriptions', array('clusterId' => $c, 'deleted' => 0));
      foreach($subscribers as $s) { $noticeId = $this->ci->database->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
      $this->ci->database->edit('clusters', array('clusterId' => $c, 'editedBy' => $userId), $clusters_update);
    }
  }

  private function new_cluster($data = array(), $userId = 0, $articleId = 0)
  {
    // Find Available Headlines and Sort by Highest Rated Headline
    $headlines = array($id);
    $meta_scores = array(0);
    foreach($data as $i)
    {
      $headlines[] = $i['id'];
      $meta = $this->ci->database->get_single('metadata', array('headlineId' => $i['id']), $select = 'score');
      $meta_scores[] = $meta->score;
    }
    array_multisort($meta_scores, SORT_DESC, $headlines);
    
    // Get Data from Headlines
    $top_item = null;
    $tags = array();
    $resources = array();
    $images = array();
    foreach($headlines as $h)
    {
      $temp_item = $this->ci->database->get_single('headlines', array('headlineId' => $h), 'headline, tags');
      if(!$top_item) { $top_item = $temp_item; }
      
      foreach(explode(',', $temp_item->tags) as $t)
      {
        if(!preg_grep("/\b".$t."\b/i", $tags)) { $tags[] = $t; }
      }
      
      $temp_resources = $this->ci->database->get_array('resources', array('headlineId' => $h, 'deleted' => 0), 'resource');
      foreach($temp_resources as $r)
      {
        if(!preg_grep("/\b".$r."/\bi", $resources)) { $resources[] = $r; }
      }
      
      $temp_images = $this->ci->database->get_array('images', array('headlineId' => $h, 'deleted' => 0), 'image');
      foreach($temp_images as $i)
      {
        if(!preg_grep("/\b".$i."\b/i", $images)) { $images[] = $i; }
      }
    }
    
    // Create Cluster
    $insert = array('headline' => $top_item->headline, 'tags' => implode(',', $tags), 'createdOn' => time(), 'createdBy' => $userId);
    if($articleId) { $insert['articleId'] = $articleId; }
    $clusterId = $this->ci->database->add('clusters', $insert, 'clusterId');
    $this->database_model->add('subscriptions', array('userId' => 3, 'clusterId' => $clusterId, 'createdOn' => time()), 'subscriptionId');
    
    // Add Metadata
    $metadata = array('clusterId' => $clusterId, 'quality' => 0, 'importance' => 0);
    $metadata['credibility'] = $this->ci->utility->credibility('cluster', $clusterId);
    $this->ci->database->add('metadata', $metadata, 'metadataId');
    
    // Add Images/Resources/etc.
    $headlines_update = array('clusterId' => $clusterId);
    $this->ci->database->add('catlist', array('categoryId' => 1)+$headlines_update, 'catlistId');
    foreach($images as $image)
    {
      $this->ci->database->add('images', array('image' => $image)+$headlines_update, 'imageId');
    }
    foreach($resources as $resource)
    {
      $this->ci->database->add('resources', array('resource' => $resource)+$headlines_update, 'resourceId');
    }
    
    // Add Headlines to Cluster
    foreach($headlines as $h)
    {
      $nInsert = array('headlineId' => $h, 'parentId' => $clusterId, 'createdOn' => time());
      $subscribers = $this->ci->database->get('subscriptions', array('headlineId' => $h, 'deleted' => 0));
      foreach($subscribers as $s) { $noticeId = $this->ci->database->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
      $this->ci->database->edit('headlines', array('headlineId' => $h, 'editedBy' => $userId), $headlines_update);
    }
  }
}

/* End of file stream_model.php */
/* Location: ./application/models/stream_model.php */