<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stream_model extends CI_Model
{
  private $ci;
  private $generic_select = "s.headline, s.tags, s.createdBy, s.createdOn, s.editedBy, s.editedOn, p.place, s.placeId, s.flagId, s.deleted ";
  private $places_join = "LEFT JOIN places p ON p.placeId = s.placeId ";
  private $decay_score = "LOG10(((s.createdOn - 1385884800) + (s.editedOn - 1385884800)) / 4)";

  public function __construct() { parent::__construct(); }

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
    return $q->result();
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
    $sql .= "WHERE deleted = 0 ";

    $q = $this->db->query($sql);
    return $q->result();
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
    return $q->result();
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
    return $q->result();
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
    return $q->result();
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
    return $q->row();
  }

  function notifications($userId, $time, $display = array('edits', 'joins', 'comments'), $limit = 10, $page = 1)
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
        if(in_array('edits', $display))
        {
          $sql .= "$select_headline 'edits' AS action, $select_two";
          $sql .= $h_notice_join;
          $sql .= "WHERE n.edited = 1 $notice_where";
          $sql .= "GROUP BY headlineId ";
        }

        if(sizeof($display) >= 2) { $sql .= "UNION ALL "; }

        if(in_array('joins', $display))
        {
          $sql .= "$select_headline 'joins' AS action, $select_two";
          $sql .= $h_notice_join;
          $sql .= "WHERE parentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY headlineId ";
        }

        if(sizeof($display) >= 2) { $sql .= "UNION ALL "; }

        if(in_array('comments', $display))
        {
          $sql .= "$select_headline 'comments' AS action, $select_two";
          $sql .= $h_notice_join;
          $sql .= "WHERE commentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY headlineId ";
        }
      $sql .= ") n ";
      $sql .= "ON n.headlineId = s.headlineId ";
      $sql .= $main_where;

      $sql .= "UNION ALL ";

      $sql .= "SELECT s.clusterId AS id, OLD_PASSWORD(s.clusterId) AS hashId, 'cluster' AS type, $select_one";
      $sql .= "FROM clusters s ";
      $sql .= "LEFT JOIN ( ";

        if(in_array('edits', $display))
        {
          $sql .= "$select_cluster 'edits' AS action, $select_two";
          $sql .= $c_notice_join;
          $sql .= "WHERE n.edited = 1 $notice_where";
          $sql .= "GROUP BY clusterId ";
        }

        if(sizeof($display) >= 2) { $sql .= "UNION ALL "; }

        if(in_array('joins', $display))
        {
          $sql .= "$select_cluster 'joins' AS action, $select_two";
          $sql .= $c_notice_join;
          $sql .= "WHERE parentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY clusterId ";
        }

        if(sizeof($display) >= 2) { $sql .= "UNION ALL "; }

        if(in_array('comments', $display))
        {
          $sql .= "$select_cluster 'comments' AS action, $select_two";
          $sql .= $c_notice_join;
          $sql .= "WHERE commentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY clusterId ";
        }
      $sql .= ") n ";
      $sql .= "ON n.clusterId = s.clusterId ";
      $sql .= $main_where;

      $sql .= "UNION ALL ";

      $sql .= "SELECT s.articleId AS id, OLD_PASSWORD(s.articleId) AS hashId, 'article' AS type, $select_one";
      $sql .= "FROM articles s ";
      $sql .= "LEFT JOIN ( ";
        if(in_array('edits', $display))
        {
          $sql .= "$select_article 'edits' AS action, $select_two";
          $sql .= $a_notice_join;
          $sql .= "WHERE n.edited = 1 $notice_where";
          $sql .= "GROUP BY articleId ";
        }

        if(sizeof($display) >= 2) { $sql .= "UNION ALL "; }

        if(in_array('joins', $display))
        {
          $sql .= "$select_article 'joins' AS action, $select_two";
          $sql .= $a_notice_join;
          $sql .= "WHERE parentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY articleId ";
        }

        if(sizeof($display) >= 2) { $sql .= "UNION ALL "; }

        if(in_array('comments', $display))
        {
          $sql .= "$select_article 'comments' AS action, $select_two";
          $sql .= $a_notice_join;
          $sql .= "WHERE commentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY articleId ";
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

  function notifications_count($userId, $time, $display = array('edits', 'joins', 'comments'), $views = "all")
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
        if(in_array('edits', $display))
        {
          $sql .= "$select_headline 'edits' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE n.edited = 1 $notice_where";
          $sql .= "GROUP BY headlineId ";
        }

        if(sizeof($display) >= 2) { $sql .= "UNION ALL "; }

        if(in_array('joins', $display))
        {
          $sql .= "$select_headline 'joins' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE parentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY headlineId ";
        }

        if(sizeof($display) >= 2) { $sql .= "UNION ALL "; }

        if(in_array('comments', $display))
        {
          $sql .= "$select_headline 'comments' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE commentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY headlineId ";
        }
      $sql .= ") n ";
      $sql .= "ON n.headlineId = s.headlineId ";
      $sql .= $main_where;

      $sql .= "UNION ALL ";

      $sql .= "SELECT s.clusterId AS id, OLD_PASSWORD(s.clusterId) AS hashId, 'cluster' AS type ";
      $sql .= "FROM clusters s ";
      $sql .= "LEFT JOIN ( ";

        if(in_array('edits', $display))
        {
          $sql .= "$select_cluster 'edits' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE n.edited = 1 $notice_where";
          $sql .= "GROUP BY clusterId ";
        }

        if(sizeof($display) >= 2) { $sql .= "UNION ALL "; }

        if(in_array('joins', $display))
        {
          $sql .= "$select_cluster 'joins' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE parentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY clusterId ";
        }

        if(sizeof($display) >= 2) { $sql .= "UNION ALL "; }

        if(in_array('comments', $display))
        {
          $sql .= "$select_cluster 'comments' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE commentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY clusterId ";
        }
      $sql .= ") n ";
      $sql .= "ON n.clusterId = s.clusterId ";
      $sql .= $main_where;

      $sql .= "UNION ALL ";

      $sql .= "SELECT s.articleId AS id, OLD_PASSWORD(s.articleId) AS hashId, 'article' AS type ";
      $sql .= "FROM articles s ";
      $sql .= "LEFT JOIN ( ";
        if(in_array('edits', $display))
        {
          $sql .= "$select_article 'edits' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE n.edited = 1 $notice_where";
          $sql .= "GROUP BY articleId ";
        }

        if(sizeof($display) >= 2) { $sql .= "UNION ALL "; }

        if(in_array('joins', $display))
        {
          $sql .= "$select_article 'joins' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE parentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY articleId ";
        }

        if(sizeof($display) >= 2) { $sql .= "UNION ALL "; }

        if(in_array('comments', $display))
        {
          $sql .= "$select_article 'comments' AS action ";
          $sql .= $notice_join;
          $sql .= "WHERE commentId IS NOT NULL $notice_where";
          $sql .= "GROUP BY articleId ";
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
              $newCluster[] = array('id' => $h, 'item' => $hl, 'ovn' => 0, 'nvo' => 0);
            }
          }
        }
        if($newCluster)
        {
          $clusterId = $this->new_cluster($newCluster, $selected['article'][0], $userId);
          foreach($newCluster as $o)
          {
            $nInsert = array('headlineId' => $o['id'], 'parentId' => $clusterId, 'createdOn' => time());
            $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $o['id'], 'deleted' => 0));
            foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
            $this->ci->database->edit('headlines', array('headlineId' => $o['id']), array('clusterId' => $clusterId));
          }
        }
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
      $i = 0;
      foreach($selected['cluster'] as $c)
      {
        $cluster = $this->database_model->get_single('clusters', array('clusterId' => $c), 'adminOnly');
        if(!$cluster->adminOnly || $this->session->userdata('level') == 'a')
        {
          $cl = $this->ci->database->get_single('clusters', array('clusterId' => $c, 'deleted' => 0));
          $newArticle[] = array('id' => $c, 'item' => $cl, 'ovn' => $i, 'nvo' => $i);
          $i++;
        }
      }
      if($newArticle)
      {
        $articleId = $this->new_article($newArticle, $userId);
        foreach($newArticle as $o)
        {
          $nInsert = array('clusterId' => $o['id'], 'parentId' => $articleId, 'createdOn' => time());
          $subscribers = $this->database_model->get('subscriptions', array('clusterId' => $o['id'], 'deleted' => 0));
          foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
          $this->ci->database->edit('clusters', array('clusterId' => $o['id']), array('articleId' => $articleId));
        }
      }
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
            $newCluster[] = array('id' => $h, 'item' => $hl, 'ovn' => 0, 'nvo' => 0);
          }
        }
      }
      if($newCluster)
      {
        $clusterId = $this->new_cluster($newCluster, $articleId, $userId);
        foreach($newCluster as $o)
        {
          $nInsert = array('headlineId' => $o['id'], 'parentId' => $clusterId, 'createdOn' => time());
          $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $o['id'], 'deleted' => 0));
          foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
          $this->ci->database->edit('headlines', array('headlineId' => $o['id']), array('clusterId' => $clusterId));
        }
      }
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
            $newCluster[] = array('id' => $h, 'item' => $hl, 'ovn' => 0, 'nvo' => 0);
          }
        }
      }
      if($newCluster)
      {
        $clusterId = $this->new_cluster($newCluster, 0, $userId);
        foreach($newCluster as $o)
        {
          $nInsert = array('headlineId' => $o['id'], 'parentId' => $clusterId, 'createdOn' => time());
          $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $o['id'], 'deleted' => 0));
          foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
          $this->ci->database->edit('headlines', array('headlineId' => $o['id']), array('clusterId' => $clusterId));
        }
      }
    }
  }

  function search($terms = "", $where = "", $order = "score", $results = "all", $comments = false, $limit = 0, $page = 1, $userId = null, $subscriptions = false, $exclusive = true)
  {
    $this->ci =& get_instance();
    $this->ci->load->model('database_model', 'database', true);
    $this->ci->load->model('utility_model', 'utility', true);

    $w = 0;
    $match = "";
    $article_match = "";
    $terms = preg_replace('/[^A-Za-z0-9 ]/', '', $terms);
    $terms = $this->ci->utility->blwords_strip($terms, 'regEx_spaces', ' ');
    if($terms)
    {
      $search = explode(' ', $terms);
      foreach($search as $s)
      {
        if($w < 10)
        {
          if($w) { $match .= " + "; }
          $match .= "IF((s.headline REGEXP '[[:<:]]".$s."[[:>:]]'), 1, IF((s.headline REGEXP '".$s."'), .75, 0)) ";
          $match .= "+ IF((s.tags REGEXP '[[:<:]]".$s."[[:>:]]'), .5, IF((s.tags REGEXP '".$s."'), .25, 0)) ";
          $article_match .= "+ IF((s.article REGEXP '[[:<:]]".$s."[[:>:]]'), .5, IF((s.article REGEXP '".$s."'), .25, 0)) ";
        }
        $w++;
      }

      $search_pair = array();
      $search_temp = $search;
      for($j = 0; $j < sizeof($search); $j++)
      {
        $imp = implode(' ', array_splice($search_temp, $j, 2));
        if(strpos($imp, ' ')) { $search_pair[] = $imp; }
        $search_temp = $search;
      }

      foreach($search_pair as $s)
      {
        if($w < 5)
        {
          if($w) { $match .= " + "; }
          $match .= "IF((s.headline REGEXP '[[:<:]]".$s."[[:>:]]'), 1, IF((s.headline REGEXP '".$s."'), .75, 0)) ";
          $match .= "+ IF((s.tags REGEXP '[[:<:]]".$s."[[:>:]]'), .5, IF((s.tags REGEXP '".$s."'), .25, 0)) ";
          $article_match .= "+ IF((s.article REGEXP '[[:<:]]".$s."[[:>:]]'), .5, IF((s.article REGEXP '".$s."'), .25, 0)) ";
        }
        $w++;
      }
    }

    $article_scores = $scores = "(quality + importance + credibility) AS cred_score, ";
    $article_scores = $scores .= "(LOG10(((s.createdOn - 1385884800) + (s.editedOn - 1385884800)) / 4) / 10) AS decay_score, ";
    if($terms)
    {
      $scores .= "((".$match.") / ".$w.") AS search_score ";
      $article_scores .= "((".$match.$article_match.") / ".$w.") AS search_score ";
    }
    else { $article_scores = $scores .= "0 AS search_score "; }

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

    $sub_score = "SUM(quality + importance + credibility) AS cred_score, ";
    $sub_score .= "SUM(LOG10(((s.createdOn - 1385884800) + (s.editedOn - 1385884800)) / 4) / 10) AS decay_score, ";
    if($terms) { $sub_score .= "SUM((".$match.") / ".$w.") AS search_score "; }
    else { $sub_score .= "0 AS search_score "; }

    if($results !== 'headline')
    {
      $headline_include = "LEFT JOIN ( ";
        $headline_include .= "SELECT s.clusterId, COUNT(s.headlineId) AS h_count, ";
        $headline_include .= $sub_score;
        $headline_include .= "FROM headlines s ";
        $headline_include .= "LEFT JOIN metadata m ON m.headlineId = s.headlineId ";
        $headline_include .= "WHERE s.clusterId IS NOT NULL ";
        $headline_include .= "AND s.deleted = 0 ";
        $headline_include .= "GROUP BY clusterId ";
      $headline_include .= ") h ON h.clusterId = s.clusterId ";
    }

    $global_select = "s.headline, i2.image, s.createdOn, s.editedOn, ";
    if($userId && !$subscriptions) { $global_select .= "s.createdBy, s.editedBy, "; }

    $sql = "SELECT ";
      $sql .= "*, OLD_PASSWORD(id) AS hashId, ((search_score + (cred_score / 2) + (sub_score / 2)) / decay_score) AS score, ((cred_score / 2) / decay_score) AS x_score ";
    $sql .= "FROM ( ";
      if(!in_array($results, array('clusters', 'articles')))
      {
        $sql .= "SELECT ";
          $sql .= "'headline' AS type, s.headlineId AS id, IF(views IS NULL, 0, views) AS views, 0 AS h_count, 0 AS c_count, 0 AS sub_score, ";
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
          $sql .= "(((h.search_score + (h.cred_score / 2)) / h.decay_score) / h_count) AS sub_score, ";
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
          $sql .= "(((c.search_score + (c.cred_score / 2) + c.sub_score) / c.decay_score) / c_count) AS sub_score, ";
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
          $sql .= "(((h.search_score + (h.cred_score / 2)) / h.decay_score) / h_count) AS sub_score, ";
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
          if($userId && !$subscriptions) { $sql .= "AND (s.createdBy = ".$userId." OR a.editedBy = ".$userId.") "; }
          if($subscriptions) { $sql .= "AND b.userId = ".$userId." AND b.deleted = 0 "; }
        $sql .= "GROUP BY s.articleId ";
      }
    $sql .= ") items ";
    $sql .= "WHERE decay_score > 0 ";
    if($terms) { $sql .= "AND (search_score > 0 OR sub_score > 0) "; }
    if($where) { $sql .= "AND ".$where." "; }
    if($results == 'visited') { $sql .= "AND visited = 1 "; }
    if($results == 'unvisited') { $sql .= "AND visited = 0 "; }
    $sql .= "ORDER BY ".$order." DESC, ";
    $sql .= "search_score DESC, cred_score DESC, decay_score DESC ";
    if($limit) { $sql .= "LIMIT ".(($page-1) * $limit).", ".$limit; }

    //die($sql);

    $q = $this->db->query($sql);
    return $q->result();
  }

  function search_count($terms = "", $where = "", $order = "score", $results = "all", $userId = null, $subscriptions = false, $exclusive = true)
  {
    $this->ci =& get_instance();
    $this->ci->load->model('database_model', 'database', true);
    $this->ci->load->model('utility_model', 'utility', true);

    $w = 0;
    $match = "";
    $article_match = "";
    $terms = preg_replace('/[^A-Za-z0-9\' ]/', '', $terms);
    $terms = $this->ci->utility->blwords_strip($terms, 'regEx_spaces', ' ');
    if($terms)
    {
      $search = explode(' ', $terms);
      foreach($search as $s)
      {
        if($w < 10)
        {
          if($w) { $match .= " + "; }
          $match .= "IF((headline REGEXP '[[:<:]]".$s."[[:>:]]'), 1, IF((headline REGEXP '".$s."'), .75, 0)) ";
          $match .= "+ IF((tags REGEXP '[[:<:]]".$s."[[:>:]]'), .5, IF((tags REGEXP '".$s."'), .25, 0)) ";
          $article_match .= "+ IF((article REGEXP '[[:<:]]".$s."[[:>:]]'), .5, IF((article REGEXP '".$s."'), .25, 0)) ";
        }
        $w++;
      }

      $search_pair = array();
      $search_temp = $search;
      for($j = 0; $j < sizeof($search); $j++)
      {
        $imp = implode(' ', array_splice($search_temp, $j, 2));
        if(strpos($imp, ' ')) { $search_pair[] = $imp; }
        $search_temp = $search;
      }

      foreach($search_pair as $s)
      {
        if($w < 5)
        {
          if($w) { $match .= " + "; }
          $match .= "IF((headline REGEXP '[[:<:]]".$s."[[:>:]]'), 1, IF((headline REGEXP '".$s."'), .75, 0)) ";
          $match .= "+ IF((tags REGEXP '[[:<:]]".$s."[[:>:]]'), .5, IF((tags REGEXP '".$s."'), .25, 0)) ";
          $article_match .= "+ IF((article REGEXP '[[:<:]]".$s."[[:>:]]'), .5, IF((article REGEXP '".$s."'), .25, 0)) ";
        }
        $w++;
      }
    }

    $article_scores = $scores = "(quality + importance + credibility) AS cred_score, ";
    $article_scores = $scores .= "(LOG10(((s.createdOn - 1385884800) + (s.editedOn - 1385884800)) / 4) / 10) AS decay_score, ";
    if($terms)
    {
      $scores .= "((".$match.") / ".$w.") AS search_score ";
      $article_scores .= "((".$match.$article_match.") / ".$w.") AS search_score ";
    }
    else { $article_scores = $scores .= "0 AS search_score "; }

    $view_include = "COUNT(viewId) AS views ";
    $view_include .= "FROM views ";

    $viewed_include = "IF(viewId IS NULL, 0, 1) AS viewed ";
    $viewed_include .= "FROM views ";
    $viewed_include .= "WHERE userId = ".$this->session->userdata('userId')." ";

    $sub_score = "SUM(quality + importance + credibility) AS cred_score, ";
    $sub_score .= "SUM(LOG10(((s.createdOn - 1385884800) + (s.editedOn - 1385884800)) / 4) / 10) AS decay_score, ";
    if($terms) { $sub_score .= "SUM((".$match.") / ".$w.") AS search_score "; }
    else { $sub_score .= "0 AS search_score "; }

    if($results !== 'headline')
    {
      $headline_include = "LEFT JOIN ( ";
        $headline_include .= "SELECT s.clusterId, COUNT(s.headlineId) AS h_count, ";
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
          if($exclusive && !$subscriptions) { $sql .= "AND s.clusterId IS null "; }
          if($userId && !$subscriptions) { $sql .= "AND (createdBy = ".$userId." OR editedBy = ".$userId.") "; }
          if($subscriptions) { $sql .= "AND b.userId = ".$userId." AND b.deleted = 0 "; }
      }

      if(!in_array($results, array('headlines', 'clusters', 'articles'))) { $sql .= "UNION ALL "; }

      if(!in_array($results, array('headlines', 'articles')))
      {
        $sql .= "SELECT ";
          $sql .= "'cluster' AS type, s.clusterId AS id, s.createdBy, s.editedBy, IF(views IS NULL, 0, views) AS views, h_count, 0 AS c_count, ";
          $sql .= "(((h.search_score + (h.cred_score / 2)) / h.decay_score) / h_count) AS sub_score, ";
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
          if(!$subscriptions) { $sql .= "AND s.articleId IS null "; }
          if($userId && !$subscriptions) { $sql .= "AND (createdBy = ".$userId." OR editedBy = ".$userId.") "; }
          if($subscriptions) { $sql .= "AND b.userId = ".$userId." AND b.deleted = 0 "; }
      }

      if(!in_array($results, array('headlines', 'clusters', 'articles'))) { $sql .= "UNION ALL "; }

      if(!in_array($results, array('headlines', 'clusters')))
      {
        $sql .= "SELECT ";
          $sql .= "'article' AS type, s.articleId AS id, s.createdBy, s.editedBy, IF(views IS NULL, 0, views) AS views, h_count, c_count, ";
          $sql .= "(((c.search_score + (c.cred_score / 2) + c.sub_score) / c.decay_score) / c_count) AS sub_score, ";
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
          $sql .= "(((h.search_score + (h.cred_score / 2)) / h.decay_score) / h_count) AS sub_score, ";
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
          if($userId && !$subscriptions) { $sql .= "AND (createdBy = ".$userId." OR editedBy = ".$userId.") "; }
          if($subscriptions) { $sql .= "AND b.userId = ".$userId." AND b.deleted = 0 "; }
      }
    $sql .= ") items ";
    $sql .= "WHERE decay_score > 0 ";
    if($terms) { $sql .= "AND (search_score > 0 OR sub_score > 0) "; }
    if($where) { $sql .= "AND ".$where." "; }
    if($results == 'visited') { $sql .= "AND visited = 1 "; }
    if($results == 'unvisited') { $sql .= "AND visited = 0 "; }

    $q = $this->db->query($sql);
    return $q->row();
  }

  /**
   * Search Stream
   *
   * @access public
   * @param string
   * @param string
   * @param array
   * @param string
   * @param int
   * @param string
   * @param bool
   *
   * @return object
   */
  public function _search($keywords = '', $place = '', $categories_list = array(), $type = '', $id = 0, $where = '', $meta = true, $limit = 0, $order = 'score', $comments = false, $page = 1, $results = 'all', $userId = 0)
  {
    $key_array = preg_split($this->config->item('regEx_spaces'), $this->db->escape_str($keywords));
    $key_array = array_filter($key_array, "strlen");
    $keyword_score = $keywords?"(k_matches / ".sizeof($key_array).")":"0";

    $place = $this->db->escape_str($place);
    $place_if = "IF(p.place = '".$place."', 1, ";
    $place_if .= "IF(p.place LIKE '%".$place."%', .667, ";
    if(strlen($place) >= 3)
    {
      $length = round(strlen($place)/3);
      $place_if .= "IF(p.place LIKE '%".substr($place, $length, -$length)."%', .333, 0) ";
    }
    else { $place_if .= "0"; }
    $place_if .= "))";
    $place_score = $place?$place_if:"0";

    $category_score = $categories_list?"(cat_count / ".sizeof($categories_list).")":"0";

    $c_keyword_score = "((h_keyword_score / h_count) + ".$keyword_score.")";
    $c_place_score = "((h_place_score / h_count) + ".$place_score.")";
    $c_category_score = "((h_category_score / h_count) + ".$category_score.")";
    if($meta)
    {
      $c_quality_score = "((h_quality_score / h_count) + quality)";
      $c_importance_score = "((h_importance_score / h_count) + importance)";
      $c_credibility_score = "((h_credibility_score / h_count) + credibility)";
    }

    $case = "CASE ";
      $case .= "WHEN keyword IN ('".implode("', '", $key_array)."') ";
        $case .= "THEN IF(isTag = 0, 1, .1) ";
      $case .= "WHEN keyword RLIKE '".implode("|", $key_array)."' ";
        $case .= "THEN IF(isTag = 0, .667, .067) ";
      $shortk = array();
      foreach($key_array as $k)
      {
        if(strlen($k) >= 3)
        {
          $length = round(strlen($k)/3);
          $begin = substr($k, 0, $length);
          $shortk[] = "^".$begin;
          $end = substr($k, (strlen($k)-$length), $length);
          $shortk[] = $end."$";
        }
        else
        {
          $shortk[] = "^".$k;
          $shortk[] = $k."$";
        }
      }
      $case .= "WHEN keyword RLIKE '".implode("|", $shortk)."' ";
        $case .= "THEN IF(isTag = 0, .333, .033) ";
      $case .= "ELSE 0 ";
    $case .= "END ";

    $view_include = "COUNT(viewId) AS views ";
    $view_include .= "FROM views ";

    $viewed_include = "IF(viewId IS NULL, 0, 1) AS viewed ";
    $viewed_include .= "FROM views ";
    $viewed_include .= "WHERE userId = ".$this->session->userdata('userId')." ";

    $keyword_include = "SUM(".$case.") AS k_matches ";
    $keyword_include .= "FROM keywords ";
    $keyword_include .= "WHERE deleted = 0 ";

    $catlist_include = "COUNT(catlistId) AS cat_count ";
    $catlist_include .= "FROM catlist ";
    $catlist_include .= "WHERE categoryId IN (".implode(',', $categories_list).") ";
    $catlist_include .= "AND deleted = 0 ";

    if($comments)
    {
      $comments_include = "COUNT(commentId) as comments ";
      $comments_include .= "FROM comments ";
      $comments_include .= "WHERE deleted = 0 ";
    }

    $headline_include = "FROM headlines s ";
    $headline_include .= "LEFT JOIN metadata m ON m.headlineId = s.headlineId ";
    $headline_include .= "LEFT JOIN ( ";
      $headline_include .= "SELECT headlineId, ";
      $headline_include .= $view_include;
      $headline_include .= "GROUP BY headlineId ";
    $headline_include .= ") v ON v.headlineId = s.headlineId ";
    $headline_include .= $this->places_join;
    if($keywords)
    {
      $headline_include .= "LEFT JOIN ( ";
        $headline_include .= "SELECT headlineId, ";
        $headline_include .= $keyword_include;
        $headline_include .= "GROUP BY headlineId ";
      $headline_include .= ") k ON k.headlineId = s.headlineId ";
    }
    if($categories_list)
    {
      $headline_include .= "LEFT JOIN ( ";
        $headline_include .= "SELECT headlineId, ";
        $headline_include .= $catlist_include;
        $headline_include .= "GROUP BY headlineId ";
      $headline_include .= ") l ON l.headlineId = s.headlineId ";
    }
    if(in_array($results, array('visited', 'unvisited')))
    {
      $headline_include .= "LEFT JOIN (";
        $headline_include .= "SELECT headlineId, ";
        $headline_include .= $viewed_include;
      $headline_include .= ") d ON d.headlineId = s.headlineId ";
    }

    if($results !== 'headlines')
    {
      $cluster_include = "FROM clusters s ";
      $cluster_include .= "LEFT JOIN metadata m ON m.clusterId = s.clusterId ";
      $cluster_include .= "LEFT JOIN ( ";
        $cluster_include .= "SELECT clusterId, ";
        $cluster_include .= $view_include;
        $cluster_include .= "GROUP BY clusterId ";
      $cluster_include .= ") v ON v.clusterId = s.clusterId ";
      $cluster_include .= $this->places_join;
      if($keywords)
      {
        $cluster_include .= "LEFT JOIN ( ";
          $cluster_include .= "SELECT clusterId, ";
          $cluster_include .= $keyword_include;
          $cluster_include .= "GROUP BY clusterId ";
        $cluster_include .= ") k ON k.clusterId = s.clusterId ";
      }
      if($categories_list)
      {
        $cluster_include .= "LEFT JOIN ( ";
          $cluster_include .= "SELECT clusterId, ";
          $cluster_include .= $catlist_include;
          $cluster_include .= "GROUP BY clusterId ";
        $cluster_include .= ") l ON l.clusterId = s.clusterId ";
      }
      if(in_array($results, array('visited', 'unvisited')))
      {
        $cluster_include .= "LEFT JOIN (";
          $cluster_include .= "SELECT clusterId, ";
          $cluster_include .= $viewed_include;
        $cluster_include .= ") d ON d.clusterId = s.clusterId ";
      }
      $cluster_include .= "LEFT JOIN ( ";
        $cluster_include .= "SELECT s.clusterId, COUNT(s.headlineId) AS h_count, ";
        $cluster_include .= "IF(views IS NULL, 0, views) AS h_views, ";
        if($meta)
        {
          $cluster_include .= "quality AS h_quality_score, ";
          $cluster_include .= "importance AS h_importance_score, ";
          $cluster_include .= "credibility AS h_credibility_score, ";
        }
        $cluster_include .= $keyword_score." AS h_keyword_score, ";
        $cluster_include .= $place_score." AS h_place_score, ";
        $cluster_include .= $category_score." AS h_category_score ";
        $cluster_include .= $headline_include;
        $cluster_include .= "WHERE s.clusterId IS NOT NULL ";
        $cluster_include .= "AND s.deleted = 0 ";
        $cluster_include .= "GROUP BY s.clusterId ";
      $cluster_include .= ") h ON h.clusterId = s.clusterId ";
    }

    $sql = "SELECT *, OLD_PASSWORD(id) AS hashId, (keyword_score + category_score + place_score ";
    if($meta) { $sql .= "+ quality_score + importance_score + credibility_score"; }
    $sql .= " + 1) / decay_score AS score, (0 ";
    if($meta) { $sql .= "+ quality_score + importance_score + credibility_score"; }
    $sql .= " + 1) / decay_score AS x_score ";
    $sql .= "FROM ( ";
      if(!in_array($results, array('clusters', 'articles')))
      {
        $sql .= "SELECT s.headlineId AS id, 'headline' AS type, ".$this->generic_select.", ";
        $sql .= "0 AS h_count, 0 AS c_count, ";
        $sql .= "IF(views IS NULL, 0, views) AS views, ";
        if($comments) { $sql .= "comments, "; }
        if($meta)
        {
          $sql .= "quality AS quality_score, ";
          $sql .= "importance AS importance_score, ";
          $sql .= "credibility AS credibility_score, ";
        }
        if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
        $sql .= $keyword_score." AS keyword_score, ";
        $sql .= $place_score." AS place_score, ";
        $sql .= $category_score." AS category_score, ";
        $sql .= $this->decay_score." AS decay_score ";
        $sql .= $headline_include;
        if($comments)
        {
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT headlineId, ";
            $sql .= $comments_include;
            $sql .= "GROUP BY headlineId ";
          $sql .= ") x ON x.headlineId = s.headlineId ";
        }
        $sql .= "WHERE s.clusterId IS NULL ";
        $sql .= "AND s.deleted = 0 ";
      }
      if(!in_array($results, array('headlines', 'clusters', 'articles'))) { $sql .= "UNION ALL "; }
      if(!in_array($results, array('headlines', 'articles')))
      {
        $sql .= "SELECT s.clusterId AS id, 'cluster' AS type, ".$this->generic_select.", ";
        $sql .= "h_count, 0 AS c_count, ";
        $sql .= "(h_views + IF(views IS NULL, 0, views)) AS views, ";
        if($comments) { $sql .= "comments, "; }
        if($meta)
        {
          $sql .= $c_quality_score." AS quality_score, ";
          $sql .= $c_importance_score." AS importance_score, ";
          $sql .= $c_credibility_score." AS credibility_score, ";
        }
        if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
        $sql .= $c_keyword_score." AS keyword_score, ";
        $sql .= $c_place_score." AS place_score, ";
        $sql .= $c_category_score." AS category_score, ";
        $sql .= $this->decay_score." AS decay_score ";
        $sql .= $cluster_include;
        if($comments)
        {
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT clusterId, ";
            $sql .= $comments_include;
            $sql .= "GROUP BY clusterId ";
          $sql .= ") x ON x.clusterId = s.clusterId ";
        }
        $sql .= "WHERE s.articleId IS NULL ";
        $sql .= "AND s.deleted = 0 ";
      }
      if(!in_array($results, array('headlines', 'clusters', 'articles'))) { $sql .= "UNION ALL "; }
      if(!in_array($results, array('headlines', 'clusters')))
      {
        $sql .= "SELECT s.articleId AS id, 'article' AS type, ".$this->generic_select.", ";
        $sql .= "h_count, c_count, ";
        $sql .= "(c_views + IF(views IS NULL, 0, views)) AS views, ";
        if($comments) { $sql .= "comments, "; }
        if($meta)
        {
          $sql .= "((c_quality_score / c_count) + quality) AS quality_score, ";
          $sql .= "((c_importance_score / c_count) + importance) AS importance_score, ";
          $sql .= "((c_credibility_score / c_count) + credibility) AS credibility_score, ";
        }
        if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
        $sql .= "((c_keyword_score / c_count) + ".$keyword_score.") AS keyword_score, ";
        $sql .= "((c_place_score / c_count) + ".$place_score.") AS place_score, ";
        $sql .= "((c_category_score / c_count) + ".$category_score.") AS category_score, ";
        $sql .= $this->decay_score." AS decay_score ";
        $sql .= "FROM articles s ";
        $sql .= "LEFT JOIN metadata m ON m.articleId = s.articleId ";
        $sql .= "LEFT JOIN ( ";
          $sql .= "SELECT articleId, ";
          $sql .= $view_include;
          $sql .= "GROUP BY articleId ";
        $sql .= ") v ON v.articleId = s.articleId ";
        $sql .= $this->places_join;
        if($keywords)
        {
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT articleId, ";
            $sql .= $keyword_include;
            $sql .= "GROUP BY articleId ";
          $sql .= ") k ON k.articleId = s.articleId ";
        }
        if($categories_list)
        {
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT articleId, ";
            $sql .= $catlist_include;
            $sql .= "GROUP BY articleId ";
          $sql .= ") l ON l.articleId = s.articleId ";
        }
        if($comments)
        {
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT articleId, ";
            $sql .= $comments_include;
            $sql .= "GROUP BY articleId ";
          $sql .= ") x ON x.articleId = s.articleId ";
        }
        if(in_array($results, array('visited', 'unvisited')))
        {
          $sql .= "LEFT JOIN (";
            $sql .= "SELECT articleId, ";
            $sql .= $viewed_include;
          $sql .= ") d ON d.articleId = s.articleId ";
        }
        $sql .= "LEFT JOIN (";
          $sql .= "SELECT s.articleId, COUNT(s.clusterId) AS c_count, ";
          $sql .= "h_count, ";
          $sql .= "(h_views + IF(views IS NULL, 0, views)) AS c_views, ";
          if($meta)
          {
            $sql .= $c_quality_score." AS c_quality_score, ";
            $sql .= $c_importance_score." AS c_importance_score, ";
            $sql .= $c_credibility_score." AS c_credibility_score, ";
          }
          $sql .= $c_keyword_score." AS c_keyword_score, ";
          $sql .= $c_place_score." AS c_place_score, ";
          $sql .= $c_category_score." AS c_category_score ";
          $sql .= $cluster_include;
          $sql .= "WHERE s.articleId IS NOT NULL ";
          $sql .= "AND s.deleted = 0 ";
          $sql .= "GROUP BY s.articleId ";
        $sql .= ") c ON c.articleId = s.articleId ";
        $sql .= "WHERE s.deleted = 0 ";
      }
    $sql .= ") items ";
    $sql .= "WHERE deleted = 0 ";
    if($where) { $sql .= "AND ".$where." "; }
    if($type && $id) { $sql .= "AND IF(type = '".$type."', IF(id = ".$id.", 0, 1), 1) = 1 "; }
    if($keywords) { $sql .= "AND keyword_score > 0.3 "; }
    if($place) { $sql .= "AND (place_score > 0 OR placeId IS NULL) "; }
    if($categories_list) { $sql .= "AND category_score > 0 "; }
    if($results == 'visited') { $sql .= "AND visited = 1 "; }
    if($results == 'unvisited') { $sql .= "AND visited = 0 "; }
    if($userId) { $sql .= "AND (createdBy = ".$userId." OR editedBy = ".$userId.") "; }
    $sql .= "ORDER BY ".$order." DESC, ";
    $sql .= "type ASC ";
    if($limit) { $sql .= "LIMIT ".(($page-1) * $limit).", ".$limit; }

    //die($sql);

    $q = $this->db->query($sql);
    return $q->result();
  }

  /**
   * Search Stream
   *
   * @access public
   * @param string
   * @param string
   * @param array
   * @param string
   * @param int
   * @param string
   * @param bool
   *
   * @return int
   */
  public function _search_count($keywords = '', $place = '', $categories_list = array(), $type = '', $id = 0, $where = '', $results = 'all')
  {
    $key_array = preg_split($this->config->item('regEx_spaces'), $this->db->escape_str($keywords));
    $key_array = array_filter($key_array, "strlen");
    $keyword_score = $keywords?"(k_matches / ".sizeof($key_array).")":"0";

    $place = $this->db->escape_str($place);
    $place_if = "IF(p.place = '".$place."', 1, ";
    $place_if .= "IF(p.place LIKE '%".$place."%', .667, ";
    if(strlen($place) >= 3)
    {
      $length = round(strlen($place)/3);
      $place_if .= "IF(p.place LIKE '%".substr($place, $length, -$length)."%', .333, 0) ";
    }
    else { $place_if .= "0"; }
    $place_if .= "))";
    $place_score = $place?$place_if:"0";

    $category_score = $categories_list?"(cat_count / ".sizeof($categories_list).")":"0";

    $c_keyword_score = "((h_keyword_score / h_count) + ".$keyword_score.")";
    $c_place_score = "((h_place_score / h_count) + ".$place_score.")";
    $c_category_score = "((h_category_score / h_count) + ".$category_score.")";

    $case = "CASE ";
      $case .= "WHEN keyword IN ('".implode("', '", $key_array)."') ";
        $case .= "THEN 1 ";
      $case .= "WHEN keyword RLIKE '".implode("|", $key_array)."' ";
        $case .= "THEN .667 ";
      $shortk = array();
      foreach($key_array as $k)
      {
        if(strlen($k) >= 3)
        {
          $length = round(strlen($k)/3);
          $begin = substr($k, 0, $length);
          $shortk[] = "^".$begin;
          $end = substr($k, (strlen($k)-$length), $length);
          $shortk[] = $end."$";
        }
        else
        {
          $shortk[] = "^".$k;
          $shortk[] = $k."$";
        }
      }
      $case .= "WHEN keyword RLIKE '".implode("|", $shortk)."' ";
        $case .= "THEN .333 ";
      $case .= "ELSE 0 ";
    $case .= "END ";

    $viewed_include = "IF(viewId IS NULL, 0, 1) AS viewed ";
    $viewed_include .= "FROM views ";
    $viewed_include .= "WHERE userId = ".$this->session->userdata('userId')." ";

    $keyword_include = "SUM(".$case.") AS k_matches ";
    $keyword_include .= "FROM keywords ";
    $keyword_include .= "WHERE deleted = 0 ";

    $catlist_include = "COUNT(catlistId) AS cat_count ";
    $catlist_include .= "FROM catlist ";
    $catlist_include .= "WHERE categoryId IN (".implode(',', $categories_list).") ";
    $catlist_include .= "AND deleted = 0 ";

    $headline_include = "FROM headlines s ";
    $headline_include .= $this->places_join;
    if(in_array($results, array('visited', 'unvisited')))
    {
      $headline_include .= "LEFT JOIN (";
        $headline_include .= "SELECT headlineId, ";
        $headline_include .= $viewed_include;
      $headline_include .= ") d ON d.headlineId = s.headlineId ";
    }
    if($keywords)
    {
      $headline_include .= "LEFT JOIN ( ";
        $headline_include .= "SELECT headlineId, ";
        $headline_include .= $keyword_include;
        $headline_include .= "GROUP BY headlineId ";
      $headline_include .= ") k ON k.headlineId = s.headlineId ";
    }
    if($categories_list)
    {
      $headline_include .= "LEFT JOIN ( ";
        $headline_include .= "SELECT headlineId, ";
        $headline_include .= $catlist_include;
        $headline_include .= "GROUP BY headlineId ";
      $headline_include .= ") l ON l.headlineId = s.headlineId ";
    }

    $cluster_include = "FROM clusters s ";
    $cluster_include .= $this->places_join;
    if(in_array($results, array('visited', 'unvisited')))
    {
      $cluster_include .= "LEFT JOIN (";
        $cluster_include .= "SELECT clusterId, ";
        $cluster_include .= $viewed_include;
      $cluster_include .= ") d ON d.clusterId = s.clusterId ";
    }
    if($keywords)
    {
      $cluster_include .= "LEFT JOIN ( ";
        $cluster_include .= "SELECT clusterId, ";
        $cluster_include .= $keyword_include;
        $cluster_include .= "GROUP BY clusterId ";
      $cluster_include .= ") k ON k.clusterId = s.clusterId ";
    }
    if($categories_list)
    {
      $cluster_include .= "LEFT JOIN ( ";
        $cluster_include .= "SELECT clusterId, ";
        $cluster_include .= $catlist_include;
        $cluster_include .= "GROUP BY clusterId ";
      $cluster_include .= ") l ON l.clusterId = s.clusterId ";
    }
    $cluster_include .= "LEFT JOIN ( ";
      $cluster_include .= "SELECT s.clusterId, COUNT(s.headlineId) AS h_count, ";
      $cluster_include .= $keyword_score." AS h_keyword_score, ";
      $cluster_include .= $place_score." AS h_place_score, ";
      $cluster_include .= $category_score." AS h_category_score ";
      $cluster_include .= $headline_include;
      $cluster_include .= "WHERE s.clusterId IS NOT NULL ";
      $cluster_include .= "AND s.deleted = 0 ";
      $cluster_include .= "GROUP BY s.clusterId ";
    $cluster_include .= ") h ON h.clusterId = s.clusterId ";

    $sql = "SELECT COUNT(id) AS items ";
    $sql .= "FROM ( ";
      if(!in_array($results, array('clusters', 'articles')))
      {
        $sql .= "SELECT s.headlineId AS id, 'headline' AS type, ".$this->generic_select.", ";
        $sql .= "0 AS h_count, 0 AS c_count, ";
        if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
        $sql .= $keyword_score." AS keyword_score, ";
        $sql .= $place_score." AS place_score, ";
        $sql .= $category_score." AS category_score ";
        $sql .= $headline_include;
        $sql .= "WHERE s.clusterId IS NULL ";
        $sql .= "AND s.deleted = 0 ";
      }
      if(!in_array($results, array('headlines', 'clusters', 'articles'))) { $sql .= "UNION ALL "; }
      if(!in_array($results, array('headlines', 'articles')))
      {
        $sql .= "SELECT s.clusterId AS id, 'cluster' AS type, ".$this->generic_select.", ";
        $sql .= "h_count, 0 AS c_count, ";
        if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
        $sql .= $c_keyword_score." AS keyword_score, ";
        $sql .= $c_place_score." AS place_score, ";
        $sql .= $c_category_score." AS category_score ";
        $sql .= $cluster_include;
        $sql .= "WHERE s.articleId IS NULL ";
        $sql .= "AND s.deleted = 0 ";
      }
      if(!in_array($results, array('headlines', 'clusters', 'articles'))) { $sql .= "UNION ALL "; }
      if(!in_array($results, array('headlines', 'clusters')))
      {
        $sql .= "SELECT s.articleId AS id, 'article' AS type, ".$this->generic_select.", ";
        $sql .= "h_count, c_count, ";
        if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
        $sql .= "((c_keyword_score / c_count) + ".$keyword_score.") AS keyword_score, ";
        $sql .= "((c_place_score / c_count) + ".$place_score.") AS place_score, ";
        $sql .= "((c_category_score / c_count) + ".$category_score.") AS category_score ";
        $sql .= "FROM articles s ";
        $sql .= "LEFT JOIN metadata m ON m.articleId = s.articleId ";
        $sql .= $this->places_join;
        if($keywords)
        {
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT articleId, ";
            $sql .= $keyword_include;
            $sql .= "GROUP BY articleId ";
          $sql .= ") k ON k.articleId = s.articleId ";
        }
        if($categories_list)
        {
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT articleId, ";
            $sql .= $catlist_include;
            $sql .= "GROUP BY articleId ";
          $sql .= ") l ON l.articleId = s.articleId ";
        }
        if(in_array($results, array('visited', 'unvisited')))
        {
          $sql .= "LEFT JOIN (";
            $sql .= "SELECT articleId, ";
            $sql .= $viewed_include;
          $sql .= ") d ON d.articleId = s.articleId ";
        }
        $sql .= "LEFT JOIN (";
          $sql .= "SELECT s.articleId, COUNT(s.clusterId) AS c_count, ";
          $sql .= "h_count, ";
          $sql .= $c_keyword_score." AS c_keyword_score, ";
          $sql .= $c_place_score." AS c_place_score, ";
          $sql .= $c_category_score." AS c_category_score ";
          $sql .= $cluster_include;
          $sql .= "WHERE s.articleId IS NOT NULL ";
          $sql .= "AND s.deleted = 0 ";
          $sql .= "GROUP BY s.articleId ";
        $sql .= ") c ON c.articleId = s.articleId ";
        $sql .= "WHERE s.deleted = 0 ";
      }
    $sql .= ") items ";
    $isWhere = false;
    if($where) { $sql .= (($isWhere)?"AND":"WHERE")." ".$where." "; $isWhere = true; }
    if($type && $id) { $sql .= (($isWhere)?"AND":"WHERE")." IF(type = '".$type."', IF(id = ".$id.", 0, 1), 1) = 1 "; $isWhere = true; }
    if($keywords) { $sql .= (($isWhere)?"AND":"WHERE")." keyword_score > 0 "; $isWhere = true; }
    if($place) { $sql .= (($isWhere)?"AND":"WHERE")." place_score > 0 "; $isWhere = true; }
    if($categories_list) { $sql .= (($isWhere)?"AND":"WHERE")." category_score > 0 "; $isWhere = true; }
    if($results == 'visited') { $sql .= (($isWhere)?"AND":"WHERE")." visited = 1 "; $isWhere = true; }
    if($results == 'unvisited') { $sql .= (($isWhere)?"AND":"WHERE")." visited = 0 "; $isWhere = true; }

    $q = $this->db->query($sql);
    return $q->row();
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

    $a_where = $where;
    if($type = 'article') { $a_where['articleId !='] = $id; }
    $a_list = $this->ci->database->get('articles', $a_where);
    foreach($a_list as $i)
    {
      if($score = $this->item_compare($i, $o_headline, $o_tags))
      {
        $o_success['article'][] = array('id' => $i->articleId, 'ovn' => $score);
      }
    }

    $c_where = $where;
    if($type = 'cluster') { $c_where['clusterId !='] = $id; }
    $c_list = $this->ci->database->get('clusters', $c_where+array('articleId' => null));
    foreach($c_list as $i)
    {
      if($score = $this->item_compare($i, $o_headline, $o_tags))
      {
        $o_success['cluster'][] = array('id' => $i->clusterId, 'ovn' => $score);
      }
    }

    $h_where = $where;
    if($type = 'headline') { $h_where['headlineId !='] = $id; }
    $h_list = $this->ci->database->get('headlines', $h_where+array('clusterId' => null));
    foreach($h_list as $i)
    {
      if($score = $this->item_compare($i, $o_headline, $o_tags))
      {
        $o_success['headline'][] = array('id' => $i->headlineId, 'ovn' => $score);
      }
    }

    foreach($o_success['article'] as $key => $o)
    {
      $n_item = $this->ci->database->get_single("articles", array("articleId" => $o['id']));
      $n_headline = $this->ci->utility->blwords_strip($n_item->headline, 'regEx_spaces', ' ');
      $n_tags = $this->ci->utility->blwords_strip($n_item->tags, 'regEx_commas', ' ');

      if($score = $this->item_compare($original, $n_headline, $n_tags))
      {
        $o_success['article'][$key]['item'] = $n_item;
        $o_success['article'][$key]['nvo'] = $score;
      }
      else { unset($o_success['article'][$key]); }
    }
    $o_success['article'] = array_values($o_success['article']);

    foreach($o_success['cluster'] as $key => $o)
    {
      $n_item = $this->ci->database->get_single("clusters", array("clusterId" => $o['id']));
      $n_headline = $this->ci->utility->blwords_strip($n_item->headline, 'regEx_spaces', ' ');
      $n_tags = $this->ci->utility->blwords_strip($n_item->tags, 'regEx_commas', ' ');

      if($score = $this->item_compare($original, $n_headline, $n_tags))
      {
        $o_success['cluster'][$key]['item'] = $n_item;
        $o_success['cluster'][$key]['nvo'] = $score;
      }
      else { unset($o_success['cluster'][$key]); }
    }
    $o_success['cluster'] = array_values($o_success['cluster']);

    foreach($o_success['headline'] as $key => $o)
    {
      $n_item = $this->ci->database->get_single("headlines", array("headlineId" => $o['id']));
      $n_headline = $this->ci->utility->blwords_strip($n_item->headline, 'regEx_spaces', ' ');
      $n_tags = $this->ci->utility->blwords_strip($n_item->tags, 'regEx_commas', ' ');

      if($score = $this->item_compare($original, $n_headline, $n_tags))
      {
        $o_success['headline'][$key]['item'] = $n_item;
        $o_success['headline'][$key]['nvo'] = $score;
      }
      else { unset($o_success['headline'][$key]); }
    }
    $o_success['headline'] = array_values($o_success['headline']);

    if(sizeof($o_success['article']) == 1)
    {
      $article_where = array('articleId' => $o_success['article'][0]['id']);
      $resources = array();
      $images = array();
      // get
      $articleResources = $this->ci->database->get_array('resources', $article_where+array('deleted' => 0), 'resource');
      foreach($articleResources as $ar)
      {
        if(!in_array($ar, $resources)) { $resources[] = $ar; }
      }
      $articleImages = $this->ci->database->get_array('images', $article_where+array('deleted' => 0), 'image');
      foreach($articleImages as $ai)
      {
        if(!in_array($ai, $images)) { $images[] = $ai; }
      }
      if($type == 'cluster')
      {
        $nInsert = array('clusterId' => $id, 'parentId' => $o_success['article'][0]['id'], 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('clusterId' => $id, 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('clusters', array('clusterId' => $id), $article_where);
        // transfer
        $cluster_where = array('clusterId' => $id, 'deleted' => 0);
        $thisResources = $this->ci->database->get_array('resources', $cluster_where, 'resource');
        foreach($thisResources as $tr)
        {
          if(!in_array($tr, $resources))
          {
            $resources[] = $tr;
            $this->ci->database->add('resources', $article_where+array('resource' => $tr, 'deleted' => 0), 'resourceId');
          }
        }
        $thisImages = $this->ci->database->get_array('images', $cluster_where, 'image');
        foreach($thisImages as $ti)
        {
          if(!in_array($ti, $images))
          {
            $images[] = $ti;
            $this->ci->database->add('images', $article_where+array('image' => $ti, 'deleted' => 0), 'imageId');
          }
        }
      }
      foreach($o_success['cluster'] as $key => $o)
      {
        $nInsert = array('clusterId' => $o['id'], 'parentId' => $o_success['article'][0]['id'], 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('clusterId' => $o['id'], 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('clusters', array('clusterId' => $o['id']), $article_where);
        // transfer
        $cluster_where = array('clusterId' => $o['id'], 'deleted' => 0);
        $thisResources = $this->ci->database->get_array('resources', $cluster_where, 'resource');
        foreach($thisResources as $tr)
        {
          if(!in_array($tr, $resources))
          {
            $resources[] = $tr;
            $this->ci->database->add('resources', $article_where+array('resource' => $tr, 'deleted' => 0), 'resourceId');
          }
        }
        $thisImages = $this->ci->database->get_array('images', $cluster_where, 'image');
        foreach($thisImages as $ti)
        {
          if(!in_array($ti, $images))
          {
            $images[] = $ti;
            $this->ci->database->add('images', $article_where+array('image' => $ti, 'deleted' => 0), 'imageId');
          }
        }
      }
      $o_success['cluster'] = array();
    }
    elseif($o_success['article'])
    {
      foreach($o_success['cluster'] as $o)
      {
        $t_score = 0;
        $t_item = null;
        foreach($o_success['article'] as $key => $n)
        {
          $n_headline = $this->ci->utility->blwords_strip($n['item']->headline, 'regEx_spaces', ' ');
          $n_tags = $this->ci->utility->blwords_strip($n['item']->tags, 'regEx_commas', ' ');

          if($score = $this->item_compare($o['item'], $n_headline, $n_tags))
          {
            if($score > $t_score)
            {
              $t_score = $score;
              $t_item = $n;
            }
          }
        }
        $article_where = array('articleId' => $t_item['id']);
        $resources = array();
        $images = array();
        // get
        $articleResources = $this->ci->database->get_array('resources', $article_where+array('deleted' => 0), 'resource');
        foreach($articleResources as $ar)
        {
          if(!in_array($ar, $resources)) { $resources[] = $ar; }
        }
        $articleImages = $this->ci->database->get_array('images', $article_where+array('deleted' => 0), 'image');
        foreach($articleImages as $ai)
        {
          if(!in_array($ai, $images)) { $images[] = $ai; }
        }
        //
        $nInsert = array('clusterId' => $o['id'], 'parentId' => $t_item['id'], 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('clusterId' => $o['id'], 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('clusters', array('clusterId' => $o['id']), $article_where);
        // transfer
        $cluster_where = array('clusterId' => $o['id'], 'deleted' => 0);
        $thisResources = $this->ci->database->get_array('resources', $cluster_where, 'resource');
        foreach($thisResources as $tr)
        {
          if(!in_array($tr, $resources))
          {
            $resources[] = $tr;
            $this->ci->database->add('resources', $article_where+array('resource' => $tr, 'deleted' => 0), 'resourceId');
          }
        }
        $thisImages = $this->ci->database->get_array('images', $cluster_where, 'image');
        foreach($thisImages as $ti)
        {
          if(!in_array($ti, $images))
          {
            $images[] = $ti;
            $this->ci->database->add('images', $article_where+array('image' => $ti, 'deleted' => 0), 'imageId');
          }
        }
      }
      if($type == 'cluster')
      {
        $t_score = 0;
        $t_item = null;
        foreach($o_success['article'] as $key => $n)
        {
          $n_headline = $this->ci->utility->blwords_strip($n['item']->headline, 'regEx_spaces', ' ');
          $n_tags = $this->ci->utility->blwords_strip($n['item']->tags, 'regEx_commas', ' ');

          $score = $o['ovn'] + $o['nvo'];
          if($score > $t_score)
          {
            if($score > $t_score)
            {
              $t_score = $score;
              $t_item = $n;
            }
          }
        }
        $nInsert = array('clusterId' => $id, 'parentId' => $t_item['id'], 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('clusterId' => $id, 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('clusters', array('clusterId' => $id), array('articleId' => $t_item['id']));
      }
      $o_success['cluster'] = array();
    }

    if(sizeof($o_success['cluster']) == 1)
    {
      if($type == 'cluster')
      {
        $new_temp = $o_success['cluster'];
        $new_temp[] = array('item' => $original, 'ovn' => 0, 'nvo' => 0);
        $articleId = $this->new_article($new_temp);
        //
        $nInsert = array('clusterId' => $id, 'parentId' => $articleId, 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('clusterId' => $id, 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('clusters', array('clusterId' => $id), array('articleId' => $articleId));
        //
        $nInsert = array('clusterId' => $o_success['cluster'][0]['id'], 'parentId' => $articleId, 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('clusterId' => $o_success['cluster'][0]['id'], 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('clusters', array('clusterId' => $o_success['cluster'][0]['id']), array('articleId' => $articleId));
      }

      $cluster_where = array('clusterId' => $o_success['cluster'][0]['id']);
      $resources = array();
      $images = array();
      // get
      $clusterResources = $this->ci->database->get_array('resources', $cluster_where+array('deleted' => 0), 'resource');
      foreach($clusterResources as $cr)
      {
        if(!in_array($cr, $resources)) { $resources[] = $cr; }
      }
      $clusterImages = $this->ci->database->get_array('images', $cluster_where+array('deleted' => 0), 'image');
      foreach($clusterImages as $ci)
      {
        if(!in_array($ci, $images)) { $images[] = $ci; }
      }

      if($type == 'headline')
      {
        $nInsert = array('headlineId' => $id, 'parentId' => $o_success['cluster'][0]['id'], 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $id, 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('headlines', array('headlineId' => $id), $cluster_where);
        // transfer
        $headline_where = array('clusterId' => $id, 'deleted' => 0);
        $thisResources = $this->ci->database->get_array('resources', $headline_where, 'resource');
        foreach($thisResources as $tr)
        {
          if(!in_array($tr, $resources))
          {
            $resources[] = $tr;
            $this->ci->database->add('resources', $cluster_where+array('resource' => $tr, 'deleted' => 0), 'resourceId');
          }
        }
        $thisImages = $this->ci->database->get_array('images', $headline_where, 'image');
        foreach($thisImages as $ti)
        {
          if(!in_array($ti, $images))
          {
            $images[] = $ti;
            $this->ci->database->add('images', $cluster_where+array('image' => $ti, 'deleted' => 0), 'imageId');
          }
        }
      }
      foreach($o_success['headline'] as $o)
      {
        $nInsert = array('headlineId' => $id, 'parentId' => $o_success['cluster'][0]['id'], 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $id, 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('headlines', array('headlineId' => $o['id']), $cluster_where);
        // transfer
        $headline_where = array('clusterId' => $o['id'], 'deleted' => 0);
        $thisResources = $this->ci->database->get_array('resources', $headline_where, 'resource');
        foreach($thisResources as $tr)
        {
          if(!in_array($tr, $resources))
          {
            $resources[] = $tr;
            $this->ci->database->add('resources', $cluster_where+array('resource' => $tr, 'deleted' => 0), 'resourceId');
          }
        }
        $thisImages = $this->ci->database->get_array('images', $headline_where, 'image');
        foreach($thisImages as $ti)
        {
          if(!in_array($ti, $images))
          {
            $images[] = $ti;
            $this->ci->database->add('images', $cluster_where+array('image' => $ti, 'deleted' => 0), 'imageId');
          }
        }
      }
      $o_success['headline'] = array();
    }
    elseif($o_success['cluster'])
    {
      if($type == 'cluster')
      {
        $new_temp = $o_success['cluster'];
        $new_temp[] = array('item' => $original, 'ovn' => 0, 'nvo' => 0);
        $articleId = $this->new_article($new_temp);
        //
        $nInsert = array('clusterId' => $id, 'parentId' => $articleId, 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('clusterId' => $id, 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('clusters', array('clusterId' => $id), array('articleId' => $articleId));
      }
      else { $articleId = $this->new_article($o_success['cluster']); }
      foreach($o_success['cluster'] as $o)
      {
        $nInsert = array('clusterId' => $o['id'], 'parentId' => $articleId, 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('clusterId' => $o['id'], 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('clusters', array('clusterId' => $o['id']), array('articleId' => $articleId));
      }

      if($type == 'headline')
      {
        $t_score = 0;
        $t_item = null;
        foreach($o_success['cluster'] as $key => $o)
        {
          $score = $o['ovn'] + $o['nvo'];
          if($score > $t_score)
          {
            $t_score = $score;
            $t_item = $o;
          }
        }
        $nInsert = array('headlineId' => $id, 'parentId' => $t_item['id'], 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $id, 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('headlines', array('headlineId' => $id), array('clusterId' => $t_item['id']));
      }
      foreach($o_success['headline'] as $o)
      {
        $t_score = 0;
        $t_item = null;
        foreach($o_success['cluster'] as $key => $n)
        {
          $n_headline = $this->ci->utility->blwords_strip($n['item']->headline, 'regEx_spaces', ' ');
          $n_tags = $this->ci->utility->blwords_strip($n['item']->tags, 'regEx_commas', ' ');

          if($score = $this->item_compare($o['item'], $n_headline, $n_tags))
          {
            if($score > $t_score)
            {
              $t_score = $score;
              $t_item = $n;
            }
          }
        }
        $cluster_where = array('clusterId' => $t_item['id']);
        $resources = array();
        $images = array();
        // get
        $clusterResources = $this->ci->database->get_array('resources', $cluster_where+array('deleted' => 0), 'resource');
        foreach($clusterResources as $cr)
        {
          if(!in_array($cr, $resources)) { $resources[] = $cr; }
        }
        $clusterImages = $this->ci->database->get_array('images', $cluster_where+array('deleted' => 0), 'image');
        foreach($clusterImages as $ci)
        {
          if(!in_array($ci, $images)) { $images[] = $ci; }
        }
        //
        $nInsert = array('headlineId' => $o['id'], 'parentId' => $t_item['id'], 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $o['id'], 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('headlines', array('headlineId' => $o['id']), array('clusterId' => $t_item['id']));
        // transfer
        $headline_where = array('clusterId' => $o['id'], 'deleted' => 0);
        $thisResources = $this->ci->database->get_array('resources', $headline_where, 'resource');
        foreach($thisResources as $tr)
        {
          if(!in_array($tr, $resources))
          {
            $resources[] = $tr;
            $this->ci->database->add('resources', $cluster_where+array('resource' => $tr, 'deleted' => 0), 'resourceId');
          }
        }
        $thisImages = $this->ci->database->get_array('images', $headline_where, 'image');
        foreach($thisImages as $ti)
        {
          if(!in_array($ti, $images))
          {
            $images[] = $ti;
            $this->ci->database->add('images', $cluster_where+array('image' => $ti, 'deleted' => 0), 'imageId');
          }
        }
      }
      $o_success['headline'] = array();
    }

    if(sizeof($o_success['headline']) == 1)
    {
      if($type == 'headline')
      {
        $new_temp = $o_success['headline'];
        $new_temp[] = array('item' => $original, 'ovn' => 0, 'nvo' => 0);
        $clusterId = $this->new_cluster($new_temp);
        //
        $nInsert = array('headlineId' => $id, 'parentId' => $clusterId, 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $id, 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('headlines', array('headlineId' => $id), array('clusterId' => $clusterId));
        //
        $nInsert = array('headlineId' => $o_success['headline'][0]['id'], 'parentId' => $clusterId, 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $o_success['headline'][0]['id'], 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('headlines', array('headlineId' => $o_success['headline'][0]['id']), array('clusterId' => $clusterId));
      }
    }
    elseif($o_success['headline'])
    {
      if($type == 'headline')
      {
        $new_temp = $o_success['headline'];
        $new_temp[] = array('item' => $original, 'ovn' => 0, 'nvo' => 0);
        $clusterId = $this->new_cluster($new_temp);
        $nInsert = array('headlineId' => $id, 'parentId' => $clusterId, 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $id, 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('headlines', array('headlineId' => $id), array('clusterId' => $clusterId));
      }
      else { $clusterId = $this->new_cluster($o_success['headline']); }
      foreach($o_success['headline'] as $o)
      {
        $nInsert = array('headlineId' => $o['id'], 'parentId' => $clusterId, 'createdOn' => time());
        $subscribers = $this->database_model->get('subscriptions', array('headlineId' => $o['id'], 'deleted' => 0));
        foreach($subscribers as $s) { $noticeId = $this->database_model->add('notices', $nInsert+array('userId' => $s->userId), 'noticeId'); }
        $this->ci->database->edit('headlines', array('headlineId' => $o['id']), array('clusterId' => $clusterId));
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

  private function new_article($clusters = array(), $userId = 0)
  {
    $t_score = 0;
    $temp_key = key($clusters);
    $t_item = $clusters[$temp_key];
    $t_item = null;
    $tags = "";
    $resources = array();
    $images = array();
    foreach($clusters as $key => $o)
    {
      $tags .= ($tags?',':'').$o['item']->tags;
      $thisResources = $this->ci->database->get_array('resources', array('clusterId' => $o['item']->clusterId, 'deleted' => 0), 'resource');
      foreach($thisResources as $tr)
      {
        if(!in_array($tr, $resources)) { $resources[] = $tr; }
      }
      $thisImages = $this->ci->database->get_array('images', array('clusterId' => $o['item']->clusterId, 'deleted' => 0), 'image');
      foreach($thisImages as $ti)
      {
        if(!in_array($ti, $images)) { $images[] = $ti; }
      }

      $score = $o['ovn'] + $o['nvo'];
      if($score > $t_score)
      {
        $t_score = $score;
        $t_item = $o;
      }
    }

    $insert = array('headline' => $t_item['item']->headline, 'tags' => $tags, 'createdOn' => time());
    if($userId) { $insert['editedBy'] = $userId; }
    $articleId = $this->ci->database->add('articles', $insert, 'articleId');
    $this->database_model->add('subscriptions', array('userId' => 3, 'articleId' => $articleId, 'createdOn' => time()), 'subscriptionId');

    // Metadata
    $metadata = array('articleId' => $articleId, 'quality' => 0, 'importance' => 0);
    $metadata['credibility'] = $this->ci->utility->credibility('article', $articleId);
    $this->ci->database->add('metadata', $metadata, 'metadataId');

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

    return $articleId;
  }

  private function new_cluster($headlines = array(), $articleId = 0, $userId = 0)
  {
    $t_score = 0;
    $temp_key = key($headlines);
    $t_item = $headlines[$temp_key];
    $tags = "";
    $resources = array();
    $images = array();
    foreach($headlines as $key => $o)
    {
      $tags .= ($tags?',':'').$o['item']->tags;
      $thisResources = $this->ci->database->get_array('resources', array('headlineId' => $o['item']->headlineId, 'deleted' => 0), 'resource');
      foreach($thisResources as $tr)
      {
        if(!in_array($tr, $resources)) { $resources[] = $tr; }
      }
      $thisImages = $this->ci->database->get_array('images', array('headlineId' => $o['item']->headlineId, 'deleted' => 0), 'image');
      foreach($thisImages as $ti)
      {
        if(!in_array($ti, $images)) { $images[] = $ti; }
      }

      $score = $o['ovn'] + $o['nvo'];
      if($score > $t_score)
      {
        $t_score = $score;
        $t_item = $o;
      }
    }

    $insert = array('headline' => $t_item['item']->headline, 'tags' => $tags, 'createdOn' => time());
    if($articleId) { $insert['articleId'] = $articleId; }
    if($userId) { $insert['editedBy'] = $userId; }
    $clusterId = $this->ci->database->add('clusters', $insert, 'clusterId');
    $this->database_model->add('subscriptions', array('userId' => 3, 'clusterId' => $clusterId, 'createdOn' => time()), 'subscriptionId');

    // Metadata
    $metadata = array('clusterId' => $clusterId, 'quality' => 0, 'importance' => 0);
    $metadata['credibility'] = $this->ci->utility->credibility('cluster', $clusterId);
    $this->ci->database->add('metadata', $metadata, 'metadataId');

    $headline_update = array('clusterId' => $clusterId);
    $this->ci->database->add('catlist', array('categoryId' => 1)+$headline_update, 'catlistId');
    foreach($images as $image)
    {
      $this->ci->database->add('images', array('image' => $image)+$headline_update, 'imageId');
    }
    foreach($resources as $resource)
    {
      $this->ci->database->add('resources', array('resource' => $resource)+$headline_update, 'resourceId');
    }

    return $clusterId;
  }
}

/* End of file stream_model.php */
/* Location: ./application/models/stream_model.php */