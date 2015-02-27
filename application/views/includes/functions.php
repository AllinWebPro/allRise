<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define('SITE_TITLE', "allRise - Collaborative News for Everyone");

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
      else { $output = "allRise Description"; }
    break;
    case 'keywords':
      if(isset($data) && !empty($data)) { $output = $data; }
      else { $output = "allRise,Keywords"; }
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
?>