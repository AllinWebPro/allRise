<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sitemap extends CI_Controller
{
  function __construct()
  {
    parent::__construct();
    header('Content-type: text/xml');
  }

  function index()
  {
    echo '<?xml version="1.0" encoding="utf-8"?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
      echo '<url>';
        echo '<loc>http:'.base_url().'</loc>';
        echo '<lastmod>2015-02-24</lastmod>';
        echo '<changefreq>yearly</changefreq>';
        echo '<priority>0.4</priority>';
      echo '</url>';
      //
      echo '<url>';
        echo '<loc>http:'.base_url().'search</loc>';
        echo '<lastmod>'.date("Y-m-d").'</lastmod>';
        echo '<changefreq>hourly</changefreq>';
        echo '<priority>1.0</priority>';
      echo '</url>';
      echo '<url>';
        echo '<loc>http:'.base_url().'search?s=score</loc>';
        echo '<lastmod>'.date("Y-m-d").'</lastmod>';
        echo '<changefreq>hourly</changefreq>';
        echo '<priority>0.6</priority>';
      echo '</url>';
      echo '<url>';
        echo '<loc>http:'.base_url().'search?s=views</loc>';
        echo '<lastmod>'.date("Y-m-d").'</lastmod>';
        echo '<changefreq>hourly</changefreq>';
        echo '<priority>0.6</priority>';
      echo '</url>';
      echo '<url>';
        echo '<loc>http:'.base_url().'search?s=createdOn</loc>';
        echo '<lastmod>'.date("Y-m-d").'</lastmod>';
        echo '<changefreq>hourly</changefreq>';
        echo '<priority>0.6</priority>';
      echo '</url>';
      //
      echo '<url>';
        echo '<loc>http:'.base_url().'p/faq</loc>';
        echo '<lastmod>2015-02-24</lastmod>';
        echo '<changefreq>monthly</changefreq>';
        echo '<priority>0.2</priority>';
      echo '</url>';
      echo '<url>';
        echo '<loc>http:'.base_url().'p/bugs</loc>';
        echo '<lastmod>2015-02-24</lastmod>';
        echo '<changefreq>yearly</changefreq>';
        echo '<priority>0.2</priority>';
      echo '</url>';
      echo '<url>';
        echo '<loc>http:'.base_url().'p/contact</loc>';
        echo '<lastmod>2015-02-24</lastmod>';
        echo '<changefreq>yearly</changefreq>';
        echo '<priority>0.2</priority>';
      echo '</url>';
      echo '<url>';
        echo '<loc>http:'.base_url().'p/terms</loc>';
        echo '<lastmod>2015-02-24</lastmod>';
        echo '<changefreq>yearly</changefreq>';
        echo '<priority>0.2</priority>';
      echo '</url>';
      echo '<url>';
        echo '<loc>http:'.base_url().'p/policy</loc>';
        echo '<lastmod>2015-02-24</lastmod>';
        echo '<changefreq>yearly</changefreq>';
        echo '<priority>0.2</priority>';
      echo '</url>';
      //
      $articles = $this->database_model->get_select('articles', array('deleted' => 0), "*, OLD_PASSWORD(articleId) as hashId");
      foreach($articles as $a)
      {
        echo '<url>';
          echo '<loc>http:'.base_url().'a/'.$a->hashId.'/'.$this->get_url_string($a->headline).'</loc>';
          echo '<lastmod>'.date("Y-m-d", (($a->editedOn)?$a->editedOn:$a->createdOn)).'</lastmod>';
          echo '<changefreq>weekly</changefreq>';
          echo '<priority>0.8</priority>';
        echo '</url>';
      }
      $clusters = $this->database_model->get_select('clusters', array('deleted' => 0), "*, OLD_PASSWORD(clusterId) as hashId");
      foreach($clusters as $c)
      {
        echo '<url>';
          echo '<loc>http:'.base_url().'c/'.$c->hashId.'/'.$this->get_url_string($c->headline).'</loc>';
          echo '<lastmod>'.date("Y-m-d", (($c->editedOn)?$c->editedOn:$c->createdOn)).'</lastmod>';
          echo '<changefreq>weekly</changefreq>';
          echo '<priority>0.6</priority>';
        echo '</url>';
      }
      $headlines = $this->database_model->get_select('headlines', array('deleted' => 0), "*, OLD_PASSWORD(headlineId) as hashId");
      foreach($headlines as $h)
      {
        echo '<url>';
          echo '<loc>http:'.base_url().'h/'.$h->hashId.'/'.$this->get_url_string($h->headline).'</loc>';
          echo '<lastmod>'.date("Y-m-d", (($h->editedOn)?$h->editedOn:$h->createdOn)).'</lastmod>';
          echo '<changefreq>weekly</changefreq>';
          echo '<priority>0.4</priority>';
        echo '</url>';
      }
      $users = $this->database_model->get('users', array('deleted' => 0));
      foreach($users as $u)
      {
        echo '<url>';
          echo '<loc>http:'.base_url().'u/'.$u->user.'</loc>';
          echo '<lastmod>'.date("Y-m-d").'</lastmod>';
          echo '<changefreq>weekly</changefreq>';
          echo '<priority>0.4</priority>';
        echo '</url>';
      }
    echo '</urlset>';
  }

  function get_url_string($string = '', $max_length = 122)
  {
    $string = preg_replace('/[^A-Za-z0-9 ]/', '', $string);
    if(strlen($string) > $max_length)
    {
      $string = wordwrap($string, $max_length);
      $string = substr($string, 0, strpos($string, "\n"));
    }
    return str_replace(' ', '-', str_replace('  ', '-', $string));
  }
}