<?php
namespace Omniverse\Module;

/**
 * Omniverse User Module class
 *
 * Admin module for handling users
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class User extends \Omniverse\Module
{
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
   * Instantiate a module
   *
   * @param string $sType (optional) - The type of module this should become
   * @param \Omniverse\Controller $oController
   */
  public function __construct($sType = null, \Omniverse\Controller $oController = null)
  {
    $this->aAllowedMethods[] = 'Resources';
    $this->aSubMenuItems[] = 'Resources';
    $this->aAllowedMethods[] = 'ResetPassword';
    $this->aSubMenuItems[] = 'ResetPassword';
    $this->aAllowedMethods[] = 'Tickets';
   $this->aSubMenuItems[] = 'Tickets';
    parent::__construct($sType, $oController);
  }

  /**
   * Prepare the template for display based on the current action and current method
   */
  public function prepareTemplate()
  {
    if ($this->sCurrentAction == 'Process' && $this->sCurrentMethod == 'Resources')
    {
      try
      {
        $hData = $this->editGetData();
        $this->oItem->setResourceKeys($hData['ResourceKey']);
        $this->getController()->templateData('success', "This user's resource update has been successful.");
      }
      catch (\Exception $e)
      {
        $this->getController()->templateData('failure', "This user's resource update has failed. <!--" . $e->getMessage() . '-->');
      }

      if (isset($_SESSION['EditData']))
      {
        unset($_SESSION['EditData']);
      }

      $this->sCurrentAction = 'Display';
      $this->sCurrentMethod = 'View';
    }

    if ($this->sCurrentAction == 'Display' && $this->sCurrentMethod == 'ResetPassword')
    {
      $this->getController()->templateData('post', $_POST);
    }

    if ($this->sCurrentAction == 'Process' && $this->sCurrentMethod == 'ResetPassword')
    {
      try
      {
        $sNewPassword = $this->oItem->resetPassword();
        $sDomain = $this->getController()->getDomain();
        $oEmail = new \Omniverse\Email();
        $oEmail->setFrom($this->getController()->user()->email);
        $oEmail->addTo($this->oItem->email);
        $oEmail->setSubject("The password for the $sDomain has been reset.");
        $oEmail->addBody("Your new password is $sNewPassword, please login and change it ass soon as possible.");

        if ($oEmail->send())
        {
          $this->getController()->templateData('success', "This user's password has been reset and an email sent.");
        }
        else
        {
          $this->getController()->templateData('failure', "This user's password has been reset, but the email failed to send.");
        }
      }
      catch (\Exception $e)
      {
        $this->getController()->templateData('failure', "This user's password reset has failed. <!--" . $e->getMessage() . '-->');
      }

      $this->sCurrentAction = 'Display';
      $this->sCurrentMethod = 'View';
    }

    return parent::prepareTemplate();
  }

  /**
   * Perform the search based on the specified criteria and return the result
   *
   * @param string|array $xSearch
   * @return \Omniverse\ItemList
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

    return parent::processSearchGetData($hSearch);
  }

  /**
   * Generate and return the value of the specified column
   *
   * @param \Omniverse\Item $oItem
   * @param string $sColumn
   * @return mixed
   */
  public function getColumnValue(\Omniverse\Item $oItem, $sColumn)
  {
    if (in_array($sColumn, ['Active', 'Visible']))
    {
      return $oItem->__get($sColumn) ? 'Yes' : 'No';
    }

    return $oItem->__get($sColumn);
  }
}