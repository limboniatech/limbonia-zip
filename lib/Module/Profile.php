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
  /**
   * The admin group that this module belongs to
   *
   * @var string
   */
  protected $sGroup = 'System';

  /**
   * Should this module's name appear in the menu?
   *
   * @var boolean
   */
  protected $bVisibleInMenu = false;

  /**
   * The type of module this is
   *
   * @var string
   */
  protected $sType = 'Profile';

  /**
   * Lists of columns to ignore when filling template data
   *
   * @var array
   */
  protected $aIgnore =
  [
    'edit' =>
    [
      'UserID',
      'Password',
      'Type',
      'Position',
      'Notes',
      'Active',
      'Visible'
    ],
    'create' => [],
    'search' =>
    [
      'Password',
      'ShippingAddress',
      'StreetAddress',
      'Notes'
    ],
    'view' =>
    [
      'Password',
      'Type',
      'Notes',
      'Active',
      'Visible'
    ],
    'boolean' =>
    [
      'Active',
      'Visible'
    ]
  ];

  /**
   * List of column names in the order required
   *
   * @var array
   */
  protected $aColumnOrder = ['FirstName', 'LastName'];

  /**
   * The default method for this module
   *
   * @var string
   */
  protected $sDefaultAction = 'view';

  /**
   * The current method being used by this module
   *
   * @var string
   */
  protected $sCurrentAction = 'view';

  /**
   * List of menu items that this module should display
   *
   * @var array
   */
  protected $hMenuItems =
  [
    'view' => 'View',
    'edit' => 'Edit',
    'changepassword' => 'Change Password'
  ];

  /**
   * List of sub-menu options
   *
   * @var array
   */
  protected $hSubMenuItems = [];

  /**
   * List of actions that are allowed to run
   *
   * @var array
   */
  protected $aAllowedActions = ['editdialog', 'edit', 'view', 'changepassword'];

  /**
   * Instantiate the profile module
   *
   * @param \Omniverse\Controller $oController
   */
  public function __construct(\Omniverse\Controller $oController)
  {
    $this->oController = $oController;
    $this->oItem = $this->oController->user();
    $this->sCurrentAction = in_array($oController->api->action, $this->aAllowedActions) ? $oController->api->action : $this->sDefaultAction;
  }

  protected function prepareTemplatePostChangepassword()
  {
    $hData = $this->editGetData();

    if ($hData['Password'] != $hData['Password2'])
    {
      $this->oController->templateData('failure', "The passwords did not match. Please try again.");
      $this->oController->server['request_method'] = 'GET';
      return parent::prepareTemplate();
    }

    try
    {
      \Omniverse\Item\User::validatePassword($hData['Password']);
    }
    catch (\Exception $e)
    {
      $this->oController->templateData('failure', $e->getMessage() . ' Please try again');
      $this->oController->server['request_method'] = 'GET';
      return parent::prepareTemplate();
    }

    $this->oItem->password = $hData['Password'];

    if ($this->oItem->save())
    {
      $this->oController->templateData('success', "The password change has been successful.");
    }
    else
    {
      $this->oController->templateData('failure', "The password change has failed.");
    }

    if (isset($_SESSION['EditData']))
    {
      unset($_SESSION['EditData']);
    }

    $this->oController->server['request_method'] = 'GET';
    $this->sCurrentAction = 'view';
  }
}