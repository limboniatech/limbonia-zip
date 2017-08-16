<?php
namespace Omniverse\Module;

/**
 * Omniverse Profile Module class
 *
 * Admin module for handling the profile of the logged in user
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Profile extends \Omniverse\Module
{
  protected static $hModule = [];
  protected $sGroup = 'System';
  protected $bVisibleInMenu = false;
  protected $sModuleName = 'Profile';
  protected $sType = 'Profile';
  protected $aIgnore =
  [
    'Edit' =>
    [
      'UserID',
      'Password',
      'Type',
      'Position',
      'Notes',
      'Active',
      'Visible'
    ],
    'Create' => [],
    'Search' =>
    [
      'Password',
      'ShippingAddress',
      'StreetAddress',
      'Notes'
    ],
    'View' =>
    [
      'Password',
      'Type',
      'Notes',
      'Active',
      'Visible'
    ],
    'Boolean' =>
    [
      'Active',
      'Visible'
    ]
  ];
  protected $aColumnOrder = ['FirstName', 'LastName'];
  protected $sDefaultAction = 'Process';
  protected $sCurrentAction = 'Process';
  protected $sDefaultMethod = 'View';
  protected $sCurrentMethod = 'View';
  protected $aMenuItems = ['View', 'Edit', 'ChangePassword'];
  protected $aSubMenuItems = [];
  protected $aAllowedMethods = ['EditDialog', 'Edit', 'View', 'ChangePassword'];

  public function __construct($sType=null, \Omniverse\Controller $oController = null)
  {
    if (!empty($oController))
    {
      $this->oController = $oController;
    }

    $this->oItem = $this->getController()->user();
    $sAdmin = isset($_GET['Admin']) ? $_GET['Admin'] : '';
    $sAdminSub = isset($_GET[$sAdmin]) ? $_GET[$sAdmin] : '';

    $this->sCurrentAction = empty($sAdmin) || $sAdmin == 'Menu' || !in_array($sAdminSub, $this->aAllowedMethods) ? $this->sDefaultAction : $sAdmin;
    $this->sCurrentMethod = empty($sAdminSub) || $sAdmin == 'Menu' || !in_array($sAdminSub, $this->aAllowedMethods) ? $this->sDefaultMethod : $sAdminSub;
  }

  public function prepareTemplate()
  {
    if ($this->sCurrentAction == 'Process' && $this->sCurrentMethod == 'ChangePassword')
    {
      $hData = $this->edit_getData();

      if ($hData['Password'] != $hData['Password2'])
      {
        $this->getController()->templateData('failure', "The passwords did not match. Please try again.");
        $this->sCurrentAction = 'Display';
        return parent::prepareTemplate();
      }

      try
      {
        \Omniverse\Item\User::validatePassword($hData['Password']);
      }
      catch (\Exception $e)
      {
        $this->getController()->templateData('failure', $e->getMessage() . ' Please try again');
        $this->sCurrentAction = 'Display';
        return parent::prepareTemplate();
      }

      $this->oItem->Password = $hData['Password'];

      if ($this->oItem->save())
      {
        $this->getController()->templateData('success', "The password change has been successful.");
      }
      else
      {
        $this->getController()->templateData('failure', "The password change has failed.");
      }

      if (isset($_SESSION['EditData']))
      {
        unset($_SESSION['EditData']);
      }

      $this->sCurrentAction = 'Display';
      $this->sCurrentMethod = 'View';
    }

    return parent::prepareTemplate();
  }

}