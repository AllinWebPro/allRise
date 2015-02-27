<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Utility_model extends CI_Model
{
  private $ci;
  private $email_config = array();
  private $email_header;
  private $email_footer;
  private $border_table = '<table border="0" cellpadding="10" cellspacing="0" width="100%" style="border:1px solid #7F8180;border-bottom:none;">';
  private $h2_style = 'font-size:12px;margin:0;';
  private $button_style = 'background-color:#E87941;color:#fff;display:inline-block;float:right;font-size:12px;font-weight:normal;padding:3px 5px;text-decoration:none;';
  private $link_style = 'color:#000000;text-decoration:none;';

  public function __construct()
  {
    parent::__construct();
    $this->ci =& get_instance();
    $this->ci->load->model('database_model', 'database', true);

    $this->email_config['mailtype'] = 'html';
    $this->email_config['protocol'] = 'smtp';
    $this->email_config['smtp_host'] = 'ssl://email-smtp.us-west-2.amazonaws.com';
    $this->email_config['smtp_user'] = 'AKIAIDUIMVKIZANXAWXQ';
    $this->email_config['smtp_pass'] = 'AmlAtgYTb7W8f8No/BgICQwJ762HmCvWmjdXPVRBXv2E';
    $this->email_config['smtp_port'] = '465';
    $this->email_config['newline'] = "\r\n";

    $this->email_header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    $this->email_header .= '<html xmlns="http://www.w3.org/1999/xhtml">';
    $this->email_header .= '<head>';
    $this->email_header .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
    $this->email_header .= '<title>allRise - HTML Email</title>';
    $this->email_header .= '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
    $this->email_header .= '</head>';
    $this->email_header .= '<body style="color:#7F8180;font-family:Verdana, Geneva, sans-serif;font-size:14px;margin:0;padding:0;">';
    $this->email_header .= '<table border="0" cellpadding="20" cellspacing="0" width="100%" style="background-color:#D5D5D5;">';
    $this->email_header .= '<tr>';
    $this->email_header .= '<td>';

    $this->email_footer = '<table align="center" border="0" cellpadding="3" cellspacing="0" width="100%" style="background-color:#E87942;">';
    $this->email_footer .= '<tr>';
    $this->email_footer .= '<td width="20%"></td>';
    $this->email_footer .= '<td width="20%" style="background-color:#F1986E;"></td>';
    $this->email_footer .= '<td width="20%"></td>';
    $this->email_footer .= '<td width="20%" style="background-color:#F1986E;"></td>';
    $this->email_footer .= '<td width="20%"></td>';
    $this->email_footer .= '</tr>';
    $this->email_footer .= '</table>';
    $this->email_footer .= '<table align="center" border="0" cellpadding="10" cellspacing="0" width="100%" style="background-color:#D5D5D5;border-collapse:collapse;">';
    $this->email_footer .= '<tr>';
    $this->email_footer .= '<td align="center">allRise &copy; 2014</td>';
    $this->email_footer .= '</tr><tr>';
    $this->email_footer .= '<td align="center">*DO NOT REPLY TO THIS E-MAIL* This is an automated e-mail message sent.</td>';
    $this->email_footer .= '</tr>';
    $this->email_footer .= '</table>';
    $this->email_footer .= '</td>';
    $this->email_footer .= '</tr>';
    $this->email_footer .= '</table>';
    $this->email_footer .= '</body>';
    $this->email_footer .= '</html>';
  }

  /**
   * Add Keywords
   *
   * @access private
   * @param string
   * @param int
   * @param string
   * @param string
   * @return void
   */
  public function add_keywords($type = '', $id = '', $headline = '', $tags = '')
  {
    $headline = $this->blwords_strip(preg_replace("/[^A-Za-z0-9 ]/", '', $headline));
    $keywords1 = $this->clean_tag_list(str_replace(' ', ',', $headline));
    $words1 = str_getcsv($keywords1);
    foreach($words1 as $w)
    {
      $w = strtolower(trim($w));
      if($w)
      {
        $insert = array($type.'Id' => $id, 'keyword' => $this->db->escape_str(strtolower(trim($w))));
        $this->ci->database->add('keywords', $insert, 'keywordId');
      }
    }
    $keywords2 = $this->clean_tag_list($tags);
    $words2 = str_getcsv($keywords2);
    foreach($words2 as $w)
    {
      $w = strtolower(trim($w));
      if($w)
      {
        $insert = array($type.'Id' => $id, 'keyword' => $this->db->escape_str(strtolower(trim($w))), 'isTag' => 1);
        $this->ci->database->add('keywords', $insert, 'keywordId');
      }
    }
  }

  /**
   * Strip BLWords
   *
   * @access public
   * @param string
   * @param string
   * @param string
   * @return string
   */
  public function blwords_strip($string = "", $delimiter = 'regEx_spaces', $implode = ' ')
  {
    // Remove Black List Words
    if($delimiter !== 'regEx_commas') { $words = preg_split($this->config->item($delimiter), $string, -1); }
    else { $words = str_getcsv($string); }
    $blwords = $this->ci->database->get_array('blwords', array('activated' => 1, 'deleted' => 0), 'blword');
    foreach($words as $key => $val) { if(in_array($val, $blwords)) { unset($words[$key]); } }
    return implode($implode, $words);
  }

  /**
   * Check for BLWords in Stream
   *
   * @access public
   * @param string
   * @return null
   */
  public function blwords_checker($string)
  {
    // Search for Black List Words\
    $where = array('keyword LIKE' => $string, 'deleted' => 0);
    $keywords = $this->ci->database->get('keywords', $where);
    foreach($keywords as $k) { $this->ci->database->edit('keywords', array('keywordId' => $k->keywordId), array('deleted' => 1)); }
  }

  /**
   * Clean Tag List
   *
   * @access public
   * @param string
   * @return object
   */
  public function clean_tag_list($tags = '')
  {
    $tag_list = str_getcsv($tags);
    foreach($tag_list as $key => $value)
    {
      $tag = trim($value);
      if($tag)
      {
        $tag = ucwords($tag);
        $tag_list[$key] = $tag;
      }
      else { unset($tag_list[$key]); }
    }
    $tag_list = array_unique($tag_list);
    return $this->blwords_strip(implode(',', $tag_list), 'regEx_commas', ',');
  }

  /**
   * Generate Item Credibility Score
   *
   * @access public
   * @param string
   * @param id
   * @return void
   */
  public function credibility($type = '', $id = 0)
  {
    $this->ci->load->model('stream_model', 'stream', true);
    //$cats = $this->ci->database->get_array('catlist', array($type.'Id' => $id, 'deleted' => 0), 'categoryId');
    $contributors = $this->stream->get_contributors_full($type, $id);
    $u_score = 0;
    $c_score = 0;
    foreach($contributors as $u)
    {
      $u_score += $u->score;
      /*$temp_c_score = 0;
      foreach($cats as $cat)
      {
        $where = array('categoryId' => $cat, 'userId' => $u->userId);
        $score = $this->ci->database->get_single('scores', $where);
        if($score) { $temp_c_score += $score->score; }
      }
      $c_score += $temp_c_score / sizeof($cats);*/
    }
    if($contributors) { return ($u_score + $c_score) / sizeof($contributors); }
    return 0;
  }

  public function emails_notification($user, $notice)
  {
    $this->load->library('email');
    $this->email->initialize($this->email_config);

    $this->email->from('no-reply@allrise.co', 'allRise');
    $this->email->to($user->email);

    $this->email->subject('Notification for allRise.co');

    $message = $this->email_header;
    // frame
    $message .= '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#FFFFFF;border-collapse:collapse;">';
      // header
      $message .= '<tr>';
        $message .= '<td>';
          $message .= $this->border_table;
            $message .= '<tr>';
              $message .= '<td align="center" width="45%">';
                $message .= '<a href="http:'.base_url().'">';
                  $message .= '<img src="http://beta.allrise.co/media/img/allRise-logo.gif" width="100%" style="max-width:96px;">';
                $message .= '</a>';
              $message .= '</td>';
              $message .= '<td width="65%">';
                $message .= '<h1 style="font-size:20px;margin:0;">Notification</h1>';
              $message .= '</td>';
            $message .= '</tr>';
          $message .= '</table>';
        $message .= '</td>';
      $message .= '</tr>';
      // content
      $message .= '<tr>';
        $message .= '<td>';
          $message .= $this->border_table;
            $message .= '<tr>';
              $message .= '<td>';
                if($notice->action == "edits")
                {
                  $message .= '<p>';
                  if($notice->image) { $message .= '<img src="'.$notice->image.'" height="42" width="42" style="float:left;height:auto;max-width:100%;padding-right:0.3em">'; }
                  $message .= '<a style="'.$this->link_style.'" href="http:'.base_url(substr($notice->type, 0, 1).'/'.$notice->hashId.'/'.$this->get_url_string($notice->headline)).'">';
                  $people = explode(' ', $notice->users);
                  if(sizeof($people) > 3) { $message .= "<strong>".$people[0]."</strong>, <strong>".$people[1]."</strong>, <strong>".$people[2]."</strong>, and ".(sizeof($people) - 3)." others have made "; }
                  elseif(sizeof($people) == 3) { $message .= "<strong>".$people[0]."</strong>, <strong>".$people[1]."</strong>, and <strong>".$people[2]."</strong> have made "; }
                  elseif(sizeof($people) == 2) { $message .= "<strong>".$people[0]."</strong> and <strong>".$people[1]."</strong> have made "; }
                  elseif(sizeof($people) == 1) { $message .= "<strong>".$people[0]."</strong> has left "; }
                  $message .= ($notice->instances > 1)?'edits ':'an edit ';
                  $message .= "on <em>".ucfirst($notice->type)."</em> &ldquo;".stripslashes($notice->headline)."&rdquo;.</a></p>";
                }
                elseif($notice->action == "comments")
                {
                  $message .= '<p>';
                  if($notice->image) { $message .= '<img src="'.$notice->image.'" height="42" width="42" style="float:left;height:auto;max-width:100%;padding-right:0.3em">'; }
                  $message .= '<a style="'.$this->link_style.'" href="http:'.base_url(substr($notice->type, 0, 1).'/'.$notice->hashId.'/'.$this->get_url_string($notice->headline)).'">';
                  $people = explode(' ', $notice->users);
                  if(sizeof($people) > 3) { $message .= "<strong>".$people[0]."</strong>, <strong>".$people[1]."</strong>, <strong>".$people[2]."</strong>, and ".(sizeof($people) - 3)." others have left "; }
                  elseif(sizeof($people) == 3) { $message .= "<strong>".$people[0]."</strong>, <strong>".$people[1]."</strong>, and <strong>".$people[2]."</strong> have left "; }
                  elseif(sizeof($people) == 2) { $message .= "<strong>".$people[0]."</strong> and <strong>".$people[1]."</strong> have left "; }
                  elseif(sizeof($people) == 1) { $message .= "<strong>".$people[0]."</strong> has left "; }
                  $message .= ($notice->instances > 1)?'comments ':'a comment ';
                  $message .= "on <em>".ucfirst($notice->type)."</em> &ldquo;".stripslashes($notice->headline)."&rdquo;.</a></p>";
                }
                elseif($notice->action == "")
                {
                  $message .= '<p>';
                  if($notice->image) { $message .= '<img src="'.$notice->image.'" height="42" width="42" style="float:left;height:auto;max-width:100%;padding-right:0.3em">'; }
                  $message .= '<a style="'.$this->link_style.'" href="http:'.base_url(substr($notice->type, 0, 1).'/'.$notice->hashId.'/'.$this->get_url_string($notice->headline)).'">';
                  $message .= "<em>".ucfirst($notice->type)."</em> &ldquo;".stripslashes($notice->headline)."&rdquo; has been upgraded to ";
                  $message .= (($notice->type == 'headline')?'a Cluster':'an Article').".</a></p>";
                }
                $message .= '<a style="'.$this->button_style.'" href="http:'.base_url('notifications').'">See What Else is New</a>';
              $message .= '</td>';
            $message .= '</tr>';
          $message .= '</table>';
        $message .= '</td>';
      $message .= '</tr>';
    $message .= '</table>';
    $message .= $this->email_footer;
    $this->email->message($message);

    return $this->email->send();
  }

  public function emails_notification_bulk($user, $frequency, $modified, $comments, $joins)
  {
    $this->load->library('email');
    $this->email->initialize($this->email_config);

    $this->email->from('no-reply@allrise.co', 'allRise');
    $this->email->to($user->email);

    $this->email->subject('Notifications for allRise.co');

    $message = $this->email_header;
    // frame
    $message .= '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#FFFFFF;border-collapse:collapse;">';
      // header
      $message .= '<tr>';
        $message .= '<td>';
          $message .= $this->border_table;
            $message .= '<tr>';
              $message .= '<td align="center" width="45%">';
                $message .= '<a href="http:'.base_url().'">';
                  $message .= '<img src="http://beta.allrise.co/media/img/allRise-logo.gif" width="100%" style="max-width:96px;">';
                $message .= '</a>';
              $message .= '</td>';
              $message .= '<td width="65%">';
                $message .= '<h1 style="font-size:20px;margin:0;">'.$frequency.' Notifications</h1>';
              $message .= '</td>';
            $message .= '</tr>';
          $message .= '</table>';
        $message .= '</td>';
      $message .= '</tr>';
      // content
      if($modified)
      {
        $message .= '<tr>';
          $message .= '<td>';
            $message .= $this->border_table;
              $message .= '<tr>';
                $message .= '<td>';
                  $message .= '<h2 style="'.$this->h2_style.'">Content Modified</h2>';
                  foreach($modified as $i)
                  {
                    $message .= '<p>';
                    if($i->photo) { $message .= '<img src="'.site_url('uploads/users/'.$i->photo).'" height="42" width="42" style="float:left;height:auto;max-width:100%;padding-right:0.3em">'; }
                    $message .= '<a style="'.$this->link_style.'" href="http:'.base_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.$this->get_url_string($i->headline)).'">';
                    $people = explode(' ', $i->users);
                    if(sizeof($people) > 3) { $message .= "<strong>".$people[0]."</strong>, <strong>".$people[1]."</strong>, <strong>".$people[2]."</strong>, and ".(sizeof($people) - 3)." others have made "; }
                    elseif(sizeof($people) == 3) { $message .= "<strong>".$people[0]."</strong>, <strong>".$people[1]."</strong>, and <strong>".$people[2]."</strong> have made "; }
                    elseif(sizeof($people) == 2) { $message .= "<strong>".$people[0]."</strong> and <strong>".$people[1]."</strong> have made "; }
                    elseif(sizeof($people) == 1) { $message .= "<strong>".$people[0]."</strong> has left "; }
                    $message .= ($i->instances > 1)?'edits ':'an edit ';
                    $message .= "on <em>".ucfirst($i->type)."</em> &ldquo;".stripslashes($i->headline)."&rdquo;.</a></p>";
                  }
                  $message .= '<a style="'.$this->button_style.'" href="http:'.base_url('notifications').'">See What Else is New</a>';
                $message .= '</td>';
              $message .= '</tr>';
            $message .= '</table>';
          $message .= '</td>';
        $message .= '</tr>';
      }
      // content
      if($comments)
      {
        $message .= '<tr>';
          $message .= '<td>';
            $message .= $this->border_table;
              $message .= '<tr>';
                $message .= '<td>';
                  $message .= '<h2 style="'.$this->h2_style.'">Content with Comments</h2>';
                  foreach($comments as $i)
                  {
                    $message .= '<p>';
                    if($i->photo) { $message .= '<img src="'.site_url('uploads/users/'.$i->photo).'" height="42" width="42" style="float:left;height:auto;max-width:100%;padding-right:0.3em">'; }
                    $message .= '<a style="'.$this->link_style.'" href="http:'.base_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.$this->get_url_string($i->headline)).'">';
                    $people = explode(' ', $i->users);
                    if(sizeof($people) > 3) { $message .= "<strong>".$people[0]."</strong>, <strong>".$people[1]."</strong>, <strong>".$people[2]."</strong>, and ".(sizeof($people) - 3)." others have left "; }
                    elseif(sizeof($people) == 3) { $message .= "<strong>".$people[0]."</strong>, <strong>".$people[1]."</strong>, and <strong>".$people[2]."</strong> have left "; }
                    elseif(sizeof($people) == 2) { $message .= "<strong>".$people[0]."</strong> and <strong>".$people[1]."</strong> have left "; }
                    elseif(sizeof($people) == 1) { $message .= "<strong>".$people[0]."</strong> has left "; }
                    $message .= ($i->instances > 1)?'comments ':'a comment ';
                    $message .= "on <em>".ucfirst($i->type)."</em> &ldquo;".stripslashes($i->headline)."&rdquo;.</a></p>";
                  }
                  $message .= '<a style="'.$this->button_style.'" href="http:'.base_url('notifications').'">See What Else is New</a>';
                $message .= '</td>';
              $message .= '</tr>';
            $message .= '</table>';
          $message .= '</td>';
        $message .= '</tr>';
      }
      // content
      if($joins)
      {
        $message .= '<tr>';
          $message .= '<td>';
            $message .= $this->border_table;
              $message .= '<tr>';
                $message .= '<td>';
                  $message .= '<h2 style="'.$this->h2_style.'">Content Evolutions</h2>';
                  foreach($joins as $i)
                  {
                    $message .= '<p>';
                    if($i->photo) { $message .= '<img src="'.site_url('uploads/users/'.$i->photo).'" height="42" width="42" style="float:left;height:auto;max-width:100%;padding-right:0.3em">'; }
                    $message .= '<a style="'.$this->link_style.'" href="http:'.base_url(substr($i->type, 0, 1).'/'.$i->hashId.'/'.$this->get_url_string($i->headline)).'">';
                    $message .= "<em>".ucfirst($i->type)."</em> &ldquo;".stripslashes($i->headline)."&rdquo; has been upgraded to ";
                    $message .= (($i->type == 'headline')?'a Cluster':'an Article').".</a></p>";
                  }
                  $message .= '<a style="'.$this->button_style.'" href="http:'.base_url('notifications').'">See What Else is New</a>';
                $message .= '</td>';
              $message .= '</tr>';
            $message .= '</table>';
          $message .= '</td>';
        $message .= '</tr>';
      }
    $message .= '</table>';
    $message .= $this->email_footer;
    $this->email->message($message);

    return $this->email->send();
  }

  /**
   * Send Account Confirmation Email
   *
   * @access public
   * @param object
   * @return void
   */
  public function emails_confirmation($user)
  {
    $this->load->library('email');
    $this->email->initialize($this->email_config);
    //$this->email->clear();

    $this->email->from('no-reply@allrise.co', 'allRise');
    $this->email->to($user->email);

    $this->email->subject('Email Change for '.$user->user.' on allRise.co');

    $link1 = "http:".base_url('confirm')."?id=".md5($user->userId)."&e=".$user->email;
    $link2 = "http:".base_url('login');

    $message = $this->email_header;
    // frame
    $message .= '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#FFFFFF;border-collapse:collapse;">';
      // header
      $message .= '<tr>';
        $message .= '<td>';
          $message .= $this->border_table;
            $message .= '<tr>';
              $message .= '<td align="center" width="45%">';
                $message .= '<a href="'.base_url().'">';
                  $message .= '<img src="http://beta.allrise.co/media/img/allRise-logo.gif" width="100%" style="max-width:96px;">';
                $message .= '</a>';
              $message .= '</td>';
              $message .= '<td width="65%">';
                $message .= '<h1 style="font-size:20px;margin:0;">Email Change</h1>';
              $message .= '</td>';
            $message .= '</tr>';
          $message .= '</table>';
        $message .= '</td>';
      $message .= '</tr>';
      // content
      $message .= '<tr>';
        $message .= '<td>';
          $message .= $this->border_table;
            $message .= '<tr>';
              $message .= '<td>';
                $message .= "<p>Dear ".$user->user.",</p>";
                $message .= "<p>The email associated with ".$user->user." has been changed to this address: ".$user->email."</p>";
                $message .= "<p>Please visit the confirmation address below to update our records:<br />";
                $message .= "<a style='".$this->link_style."' href='".$link1."'>".$link1."</a></p>";
                $message .= "<p>Your account can now be logged into with the new email by visiting our website or at the following URL:<br />";
                $message .= "<a style='".$this->link_style."' href='".$link2."'>".$link2."</a></p>";
                $message .= "<p>If you did not request for this account change, we apologize for the inconvenience. You may change your password by visiting our website.</p>";
              $message .= '</td>';
            $message .= '</tr>';
          $message .= '</table>';
        $message .= '</td>';
      $message .= '</tr>';
    $message .= $this->email_footer;

    $this->email->message($message);

    return $this->email->send();
  }

  /**
   * Send Forgot Password Email
   *
   * @access public
   * @param object
   * @param String
   * @return void
   */
  public function emails_password($user, $password)
  {
    $this->load->library('email');
    $this->email->initialize($this->email_config);
    //$this->email->clear();

    $this->email->from('no-reply@allrise.co', 'allRise');
    $this->email->to($user->email);

    $this->email->subject('Login Information for allRise.co');

    $link = "http:".base_url('login');

    $message = $this->email_header;
    // frame
    $message .= '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#FFFFFF;border-collapse:collapse;">';
      // header
      $message .= '<tr>';
        $message .= '<td>';
          $message .= $this->border_table;
            $message .= '<tr>';
              $message .= '<td align="center" width="45%">';
                $message .= '<a href="'.base_url().'">';
                  $message .= '<img src="http://beta.allrise.co/media/img/allRise-logo.gif" width="100%" style="max-width:96px;">';
                $message .= '</a>';
              $message .= '</td>';
              $message .= '<td width="65%">';
                $message .= '<h1 style="font-size:20px;margin:0;">Password Reset</h1>';
              $message .= '</td>';
            $message .= '</tr>';
          $message .= '</table>';
        $message .= '</td>';
      $message .= '</tr>';
      // content
      $message .= '<tr>';
        $message .= '<td>';
          $message .= $this->border_table;
            $message .= '<tr>';
              $message .= '<td>';
                $message .= "<p>Dear ".$user->user.",</p>";
                $message .= "<p>Your password has been reset by request, and below is your new password.<br />";
                $message .= $password."</p>";
                $message .= "<p>Your account can now be logged into with the new password by visiting our website or at the following URL:<br />";
                $message .= "<a style='".$this->link_style."' href='".$link."'>".$link."</a></p>";
                $message .= "<p>If you did not request for this account change, we apologize for the inconvenience. You may change your password by visiting our website.</p>";
              $message .= '</td>';
            $message .= '</tr>';
          $message .= '</table>';
        $message .= '</td>';
      $message .= '</tr>';
    $message .= $this->email_footer;

    $this->email->message($message);

    return $this->email->send();
  }

  /**
   * Send Account Signup Email
   *
   * @access public
   * @param array
   * @return void
   */
  public function emails_signup($user)
  {
    $this->load->library('email');
    $this->email->initialize($this->email_config);
    //$this->email->clear();

    $this->email->from('no-reply@allrise.co', 'allRise');
    $this->email->to($user->email);

    $this->email->subject('Welcome '.$user->user.' to allRise.co');

    $link1 = "http:".base_url('login');
    $link2 = "http:".base_url('confirm')."?id=".md5($user->userId)."&e=".$user->email;

    $message = $this->email_header;
    // frame
    $message .= '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#FFFFFF;border-collapse:collapse;">';
      // header
      $message .= '<tr>';
        $message .= '<td>';
          $message .= $this->border_table;
            $message .= '<tr>';
              $message .= '<td align="center" width="45%">';
                $message .= '<a href="'.base_url().'">';
                  $message .= '<img src="http://beta.allrise.co/media/img/allRise-logo.gif" width="100%" style="max-width:96px;">';
                $message .= '</a>';
              $message .= '</td>';
              $message .= '<td width="65%">';
                $message .= '<h1 style="font-size:20px;margin:0;">Account Created</h1>';
              $message .= '</td>';
            $message .= '</tr>';
          $message .= '</table>';
        $message .= '</td>';
      $message .= '</tr>';
      // content
      $message .= '<tr>';
        $message .= '<td>';
          $message .= $this->border_table;
            $message .= '<tr>';
              $message .= '<td>';
                $message .= "<p>Welcome and thank you for registering to allRise.com!</p>";
                $message .= "<p>Your account has now been created and you can log in by using your email/username and password by visiting our website or at the following URL:<br />";
                $message .= "<a style='".$this->link_style."' href='".$link1."'>".$link1."</a></p>";
                $message .= "<p>Please visit the confirmation address below to update our records:<br />";
                $message .= "<a style='".$this->link_style."' href='".$link2."'>".$link2."</a></p>";
                $message .= "<p>If you did not sign up for this account, please contact us at support@allrise.co. We apologize for any inconvenience this correspondence may have caused, and we assure you that it was only sent at the request of someone visiting our site requesting an account.</p>";
              $message .= '</td>';
            $message .= '</tr>';
          $message .= '</table>';
        $message .= '</td>';
      $message .= '</tr>';
    $message .= $this->email_footer;

    $this->email->message($message);

    return $this->email->send();
  }

  /**
   * Random Password Generator
   *
   * @access private
   * @return void
   */
  public function generate_password()
  {
    $array = array('!', '@', '#', '$', '&', '?');
    $start = rand(0, 22);

    $string = substr(md5(rand(0, 9999999)), $start, $start+10);

    $lower = strtolower(substr($string, 0, 5));
    $upper = strtoupper(substr($string, 6, 10));
    $string = str_shuffle($lower.$upper);

    $string .= $array[array_rand($array, 1)];
    $string = str_shuffle($string);

    $string .= $array[array_rand($array, 1)];
    $string = str_shuffle($string);

    return $string;
  }

  function get_admin_emails()
  {
    $emails = array();
    $admins = $this->database->get('users', array('level' => 'a', 'deleted' => 0));
    foreach($admins as $a) { $emails[] = $a->email; }
    return $emails;
  }

  /**
   * Edit Keywords
   *
   * @access private
   * @param string
   * @param int
   * @param string
   * @param string
   * @return void
   */
  public function keywords($type = '', $id = 0, $headline = '', $tags = '')
  {
    $key_array = $this->ci->database->get_array('keywords', array($type.'Id' => $id, 'deleted' => 0), 'keyword', 'keywordId');
    $headline = $this->blwords_strip(preg_replace("/[^A-Za-z0-9 ]/", '', $headline));
    $keywords1 = $this->clean_tag_list(str_replace(' ', ',', $headline));
    $words1 = str_getcsv($keywords1);
    foreach($words1 as $w)
    {
      $w = strtolower(trim($w));
      if(in_array($w, $key_array)) { unset($key_array[array_search($w, $key_array)]); }
      elseif($w)
      {
        $insert = array($type.'Id' => $id, 'keyword' => $this->db->escape_str(strtolower(trim($w))));
        $this->ci->database->add('keywords', $insert, 'keywordId');
      }
    }
    $keywords2 = $this->clean_tag_list($tags);
    $words2 = str_getcsv($keywords2);
    foreach($words2 as $w)
    {
      $w = strtolower(trim($w));
      if(in_array($w, $key_array)) { unset($key_array[array_search($w, $key_array)]); }
      elseif($w)
      {
        $insert = array($type.'Id' => $id, 'keyword' => $this->db->escape_str(strtolower(trim($w))), 'isTag' => 1);
        $this->ci->database->add('keywords', $insert, 'keywordId');
      }
    }
    foreach($key_array as $key => $val) { $this->ci->database->edit('keywords', array('keywordId' => $key), array('deleted' => 1)); }
  }

  /**
   * Metadata Update
   *
   * @access public
   * @param string
   * @param id
   * @return void
   */
  public function metadata($type = '', $id = 0)
  {
    $numUsers = $this->ci->database->get_count('users', array('deleted' => 0));
    $select = 'SUM(qPositive) AS qpc, SUM(qNegative) AS qnc, SUM(iPositive) AS ipc, SUM(iNegative) AS inc';
    $scores = $this->ci->database->get_single('rankings', array($type.'Id' => $id), $select, $type.'Id');
    $update = array($type.'Id' => $id);
    if($scores && ($scores->qpc || $scores->qnc))
    {
      if($scores->qpc > 0 && $scores->qnc > 0) { $update['quality'] = ($scores->qpc + ($scores->qnc * -1.1)) / $numUsers; }
      elseif($scores->qpc > 0) { $update['quality'] = $scores->qpc / $numUsers; }
      else { $update['quality'] = (($scores->qpc * -1.1))  / $numUsers; }
    }
    else { $update['quality'] = 0; }
    if($scores && ($scores->ipc || $scores->inc))
    {
      if($scores->ipc > 0 && $scores->inc > 0) { $update['importance'] = ($scores->ipc + ($scores->inc * -1.1)) / $numUsers; }
      elseif($scores->ipc > 0) { $update['importance'] = $scores->ipc / $numUsers; }
      else { $update['importance'] = (($scores->inc * -1.1))  / $numUsers; }
    }
    else { $update['importance'] = 0; }
    $update['credibility'] = $this->credibility($type, $id);
    $metadata = $this->ci->database->get_single('metadata', array($type.'Id' => $id));
    $this->ci->database->edit('metadata', array('metadataId' => $metadata->metadataId), $update);
  }

  /**
   * Generate Password Encryption
   *
   * @access public
   * @param array
   * @return void
   */
  public function password_encrypt($username, $password)
  {
    $salt = "$2a$07$".substr($this->config->item('encryption_key'), 0, 22)."$";
    $hash = hash("sha512", $salt.md5($username).md5($password), false);
    return $hash;
  }

  /**
   * Old Pass Hash
   *
   * @access private
   * @return void
   */
  function old_password_encrypt($password)
  {
    $salt = "$2a$07$".substr($this->config->item('encryption_key'), 0, 22)."$";
    return md5($salt.md5($password));
  }

  /**
   * Convert String to Slug
   *
   * @access public
   * @param string
   * @param array
   * @param string
   * @return void
   */
  public function slug_generator($string = '', $replace = array(), $delimiter = '-')
  {
    if(!empty($replace)) { $string = str_replace((array)$replace, ' ', $string); }
    $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower(trim($clean, '-'));
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
    return $clean;
  }

  private function get_url_string($string = '', $max_length = 122)
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

/* End of file utility_model.php */
/* Location: ./application/models/utility_model.php */