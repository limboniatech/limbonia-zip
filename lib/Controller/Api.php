<?php
namespace Omniverse\Controller;

/**
 * Omniverse API Controller Class
 *
 * This allows the basic controller retrieve data base on the API URL and return
 * that data in JSON format
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Api extends \Omniverse\Controller\Web
{
  /**
   * Handle any Exceptions thrown while generating the current user
   *
   * @param \Exception $oException
   */
  protected function handleGenerateUserException(\Exception $oException)
  {
    throw new \Exception($oException->getMessage(), 401);
  }

  /**
   * Run everything needed to react and display data in the way this controller is intended
   */
  public function run()
  {
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
    header("Content-Type: application/json");

    ob_start();
    try
    {
      parent::run();

      if ($this->oUser->id == 0)
      {
        throw new \Exception('Authentication failed', 401);
      }

      if (is_null($this->api->module))
      {
        throw new \Exception('No module found');
      }

      $oModule = $this->moduleFactory($this->api->module);

      ob_end_clean();
      $xResult = $oModule->processApi();

      if (is_null($xResult))
      {
        die();
      }

      if ($xResult instanceof \Omniverse\ItemList)
      {
        $hList = [];

        foreach ($xResult as $oItem)
        {
          $hList[$oItem->id] = $oItem->getAll();
        }

        die(json_encode($hList));
      }

      if ($xResult instanceof \Omniverse\Item)
      {
        die(json_encode($xResult->getAll()));
      }

      die(json_encode($xResult));
    }
    catch (\Exception $e)
    {
      $iExceptionCode = $e->getCode();

      //if the exception didn't have numeric code or at least 400, then use 400 instead...
      $iResponseCode = empty($iExceptionCode) || !is_numeric($iExceptionCode) || $iExceptionCode < 400 ? 400 : $iExceptionCode;
      http_response_code($iResponseCode);
      die(json_encode($e->getMessage()));
    }
  }
}