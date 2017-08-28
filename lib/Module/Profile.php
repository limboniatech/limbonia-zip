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
   * The name of the module
   *
   * @var string
   */
  protected $sModuleName = 'Profile';

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

  /**
   * List of column names in nthe order required
   *
   * @var array
   */
  protected $aColumnOrder = ['FirstName', 'LastName'];

  /**
   * The default method for this module
   *
   * @var string
   */
  protected $sDefaultMethod = 'View';

  /**
   * The current method being used by this module
   *
   * @var string
   */
  protected $sCurrentMethod = 'View';

  /**
   * List of menu items that this module shoud display
   *
   * @var array
   */
  protected $aMenuItems = ['View', 'Edit', 'ChangePassword'];

  /**
   * List of sub-menu options
   *
   * @var array
   */
  protected $aSubMenuItems = [];

  /**
   * List of methods that are allowed to run
   *
   * @var array
   */
  protected $aAllowedMethods = ['EditDialog', 'Edit', 'View', 'ChangePassword'];

  /**
   * Instantiate the profile module
   *
   * @param string $sType (optional) - The type of module this should become
   * @param \Omniverse\Controller $oController
   */
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

  /**
   * Prepare the template for display based on the current action and current method
   */
  public function prepareTemplate()
  {
    if ($this->sCurrentAction == 'Process' && $this->sCurrentMethod == 'ChangePassword')
    {
      $hData = $this->editGetData();

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