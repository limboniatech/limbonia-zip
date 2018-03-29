<?php
namespace Limbonia\Module;

/**
 * Limbonia User Module class
 *
 * Admin module for handling users
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class User extends \Limbonia\Module
{
  use \Limbonia\Traits\ItemModule
  {
    \Limbonia\Traits\ItemModule::processSearchGetData as originalProcessSearchGetData;
    \Limbonia\Traits\ItemModule::processApiGetItem as originalprocessApiGetItem;
  }

  /**
   * Lists of columns to ignore when filling template data
   *
   * @var array
   */
  protected $aIgnore =
  [
    'edit' =>
    [
      'Password'
    ],
    'create' => [],
    'search' =>
    [
      'Password',
      'ShippingAddress',
      'Country',
      'Notes',
      'StreetAddress',
      'City',
      'State',
      'Zip',
      'HomePhone',
      'CellPhone',
      'Active',
      'Visible'
    ],
    'view' =>
    [
      'Password'
    ]
  ];

  /**
   * List of sub-menu options
   *
   * @var array
   */
  protected $hSubMenuItems =
  [
    'view' => 'View',
    'edit' => 'Edit',
    'resources' => 'Resources',
    'tickets' => 'Tickets',
    'resetpassword' => 'Reset Password'
  ];

  /**
   * Generate and return the default item data, filtered by API controls
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGetItem()
  {
    switch ($this->oApi->action)
    {
      case 'resources':
        $hResourceList = [];
        $hKeys = $this->oItem->getResourceKeys();

        foreach ($this->oItem->getResourceList() as $oResource)
        {
          $hResourceList[$oResource->id] = $oResource->getAll();
          $hResourceList[$oResource->id]['Level'] = $hKeys[$oResource->id];
        }

        return $hResourceList;

      case 'tickets':
        return $this->oItem->getTickets();
    }

    return $this->originalProcessApiGetItem();
  }

  /**
   * List of actions that are allowed to run
   *
   * @var array
   */
  protected $aAllowedActions = ['search', 'create', 'editcolumn', 'edit', 'list', 'view', 'resources', 'resetpassword', 'tickets'];

  /**
   * Process the posted resource data and display the result
   */
  protected function prepareTemplatePostResources()
  {
    try
    {
      $hData = $this->editGetData();
      $this->oItem->setResourceKeys($hData['ResourceKey']);
      $this->oController->templateData('success', "This user's resource update has been successful.");
    }
    catch (\Exception $e)
    {
      $this->oController->templateData('failure', "This user's resource update has failed. <!--" . $e->getMessage() . '-->');
    }

    if (isset($_SESSION['EditData']))
    {
      unset($_SESSION['EditData']);
    }

    $this->oController->server['request_method'] = 'GET';
    $this->sCurrentAction = 'view';
  }

  /**
   * Process the posted password reset data and display the result
   */
  protected function prepareTemplatePostResetpassword()
  {
    try
    {
      $sNewPassword = $this->oItem->resetPassword();
      $sDomain = $this->oController->getDomain();
      $oEmail = new \Limbonia\Email();
      $oEmail->setFrom($this->oController->user()->email);
      $oEmail->addTo($this->oItem->email);
      $oEmail->setSubject("The password for the $sDomain has been reset.");
      $oEmail->addBody("Your new password is $sNewPassword, please login and change it ass soon as possible.");

      if ($oEmail->send())
      {
        $this->oController->templateData('success', "This user's password has been reset and an email sent.");
      }
      else
      {
        $this->oController->templateData('failure', "This user's password has been reset, but the email failed to send.");
      }
    }
    catch (\Exception $e)
    {
      $this->oController->templateData('failure', "This user's password reset has failed. <!--" . $e->getMessage() . '-->');
    }

    $this->oController->server['request_method'] = 'GET';
    $this->sCurrentAction = 'view';
  }

  /**
   * Perform the search based on the specified criteria and return the result
   *
   * @param string|array $xSearch
   * @return \Limbonia\ItemList
   */
  protected function processSearchGetData($xSearch)
  {
    $hSearch = (array)$xSearch;

    if (!isset($hSearch['Email']))
    {
      $hSearch['Email'] = '!=:MasterAdmin';
    }
    elseif (preg_match("/MasterAdmin$/", $hSearch['Email']))
    {
      $hSearch['Email'] = '';
    }

    return $this->originalProcessSearchGetData($hSearch);
  }

  /**
   * Generate and return the value of the specified column
   *
   * @param \Limbonia\Item $oItem
   * @param string $sColumn
   * @return mixed
   */
  public function getColumnValue(\Limbonia\Item $oItem, $sColumn)
  {
    if (in_array($sColumn, ['Active', 'Visible']))
    {
      return $oItem->__get($sColumn) ? 'Yes' : 'No';
    }

    return $oItem->__get($sColumn);
  }
}