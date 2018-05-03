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
      http_response_code(401);
      die(parent::outputJson($e->getMessage()));
    }
  }

  protected function renderPage()
  {
    try
    {
      ob_start();
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
        return null;
      }

      if ($xResult instanceof \Limbonia\ItemList)
      {
        $hList = [];

        foreach ($xResult as $oItem)
        {
          $hList[$oItem->id] = $oItem->getAll();
        }

        return parent::outputJson($hList);
      }

      if ($xResult instanceof \Limbonia\Item)
      {
        return parent::outputJson($xResult->getAll());
      }

      if ($xResult instanceof \Limbonia\Interfaces\Result)
      {
        return parent::outputJson($xResult->getAll());
      }

      return json_encode($xResult);
    }
    catch (\Exception $e)
    {
      $iExceptionCode = $e->getCode();

      //if the exception didn't have numeric code or at least 400, then use 400 instead...
      $iResponseCode = empty($iExceptionCode) || !is_numeric($iExceptionCode) || $iExceptionCode < 400 ? 400 : $iExceptionCode;
      http_response_code($iResponseCode);
      return parent::outputJson($e->getMessage());
    }
  }
}