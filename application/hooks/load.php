<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Load extends CI_Controller
{
  private $CI;
  private $RTX;

  public function __construct()
  {
    parent::__construct();
    $this->CI =& get_instance();
    $this->RTX =& load_class('Router', 'core');
  }

  /**
   * Set Global Arrays
   *
   * @access public
   * @return null
   */
  public function set_arrays()
  {
    $data = array();
    $data['levels'] = array(
      'u' => 'Reporter',
      'm' => 'Organizer',
      'a' => 'Director'
    );
    $this->CI->load->vars($data);
  }
}

/* End of file load.php */
/* Location: ./application/hooks/load.php */