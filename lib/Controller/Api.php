<?php
namespace Limbonia\Controller;

/**
 * Limbonia API Controller Class
 *
 * This allows the basic controller retrieve data base on the API URL and return
 * that data in JSON format
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Api extends \Limbonia\Controller\Web
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

      if ($xResult instanceof \Limbonia\ItemList)
      {
        $hList = [];

        foreach ($xResult as $oItem)
        {
          $hList[$oItem->id] = $oItem->getAll();
        }

        parent::outputJson($hList);
      }

      if ($xResult instanceof \Limbonia\Item)
      {
        parent::outputJson($xResult->getAll());
      }

      if ($xResult instanceof \Limbonia\Interfaces\Result)
      {
        parent::outputJson($xResult->getAll());
      }

      die(json_encode($xResult));
    }
    catch (\Exception $e)
    {
      $iExceptionCode = $e->getCode();

      //if the exception didn't have numeric code or at least 400, then use 400 instead...
      $iResponseCode = empty($iExceptionCode) || !is_numeric($iExceptionCode) || $iExceptionCode < 400 ? 400 : $iExceptionCode;
      http_response_code($iResponseCode);
      parent::outputJson($e->getMessage());
    }
  }
}