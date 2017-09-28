<?php
namespace Omniverse\Module;

/**
 * Omniverse Software Module class
 *
 * Admin module for handling Software
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Software extends \Omniverse\Module
{
  /**
   * List of sub-menu options
   *
   * @var array
   */
  protected $hSubMenuItems =
  [
    'view' => 'View',
    'edit' => 'Edit',
    'elements' => 'Elements',
    'releases' => 'Releases',
    'changelog' => 'Change Log',
    'roadmap' => 'Road Map'
  ];

  /**
   * List of actions that are allowed to run
   *
   * @var array
   */
  protected $aAllowedActions = ['search', 'create', 'editdialog', 'editcolumn', 'edit', 'list', 'view', 'elements', 'releases', 'changelog', 'roadmap'];

  protected function prepareTemplateElements()
  {
    $oSearch = $this->oController->itemSearch('User', ['Type' => 'internal', 'Active' => 1], ['LastName', 'FirstName']);
    $this->oController->templateData('internalUserList', $oSearch);
  }

  protected function prepareTemplateGetElements()
  {
    if (isset($this->oController->api->subId))
    {
      $oElement = $this->oController->itemFromId('SoftwareElement', $this->oController->api->subId);
      $this->oController->templateData('element', $oElement);
    }
  }

  protected function prepareTemplatePostElementsCreate()
  {
    $sName = trim($this->oController->post['Name']);

    if (empty($sName))
    {
      throw new Exception('Software element creation failed: no name given');
    }

    $iUser = (integer)$this->oController->post['UserID'] ?? 0;
    $this->oItem->addElement($sName, $iUser);
    $this->oController->templateData('success', "Software element creation has been successful.");
  }

  protected function prepareTemplatePostElementsEdit()
  {
    $oElement = $this->oController->itemFromId('SoftwareElement', $this->oController->api->subId);
    $oElement->setAll($this->editGetData());
    $oElement->save();
    $this->oController->templateData('success', "Software element successfully updated");
  }

  protected function prepareTemplatePostElementsDelete()
  {
    $this->oItem->removeElement($this->oController->api->subId);
    $this->oController->templateData('success', "Software element successfully deleted");
  }

  protected function prepareTemplateGetReleases()
  {
    if (isset($this->oController->api->subId))
    {
      $oRelease = $this->oController->itemFromId('SoftwareRelease', $this->oController->api->subId);
      $this->oController->templateData('release', $oRelease);
    }
  }

  protected function prepareTemplatePostReleasesCreate()
  {
    $sVersion = trim($this->oController->post['Version']);

    if (empty($sVersion))
    {
      throw new Exception('Software release creation failed: no version given');
    }

    $this->oItem->addRelease($sVersion, $this->oController->post['Note']);
    $this->oController->templateData('success', "Software release creation has been successful.");
  }

  protected function prepareTemplatePostReleasesEdit()
  {
    $oRelease = $this->oController->itemFromId('SoftwareRelease', $this->oController->api->subId);
    $oRelease->setAll($this->editGetData());
    $oRelease->save();
    $this->oController->templateData('success', "Software release successfully updated");
  }

  protected function prepareTemplatePostReleasesDelete()
  {
    $this->oItem->removeRelease($this->oController->api->subId);
    $this->oController->templateData('success', "Software release successfully deleted");
  }
}