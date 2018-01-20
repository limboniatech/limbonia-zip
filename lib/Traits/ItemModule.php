<?php
namespace Omniverse\Traits;

/**
 * Omniverse ItemModule Trait
 *
 * This trait allows an inheriting module to use an item
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
trait ItemModule
{
  /**
   * The item object associated with this module
   *
   * @var \Omniverse\Item
   */
  protected $oItem = null;

  /**
   * Initialize this module's custom data, if there is any
   */
  protected function init()
  {
    $sItemDriver = \Omniverse\Item::driver($this->sType);

    if (empty($sItemDriver))
    {
      throw new \Omniverse\Exception\Object("Driver for type ($this->sType) not found");
    }

    $this->oItem = $this->oController->itemFactory($this->sType);

    if (isset($this->oController->api->id) && strtolower($this->sType) == $this->oController->api->module)
    {
      $this->oItem->load($this->oController->api->id);
    }

    if ($this->oItem->id > 0)
    {
      $this->hMenuItems['item'] = 'Item';
      $this->aAllowedActions[] = 'item';
    }
  }

  public function getItem()
  {
    return $this->oItem;
  }

  /**
   * Make sure a valid item is loaded
   *
   * @throws \Exception
   */
  protected function processApiCheckItem()
  {
    if ($this->oItem->id == 0)
    {
      throw new \Exception($this->getType() . ' #' . $this->oController->api->id . ' not found', 404);
    }
  }

  /**
   * Perform the base "GET" code then return null on success
   *
   * @return null
   * @throws \Exception
   */
  protected function processApiHead()
  {
    if (is_null($this->oController->api->id))
    {
      $oDatabase = $this->oController->getDB();
      $oDatabase->query($oDatabase->makeSearchQuery($this->oItem->getTable(), ['id'], $this->oController->api->search, null));
      return null;
    }

    $this->processApiCheckItem();
    return null;
  }

  /**
   * Generate and return the default list of data, filtered and ordered by API controls
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGetList()
  {
    $sTable = $this->oItem->getTable();
    $oDatabase = $this->oController->getDB();
    $aRawFields = isset($this->oController->api->fields) ? array_merge(['id'], $this->oController->api->fields) : [];
    $aFields = array_diff($oDatabase->verifyColumns($sTable, $aRawFields), $this->aIgnore['view']);

    //default order is according to the ID column of this item
    $aOrder = $this->oController->api->sort ?? ['id'];
    $oResult = $oDatabase->query($oDatabase->makeSearchQuery($sTable, $aFields, $this->oController->api->search, $aOrder));
    $hList = [];

    foreach ($oResult as $hRow)
    {
      //filter the data through the module's item
      $oItem = $this->oController->itemFromArray($sTable, $hRow);
      $hItem = $this->removeIgnoredFields('view', $oItem->getAll());

      if (empty($aFields))
      {
        $hList[$oItem->id] = $hItem;
      }
      else
      {
        $hTemp = [];

        foreach ($aFields as $sField)
        {
          if (isset($hItem[$sField]))
          {
            $hTemp[$sField] = $hItem[$sField];
          }
        }

        $hList[$oItem->id] = $hTemp;
      }
    }

    return $hList;
  }

  /**
   * Generate and return the default item data, filtered by API controls
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGetItem()
  {
    $hRaw = $this->removeIgnoredFields('view', $this->oItem->getAll());

    if ($this->oController->api->fields)
    {
      $hResult = [];
      $sTable = $this->oItem->getTable();

      foreach ($this->oController->api->fields as $sColumn)
      {
        $sRealColumn = $this->oController->getDB()->hasColumn($sTable, $sColumn);

        if ($sRealColumn)
        {
          if (isset($hRaw[$sRealColumn]))
          {
            $hResult[$sRealColumn] = $hRaw[$sRealColumn];
          }
        }
      }

      return $hResult;
    }

    return $hRaw;
  }

  /**
   * Perform and return the default "GET" code
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGet()
  {
    if (is_null($this->oController->api->id))
    {
      return $this->processApiGetList();
    }

    $this->processApiCheckItem();
    return $this->processApiGetItem();
  }

  /**
   * Update the API specified item with the API specified data then return the updated item
   *
   * @return \Omniverse\Item
   * @throws \Exception
   */
  protected function processApiPutItem()
  {
    $hItem = $this->oController->api->data;
    $hLowerItem = \array_change_key_case($hItem, CASE_LOWER);

    foreach ($this->aIgnore['edit'] as $sField)
    {
      $sLowerField = strtolower($sField);

      if (isset($hLowerItem[$sLowerField]))
      {
        unset($hLowerItem[$sLowerField]);
      }
    }

    $this->oItem->setAll($hLowerItem);
    $this->oItem->save();
    return $this->oItem;
  }

  /**
   * Update the API specified list of items with the API specified data then return the updated list
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiPutList()
  {
    $sTable = $this->oItem->getTable();
    $sIdColumn = strtolower($this->oItem->getIDColumn());
    $hList = [];

    foreach ($this->oController->api->data as $iKey => $hItem)
    {
      $hLowerItem = \array_change_key_case($hItem, CASE_LOWER);

      foreach ($this->aIgnore['edit'] as $sField)
      {
        $sLowerField = strtolower($sField);

        if (isset($hLowerItem[$sLowerField]))
        {
          unset($hLowerItem[$sLowerField]);
        }
      }

      if (!isset($hLowerItem['id']) && !isset($hLowerItem[$sIdColumn]))
      {
        throw new \Exception("Valid item ID not found", 409);
      }

      $iItem = $hLowerItem['id'] ?? $hLowerItem[$sIdColumn];

      if ($iKey != $iItem)
      {
        throw new \Exception("Hash key and item ID mismatch", 409);
      }

      $oItem = $this->oController->itemFromId($sTable, $hLowerItem);
      $oItem->setAll($hItem);
      $oItem->save();
      $hItem[$oItem->id] = $oItem->getAll();
    }

    return $hList;
  }

  /**
   * Run the default "PUT" code and return the updated data
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiPut()
  {
    if (!is_array($this->oController->api->data) || count($this->oController->api->data) == 0)
    {
      throw new \Exception('No valid data found to process', 400);
    }

    if (is_null($this->oController->api->id))
    {
      return $this->processApiPutList();
    }

    $this->processApiCheckItem();
    return $this->processApiPutItem();
  }


  /**
   * Create the API specified item with the API specified data then return the created item
   *
   * @return \Omniverse\Item
   * @throws \Exception
   */
  protected function processApiPostItem()
  {
    $hLowerItem = \array_change_key_case($this->oController->api->data, CASE_LOWER);
    $sIdColumn = strtolower($this->oItem->getIDColumn());

    if (isset($hLowerItem['id']))
    {
      unset($hLowerItem['id']);
    }

    if (isset($hLowerItem[$sIdColumn]))
    {
      unset($hLowerItem[$sIdColumn]);
    }

    $oItem = $this->oController->itemFromArray($this->oItem->getTable(), $hLowerItem);
    $oItem->save();
    return $oItem->getAll();
  }

  /**
   * Create the API specified list of items with the API specified data then return that list
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiPostList()
  {
    $sTable = $this->oItem->getTable();
    $sIdColumn = strtolower($this->oItem->getIDColumn());
    $hList = [];

    foreach ($this->oController->api->data as $hItem)
    {
      $hLowerItem = \array_change_key_case($hItem, CASE_LOWER);

      if (isset($hLowerItem['id']))
      {
        unset($hLowerItem['id']);
      }

      if (isset($hLowerItem[$sIdColumn]))
      {
        unset($hLowerItem[$sIdColumn]);
      }

      $oItem = $this->oController->itemFromArray($sTable, $hLowerItem);
      $oItem->save();
      $hItem[$oItem->id] = $oItem->getAll();
    }

    return $hList;
  }

  /**
   * Run the default "POST" code and return the created data
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiPost()
  {
    if (!is_array($this->oController->api->data) || count($this->oController->api->data) == 0)
    {
      throw new \Exception('No valid data found to process', 400);
    }

    $aKeys = array_keys($this->oController->api->data);

    //if the first data key is numeric
    if (is_numeric($aKeys[0]))
    {
      //then we must be processing a list of items...
      return $this->processApiPostList();
    }

    //otherwise it is a single item
    return $this->processApiPostItem();
  }

  /**
   * Delete the API specified item then return true
   *
   * @return \Omniverse\Item
   * @throws \Exception
   */
  protected function processApiDeleteItem()
  {
    return $this->oItem->delete();
  }

  /**
   * Delete the API specified list of items then return true
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiDeleteList()
  {
    $sIdColumn = $this->oItem->getIDColumn();
    $oDatabase = $this->oController->getDB();
    $oResult = $oDatabase->query($oDatabase->makeSearchQuery($this->oItem->getTable(), ['id'], $this->oController->api->search));
    $aList = [];

    foreach ($oResult as $hRow)
    {
      $aList[] = $hRow[$sIdColumn];
    }

    if (empty($aList))
    {
      return true;
    }

    $sTable = $this->oItem->getTable();
    $sSql = "DELETE FROM $sTable WHERE $sIdColumn IN (" . implode(', ', $aList) . ")";
    $iRowsDeleted = $oDatabase->exec($sSql);

    if ($iRowsDeleted === false)
    {
      $aError = $this->errorInfo();
      throw new \Omniverse\Exception\DBResult("Item list not deleted from $sTable: {$aError[0]} - {$aError[2]}", $this->getType(), $sSql, $aError[1]);
    }

    return true;
  }

  /**
   * Run the default "DELETE" code and return true
   *
   * @return boolean - True on success
   * @throws \Exception
   */
  protected function processApiDelete()
  {
    if (is_null($this->oController->api->id))
    {
      return $this->processApiDeleteList();
    }

    $this->processApiCheckItem();
    $this->processApiDeleteItem();
  }

  /**
   * Run the code needed to display the default "list" template
   */
  protected function prepareTemplateList()
  {
    $this->prepareTemplatePostSearch();
  }

  /**
   * Run the code needed to display the default "create" template
   */
  protected function prepareTemplateGetCreate()
  {
    $sIDColumn = $this->oItem->getIDColumn();
    $hTemp = $this->oItem->getColumns();

    if (isset($hTemp[$sIDColumn]))
    {
      unset($hTemp[$sIDColumn]);
    }

    $hColumn = [];

    foreach ($hTemp as $sKey => $hValue)
    {
      if ((in_array($sKey, $this->aIgnore['create'])) || (isset($hValue['Key']) && preg_match("/Primary/", $hValue['Key'])))
      {
        continue;
      }

      $hColumn[preg_replace("/^.*?\./", "", $sKey)] = $hValue;
    }

    $this->oController->templateData('createColumns', $hColumn);
  }

  /**
   * Run the code needed to display the default "edit" template
   */
  protected function prepareTemplateGetEdit()
  {
    if (!$this->allow('edit') || isset($this->oController->post['No']))
    {
      $this->oController->templateData('close', true);
      return null;
    }

    $hTemp = $this->oItem->getColumns();
    $aColumn = $this->getColumns('Edit');
    $hColumn = [];

    foreach ($aColumn as $sColumnName)
    {
      if (isset($hTemp[$sColumnName]))
      {
        $hColumn[$sColumnName] = $hTemp[$sColumnName];
      }
    }

    $sIDColumn = preg_replace("/.*?\./", "", $this->oItem->getIDColumn());
    $this->oController->templateData('idColumn', $sIDColumn);
    $this->oController->templateData('noID', $this->oItem->id == 0);
    $this->oController->templateData('editColumns', $hColumn);
  }

  /**
   * Run the code needed to display the default "search" template
   */
  protected function prepareTemplateGetSearch()
  {
    $hTemp = $this->oItem->getColumns();
    $aColumn = $this->getColumns('search');
    $hColumn = [];

    foreach ($aColumn as $sColumnName)
    {
      if (isset($hTemp[$sColumnName]) && $hTemp[$sColumnName] != 'password')
      {
        $hColumn[$sColumnName] = $hTemp[$sColumnName];

        if ($hColumn[$sColumnName]['Type'] == 'text')
        {
          $hColumn[$sColumnName]['Type'] = 'varchar';
        }

        if ($hColumn[$sColumnName]['Type'] == 'date')
        {
          $hColumn[$sColumnName]['Type'] = 'searchdate';
        }
      }
    }

    $this->oController->templateData('searchColumns', $hColumn);
  }

  /**
   * Process the default "create" code then display the results
   */
  protected function prepareTemplatePostCreate()
  {
    $this->oItem->setAll($this->processCreateGetData());
    $this->oItem->save();
  }

  /**
   * Process the default "edit" code then display the results
   */
  protected function prepareTemplatePostEdit()
  {
    $this->oItem->setAll($this->editGetData());

    if ($this->oItem->save())
    {
      $this->oController->templateData('success', "This " . $this->getType() . " update has been successful.");
    }
    else
    {
      $this->oController->templateData('failure', "This " . $this->getType() . " update has failed.");
    }

    if (isset($_SESSION['EditData']))
    {
      unset($_SESSION['EditData']);
    }

    $this->sCurrentAction = 'view';
  }

  /**
   * Process the default "search" code then display the results
   */
  protected function prepareTemplatePostSearch()
  {
    $xSearch = $this->processSearchGetCriteria();

    if (is_array($xSearch))
    {
      foreach (array_keys($xSearch) as $sKey)
      {
        $this->processSearchTerm($xSearch, $sKey);
      }
    }

    $oData = $this->processSearchGetData($xSearch);

    if ($oData->count() == 1)
    {
      if (isset($this->oController->api->ajax))
      {
        $this->oItem = $oData[0];
        $this->oController->templateData('currentItem', $this->oItem);
        $this->hMenuItems['item'] = 'Item';
        $this->aAllowedActions[] = 'item';
        $this->sCurrentAction = 'view';
        return true;
      }

      if (isset($this->oController->api->subAction) && $this->oController->api->subAction == 'quick')
      {
        $oItem = $oData[0];
        header('Location: '. $this->generateUri($oItem->id));
      }
    }

    $this->oController->templateData('data', $oData);
    $this->oController->templateData('idColumn', preg_replace("/.*?\./", '', $this->oItem->getIDColumn()));
    $hColumns = $this->getColumns('Search');

    foreach (array_keys($hColumns) as $sKey)
    {
      $this->processSearchColumnHeader($hColumns, $sKey);
    }

    $this->oController->templateData('dataColumns', $hColumns);
    $this->oController->templateData('table', $this->oController->widgetFactory('Table'));
  }

  /**
   * Prepare the template for display based on the current action and current method
   */
  public function prepareTemplate()
  {
    parent::prepareTemplate();
    $this->oController->templateData('currentItem', $this->oItem);
  }

  /**
   * Return the name / title of this module's current item, if there is one
   *
   * @return string
   */
  public function getCurrentItemTitle()
  {
    return isset($this->oItem->name) ? $this->oItem->name : '';
  }

  /**
   * Generate and return a list of columns based on the specified type
   *
   * @param string $sType (optional)
   * @return array
   */
  public function getColumns($sType = null)
  {
    $sLowerType = strtolower($sType);
    $hColumn = $this->oItem->getColumns();
    $sIDColumn = $this->oItem->getIDColumn();

    //remove the id column
    if (isset($hColumn[$sIDColumn]))
    {
      unset($hColumn[$sIDColumn]);
    }

    if (empty($sLowerType) || !isset($this->aIgnore[$sLowerType]))
    {
      return array_keys($hColumn);
    }

    //get the column names and remove the ignored columns
    $aColumn = array_diff(array_keys($hColumn), $this->aIgnore[$sLowerType]);

    //reorder the columns
    return array_unique(array_merge($this->aColumnOrder, $aColumn));
  }

  /**
   * Generate and return the data for the "Create" process
   *
   * @return array
   */
  protected function processCreateGetData()
  {
    $hData = isset($this->oController->post[$this->sType]) ? $this->oController->post[$this->sType] : [];

    foreach (array_keys($hData) as $sKey)
    {
      if (empty($hData[$sKey]))
      {
        unset($hData[$sKey]);
      }
    }

    foreach ($this->oItem->getColumns() as $sName => $hColumnData)
    {
      if (strtolower($hColumnData['Type']) == 'tinyint(1)')
      {
        $hData[$sName] = isset($hData[$sName]);
      }
    }

    return $hData;
  }

  /**
   * Return the name of the ID column to use in the search
   *
   * @return string
   */
  protected function processSearchGetSortColumn()
  {
    return $this->oItem->getIDColumn();
  }

  /**
   * Perform the search based on the specified criteria and return the result
   *
   * @param string|array $xSearch
   * @return \Omniverse\ItemList
   */
  protected function processSearchGetData($xSearch)
  {
    return $this->oController->itemSearch($this->oItem->getTable(), $xSearch, $this->processSearchGetSortColumn());
  }

  /**
   * Generate and return the HTML displayed after the edit has finished
   *
   * @param string $sText
   * @param boolean $bReload
   * @return string
   */
  public function editFinish($sText)
  {
    if ($this->oItem->id > 0)
    {
      $sURL = $this->generateUri($this->oItem->id, 'view');
      $sClass = ' class="item"';
    }
    else
    {
      $sURL = $this->generateUri('list');
      $sClass = ' class="module"';
    }

    if (isset($_SESSION['EditData']))
    {
      unset($_SESSION['EditData']);
    }

    return "<center><h1>$sText</h1> Click <a$sClass href=\"$sURL\">here</a> to continue.</center>";
  }

  /**
   * Generate and return the HTML for dealing with updates to rows of data
   *
   * @return string
   */
  public function editColumn()
  {
    if (!$this->allow('Edit') || isset($this->oController->post['No']))
    {
      if (isset($_SESSION['EditData']))
      {
        unset($_SESSION['EditData']);
      }

      return "<script type=\"text/javascript\" language=\"javascript\">history.go(-2);</script>";
    }

    $sFullIDColumn = $this->oItem->getIDColumn();
    $sIDColumn = preg_replace("/.*?\./", "", $sFullIDColumn);

    if (isset($this->oController->post[$sIDColumn]))
    {
      $_SESSION['EditData'][$sIDColumn] = $this->oController->post[$sIDColumn];
    }

    if (isset($this->oController->post['Delete']))
    {
      $_SESSION['EditData']['Delete'] = $this->oController->post['Delete'];
    }

    if (isset($this->oController->post['All']))
    {
      $_SESSION['EditData']['All'] = $this->oController->post['All'];
    }

    if (isset($this->oController->post['Column']))
    {
      $_SESSION['EditData']['Column'] = $this->oController->post['Column'];
    }

    if (!isset($_SESSION['EditData'][$sIDColumn]) && !isset($_SESSION['EditData']['All']))
    {
      $sUse = isset($_SESSION['EditData']['Delete']) ? 'delete' : 'edit';
      //for now we are going to fail insted of asking to use all items...
      //return $this->editDialog("No IDs were checked!  Did you want to $sUse all of them?<br />\n", 'All');
      return $this->editFinish("No IDs were checked, $sUse has failed.  Please check some items and try again!<br />\n");
    }

    if (isset($_SESSION['EditData']['Delete']))
    {
      if (!isset($this->oController->post['Check']))
      {
        return $this->editDialog("Once deleted these items can <b>not</b> restored!  Continue anyway?\n", 'Check');
      }

      $bSuccess = false;

      $hWhere = isset($_SESSION['EditData']['All']) ? [] : [$sFullIDColumn => array_keys($_SESSION['EditData'][$sIDColumn])];
      $oItemList = \Omniverse\Item::search($this->getType(), $hWhere);

      if (isset($oItemList))
      {
        foreach ($oItemList as $oItem)
        {
          $oItem->delete();
        }

        $bSuccess = true;
      }

      $sSuccess = $bSuccess ? 'complete' : 'failed';
      return $this->editFinish("Deletion $sSuccess!");
    }

    if (!$sFullColumn = $_SESSION['EditData']['Column'])
    {
      return $this->editFinish("The column \"{$_SESSION['EditData']['Column']}\" does not exist!");
    }

    if (!isset($this->oController->post['Update']))
    {
      $hColumn = $this->oItem->getColumn($sFullColumn);
      return $this->editDialog($this->getFormFields([$_SESSION['EditData']['Column'] => $hColumn]), 'Update');
    }

    //the first item in the _POST array will be our data
    $sData = array_shift($this->oController->post);

    foreach ($_SESSION['EditData']['AdList'] as $oItem)
    {
      $oItem->setAll($sData);
      $oItem->save();
    }

    return $this->editFinish("Update complete!");
  }

  /**
   * Return the appropriate data for the current edit
   *
   * @return array
   */
  protected function editGetData()
  {
    $hPost = isset($this->oController->post[$this->sType]) ? $this->oController->post[$this->sType] : $this->oController->post->getRaw();

    if (empty($hPost))
    {
      throw new \Exception('No POST data found');
    }

    $hTemp = $this->oItem->getColumns();
    $aIgnore = isset($this->aIgnore['boolean']) ? $this->aIgnore['boolean'] : [];

    foreach ($hTemp as $sName => $hColumnData)
    {
      if (!in_array($sName, $aIgnore) && strtolower($hColumnData['Type']) == 'tinyint(1)')
      {
        $hPost[$sName] = isset($hPost[$sName]);
      }
    }

    return $hPost;
  }
}
