<?php
namespace Limbonia\Module;

/**
 * Limbonia Project Module class
 *
 * Admin module for handling Project
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Project extends \Limbonia\Module
{
  use \Limbonia\Traits\ItemModule
  {
    \Limbonia\Traits\ItemModule::processApiGetItem as originalProcessApiGetItem;
  }

  /**
   * Lists of columns to ignore when filling template data
   *
   * @var array
   */
  protected $aIgnore =
  [
    'edit' => ['TopCategoryID'],
    'create' => ['TopCategoryID'],
    'search' => ['TopCategoryID'],
    'view' => ['TopCategoryID'],
    'boolean' => []
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
    'categories' => 'Categories',
    'releases' => 'Releases',
    'changelog' => 'Change Log',
    'roadmap' => 'Road Map'
  ];

  /**
   * List of actions that are allowed to run
   *
   * @var array
   */
  protected $aAllowedActions = ['search', 'create', 'editdialog', 'editcolumn', 'edit', 'list', 'view', 'categories', 'releases', 'changelog', 'roadmap'];

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
      case 'releases':
        $oDatabase = $this->oController->getDB();
        $sTable = 'ProjectRelease';
        $sIdColumn = $oDatabase->getIdColumn($sTable);
        $hWhere = $this->oRouter->search ?? [];
        $bUseTableName = false;
        $aWhere = ['ProjectID = ' . $this->oItem->id];

        if (isset($hWhere['status']))
        {
          $bUseTableName = true;
          $sTable .= ', Ticket';
          $aWhere = ['ProjectRelease.ProjectID = ' . $this->oItem->id];
          $aWhere[] = 'ProjectRelease.TicketID = Ticket.TicketID';
          $aWhere = array_merge($aWhere, $this->getController()->getDB()->verifyWhere('Ticket', ['status' => $hWhere['status']]));
          unset($hWhere['status']);
        }

        $aRawFields = isset($this->oRouter->fields) ? array_merge(['id'], $this->oRouter->fields) : null;
        $aSqlFields = $oDatabase->verifyColumns('ProjectRelease', $aRawFields, $bUseTableName);
        $aFields = $oDatabase->verifyColumns('ProjectRelease', $aRawFields);

        if (!empty($aRawFields) && in_array('version', \array_change_key_case($aRawFields, CASE_LOWER)))
        {
          $aFields[] = 'Version';
          $aSqlFields[] = 'Major';
          $aSqlFields[] = 'Minor';
          $aSqlFields[] = 'Patch';
        }

        $sSelect = \Limbonia\Database::makeSelect($aSqlFields, 'ProjectRelease');
        $aWhere = array_merge($aWhere, $oDatabase->verifyWhere('ProjectRelease', $hWhere, $bUseTableName));
        $sWhere = \Limbonia\Database::makeWhere($aWhere);

        //default order is according to the ID column of this item
        $aOrder = $this->oRouter->sort ?? ['id'];
        $sOrder = \Limbonia\Database::makeOrder($oDatabase->verifyOrder('ProjectRelease', $aOrder, $bUseTableName));

        $sSQL = "SELECT DISTINCT $sSelect FROM $sTable$sWhere$sOrder";
        $oResult = $oDatabase->query($sSQL);
        $hList = [];

        foreach ($oResult as $hRow)
        {
          //filter the data through the module's item
          $oItem = $this->oController->itemFromArray('ProjectRelease', $hRow);

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

      case 'categories':
        $oDatabase = $this->oController->getDB();
        $sTable = 'TicketCategory';
        $sIdColumn = $oDatabase->getIdColumn($sTable);

        $aRawFields = isset($this->oRouter->fields) ? array_merge(['id'], $this->oRouter->fields) : null;
        $aFields = $oDatabase->verifyColumns($sTable, $aRawFields);
        $sSelect = \Limbonia\Database::makeSelect($aFields);

        $hWhere = $this->oRouter->search ?? [];
        $aWhere = array_merge(['ProjectID = ' . $this->oItem->id], $oDatabase->verifyWhere($sTable, $hWhere));
        $sWhere = \Limbonia\Database::makeWhere($aWhere);

        //default order is according to the name column of this item
        $aOrder = $this->oRouter->sort ?? ['name'];
        $sOrder = \Limbonia\Database::makeOrder($oDatabase->verifyOrder($sTable, $aOrder));

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
   * Prepare to display any version of the "categories" template
   */
  protected function prepareTemplateCategories()
  {
    $oSearch = $this->oController->itemSearch('User', ['Type' => 'internal', 'Active' => 1], ['LastName', 'FirstName']);
    $this->oController->templateData('internalUserList', $oSearch);
  }

  /**
   * Display the "categories" template
   */
  protected function prepareTemplateGetCategories()
  {
    if (isset($this->oRouter->subId))
    {
      $oCategory = $this->oController->itemFromId('TicketCategory', $this->oRouter->subId);
      $this->oController->templateData('category', $oCategory);
    }
  }

  /**
   * Process the category creation and display the results
   *
   * @throws Exception
   */
  protected function prepareTemplatePostCategoriesCreate()
  {
    if ($this->oItem->addCategory($this->oController->post->getRaw()))
    {
      $this->oController->templateData('success', "Project category creation has been successful.");
    }
  }

  /**
   * Process the category update and display the results
   */
  protected function prepareTemplatePostCategoriesEdit()
  {
    $oCategory = $this->oController->itemFromId('TicketCategory', $this->oRouter->subId);
    $oCategory->setAll($this->editGetData());

    if ($oCategory->save())
    {
      $this->oController->templateData('success', "Project category successfully updated");
    }
  }

  /**
   * Process the category deletion and display the results
   */
  protected function prepareTemplatePostCategoriesDelete()
  {
    if ($this->oItem->removeCategory($this->oRouter->subId))
    {
      $this->oController->templateData('success', "Project category successfully deleted");
    }
  }

  /**
   * Display the "releases" template
   */
  protected function prepareTemplateGetReleases()
  {
    if (isset($this->oRouter->subId))
    {
      $oRelease = $this->oController->itemFromId('ProjectRelease', $this->oRouter->subId);
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
      throw new Exception('Project release creation failed: no version given');
    }

    $this->oItem->addRelease($sVersion, $this->oController->post['Note']);
    $this->oController->templateData('success', "Project release creation has been successful.");
  }

  /**
   * Process the release update and display the results
   */
  protected function prepareTemplatePostReleasesEdit()
  {
    $oRelease = $this->oController->itemFromId('ProjectRelease', $this->oRouter->subId);
    $oRelease->setAll($this->editGetData());
    $oRelease->save();
    $this->oController->templateData('success', "Project release successfully updated");
  }

  /**
   * Process the release deletion and display the results
   */
  protected function prepareTemplatePostReleasesDelete()
  {
    $this->oItem->removeRelease($this->oRouter->subId);
    $this->oController->templateData('success', "Project release successfully deleted");
  }
}