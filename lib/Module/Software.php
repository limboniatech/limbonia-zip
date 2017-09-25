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

  protected function prepareTemplateGetElements()
  {
    $oSearch = $this->oController->itemSearch('User', ['Type' => 'internal', 'Active' => 1], ['LastName', 'FirstName']);
    $this->oController->templateData('internalUserList', $oSearch);

    if (isset($this->oController->api->subid))
    {
      $oElement = $this->oController->itemFromId('SoftwareElement', $this->oController->api->subid);
      $this->oController->templateData('element', $oElement);
    }
  }

  protected function prepareTemplatePostElements()
  {
    $oSearch = $this->oController->itemSearch('User', ['Type' => 'internal', 'Active' => 1], ['LastName', 'FirstName']);
    $this->oController->templateData('internalUserList', $oSearch);
    $this->oController->server['request_method'] = 'GET';

    if (isset($this->oController->post['Name']))
    {
      $sName = trim($this->oController->post['Name']);

      if (empty($sName))
      {
        $this->oController->templateData('failure', "Software element creation failed: no name given");
      }
      else
      {
        try
        {
          $iUser = isset($this->oController->post['UserID']) ? (integer)$this->oController->post['UserID'] : 0;
          $this->oItem->addElement($sName, $iUser);
          $this->oController->templateData('success', "Software element creation has been successful.");
        }
        catch (\Exception $e)
        {
          $this->oController->templateData('failure', "Software element creation failed: " . $e->getMessage());
        }
      }
    }
    elseif ($this->oController->get['Edit'] == 1)
    {
      unset($this->oController->get['Edit']);

      try
      {
        $oElement = $this->oController->itemFromId('SoftwareElement', $this->oController->get['ElementID']);
        $oElement->setAll($this->editGetData());
        $oElement->save();
        $this->oController->templateData('success', "Software element successfully updated");
      }
      catch (\Exception $e)
      {
        $this->oController->templateData('failure', "Software element update failed: " . $e->getMessage());
      }
    }
    elseif ($this->oController->get['Delete'] == 1)
    {
      unset($this->oController->get['Delete']);

      try
      {
        $this->oItem->removeElement($this->oController->get['ElementID']);
        $this->oController->templateData('success', "Software element successfully deleted");
      }
      catch (\Exception $e)
      {
        $this->oController->templateData('failure', "Software element delete failed: " . $e->getMessage());
      }
    }
  }

  protected function prepareTemplateGetReleases()
  {
    if (isset($this->oController->get['ReleaseID']))
    {
      try
      {
        $oRelease = $this->oController->itemFromId('SoftwareRelease', $this->oController->get['ReleaseID']);
        $this->oController->templateData('release', $oRelease);
      }
      catch (\Exception $e)
      {
        $this->oController->templateData('failure', "Software release could not be found: " . $e->getMessage());
      }
    }
  }

  protected function prepareTemplatePostReleases()
  {
    $this->oController->server['request_method'] = 'GET';
    $sVersion = $this->oController->post['Version'];

    if (!is_null($sVersion))
    {
      $sVersion = trim($sVersion);

      if (empty($sVersion))
      {
        $this->oController->templateData('failure', "Software release creation failed: no version given");
      }
      else
      {
        try
        {
          $this->oItem->addRelease($sVersion, $this->oController->post['Note']);
          $this->oController->templateData('success', "Software release creation has been successful.");
        }
        catch (\Exception $e)
        {
          $this->oController->templateData('failure', "Software release creation failed: " . $e->getMessage());
        }
      }
    }
    elseif ($this->oController->get['Edit'] == 1)
    {
      unset($this->oController->get['Edit']);

      try
      {
        $oRelease = $this->oController->itemFromId('SoftwareRelease', $this->oController->get['ReleaseID']);
        $oRelease->setAll($this->editGetData());
        $oRelease->save();
        $this->oController->templateData('success', "Software release successfully updated");
      }
      catch (\Exception $e)
      {
        $this->oController->templateData('failure', "Software release update failed: " . $e->getMessage());
      }
    }
    elseif ($this->oController->get['Delete'] == 1)
    {
      unset($this->oController->get['Delete']);

      try
      {
        $this->oItem->removeRelease($this->oController->get['ReleaseID']);
        $this->oController->templateData('success', "Software release successfully deleted");
      }
      catch (\Exception $e)
      {
        $this->oController->templateData('failure', "Software release delete failed: " . $e->getMessage());
      }
    }
  }
}