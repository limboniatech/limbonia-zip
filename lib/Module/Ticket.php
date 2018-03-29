<?php
namespace Limbonia\Module;

/**
 * Limbonia Ticket Module class
 *
 * Admin module for handling tickets
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Ticket extends \Limbonia\Module
{
  use \Limbonia\Traits\ItemModule
  {
    \Limbonia\Traits\ItemModule::processApiGetItem as originalProcessApiGetItem;
    \Limbonia\Traits\ItemModule::processApiGetList as originalProcessApiGetList;
    \Limbonia\Traits\ItemModule::processCreateGetData as originalProcessCreateGetData;
    \Limbonia\Traits\ItemModule::editGetData as originalEditGetData;
  }

  /**
   * Lists of columns to ignore when filling template data
   *
   * @var array
   */
  protected $aIgnore =
  [
    'edit' =>
    [
      'LastUpdate',
      'CreateTime',
      'CompletionTime',
      'CreatorID'
    ],
    'create' =>
    [
      'CreatorID',
  		'TimeSpent',
      'CreateTime',
      'CompletionTime',
      'LastUpdate'
    ],
    'search' =>
    [
      'TimeSpent',
      'ParentID',
      'CompletionTime',
      'CreateTime',
      'SoftwareID',
      'ElementID',
      'ReleaseID',
      'Severity',
      'Projection',
      'DevStatus',
      'QualityStatus',
      'Description',
      'StepsToReproduce'
    ],
    'view' => [],
    'usertickets' =>
    [
      'TimeSpent',
      'OwnerID',
      'ParentID',
      'CompletionTime',
      'CreateTime',
      'SoftwareID',
      'ElementID',
      'ReleaseID',
      'Severity',
      'Projection',
      'DevStatus',
      'QualityStatus',
      'Description',
      'StepsToReproduce'
    ],
  ];

  /**
   * List of column names in the order required
   *
   * @var array
   */
  protected $aColumnOrder =
  [
    'Status',
    'Priority',
    'OwnerID',
    'Subject',
    'CategoryID'
  ];

  /**
   * List of quick search items to display
   *
   * @var array
   */
  protected $hQuickSearch =
  [
    'TicketID' => 'Ticket ID'
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
    'attachments' => 'Attachments',
    'relationships' => 'Relationships'
  ];

  /**
   * List of actions that are allowed to run
   *
   * @var array
   */
  protected $aAllowedActions = ['search', 'create', 'editdialog', 'editcolumn', 'edit', 'list', 'view', 'attachments', 'relationships', 'watchers'];

  /**
   * List of column names that are allowed to generate "edit" links
   *
   * @var array
   */
  protected $aEditColumn =
  [
    'Status',
    'Priority',
    'Owner',
    'Category',
    'DueDate',
    'StartDate'
  ];

  /**
   * Generate and return the default item data, filtered by API controls
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGetItem()
  {
    switch ($this->oApi->action)
    {
      case 'attachments':
        return $this->oItem->getAttachmentList();

      case 'parent':
        return $this->oItem->parentID > 0 ? $this->oItem->parent : [];

      case 'children':
        return $this->oItem->getChildren();
    }

    return $this->originalProcessApiGetItem();
  }

  /**
   * Generate and return the default list of data, filtered and ordered by API controls
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGetList()
  {
    //if the field list is either not narrowed down at all or includes "children"
    if (empty($this->oApi->fields) || in_array('children', $this->oApi->fields))
    {
      //then add the children list to each ticket
      $hList = $this->originalProcessApiGetList();

      if (empty($hList))
      {
        return $hList;
      }

      $sSQL = "SELECT ParentID AS 'id', TicketID AS 'child' FROM Ticket WHERE ParentID IN (" . implode(', ', array_keys($hList)) . ')';
      $oResult = $this->oController->getDB()->query($sSQL);

      foreach (array_keys($hList) as $iID)
      {
        $hList[$iID]['Children'] = [];
      }

      foreach ($oResult as $hRow)
      {
        $hList[$hRow['id']]['Children'][] = (integer)$hRow['child'];
      }

      return $hList;
    }

    //otherwise directly return the original list
    return $this->originalProcessApiGetList();
  }

  /**
   * Return the module criteria
   *
   * @return array
   */
  protected function processSearchGetCriteria()
  {
    $hCriteria = parent::processSearchGetCriteria();

    //if the search criteria is empty then assign a default
    //of no closed tickets.
    if (empty($hCriteria))
    {
      $hCriteria['Status'] = "!=:closed";
    }

    return $hCriteria;
  }

  /**
   * Generate and return the data for the "Create" process
   *
   * @return array
   */
  protected function processCreateGetData()
  {
    $hData = $this->originalProcessCreateGetData();
    $hData['CreatorID'] = $this->oController->user()->id;
    return $hData;
  }

  /**
   * Process the attachment addition then display the result
   */
  protected function prepareTemplateAttachmentsAdd()
  {
    if (isset($_FILES['Attachment']))
    {
      $this->oItem->addAttachment($_FILES['Attachment']['tmp_name'], $_FILES['Attachment']['name']);
      $this->oController->templateData('success', "Successfully added attachment.");
    }
    else
    {
      $this->oController->templateData('failure', "Uploaded attachment not found.");
    }
  }

  /**
   * Process the attachment deletion then display the result
   */
  protected function prepareTemplateAttachmentsDelete()
  {
    $this->oItem->removeAttachmentById($this->oApi->subId);
    $this->oController->templateData('success', "Successfully removed attachment.");
  }

  /**
   * Process the new parent then display the result
   */
  protected function prepareTemplateRelationshipsSetparent()
  {
    $oParent = $this->oController->itemFromId('ticket', $this->oController->post['SetParent']);
    $this->oItem->parentId = $oParent->id;
    $this->oItem->save();
    $this->oController->templateData('success', "Successfully set parent ticket.");
  }

  /**
   * Process parent removal then display the result
   */
  protected function prepareTemplateRelationshipsRemoveparent()
  {
    $this->oItem->parentId = 0;
    $this->oItem->save();
    $this->oController->templateData('success', "Successfully removed parent ticket.");
  }

  /**
   * Process the new child then display the result
   */
  protected function prepareTemplateRelationshipsAddchild()
  {
    $oChild = $this->oController->itemFromId('ticket', $this->oController->post['AddChild']);
    $this->oItem->addChild($oChild);
    $this->oItem->save();
    $this->oController->templateData('success', "Successfully added child ticket.");
  }

  /**
   * Process the child removal then display the result
   */
  protected function prepareTemplateRelationshipsRemovechild()
  {
    $oChild = $this->oController->itemFromId('ticket', $this->oApi->subId);
    $this->oItem->removeChild($oChild);
    $this->oItem->save();
    $this->oController->templateData('success', "Successfully removed child ticket.");
  }

  /**
   * Process the watcher addition then display the result
   */
  protected function prepareTemplateWatchersAdd()
  {
    $this->oItem->addWatcher($this->oController->user()->id);
    $this->sCurrentAction = 'view';
  }

  /**
   * Process the watcher removal then display the result
   */
  protected function prepareTemplateWatchersRemove()
  {
    $this->oItem->removeWatcher($this->oController->user()->id);
    $this->sCurrentAction = 'view';
  }

  /**
   * Return the appropriate data for the current edit
   *
   * @return array
   */
  protected function editGetData()
  {
    $hPost = $this->originalEditGetData();
    $hPost['UserID'] = $this->oController->user()->id;
    return $hPost;
  }

  /**
   * Return the subject of this module's current ticket, if there is one
   *
   * @return string
   */
  public function getCurrentItemTitle()
  {
    return $this->oItem->subject;
  }

  /**
   * Generate and return the value of the specified column
   *
   * @param \Limbonia\Item $oItem
   * @param string $sColumn
   * @return mixed
   */
  public function getColumnValue(\Limbonia\Item $oItem, $sColumn)
  {
    if ($sColumn == 'ReleaseID')
    {
      return $oItem->releaseId == 0 ? 'None' : '<a class="item" href="' . $this->oController->generateUri('software', $oItem->softwareId, 'roadmap', '#' . $oItem->release->version) . '">' . $oItem->release->version . '</a>';
    }

    if ($sColumn == 'CreatorID')
    {
      return $oItem->creatorId == 0 ? 'None' : '<a class="item" href="' . $this->oController->generateUri('user', $oItem->creatorId) . '">' . $oItem->creator->name . '</a>';
    }

    if ($sColumn == 'OwnerID')
    {
      return $oItem->ownerId == 0 ? 'None' : '<a class="item" href="' . $this->oController->generateUri('user', $oItem->ownerId) . '">' . $oItem->owner->name . '</a>';
    }

    if (in_array($sColumn, ['Type', 'Status', 'Priority', 'Severity', 'Projection', 'DevStatus', 'QualityStatus']))
    {
      return ucfirst(parent::getColumnValue($oItem, $sColumn));
    }

    if (in_array($sColumn, ['LastUpdate', 'CompletionTime']))
    {
      $sValue = parent::getColumnValue($oItem, $sColumn);
      return $sValue == '0000-00-00 00:00:00' || empty($sValue) ? '' : $sValue;
    }

    if (in_array($sColumn, ['DueDate']))
    {
      $sValue = parent::getColumnValue($oItem, $sColumn);
      return $sValue == '0000-00-00' || empty($sValue) ? '' : $sValue;
    }

    return parent::getColumnValue($oItem, $sColumn);
  }

  /**
   * Generate and return the HTML for the specified form field based on the specified information
   *
   * @param string $sName
   * @param string $sValue
   * @param array $hData
   * @param boolean $bInTable - Should the returned HTML use a table to contain the data
   * @return string
   */
  public function getFormField($sName, $sValue = null, $hData = [], $bInTable = false)
  {
    if ($sName == 'CategoryID')
    {
      $oList = \Limbonia\Item::search('TicketCategory');
      $oSelect = $this->oController->widgetFactory('Select', "$this->sType[$sName]");
      $sEmptyItemLabel = $this->isSearch() ? 'None' : 'Select Category';
      $oSelect->addOption($sEmptyItemLabel, '');

      foreach ($oList as $oTempItem)
      {
        $oSelect->addOption($oTempItem->name, $oTempItem->id);
      }

      if (!empty($sValue))
      {
        $oSelect->setSelected($sValue);
      }

      return "        <div class=\"field\"><span class=\"label\">Category</span><span class=\"data\">" . $oSelect . "</span></div>";
    }

    if (in_array($sName, ['OwnerID', 'CreatorID']))
    {
      $sType = strtolower(preg_replace('/id$/i', '', $sName));
      $oUsers = \Limbonia\Item::search('User', ['Visible' => true, 'Active' => true]);
      $oSelect = $this->oController->widgetFactory('Select', "$this->sType[$sName]");
      $sEmptyItemLabel = $this->isSearch() ? 'None' : "Select an $sType";
      $oSelect->addOption($sEmptyItemLabel, '');

      foreach ($oUsers as $oUser)
      {
        $oSelect->addOption($oUser->name, $oUser->id);
      }

      $oSelect->setSelected($sValue);
      return "        <div class=\"field\"><span class=\"label\">Owner</span><span class=\"data\">" . $oSelect . "</span></div>";
    }

    if ($sName == 'ParentID')
    {
      return null;
    }

    if ($sName == 'UpdateText')
    {
      $oText = $this->oController->widgetFactory('Editor', "$this->sType[$sName]");
      $oText->setToolBar('Basic');
      $oText->setText($sValue);
      return "        <div class=\"field\"><span class=\"label\">Update</span><span class=\"data\">" . $oText . "</span></div>";
    }

    if ($sName == 'UpdateType')
    {
      if (in_array($this->oItem->Type, ['internal', 'system']))
      {
        return "        <div class=\"field\"><span class=\"label\">Update Type</span><span class=\"data\">Private<input type=\"hidden\" name=\"UpdateType\" value=\"private\" /></span></div>";
      }

      $oSelect = $this->oController->widgetFactory('Select', "$this->sType[$sName]");
      $oSelect->addOption('Public', 'public');
      $oSelect->addOption('Private', 'private');
      $oSelect->setSelected('public');
      return "        <div class=\"field\"><span class=\"label\">Update Type</span><span class=\"data\">" . $oSelect . "</span></div>";
    }

    if ($sName == 'TimeWorked')
    {
      return "        <div class=\"field\"><span class=\"label\">Time Worked</span><span class=\"data\"><input type=\"text\" name=\"$this->sType[$sName]\" id=\"$this->sType[$sName]\" value=\"$sValue\"></span></div>";
    }

    static $bSoftwareDone = false;

    if ($sName == 'SoftwareID' || $sName == 'ReleaseID' || $sName == 'ElementID')
    {
      if ($bSoftwareDone)
      {
        if ($sName == 'SoftwareID' && !empty($sValue))
        {
          return "<script type=\"text/javascript\">setSoftware('$sValue');</script>\n";
        }

        if ($sName == 'ReleaseID' && !empty($sValue))
        {
          return "<script type=\"text/javascript\">setRelease('$sValue');</script>\n";
        }

        if ($sName == 'ElementID' && !empty($sValue))
        {
          return "<script type=\"text/javascript\">setElement('$sValue');</script>\n";
        }

        return null;
      }

      $oSoftwareWidget = $this->oController->widgetFactory('Software', "$this->sType[SoftwareID]");
      $sSoftwareID = $oSoftwareWidget->getID();

      $oReleaseWidget = $this->oController->widgetFactory('Select', "$this->sType[ReleaseID]");
      $sReleaseID = $oReleaseWidget->getID();

      $oElementWidget = $this->oController->widgetFactory('Select', "$this->sType[ElementID]");
      $sElementID = $oElementWidget->getID();

      $sGetReleases = $oSoftwareWidget->addAjaxFunction('getReleasesBySoftware', TRUE);
      $sGetElements = $oSoftwareWidget->addAjaxFunction('getElementsBySoftware', TRUE);

      $sSoftwareScript  = "var softwareSelect = document.getElementById('$sSoftwareID');\n";
      $sSoftwareScript .= "var softwareID = '';\n";
      $sSoftwareScript .= "var releaseID = '';\n";
      $sSoftwareScript .= "var elementID = '';\n";
      $sSoftwareScript .= "function setSoftware(iSoftware)\n";
      $sSoftwareScript .= "{\n";
      $sSoftwareScript .= "  softwareID = iSoftware;\n";
      $sSoftwareScript .= "  softwareSelect.value = iSoftware;\n";
      $sSoftwareScript .= '  ' . $sGetReleases . "(iSoftware, '$sReleaseID', releaseID);\n";
      $sSoftwareScript .= '  ' . $sGetElements . "(iSoftware, '$sElementID', elementID);\n";
      $sSoftwareScript .= "}\n";

      if ($sName == 'SoftwareID')
      {
        $sSoftwareScript .= "setSoftware('" . $sValue . "');\n";
      }

      $oSoftwareWidget->writeJavascript($sSoftwareScript);

      $sReleaseScript = "var releaseSelect = document.getElementById('$sReleaseID');\n";
      $sReleaseScript .= "function setRelease(iRelease)\n";
      $sReleaseScript .= "{\n";
      $sReleaseScript .= "  releaseID = iRelease;\n";
      $sReleaseScript .= "  if (releaseSelect.options.length > 1)\n";
      $sReleaseScript .= "  {\n";
      $sReleaseScript .= "    for (i = 0; i < releaseSelect.options.length; i++)\n";
      $sReleaseScript .= "    {\n";
      $sReleaseScript .= "      if (releaseSelect.options[i].value == iRelease)\n";
      $sReleaseScript .= "      {\n";
      $sReleaseScript .= "        releaseSelect.options[i].selected = true;\n";
      $sReleaseScript .= "        break;\n";
      $sReleaseScript .= "      }\n";
      $sReleaseScript .= "    }\n";
      $sReleaseScript .= "  }\n";
      $sReleaseScript .= "  else\n";
      $sReleaseScript .= "  {\n";
      $sReleaseScript .= '    ' . $sGetReleases . "(softwareID, '$sReleaseID', iRelease);\n";
      $sReleaseScript .= "  }\n";
      $sReleaseScript .= "  releaseSelect.options[1] = new Option(iRelease, iRelease, true);\n";
      $sReleaseScript .= "}\n";

      if ($sName == 'releaseID')
      {
        $sReleaseScript .= "setRelease('" . $sValue . "');\n";
      }

      $oReleaseWidget->writeJavascript($sReleaseScript);

      $sElementScript = "var elementSelect = document.getElementById('$sElementID');\n";
      $sElementScript .= "function setElement(iElement)\n";
      $sElementScript .= "{\n";
      $sElementScript .= "  elementID = iElement;\n";
      $sElementScript .= "  if (elementSelect.options.length > 1)\n";
      $sElementScript .= "  {\n";
      $sElementScript .= "    for (i = 0; i < elementSelect.options.length; i++)\n";
      $sElementScript .= "    {\n";
      $sElementScript .= "      if (elementSelect.options[i].value == iElement)\n";
      $sElementScript .= "      {\n";
      $sElementScript .= "        elementSelect.options[i].selected = true;\n";
      $sElementScript .= "        break;\n";
      $sElementScript .= "      }\n";
      $sElementScript .= "    }\n";
      $sElementScript .= "  }\n";
      $sElementScript .= "  else\n";
      $sElementScript .= "  {\n";
      $sElementScript .= '    ' . $sGetElements . "(softwareID, '$sElementID', elementID);\n";
      $sElementScript .= "  }\n";
      $sElementScript .= "  elementSelect.options[1] = new Option(iElement, iElement, true);\n";
      $sElementScript .= "}\n";

      if ($sName == 'ElementID')
      {
        $sElementScript .= "setElement('" . $sValue . "');\n";
      }

      $oElementWidget->writeJavascript($sElementScript);

      $oSoftwareWidget->addEvent('change', $sGetReleases . "(this.options[this.selectedIndex].value, '$sReleaseID', releaseID);" . $sGetElements . "(this.options[this.selectedIndex].value, '$sElementID', elementID);");
      $sFormField = "        <div class=\"field\"><span class=\"label\">Software</span><span class=\"data\">" . $oSoftwareWidget . "</span></div>";

      $oReleaseWidget->addOption('Select a version', '0');
      $sFormField .= "        <div class=\"field\"><span class=\"label\">Version</span><span class=\"data\">" . $oReleaseWidget . "</span></div>";

      $oElementWidget->addOption('Select an element', '0');
      $sFormField .= "        <div class=\"field\"><span class=\"label\">Element</span><span class=\"data\">" . $oElementWidget . "</span></div>";

      $bSoftwareDone = TRUE;
      return $sFormField;
    }

    if ($sName == 'Watchers')
    {
      $aWatcherList = $this->oItem->getWatcherList();
      $aWatcher = [];
      $bCurrentlyWatching = false;

      foreach ($aWatcherList as $oWatcher)
      {
        if ($oWatcher->id == $this->oController->user()->id)
        {
          $bCurrentlyWatching = true;
        }

        $aWatcher[] = "<a class=\"item\" href=\"" . $this->oController->generateUri('user', $oWatcher->id) . "\">$oWatcher->name</a>";
      }

      $sWatcherList = count($aWatcher) == 0 ? 'None' : implode(', ', $aWatcher);
      $sButtonText = $bCurrentlyWatching ? 'Stop watching this ticket' : 'Watch this ticket';
      $sSubAction = $bCurrentlyWatching ? 'remove' : 'add';

      return "        <div class=\"field\"><span class=\"label\">Watchers</span><span class=\"data\">$sWatcherList<br /><form method=\"post\" action=\"" . $this->generateUri($this->oItem->id, 'watchers', $sSubAction) . "\"><button type=\"submit\">$sButtonText</button></form></span></div>";
    }

    return parent::getFormField($sName, $sValue, $hData, $bInTable);
  }

  /**
   * Generate and return the column title from the specified column name
   *
   * @param string $sColumn
   * @return string
   */
  public function getColumnTitle($sColumn)
  {
    if (in_array($sColumn, ['CreatorID', 'CategoryID', 'ParentID', 'KeyID', 'OwnerID']))
    {
      return preg_replace('/ID$/', '', $sColumn);
    }

    return parent::getColumnTitle($sColumn);
  }
}