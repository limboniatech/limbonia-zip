<?php
namespace Limbonia\Traits;

/**
 * Limbonia ItemModule Trait
 *
 * This trait allows an inheriting module to use an item
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
trait ItemModule
{
  /**
   * The type of Item that this module uses
   *
   * @var string
   */
  protected $sItemType = '';

  /**
   * The item object associated with this module
   *
   * @var \Limbonia\Item
   */
  protected $oItem = null;

  /**
   * List of column names in the order required
   *
   * @return array
   */
  protected function columnOrder()
  {
    return [];
  }

  /**
   * Do whatever setup is needed to make this module work...
   */
  public function setup()
  {
    $this->oItem->setup();
  }

  /**
   * Initialize this module's custom data, if there is any
   *
   * @throws \Limbonia\Exception
   */
  protected function init()
  {
    if (empty($this->sItemType))
    {
      $this->sItemType = $this->getType();
    }

    $sItemDriver = \Limbonia\Item::driver($this->sItemType);

    if (empty($sItemDriver))
    {
      throw new \Limbonia\Exception("Driver for type ($this->sItemType) not found");
    }

    $this->oItem = $this->oController->itemFactory($this->sItemType);

    if (isset($this->oRouter->id) && strtolower($this->sType) == $this->oRouter->module)
    {
      $this->oItem->load($this->oRouter->id);
    }

    if ($this->oItem->id > 0)
    {
      $this->hMenuItems['item'] = 'Item';
      $this->aAllowedActions[] = 'item';
    }
  }

  /**
   * Return the item object stored for use with this module
   *
   * @return /Limbonia/Item
   */
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
      throw new \Limbonia\Exception\Web($this->getType() . ' #' . $this->oRouter->call[1] . ' not found', null, 404);
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
    if (empty($this->oRouter->call[1]))
    {
      $oDatabase = $this->oController->getDB();
      $oDatabase->query($oDatabase->makeSearchQuery($this->oItem->getTable(), ['id'], $this->oRouter->search, null));
      return null;
    }

    $this->processApiCheckItem();
    return null;
  }

  protected function getList(array $aFields = [])
  {
    $sTable = $this->oItem->getTable();
    $oDatabase = $this->oController->getDB();
    $aRawFields = empty($aFields) ? [] : array_merge(['id'], $aFields);
    $aFields = array_diff($oDatabase->verifyColumns($sTable, $aRawFields), $this->aIgnore['view']);

    //default order is according to the ID column of this item
    $aOrder = $this->oRouter->sort ?? ['id'];
    $oResult = $oDatabase->query($oDatabase->makeSearchQuery($sTable, $aFields, $this->oRouter->search, $aOrder));
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
   * Generate and return the default list of data, filtered and ordered by API controls
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGetList()
  {
    $aField = empty($this->oRouter->fields) ? [] : $this->oRouter->fields;
    return array_values($this->getList($aField));
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

    if ($this->oRouter->fields)
    {
      $hResult = [];
      $sTable = $this->oItem->getTable();

      foreach ($this->oRouter->fields as $sColumn)
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
    if (empty($this->oRouter->call[1]))
    {
      return $this->processApiGetList();
    }

    $this->processApiCheckItem();
    return $this->processApiGetItem();
  }

  protected function putData()
  {
    $hLowerItem = \array_change_key_case($this->oRouter->data, CASE_LOWER);

    foreach ($this->aIgnore['edit'] as $sField)
    {
      $sLowerField = strtolower($sField);

      if (isset($hLowerItem[$sLowerField]))
      {
        unset($hLowerItem[$sLowerField]);
      }
    }

    return $hLowerItem;
  }

  /**
   * Update the API specified item with the API specified data then return the updated item
   *
   * @return \Limbonia\Item
   * @throws \Exception
   */
  protected function processApiPutItem()
  {
    $this->oItem->setAll($this->putData());
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
    if (empty($this->oRouter->search))
    {
      throw new \Limbonia\Exception\Web("No list criteria specified", null, 403);
    }

    $hItemList = $this->getList(['id']);

    if (empty($hItemList))
    {
      return true;
    }

    $aItemList = array_keys($hItemList);
    $aList = [];
    $sTable = $this->oItem->getTable();
    $hPutData = $this->putData();

    foreach ($aItemList as $iItem)
    {
      $oItem = $this->oController->itemFromId($sTable, $iItem);
      $oItem->setAll($hPutData);
      $oItem->save();
      $aList[] = $oItem->getAll();
    }

    return $aList;
  }

  /**
   * Run the default "PUT" code and return the updated data
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiPut()
  {
    if (!is_array($this->oRouter->data) || count($this->oRouter->data) == 0)
    {
      throw new \Exception('No valid data found to process');
    }

    if (empty($this->oRouter->call[1]))
    {
      return $this->processApiPutList();
    }

    $this->processApiCheckItem();
    return $this->processApiPutItem();
  }

  protected function itemFromArray($hItem)
  {
    $sTable = $this->oItem->getTable();
    $sIdColumn = strtolower($this->oItem->getIDColumn());
    $hLowerItem = \array_change_key_case($hItem, CASE_LOWER);

    if (isset($hLowerItem['id']))
    {
      unset($hLowerItem['id']);
    }

    if (isset($hLowerItem[$sIdColumn]))
    {
      unset($hLowerItem[$sIdColumn]);
    }

    return $this->oController->itemFromArray($sTable, $hLowerItem);
  }

  /**
   * Create the API specified item with the API specified data then return the created item
   *
   * @return \Limbonia\Item
   * @throws \Exception
   */
  protected function processApiPostItem()
  {
    $oItem = $this->itemFromArray($this->oRouter->data);
    $oItem->save();
    header('Location: ' . $this->oController->getDomain()->currenturl . '/' . $this->oRouter->rawPath . '/' . $oItem->id);
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
    $hList = [];

    foreach ($this->oRouter->data as $hItem)
    {
      $oItem = $this->itemFromArray($hItem);
      $oItem->save();
      $hList[$oItem->id] = $oItem->getAll();
    }

    $aIdList = array_keys($hList);
    header('Location: ' . $this->oController->getDomain()->currenturl . '/' . $this->oRouter->rawPath . '/?id=' . implode(',', $aIdList));
    return array_values($hList);
  }

  /**
   * Run the default "POST" code and return the created data
   *
   * @return array
   * @throws \Limbonia\Exception
   */
  protected function processApiPost()
  {
    if (!is_array($this->oRouter->data) || count($this->oRouter->data) == 0)
    {
      throw new \Limbonia\Exception('No valid data found to process');
    }

    $aKeys = array_keys($this->oRouter->data);

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
   * @return \Limbonia\Item
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
    if (empty($this->oRouter->search))
    {
      throw new \Limbonia\Exception\Web("No list criteria specified", null, 403);
    }

    $hList = $this->getList(['id']);
    $aList = array_keys($hList);

    if (empty($aList))
    {
      throw new \Limbonia\Exception\Web("List criteria produced no results", null, 403);
    }

    $sTable = $this->oItem->getTable();
    $sIdColumn = $this->oItem->getIDColumn();
    $sSql = "DELETE FROM $sTable WHERE $sIdColumn IN (" . implode(', ', $aList) . ")";
    $iRowsDeleted = $this->oController->getDB()->exec($sSql);

    if ($iRowsDeleted === false)
    {
      $aError = $this->errorInfo();
      throw new \Limbonia\Exception\DBResult("Item list not deleted from $sTable: {$aError[0]} - {$aError[2]}", $this->getType(), $sSql, $aError[1]);
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
    if (empty($this->oRouter->call[1]))
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
    $this->oItem->setAll($this->getController()->get->getRaw());
  }

  /**
   * Run the code needed to display the default "create" template
   */
  protected function prepareTemplateCreate()
  {
    $this->getController()->templateData('fields', $this->getColumns('create'));
  }

  /**
   * Run the code needed to display the default "edit" template
   */
  protected function prepareTemplateEdit()
  {
    if (!$this->allow('edit') || isset($this->oController->post['No']))
    {
      $this->oController->templateData('close', true);
      return null;
    }

    $this->oController->templateData('fields', $this->getColumns('Edit'));
  }

  /**
   * Run the code needed to display the default "search" template
   */
  protected function prepareTemplateSearch()
  {
    $this->oController->templateData('fields', $this->getColumns('search'));
  }

  /**
   * Run the code needed to display the default "view" template
   */
  protected function prepareTemplateView()
  {
    $this->oController->templateData('fields', $this->getColumns('View'));
  }

  /**
   * Process the default "create" code then display the results
   */
  protected function prepareTemplatePostCreate()
  {
    try
    {
      $this->oItem->setAll($this->processCreateGetData());
      $this->oItem->save();
      $this->getController()->templateData('success', "Successfully created new " . $this->getType() . "<a class=\"item\" href=\"" . $this->generateUri('create') . "\">Create another?</a>");
    }
    catch (\Exception $e)
    {
      $this->getController()->templateData('failure', 'Failed creating new ' . $this->getType() . ': ' . $e->getMessage());
    }

    $this->sCurrentAction = 'view';
  }

  /**
   * Process the default "edit" code then display the results
   */
  protected function prepareTemplatePostEdit()
  {
    try
    {
      $this->oItem->setAll($this->editGetData());
      $this->oItem->save();
      $this->oController->templateData('success', "This " . $this->getType() . " update has been successful.");
    }
    catch (\Exception $e)
    {
      $this->oController->templateData('failure', "This " . $this->getType() . " update has failed: " . $e->getMessage());
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
    $hSearch = $this->processSearchTerms($this->processSearchGetCriteria());
    $oData = $this->processSearchGetData($hSearch);

    if ($oData->count() == 1)
    {
      if (isset($this->oRouter->ajax))
      {
        $this->oItem = $oData[0];
        $this->hMenuItems['item'] = 'Item';
        $this->aAllowedActions[] = 'item';
        $this->sCurrentAction = 'view';
        return true;
      }

      if (isset($this->oRouter->subAction) && $this->oRouter->subAction == 'quick')
      {
        $oItem = $oData[0];
        header('Location: '. $this->generateUri($oItem->id));
      }
    }

    $this->oController->templateData('data', $oData);
    $this->oController->templateData('idColumn', preg_replace("/.*?\./", '', $this->oItem->getIDColumn()));
    $aColumns = array_keys($this->getColumns('Search'));

    foreach (array_keys($aColumns) as $sKey)
    {
      $this->processSearchColumnHeader($aColumns, $sKey);
    }

    $this->oController->templateData('dataColumns', $aColumns);
    $this->oController->templateData('table', $this->oController->widgetFactory('Table'));
  }

  /**
   * Prepare the template for display based on the current action and current method
   */
  public function prepareTemplate()
  {
    $this->oController->templateData('currentItem', $this->oItem);
    parent::prepareTemplate();
  }

  /**
   * Return an array of data that is needed to display the module's admin output
   *
   * @return array
   */
  public function getAdminOutput()
  {
    if ($this->oItem->id > 0)
    {
      return array_merge(parent::getAdminOutput(),
      [
        'itemTitle' => $this->getCurrentItemTitle(),
        'subMenu' => $this->getSubMenuItems(true),
        'id' => $this->oItem->id,
        'itemUri' => $this->generateUri($this->oItem->id)
      ]);
    }

    return parent::getAdminOutput();
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

    if (!empty($sLowerType) && !empty($this->aIgnore[$sLowerType]))
    {
      foreach ($this->aIgnore[$sLowerType] as $sIgnoreColumn)
      {
        if (isset($hColumn[$sIgnoreColumn]))
        {
          unset($hColumn[$sIgnoreColumn]);
        }
      }
    }

    if ($sLowerType == 'search')
    {
      foreach (array_keys($hColumn) as $sColumn)
      {
        if ($hColumn[$sColumn]['Type'] == 'text')
        {
          $hColumn[$sColumn]['Type'] = 'varchar';
        }

        if ($hColumn[$sColumn]['Type'] == 'date')
        {
          $hColumn[$sColumn]['Type'] = 'searchdate';
        }
      }
    }

    $aColumnOrder = $this->columnOrder();

    if (empty($aColumnOrder))
    {
      return $hColumn;
    }

    //reorder the columns
    $hOrderedColumn = [];

    //only order the columns that are in the list
    foreach ($aColumnOrder as $sColumn)
    {
      if (isset($hColumn[$sColumn]))
      {
        $hOrderedColumn[$sColumn] = $hColumn[$sColumn];
        unset($hColumn[$sColumn]);
      }
    }

    //add the rest of the columns at the end of the orderded columns
    return array_merge($hOrderedColumn, $hColumn);
  }

  /**
   * Echo the form generated by the specified data
   *
   * @param string $sType
   * @param array $hFields
   * @param array $hValues
   */
  public function getForm($sType, $hFields, $hValues = [])
  {
    $sForm = parent::getForm($sType, $hFields, $hValues);

    if ($this->oItem->id == 0)
    {
      return $sForm;
    }

    $sType = preg_replace('/ /', '', $sType);
    return preg_replace("/action=\".*?\"/", 'action="' . $this->generateUri($this->oItem->id, $sType) . '"', $sForm);
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
   * @return \Limbonia\ItemList
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
    if (!$this->allow('Edit') || (isset($this->oController->post['No']) && $this->oController->post['No'] == 1))
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
        return $this->editDialog("<input name=\"Check\" id=\"Check\" value=\"1\" type=\"hidden\">\nOnce deleted these items can <b>not</b> restored!  Continue anyway?\n", 'Check');
      }

      $bSuccess = false;

      $hWhere = isset($_SESSION['EditData']['All']) ? [] : [$sFullIDColumn => array_keys($_SESSION['EditData'][$sIDColumn])];
      $oItemList = \Limbonia\Item::search($this->getType(), $hWhere);

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
