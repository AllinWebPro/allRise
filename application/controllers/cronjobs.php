<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cronjobs extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('utility_model');
    $this->load->model('stream_model');
  }

  public function index() { exit('No direct script access allowed'); }

  public function hourly()
  {
    if(!$this->input->is_cli_request())
    {
      echo "This script can only be accessed via the command line" . PHP_EOL;
      return;
    }
    // Functions
    $time = strtotime("-5 minutes");
    $users = $this->database_model->get('users', array('notices' => 'h', 'deleted' => 0));
    foreach($users as $u)
    {
      $display = array();
      if($u->comments) { $display[] = "comments"; }
      if($u->edits) { $display[] = "edits"; }
      if($u->parents) { $display[] = "joins"; }
      $notices = $this->stream_model->notifications($u->userId, $time, $display, 10000);
      foreach($notices as $n)
      {
        $this->utility_model->emails_notification($u, $n);
      }
    }
  }

  public function daily()
  {
    if(!$this->input->is_cli_request())
    {
      echo "This script can only be accessed via the command line" . PHP_EOL;
      return;
    }
    // Functions
    $time = strtotime("-1 day");
    $users = $this->database_model->get('users', array('notices' => 'd', 'deleted' => 0));
    foreach($users as $u)
    {
      if($users->comments) { $comments = $this->stream_model->notifications($u->userId, $time, array('comments'), 10000); }
      else { $comments = array(); }
      if($users->edits) { $modified = $this->stream_model->notifications($u->userId, $time, array('edits'), 10000); }
      else { $modified = array(); }
      if($users->parents) { $joins = $this->stream_model->notifications($u->userId, $time, array('joins'), 10000); }
      else { $joins = array(); }
      if($modified || $comments || $joins)
      {
        echo $u->email;
        echo "<br>";
        $this->utility_model->emails_notification_bulk($u, 'Daily', $modified, $comments, $joins);
      }
    }
  }

  public function weekly()
  {
    if(!$this->input->is_cli_request())
    {
      echo "This script can only be accessed via the command line" . PHP_EOL;
      return;
    }
    // Functions
    $time = strtotime("-1 week");
    $users = $this->database_model->get('users', array('notices' => 'w', 'deleted' => 0));
    foreach($users as $u)
    {
      if($users->comments) { $comments = $this->stream_model->notifications($u->userId, $time, array('comments'), 10000); }
      else { $comments = array(); }
      if($users->edits) { $modified = $this->stream_model->notifications($u->userId, $time, array('edits'), 10000); }
      else { $modified = array(); }
      if($users->parents) { $joins = $this->stream_model->notifications($u->userId, $time, array('joins'), 10000); }
      else { $joins = array(); }
      if($modified || $comments || $joins)
      {
        echo $u->email;
        echo "<br>";
        $this->utility_model->emails_notification_bulk($u, 'Weekly', $modified, $comments, $joins);
      }
    }
  }
}

/* End of file cronjobs.php */
/* Location: ./application/controllers/cronjobs.php */