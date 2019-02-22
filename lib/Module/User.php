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
   * List of modules this module depends on to function correctly
   *
   * @var array
   */
  protected static $aModuleDependencies =
  [
    'resourcekey',
    'resourcelock',
    'role'
  ];

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
    'roles' => 'Roles',
    'tickets' => 'Tickets',
    'resetpassword' => 'Reset Password'
  ];

  /**
   * List of actions that are allowed to run
   *
   * @var array
   */
  protected $aAllowedActions = ['search', 'create', 'editcolumn', 'edit', 'list', 'view', 'roles', 'resetpassword', 'tickets'];

  /**
   * Generate and return the default item data, filtered by API controls
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGetItem()
  {
    switch ($this->oRouter->action)
    {
      case 'roles':
        return $this->oItem->getRoles();

      case 'tickets':
        return $this->oItem->getTickets();
    }

    return $this->originalProcessApiGetItem();
  }

  /**
   * Delete the API specified list of items then return true
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiDeleteList()
  {
    if (empty($this->oRouter->search))
    {
      throw new \Limbonia\Exception\Web("No list criteria specified", null, 403);
    }

    $hList = $this->getList(['id']);
    $aList = array_keys($hList);

    if (empty($aList))
    {
      throw new \Limbonia\Exception\Web("List criteria produced no results", null, 403);
    }

    if (in_array($this->oController->user()->id, $aList))
    {
      throw new \Limbonia\Exception\Web("List results cannot contain the current user", null, 403);
    }

    $oMasterUser = $this->oController->userByEmail('MasterAdmin');

    if (in_array($oMasterUser->id, $aList))
    {
      throw new \Limbonia\Exception\Web("List results cannot contain the master user", null, 403);
    }

    $sTable = $this->oItem->getTable();
    $sIdColumn = $this->oItem->getIDColumn();
    $sSql = "DELETE FROM $sTable WHERE $sIdColumn IN (" . implode(', ', $aList) . ")";
    $iRowsDeleted = $this->oController->getDB()->exec($sSql);

    if ($iRowsDeleted === false)
    {
      $aError = $this->errorInfo();
      throw new \Limbonia\Exception\DBResult("Item list not deleted from $sTable: {$aError[0]} - {$aError[2]}", $this->getType(), $sSql, $aError[1]);
    }

    return true;
  }

  /**
   * Process the posted resource data and display the result
   */
  protected function prepareTemplatePostRoles()
  {
    try
    {
      $hData = $this->editGetData();
      $aRoleList = isset($hData['RoleID']) ? $hData['RoleID'] : [];
      $this->oItem->setRoles($aRoleList);
      $this->oController->templateData('success', "This user's role list update has been successful.");
    }
    catch (\Exception $e)
    {
      $this->oController->templateData('failure', "This user's role list update has failed. <!--" . $e->getMessage() . '-->');
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
      $oEmail->addBody("Your new password is $sNewPassword, please login and change it as soon as possible.");

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
   * Generate and return the HTML for the specified form field based on the specified information
   *
   * @param string $sName
   * @param string $sValue
   * @param array $hData
   * @return string
   */
  public function getFormField($sName, $sValue = null, $hData = [])
  {
    if (is_null($sValue) && isset($hData['Default']) && !$this->isSearch())
    {
      $sValue = $hData['Default'];
    }

    if ($sName == 'RoleID')
    {
      $aCurrentRoles = [];
      $sRoleIdField = parent::getFormField('ForceSubmit', '1', ['Type' => 'hidden']);

      foreach ($this->oItem->getRoles() as $oRole)
      {
        $aCurrentRoles[] = $oRole->id;
      }

      foreach ($this->oItem->getRoleList() as $oRole)
      {
        $sChecked = in_array($oRole->id, $aCurrentRoles) ? ' checked' : '';
        $sRoleIdField .= "<div class=\"field\"><label class=\"label\" for=\"UserRole-$oRole->name\">$oRole->name</label><span class=\"data\"><input type=\"checkbox\" id=\"UserRole-$oRole->name\" name=\"User[RoleID][]\" value=\"$oRole->id\"$sChecked></span></div>";
      }

      return $sRoleIdField;
    }

    return parent::getFormField($sName, $sValue, $hData);
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