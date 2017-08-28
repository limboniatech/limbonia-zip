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
  protected $aSubMenuItems = ['View', 'Edit', 'Elements', 'Releases', 'ChangeLog', 'RoadMap'];

  /**
   * List of methods that are allowed to run
   *
   * @var array
   */
  protected $aAllowedMethods = ['Search', 'Create', 'EditDialog', 'EditColumn', 'Edit', 'List', 'View', 'Elements', 'Releases', 'ChangeLog', 'RoadMap'];

  /**
   * Prepare the template for display based on the current action and current method
   */
  public function prepareTemplate()
  {
    if ($this->sCurrentMethod == 'Elements')
    {
      $oSearch = $this->getController()->itemSearch('User', ['Type' => 'internal', 'Active' => 1], ['LastName', 'FirstName']);
      $this->getController()->templateData('internalUserList', $oSearch);

      if ($this->sCurrentAction == 'Display')
      {
        if (isset($_GET['ElementID']))
        {
          try
          {
            $oElement = $this->getController()->itemFromId('SoftwareElement', $_GET['ElementID']);
            $this->getController()->templateData('element', $oElement);
          }
          catch (\Exception $e)
          {
            $this->getController()->templateData('failure', "Software element could not be found: " . $e->getMessage());
          }
        }
      }
      elseif ($this->sCurrentAction == 'Process')
      {
        $this->sCurrentAction = 'Display';

        if (isset($_POST['Name']))
        {
          $sName = trim($_POST['Name']);

          if (empty($sName))
          {
            $this->getController()->templateData('failure', "Software element creation failed: no name given");
          }
          else
          {
            try
            {
              $iUser = isset($_POST['UserID']) ? (integer)$_POST['UserID'] : 0;
              $this->oItem->addElement($sName, $iUser);
              $this->getController()->templateData('success', "Software element creation has been successful.");
            }
            catch (\Exception $e)
            {
              $this->getController()->templateData('failure', "Software element creation failed: " . $e->getMessage());
            }
          }
        }
        elseif (isset($_GET['Edit']) && $_GET['Edit'] = 1)
        {
          unset($_GET['Edit']);
          $this->getController()->templateData('get', $_GET);

          try
          {
            $oElement = $this->getController()->itemFromId('SoftwareElement', $_GET['ElementID']);
            $oElement->setAll($this->editGetData());
            $oElement->save();
            $this->getController()->templateData('success', "Software element successfully updated");
          }
          catch (\Exception $e)
          {
            $this->getController()->templateData('failure', "Software element update failed: " . $e->getMessage());
          }
        }
        elseif (isset($_GET['Delete']) && $_GET['Delete'] = 1)
        {
          unset($_GET['Delete']);
          $this->getController()->templateData('get', $_GET);

          try
          {
            $this->oItem->removeElement($_GET['ElementID']);
            $this->getController()->templateData('success', "Software element successfully deleted");
          }
          catch (\Exception $e)
          {
            $this->getController()->templateData('failure', "Software element delete failed: " . $e->getMessage());
          }
        }
      }
    }
    elseif ($this->sCurrentMethod == 'Releases')
    {
      if ($this->sCurrentAction == 'Display')
      {
        if (isset($_GET['ReleaseID']))
        {
          try
          {
            $oRelease = $this->getController()->itemFromId('SoftwareRelease', $_GET['ReleaseID']);
            $this->getController()->templateData('release', $oRelease);
          }
          catch (\Exception $e)
          {
            $this->getController()->templateData('failure', "Software release could not be found: " . $e->getMessage());
          }
        }
      }
      elseif ($this->sCurrentAction == 'Process')
      {
        $this->sCurrentAction = 'Display';
        $sVersion = filter_input(INPUT_POST, 'Version');

        if (!is_null($sVersion))
        {
          $sVersion = trim($sVersion);

          if (empty($sVersion))
          {
            $this->getController()->templateData('failure', "Software release creation failed: no version given");
          }
          else
          {
            try
            {
              $this->oItem->addRelease($sVersion, filter_input(INPUT_POST, 'Note'));
              $this->getController()->templateData('success', "Software release creation has been successful.");
            }
            catch (\Exception $e)
            {
              $this->getController()->templateData('failure', "Software release creation failed: " . $e->getMessage());
            }
          }
        }
        elseif (isset($_GET['Edit']) && $_GET['Edit'] = 1)
        {
          unset($_GET['Edit']);
          $this->getController()->templateData('get', $_GET);

          try
          {
            $oRelease = $this->getController()->itemFromId('SoftwareRelease', $_GET['ReleaseID']);
            $oRelease->setAll($this->editGetData());
            $oRelease->save();
            $this->getController()->templateData('success', "Software release successfully updated");
          }
          catch (\Exception $e)
          {
            $this->getController()->templateData('failure', "Software release update failed: " . $e->getMessage());
          }
        }
        elseif (isset($_GET['Delete']) && $_GET['Delete'] = 1)
        {
          unset($_GET['Delete']);
          $this->getController()->templateData('get', $_GET);

          try
          {
            $this->oItem->removeRelease($_GET['ReleaseID']);
            $this->getController()->templateData('success', "Software release successfully deleted");
          }
          catch (\Exception $e)
          {
            $this->getController()->templateData('failure', "Software release delete failed: " . $e->getMessage());
          }
        }
      }
    }

    return parent::prepareTemplate();
  }
}