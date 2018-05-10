<?php
namespace Limbonia\Controller;

/**
 * Limbonia Ajax Controller Class
 *
 * This allows the basic controller to feed data to JavaScript on pages through
 * the use of AJAX
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Ajax extends \Limbonia\Controller\Web
{
  /**
   * Generate and return the current user
   *
   * @return \Limbonia\Item\User
   * @throws \Exception
   */
  protected function generateUser()
  {
    try
    {
      return parent::generateUser();
    }
    catch (\Exception $e)
    {
      die(parent::outputJson($e->getMessage()));
    }
  }

  /**
   * Render this controller instance for output and return that data
   *
   * @return string
   */
  protected function render()
  {
    ob_start();
    $aApiCall = $this->oApi->rawcall;
    $sFunction = 'ajax_' . urldecode(array_pop($aApiCall));
    $aApiCall[0] = 'Limbonia';
    $sClass = implode('\\', $aApiCall);

    try
    {
      $oRequest = new $sClass();
    }
    catch (\Limbonia\Exception\Object $oException)
    {
      return "alert('Could not create an object from \"$sClass\":  " . $oException->getMessage() . "');";
    }

    if (!method_exists($oRequest, $sFunction))
    {
      return "alert('Class \"$sClass\" does *not* contain the method \"$sFunction\"!');";
    }

    $sReslult = call_user_func_array([&$oRequest, $sFunction], $this->post->getRaw());

    if (ob_get_length() > 10)
    {
      $sReslult .= " alert('This data was detected:  " . ob_get_contents() . "');";
    }

    ob_end_clean();
    return $sReslult;
  }
}