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
  use \Omniverse\Traits\ItemModule
  {
    \Omniverse\Traits\ItemModule::processApiGetItem as originalProcessApiGetItem;
  }

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

  /**
   * Generate and return the default item data, filtered by API controls
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGetItem()
  {
    switch ($this->oController->api->action)
    {
      case 'releases':
        $oDatabase = $this->oController->getDB();
        $sTable = 'SoftwareRelease';
        $sIdColumn = $oDatabase->getIdColumn($sTable);
        $hWhere = $this->oController->api->search ?? [];
        $bUseTableName = false;
        $aWhere = ['SoftwareID = ' . $this->oItem->id];

        if (isset($hWhere['status']))
        {
          $bUseTableName = true;
          $sTable .= ', Ticket';
          $aWhere = ['SoftwareRelease.SoftwareID = ' . $this->oItem->id];
          $aWhere[] = 'SoftwareRelease.TicketID = Ticket.TicketID';
          $aWhere[] = "Ticket.Status = '{$hWhere['status']}'";
          unset($hWhere['status']);
        }

        $aRawFields = isset($this->oController->api->fields) ? array_merge(['id'], $this->oController->api->fields) : null;
        $aFields = $oDatabase->verifyColumns('SoftwareRelease', $aRawFields, $bUseTableName);
        $sSelect = \Omniverse\Database::makeSelect($aFields, 'SoftwareRelease');

        $aWhere = array_merge($aWhere, $oDatabase->verifyWhere('SoftwareRelease', $hWhere, $bUseTableName));
        $sWhere = \Omniverse\Database::makeWhere($aWhere);

        //default order is according to the ID column of this item
        $aOrder = $this->oController->api->sort ?? ['id'];
        $sOrder = \Omniverse\Database::makeOrder($oDatabase->verifyOrder('SoftwareRelease', $aOrder, $bUseTableName));

        $sSQL = "SELECT DISTINCT $sSelect FROM $sTable$sWhere$sOrder";
        $oResult = $oDatabase->query($sSQL);
        $hList = [];

        foreach ($oResult as $hRow)
        {
          //filter the data through the module's item
          $oItem = $this->oController->itemFromArray($sTable, $hRow);

          if (empty($aFields))
          {
            $hList[$oItem->id] = $oItem->getAll();
          }
          else
          {
            $hItem = $oItem->getAll();
            $hTemp = [];

            foreach ($aFields as $sField)
            {
              $hTemp[$sField] = $hItem[$sField];
            }

            $hList[$oItem->id] = $hTemp;
          }
        }

        return $hList;

      case 'elements':
        $oDatabase = $this->oController->getDB();
        $sTable = 'SoftwareElement';
        $sIdColumn = $oDatabase->getIdColumn($sTable);

        $aRawFields = isset($this->oController->api->fields) ? array_merge(['id'], $this->oController->api->fields) : null;
        $aFields = $oDatabase->verifyColumns($sTable, $aRawFields);
        $sSelect = \Omniverse\Database::makeSelect($aFields);

        $hWhere = $this->oController->api->search ?? [];
        $aWhere = array_merge(['SoftwareID = ' . $this->oItem->id], $oDatabase->verifyWhere($sTable, $hWhere));
        $sWhere = \Omniverse\Database::makeWhere($aWhere);

        //default order is according to the name column of this item
        $aOrder = $this->oController->api->sort ?? ['name'];
        $sOrder = \Omniverse\Database::makeOrder($oDatabase->verifyOrder($sTable, $aOrder));

        $sSQL = "SELECT DISTINCT $sSelect FROM $sTable$sWhere$sOrder";
        $oResult = $oDatabase->query($sSQL);
        $hList = [];

        foreach ($oResult as $hRow)
        {
          //filter the data through the module's item
          $oItem = $this->oController->itemFromArray($sTable, $hRow);

          if (empty($aFields))
          {
            $hList[$oItem->id] = $oItem->getAll();
          }
          else
          {
            $hItem = $oItem->getAll();
            $hTemp = [];

            foreach ($aFields as $sField)
            {
              $hTemp[$sField] = $hItem[$sField];
            }

            $hList[$oItem->id] = $hTemp;
          }
        }

        return $hList;

      case 'changelog':
        header('Location: ' . $this->generateUri($this->oItem->id, 'releases') . '?ststus=closed&sort=-major,-minor,-patch');
        die();

      case 'roadmap':
        throw new \Exception('Roadmap is not yet, available', 404);
    }

    return $this->originalProcessApiGetItem();
  }

  /**
   * Prepare to display any version of the "elements" template
   */
  protected function prepareTemplateElements()
  {
    $oSearch = $this->oController->itemSearch('User', ['Type' => 'internal', 'Active' => 1], ['LastName', 'FirstName']);
    $this->oController->templateData('internalUserList', $oSearch);
  }

  /**
   * Display the "elements" template
   */
  protected function prepareTemplateGetElements()
  {
    if (isset($this->oController->api->subId))
    {
      $oElement = $this->oController->itemFromId('SoftwareElement', $this->oController->api->subId);
      $this->oController->templateData('element', $oElement);
    }
  }

  /**
   * Process the element creation and display the results
   *
   * @throws Exception
   */
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

  /**
   * Process the element update and display the results
   */
  protected function prepareTemplatePostElementsEdit()
  {
    $oElement = $this->oController->itemFromId('SoftwareElement', $this->oController->api->subId);
    $oElement->setAll($this->editGetData());
    $oElement->save();
    $this->oController->templateData('success', "Software element successfully updated");
  }

  /**
   * Process the element deletion and display the results
   */
  protected function prepareTemplatePostElementsDelete()
  {
    $this->oItem->removeElement($this->oController->api->subId);
    $this->oController->templateData('success', "Software element successfully deleted");
  }

  /**
   * Display the "releases" template
   */
  protected function prepareTemplateGetReleases()
  {
    if (isset($this->oController->api->subId))
    {
      $oRelease = $this->oController->itemFromId('SoftwareRelease', $this->oController->api->subId);
      $this->oController->templateData('release', $oRelease);
    }
  }

  /**
   * Process the release creation and display the results
   *
   * @throws \Exception
   */
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

  /**
   * Process the release update and display the results
   */
  protected function prepareTemplatePostReleasesEdit()
  {
    $oRelease = $this->oController->itemFromId('SoftwareRelease', $this->oController->api->subId);
    $oRelease->setAll($this->editGetData());
    $oRelease->save();
    $this->oController->templateData('success', "Software release successfully updated");
  }

  /**
   * Process the release deletion and display the results
   */
  protected function prepareTemplatePostReleasesDelete()
  {
    $this->oItem->removeRelease($this->oController->api->subId);
    $this->oController->templateData('success', "Software release successfully deleted");
  }
}