<?php
namespace Omniverse\Module;

/**
 * Omniverse Customer Module class
 *
 * Admin module for handling customers
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Customer extends \Omniverse\Module
{
  protected $aSubMenuItems = array('View', 'Edit', 'Contacts', 'Attachments', 'Reports');
  protected $aAllowedMethods = array('Search', 'Create', 'EditDialog', 'EditColumn', 'Edit', 'List', 'View', 'Contacts', 'Attachments', 'Reports', 'AddContact');

  public function prepareTemplate()
  {
    if ($this->sCurrentMethod == 'Contacts')
    {
      $oUser = $this->getController()->itemFactory('user');
      $oUserModule = $this->getController()->moduleFactory('user');
      $this->getController()->templateData('data', $this->oItem->getContactList());
      $this->getController()->templateData('idColumn', preg_replace("/.*?\./", '', $oUser->getIDColumn()));

      $aColumns = $oUserModule->getColumns('Search');

      foreach ($aColumns as $sKey => $sColumn)
      {
        $aColumns[$sKey] = preg_replace("/^.*?\./", "", $aColumns[$sKey]);
      }

      $this->getController()->templateData('userModule', $oUserModule);
      $this->getController()->templateData('dataColumns', $aColumns);
      $this->getController()->templateData('table', $this->getController()->widgetFactory('Table'));
    }

    if ($this->sCurrentMethod == 'AddContact')
    {
      $oUser = $this->getController()->itemFactory('user');
      $oUserModule = $this->getController()->moduleFactory('user');
      $oSearch = $this->getController()->itemList('user', 'SELECT * FROM User U NATURAL JOIN Customer_User CU WHERE CU.CustomerID <> ?', array($this->oItem->id));
      $this->getController()->templateData('data', $oSearch);
      $this->getController()->templateData('idColumn', preg_replace("/.*?\./", '', $oUser->getIDColumn()));

      $aColumns = $oUserModule->getColumns('Search');

      foreach ($aColumns as $sKey => $sColumn)
      {
        $aColumns[$sKey] = preg_replace("/^.*?\./", "", $aColumns[$sKey]);
      }

      $this->getController()->templateData('userModule', $oUserModule);
      $this->getController()->templateData('dataColumns', $aColumns);
      $this->getController()->templateData('table', $this->getController()->widgetFactory('Table'));
    }

    return parent::prepareTemplate();
  }

  public function getColumnValue(\Omniverse\Item $oItem, $sColumn)
  {
    if (in_array($sColumn, array('Active')))
    {
      return $oItem->__get($sColumn) ? 'Yes' : 'No';
    }

    return $oItem->__get($sColumn);
  }

}