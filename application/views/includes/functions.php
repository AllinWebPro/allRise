<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define('SITE_TITLE', "allRise: Collaborative News for Everyone");

function indexpage($segments = array())
{
  $noindex = array('create', 'modify', 'admin');
  if(in_array($noindex, $segments)) { $output = "noindex"; }
  else { $output = "index"; }
  return $output;
}

function metadata($name, $data)
{
  switch($name)
  {
    case 'author':
      if(isset($data) && !empty($data)) { $output = $data; }
      else { $output = "allRise"; }
    break;
    case 'description':
      if(isset($data) && !empty($data)) { $output = $data; }
      else { $output = "allRise helps communities large and small connect in new ways to share real-time important information and easily build it into high-quality information, news, and articles."; }
    break;
    case 'keywords':
      if(isset($data) && !empty($data)) { $output = $data; }
      else { $output = "allRise,communities,connect,share,information,quality,news,articles"; }
    break;
    default:
      $output = "No Data Available.";
    break;
  }
  return str_replace("\r", ' ', str_replace("\n", ' ', $output));
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

function get_100_char($string = '')
{
  if(strlen($string) > 100)
  {
    $string = wordwrap($string, 100);
    $string = substr($string, 0, strpos($string, "\n"));
    $string .= "...";
  }
  return $string;
}
?>