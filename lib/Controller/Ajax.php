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
    $sGetClass = filter_input(INPUT_GET, 'class');
    $sClass = empty($sGetClass) ? null : 'Omniverse\\' . $sGetClass;
    $sGetFunction = filter_input(INPUT_GET, 'function');
    $sFunction = empty($sGetFunction) ? null : 'ajax_' . $sGetFunction;

    try
    {
      $oRequest = new $sClass();
    }
    catch (Omnisys_Exception_Object $oException)
    {
      die("alert('Could not create an object from \"$sClass\":  " . $oException->getMessage() . "');");
    }

    if (!method_exists($oRequest, $sFunction))
    {
      die("alert('Class \"$sClass\" does *not* contain the method \"$sFunction\"!');");
    }

    $sReslult = call_user_func_array(array(&$oRequest, $sFunction), $_POST);

    if (ob_get_length() > 10)
    {
      $sReslult .= " alert('This data was detected:  " . ob_get_contents() . "');";
    }

    ob_end_clean();
    die($sReslult);
  }
}