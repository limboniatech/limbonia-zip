<?php
namespace Omniverse\Controller;

/**
 * Omniverse Ajax Controller Class
 *
 * This allows the basic controller to feed data to JavaScript on pages through
 * the use of AJAX
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Ajax extends \Omniverse\Controller
{
  /**
   * Run everything needed to react and display data in the way this controller is intended
   */
  public function run()
  {
    ob_start();
    $sApiPath = trim(preg_replace("#\?.*#", '', str_replace($this->baseUrl, '', $this->server['request_uri'])), '/');
    $aApiCall = explode('/', $sApiPath);
    $sFunction = 'ajax_' . urldecode(array_pop($aApiCall));
    array_unshift($aApiCall, 'Omniverse');
    $sClass = implode('\\', $aApiCall);

    try
    {
      $oRequest = new $sClass();
    }
    catch (\Omniverse\Exception\Object $oException)
    {
      die("alert('Could not create an object from \"$sClass\":  " . $oException->getMessage() . "');");
    }

    if (!method_exists($oRequest, $sFunction))
    {
      die("alert('Class \"$sClass\" does *not* contain the method \"$sFunction\"!');");
    }

    $sReslult = call_user_func_array([&$oRequest, $sFunction], $this->post->getRaw());

    if (ob_get_length() > 10)
    {
      $sReslult .= " alert('This data was detected:  " . ob_get_contents() . "');";
    }

    ob_end_clean();
    die($sReslult);
  }
}