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
    \Limbonia\Traits\ItemModule::getColumns as originalGetColumns;
  }

  /**
   * List of fields used by module settings
   *
   * @var array
   */
  protected static $hSettingsFields =
  [
    'server' => ['Type' => 'char'],
    'secure' => ['Type' => "enum('Off','SSL','TLS')"],
    'port' => ['Type' => 'int'],
    'user' => ['Type' => 'char'],
    'password' => ['Type' => 'password'],
    'folder' => ['Type' => 'char'],
    'timeout' => ['Type' => 'int'],
  ];

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
      'CreatorID',
      'Type'
    ],
    'create' =>
    [
      'CreatorID',
      'CreateTime',
      'CompletionTime',
      'LastUpdate'
    ],
    'search' =>
    [
      'ParentID',
      'CompletionTime',
      'CreateTime',
      'Severity',
      'Projection',
      'DevStatus',
      'QualityStatus',
      'Description',
      'StepsToReproduce'
    ],
    'view' =>
    [
      'CreateTime',
      'ParentID'
    ],
    'usertickets' =>
    [
      'OwnerID',
      'ParentID',
      'CompletionTime',
      'CreateTime',
      'Severity',
      'Projection',
      'DevStatus',
      'QualityStatus',
      'Description',
      'StepsToReproduce'
    ],
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
  protected $aAllowedActions = ['search', 'create', 'editdialog', 'editcolumn', 'edit', 'list', 'view', 'attachments', 'relationships', 'watchers', 'processemail'];

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
   * List of column names in the order required
   *
   * @return array
   */
  protected function columnOrder()
  {
    return
    [
      'CreatorID',
      'Status',
      'Priority',
      'OwnerID',
      'Subject',
      'CategoryID',
      'ProjectID',
      'ReleaseID',
      'Type',
      'LastUpdate',
      'StartDate',
      'DueDate',
      'CompletionTime',
      'Severity',
      'Projection',
      'DevStatus',
      'QualityStatus',
      'StepsToReproduce',
      'Description'
    ];
   }

  /**
   * Return the default settings
   *
   * @return array
   */
  protected function defaultSettings()
  {
    return
    [
      'server' => '',
      'user' => '',
      'password' => '',
      'secure' => 'SSL',
      'port' => \Limbonia\Imap::SECURE_PORT,
      'folder' => \Limbonia\Imap::DEFAULT_FOLDER,
      'timeout' => \Limbonia\Imap::DEFAULT_TIMEOUT,
    ];
  }

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
   * Run the code needed to display the default "create" template
   */
  protected function prepareTemplateCreate()
  {
    $hFields = array_merge(['Header' => ['Type' => 'Text']], $this->getColumns('create'));
    $this->oController->templateData('fields', $hFields);
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

    $hFields = $this->getColumns('edit');
    $hFields['UpdateText'] = ['Type' => 'Text'];
    $hFields['UpdateType'] = ['Type' => 'Text'];
    $hFields['TimeWorked'] = ['Type' => 'Text'];
    $this->oController->templateData('fields', $hFields);
  }

  /**
   * Run the code needed to display the default "view" template
   */
  protected function prepareTemplateView()
  {
    $hFields = $this->getColumns('View');
    $hFields['Content'] =
    [
      'Type' => 'text'
    ];
    $this->oController->templateData('fields', $hFields);
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
   * Prepare the Processemail template for use
   */
  protected function prepareTemplateProcessemail()
  {
    if ($this->oController->type == 'cli')
    {
      $this->oController->setDescription('Process incoming emails from the configured account and create or update tickets based on those emails');
      $this->oController->addOption
      ([
        'short' => 'c',
        'long' => 'display-config',
        'desc' => 'Display the current email config',
        'value' => \Limbonia\Controller\Cli::OPTION_VALUE_NONE
      ]);
      $this->oController->addOption
      ([
        'short' => 't',
        'long' => 'test',
        'desc' => 'Put this utility into test mode so that it outputs to the screen instead rather than actually creating and updating tickets',
        'value' => \Limbonia\Controller\Cli::OPTION_VALUE_NONE
      ]);
    }

    $this->oController->templateData('settings', $this->hSettings);
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
   * Generate and return a list of columns based on the specified type
   *
   * @param string $sType (optional)
   * @return array
   */
  public function getColumns($sType = null)
  {
    $sLowerType = strtolower($sType);

    if (in_array($sLowerType, ['view', 'edit']))
    {
      switch ($this->oItem->type)
      {
        case 'internal':
        case 'contact':
        case 'system':
          $this->aIgnore[$sLowerType] = array_merge($this->aIgnore[$sLowerType],
          [
            'Severity',
            'Projection',
            'DevStatus',
            'QualityStatus',
            'StepsToReproduce'
          ]);
          break;
      }
    }

    return $this->originalGetColumns($sType);
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
      return $oItem->releaseId == 0 ? 'None' : '<a class="item" href="' . $this->oController->generateUri('project', $oItem->projectId, 'roadmap', '#' . $oItem->release->version) . '">' . $oItem->release->version . '</a>';
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
   * @return string
   */
  public function getFormField($sName, $sValue = null, $hData = [])
  {
    if ($sName == 'Header')
    {
      return "\n<style>
#TicketSeverityField,
#TicketProjectionField,
#TicketDevStatusField,
#TicketQualityStatusField,
#TicketStepsToReproduceField
{
  display: none;
}
</style>\n
<script type=\"text/javascript\">
/**
 * Toggle the associated data when the Asignment Method is changed
 *
 * @param {String} sOption
 * @returns {Boolean}
 */
function toggleMethod(sOption)
{
  severityDiv = document.getElementById('TicketSeverityField');
  projDiv = document.getElementById('TicketProjectionField');
  devDiv = document.getElementById('TicketDevStatusField');
  qualityDiv = document.getElementById('TicketQualityStatusField');
  stepsDiv = document.getElementById('TicketStepsToReproduceField');

  switch (sOption)
  {
    case 'software':
      severityDiv.style.display = 'block';
      projDiv.style.display = 'block';
      devDiv.style.display = 'block';
      qualityDiv.style.display = 'block';
      stepsDiv.style.display = 'block';
      break;

    case 'internal':
    case 'contact':
    case 'system':
      severityDiv.style.display = 'none';
      projDiv.style.display = 'none';
      devDiv.style.display = 'none';
      qualityDiv.style.display = 'none';
      stepsDiv.style.display = 'none';
      break;
  }
}

$('#TicketType').change(function()
{
  toggleMethod($(this).val());
});

toggleMethod($('#TicketType').val());
</script>\n";
    }

    $sLabel = preg_replace("/([A-Z])/", "$1", $sName);

    if (is_null($sValue) && isset($hData['Default']) && !$this->isSearch())
    {
      $sValue = $hData['Default'];
    }

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

      return static::widgetField($oSelect, 'Category');
    }

    if (in_array($sName, ['OwnerID', 'CreatorID']))
    {
      $sLabel = preg_replace('/id$/i', '', $sName);
      $sType = strtolower($sLabel);
      $oUsers = \Limbonia\Item::search('User', ['Visible' => true, 'Active' => true]);
      $oSelect = $this->oController->widgetFactory('Select', "$this->sType[$sName]");
      $sEmptyItemLabel = $this->isSearch() ? 'None' : "Select an $sType";
      $oSelect->addOption($sEmptyItemLabel, '');

      foreach ($oUsers as $oUser)
      {
        $oSelect->addOption($oUser->name, $oUser->id);
      }

      $oSelect->setSelected($sValue);
      return static::widgetField($oSelect, $sLabel);
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
      return static::widgetField($oText, 'Update');
    }

    if ($sName == 'UpdateType')
    {
      if (in_array($this->oItem->type, ['internal', 'system']))
      {
        return static::field("Private<input type=\"hidden\" name=\"$this->sType[$sName]\" id=\"$this->sType$sName\" value=\"private\" />", 'Update Type', "$this->sType$sName");
      }

      $oSelect = $this->oController->widgetFactory('Select', "$this->sType[$sName]");
      $oSelect->addOption('Public', 'public');
      $oSelect->addOption('Private', 'private');
      $oSelect->setSelected('public');
      return static::widgetField($oSelect, 'Update Type');
    }

    if ($sName == 'TimeWorked')
    {
      return parent::getFormField($sName, $sValue, ['Type' => 'int']);
    }

    static $oProjectWidget = null;
    static $oReleaseWidget = null;

    if ($sName == 'ProjectID')
    {
      if (is_null($oProjectWidget))
      {
        $oProjectWidget = $this->oController->widgetFactory('Project', "$this->sType[ProjectID]");
        $oReleaseWidget = $this->oController->widgetFactory('Select', "$this->sType[ReleaseID]");
      }

      $sProjectID = $oProjectWidget->getId();
      $sReleaseID = $oReleaseWidget->getId();

      $sGetReleases = $oProjectWidget->addAjaxFunction('getReleasesByProject', TRUE);

      $sProjectScript  = "var projectSelect = document.getElementById('$sProjectID');\n";
      $sProjectScript .= "var projectID = '';\n";
      $sProjectScript .= "var releaseID = '';\n";
      $sProjectScript .= "function setProject(iProject)\n";
      $sProjectScript .= "{\n";
      $sProjectScript .= "  projectID = iProject;\n";
      $sProjectScript .= "  projectSelect.value = iProject;\n";
      $sProjectScript .= '  ' . $sGetReleases . "(iProject, '$sReleaseID', releaseID);\n";
      $sProjectScript .= "}\n";
      $sProjectScript .= "setProject('" . $sValue . "');\n";

      $oProjectWidget->writeJavascript($sProjectScript);
      $oProjectWidget->addEvent('change', $sGetReleases . "(this.options[this.selectedIndex].value, '$sReleaseID', releaseID);");
      return static::widgetField($oProjectWidget, 'Project');
    }

    if ($sName == 'ReleaseID')
    {
      if (is_null($oProjectWidget))
      {
        $oProjectWidget = $this->oController->widgetFactory('Project', "$this->sType[ProjectID]");
        $oReleaseWidget = $this->oController->widgetFactory('Select', "$this->sType[ReleaseID]");
      }

      $sReleaseID = $oReleaseWidget->getId();

      $sGetReleases = $oReleaseWidget->addAjaxFunction('getReleasesByProject', TRUE);

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
      $sReleaseScript .= '    ' . $sGetReleases . "(projectID, '$sReleaseID', iRelease);\n";
      $sReleaseScript .= "  }\n";
      $sReleaseScript .= "  releaseSelect.options[1] = new Option(iRelease, iRelease, true);\n";
      $sReleaseScript .= "}\n";
      $sReleaseScript .= "setRelease('" . $sValue . "');\n";

      $oReleaseWidget->writeJavascript($sReleaseScript);
      $oReleaseWidget->addOption('Select a version', '0');
      return static::widgetField($oReleaseWidget, 'Version');
    }

    $sType = strtolower(preg_replace("/( |\().*/", "", $hData['Type']));

    switch ($sType)
    {
      case 'password':
        return static::field("<input type=\"password\" name=\"$this->sType[$sName]\" id=\"$this->sType$sName\" value=\"$sValue\">", $sLabel, "$this->sType$sName");
    }

    return parent::getFormField($sName, $sValue, $hData);
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

    /**
   * Generate and return the HTML for the specified form field based on the specified information
   *
   * @param string $sName
   * @param string $sValue
   * @param array $hData
   * @return string
   */
  public function getField($sName, $sValue = null, $hData = [])
  {
    $sLabel = $this->getColumnTitle($sName);

    if ($sName == 'CreatorID')
    {
      if ($this->oItem->creatorID == 0)
      {
        return '';
      }

      return static::field($this->getColumnValue($this->oItem, $sName) . ' on ' . $this->oItem->createTime, 'Created By');
    }

    if ($sName == 'OwnerID')
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

      return static::field($this->getColumnValue($this->oItem, $sName), $sLabel, $this->sType . $sName) .
      static::field("$sWatcherList<br /><form method=\"post\" action=\"" . $this->generateUri($this->oItem->id, 'watchers', $sSubAction) . "\"><button type=\"submit\">$sButtonText</button></form>", 'Watchers', 'Watchers');
    }

    if (in_array($sName, ['Status', 'Priority', 'CategoryID', 'ProjectID', 'Type', 'StartDate', 'DueDate', 'Severity', 'Projection', 'DevStatus', 'QualityStatus']))
    {
      $sValue = $this->getColumnValue($this->oItem, $sName);

      if (in_array($sName, ['StartDate', 'DueDate']) && empty($sValue))
      {
        return '';
      }

      return static::field($sValue, $sLabel, $this->sType . $sName);
    }

    if ($sName == 'CompletionTime')
    {
      $sValue = $this->getColumnValue($this->oItem, $sName);
      $sField = empty($sValue) ? '' : static::field($sValue, $sLabel, $this->sType . $sName);

      if (!empty($this->oItem->totalTime))
      {
        $sField .= static::field(\Limbonia\Item::outputTimeInterval($this->oItem->totalTime), 'Total Time Worked');
      }

      return $sField;
    }

    if ($sName == 'ReleaseID')
    {
      return static::field($this->getColumnValue($this->oItem, $sName), 'Version', $this->sType . $sName);
    }

    if ($sName == 'Content')
    {
      $sField = '';

      foreach ($this->oItem->contentList as $oContent)
      {
        $sField .= "<div class=\"field ticketContent\">\n";
        $sField .= "  <span class=\"label\">\n";

        if ($oContent->user->id > 0)
        {
          $sField .= "<a class=\"item\" href=\"" . $this->getController()->generateUri('user', $oContent->user->id) . "\">{$oContent->user->name}</a>\n";
        }
        else
        {
          $sField .= "Auto Created";
        }

        $sField .= ' [' . ucwords($oContent->updateType) . ']';
        $sField .= "  <br>\n";
        $sField .= $oContent->updateTime;

        if (!empty($oContent->timeWorked))
        {
          $sField .= '<br>' . \Limbonia\Item::outputTimeInterval($oContent->timeWorked);
        }

        $sField .= "</span>\n";
        $sField .= "<span class=\"$oContent->updateType data\">\n";
        $sField .= preg_replace("/\n/", "<br>\n", $oContent->updateText) . "\n";
        $historyList = $oContent->getHistory();

        if (count($historyList) > 0)
        {
          $sField .= "<div class=\"history\">\n";

          foreach ($historyList as $history)
          {
            if (!empty($history->note))
            {
              $sField .= "<div class=\"note\">$history->note</div>\n";
            }
          }

          $sField .= "</div>\n";
        }

        $sField .= "  </span>\n";
        $sField .= "</div>\n";
      }

      return $sField;
    }

    return parent::getField($sName, $sValue, $hData);
  }
}