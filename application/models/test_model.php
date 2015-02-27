<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test_model extends CI_Model
{
  private $ci;

  public function __construct()
  {
    parent::__construct();
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
    $where = array($if => 1, 'deleted' => 0);
    $o_success = array(
      'headline' => array(),
      'cluster' => array(),
      'article' => array()
    );

    $a_list = $this->ci->database->get('articles', $where);
    foreach($a_list as $i)
    {
      if($score = $this->item_compare($i, $o_headline, $o_tags))
      {
        $o_success['article'][] = array('id' => $i->articleId, 'ovn' => $score);
      }
    }

    $c_list = $this->ci->database->get('clusters', $where+array('articleId' => null));
    foreach($c_list as $i)
    {
      if($score = $this->item_compare($i, $o_headline, $o_tags))
      {
        $o_success['cluster'][] = array('id' => $i->clusterId, 'ovn' => $score);
      }
    }

    $h_list = $this->ci->database->get('headlines', $where+array('clusterId' => null));
    foreach($h_list as $i)
    {
      if($score = $this->item_compare($i, $o_headline, $o_tags))
      {
        $o_success['headline'][] = array('id' => $i->headlineId, 'ovn' => $score);
      }
    }

    foreach($o_success['article'] as $key => $o)
    {
      if(!($type == 'article' && $id == $o['id']))
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
    }

    foreach($o_success['cluster'] as $key => $o)
    {
      if(!($type == 'cluster' && $id == $o['id']))
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
    }

    foreach($o_success['headline'] as $key => $o)
    {
      if(!($type == 'headline' && $id == $o['id']))
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
    }

    if(sizeof($o_success['article']) == 1)
    {
      if($type == 'cluster')
      {
        $this->ci->database->edit('clusters', array('clusterId' => $id), array('articleId' => $o_success['article'][0]['id']));
      }
      foreach($o_success['cluster'] as $key => $o)
      {
        $this->ci->database->edit('clusters', array('clusterId' => $o['id']), array('articleId' => $o_success['article'][0]['id']));
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
        $this->ci->database->edit('clusters', array('clusterId' => $o['id']), array('articleId' => $t_item['id']));
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
        $this->ci->database->edit('clusters', array('clusterId' => $id), array('articleId' => $articleId));
        $this->ci->database->edit('clusters', array('clusterId' => $o_success['cluster'][0]['id']), array('articleId' => $articleId));
      }

      if($type == 'headline')
      {
        $this->ci->database->edit('headlines', array('headlineId' => $id), array('clusterId' => $o_success['cluster'][0]['id']));
      }
      foreach($o_success['headline'] as $o)
      {
        $this->ci->database->edit('headlines', array('headlineId' => $o['id']), array('clusterId' => $o_success['cluster'][0]['id']));
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
        $this->ci->database->edit('clusters', array('clusterId' => $id), array('articleId' => $articleId));
      }
      else { $articleId = $this->new_article($o_success['cluster']); }
      foreach($o_success['cluster'] as $o)
      {
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
        $this->ci->database->edit('headlines', array('headlineId' => $o['id']), array('clusterId' => $t_item['id']));
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
        $this->ci->database->edit('headlines', array('headlineId' => $id), array('clusterId' => $clusterId));
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
        $this->ci->database->edit('headlines', array('headlineId' => $id), array('clusterId' => $clusterId));
      }
      else { $clusterId = $this->new_cluster($o_success['headline']); }
      foreach($o_success['headline'] as $o)
      {
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

  private function new_article($clusters = array())
  {
    $t_score = 0;
    $t_item = null;
    $tags = "";
    $resources = array();
    foreach($clusters as $key => $o)
    {
      $tags .= ($tags?',':'').$o['item']->tags;
      $thisResources = $this->ci->database->get_array('resources', array('clusterId' => $o['item']->clusterId, 'deleted' => 0), 'resource');
      foreach($thisResources as $tr)
      {
        if(!in_array($tr, $resources)) { $resources[] = $tr; }
      }

      $score = $o['ovn'] + $o['nvo'];
      if($score > $t_score)
      {
        $t_score = $score;
        $t_item = $o;
      }
    }

    $insert = array('headline' => $t_item['item']->headline, 'tags' => $tags, 'createdOn' => time());
    $articleId = $this->ci->database->add('articles', $insert, 'articleId');

    $clusters_update = array('articleId' => $articleId);
    $this->ci->database->add('catlist', array('categoryId' => 1)+$clusters_update, 'catlistId');
    foreach($resources as $resource)
    {
      $this->ci->database->add('resources', array('resource' => $resource)+$clusters_update, 'resourceId');
    }

    return $articleId;
  }

  private function new_cluster($headlines = array())
  {
    $t_score = 0;
    $t_item = null;
    $tags = "";
    $resources = array();
    foreach($headlines as $key => $o)
    {
      $tags .= ($tags?',':'').$o['item']->tags;
      $thisResources = $this->ci->database->get_array('resources', array('headlineId' => $o['item']->headlineId, 'deleted' => 0), 'resource');
      foreach($thisResources as $tr)
      {
        if(!in_array($tr, $resources)) { $resources[] = $tr; }
      }

      $score = $o['ovn'] + $o['nvo'];
      if($score > $t_score)
      {
        $t_score = $score;
        $t_item = $o;
      }
    }

    $insert = array('headline' => $t_item['item']->headline, 'tags' => $tags, 'createdOn' => time());
    $clusterId = $this->ci->database->add('clusters', $insert, 'clusterId');

    $clusters_update = array('clusterId' => $clusterId);
    $this->ci->database->add('catlist', array('categoryId' => 1)+$clusters_update, 'catlistId');
    foreach($resources as $resource)
    {
      $this->ci->database->add('resources', array('resource' => $resource)+$clusters_update, 'resourceId');
    }

    return $clusterId;
  }

  function search($terms = "", $where = "", $order = "score", $results = "all", $comments = false, $limit = 0, $page = 1, $userId = 0)
  {
    $this->ci =& get_instance();
    $this->ci->load->model('database_model', 'database', true);
    $this->ci->load->model('utility_model', 'utility', true);

    $w = 0;
    $match = "";
    $article_match = "";
    $terms = $this->ci->utility->blwords_strip($terms, 'regEx_spaces', ' ');
    if($terms)
    {
      $search = explode(' ', $terms);
      foreach($search as $s)
      {
        if($w) { $match .= " + "; }
        $match .= "IF((headline REGEXP '[[:<:]]".$s."[[:>:]]'), 1, IF((headline REGEXP '".$s."'), .75, 0)) ";
        $match .= "+ IF((tags REGEXP '[[:<:]]".$s."[[:>:]]'), .5, IF((tags REGEXP '".$s."'), .25, 0)) ";
        $article_match .= "+ IF((article REGEXP '[[:<:]]".$s."[[:>:]]'), .5, IF((article REGEXP '".$s."'), .25, 0)) ";
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
        if($w) { $match .= " + "; }
        $match .= "IF((headline REGEXP '[[:<:]]".$s."[[:>:]]'), 1, IF((headline REGEXP '".$s."'), .75, 0)) ";
        $match .= "+ IF((tags REGEXP '[[:<:]]".$s."[[:>:]]'), .5, IF((tags REGEXP '".$s."'), .25, 0)) ";
        $article_match .= "+ IF((article REGEXP '[[:<:]]".$s."[[:>:]]'), .5, IF((article REGEXP '".$s."'), .25, 0)) ";
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

    $sub_score = "SUM(quality + importance + credibility) AS cred_score, ";
    $sub_score .= "SUM((".$match.") / ".$w.") AS search_score, ";
    $sub_score .= "SUM(LOG10(((s.createdOn - 1385884800) + (s.editedOn - 1385884800)) / 4) / 10) AS decay_score ";

    $sql = "SELECT ";
      $sql .= "*, ((search_score + (cred_score / 2) + (sub_score / 2)) / decay_score) AS score ";
    $sql .= "FROM ( ";
        $sql .= "SELECT ";
          $sql .= "'headline' AS type, s.headlineId AS id, IF(views IS NULL, 0, views) AS views, 0 AS h_count, 0 AS c_count, 0 AS sub_score, ";
          if($comments) { $sql .= "comments, "; }
          if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
          $sql .= $scores;
        $sql .= "FROM headlines s ";
        $sql .= "LEFT JOIN metadata m ON m.headlineId = s.headlineId ";
        $sql .= "LEFT JOIN ( ";
          $sql .= "SELECT headlineId, ";
          $sql .= $view_include;
          $sql .= "GROUP BY headlineId ";
        $sql .= ") v ON v.headlineId = s.headlineId ";
        if($comments)
        {
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT headlineId, ";
            $sql .= $comments_include;
            $sql .= "GROUP BY headlineId ";
          $sql .= ") x ON x.headlineId = s.headlineId ";
        }
        if(in_array($results, array('visited', 'unvisited')))
        {
          $sql .= "LEFT JOIN (";
            $sql .= "SELECT headlineId, ";
            $sql .= $viewed_include;
            $sql .= "GROUP BY headlineId ";
          $sql .= ") d ON d.headlineId = s.headlineId ";
        }
        $sql .= "WHERE s.deleted = 0 ";
          $sql .= "AND s.clusterId IS null ";

        $sql .= "UNION ALL ";

        $sql .= "SELECT ";
          $sql .= "'cluster' AS type, s.clusterId AS id, IF(views IS NULL, 0, views) AS views, h_count, 0 AS c_count, ";
          $sql .= "(((h.search_score + (h.cred_score / 2)) / h.decay_score) / h_count) AS sub_score, ";
          if($comments) { $sql .= "comments, "; }
          if(in_array($results, array('visited', 'unvisited'))) { $sql .= "IF(viewed IS NULL, 0, 1) AS visited, "; }
          $sql .= $scores;
        $sql .= "FROM clusters s ";
        $sql .= "LEFT JOIN metadata m ON m.clusterId = s.clusterId ";
        $sql .= "LEFT JOIN ( ";
          $sql .= "SELECT clusterId, ";
          $sql .= $view_include;
          $sql .= "GROUP BY clusterId ";
        $sql .= ") v ON v.clusterId = s.clusterId ";
        $sql .= "LEFT JOIN ( ";
          $sql .= "SELECT s.clusterId, COUNT(s.headlineId) AS h_count, ";
          $sql .= $sub_score;
          $sql .= "FROM headlines s ";
          $sql .= "LEFT JOIN metadata m ON m.headlineId = s.headlineId ";
          $sql .= "WHERE s.clusterId IS NOT NULL ";
          $sql .= "AND s.deleted = 0 ";
          $sql .= "GROUP BY clusterId ";
        $sql .= ") h ON h.clusterId = s.clusterId ";
        if($comments)
        {
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT clusterId, ";
            $sql .= $comments_include;
            $sql .= "GROUP BY clusterId ";
          $sql .= ") x ON x.clusterId = s.clusterId ";
        }
        if(in_array($results, array('visited', 'unvisited')))
        {
          $sql .= "LEFT JOIN (";
            $sql .= "SELECT clusterId, ";
            $sql .= $viewed_include;
          $sql .= ") d ON d.clusterId = s.clusterId ";
        }
        $sql .= "WHERE s.deleted = 0 ";
          $sql .= "AND s.articleId IS null ";

        $sql .= "UNION ALL ";

        $sql .= "SELECT ";
          $sql .= "'article' AS type, s.articleId AS id, IF(views IS NULL, 0, views) AS views, h_count, c_count, ";
          $sql .= "(((c.search_score + (c.cred_score / 2) + c.sub_score) / c.decay_score) / c_count) AS sub_score, ";
          if($comments) { $sql .= "comments, "; }
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
          $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT s.clusterId, COUNT(s.headlineId) AS h_count, ";
            $sql .= $sub_score;
            $sql .= "FROM headlines s ";
            $sql .= "LEFT JOIN metadata m ON m.headlineId = s.headlineId ";
            $sql .= "WHERE s.clusterId IS NOT NULL ";
            $sql .= "AND s.deleted = 0 ";
            $sql .= "GROUP BY clusterId ";
          $sql .= ") h ON h.clusterId = s.clusterId ";
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
        if(in_array($results, array('visited', 'unvisited')))
        {
          $sql .= "LEFT JOIN (";
            $sql .= "SELECT articleId, ";
            $sql .= $viewed_include;
          $sql .= ") d ON d.articleId = s.articleId ";
        }
        $sql .= "WHERE s.deleted = 0 ";
    $sql .= ") items ";
    $sql .= "WHERE decay_score > 0 ";
    if($terms) { $sql .= "AND search_score > 0 "; }
    if($where) { $sql .= "AND ".$where." "; }
    if($results == 'visited') { $sql .= "AND visited = 1 "; }
    if($results == 'unvisited') { $sql .= "AND visited = 0 "; }
    if($userId) { $sql .= "AND (createdBy = ".$userId." OR editedBy = ".$userId.") "; }
    $sql .= "ORDER BY ".$order." DESC, ";
    $sql .= "search_score DESC, cred_score DESC, decay_score DESC ";
    if($limit) { $sql .= "LIMIT ".(($page-1) * $limit).", ".$limit; }

    $q = $this->db->query($sql);
    return $q->result();
  }
}

/* End of file test_model.php */
/* Location: ./application/models/test_model.php */