<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation
{
  public function error_array()
  {
    return $this->_error_array;
  }

  // --------------------------------------------------------------------

  /**
   * Password Validation
   *
   * @access public
   * @param string
   * @return bool
   */
  public function password_check($str)
  {
    // Set ReGex Patterns
    $pattern = array('/[a-z]+/', '/[A-Z]+/', '/[0-9]+/');
    // Loop Password Compare
    foreach($pattern as $RegExp)
    {
      if(!preg_match($RegExp, $str)) { return false; }
    }
    return true;
  }

  // --------------------------------------------------------------------

  /**
   * Clean URL from String
   *
   * @access public
   * @param string
   * @return bool
   */
  public function clean_url($str)
  {
    return preg_replace($this->config->item('regEx_urls'), '', $str);
  }
}

/* End of file MY_Form_validation.php */
/* Location: ./application/core/MY_Form_validation.php */