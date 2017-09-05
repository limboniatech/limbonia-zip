<?php
namespace Omniverse\Module;

/**
 * Omniverse Ticket Module class
 *
 * Admin module for handling tickets
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Ticket extends \Omniverse\Module
{
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
   * List of sub-menu options
   *
   * @var array
   */
  protected $aSubMenuItems = ['View', 'Edit', 'Attachments', 'Relationships'];

  /**
   * List of methods that are allowed to run
   *
   * @var array
   */
  protected $aAllowedMethods = ['Search', 'Create', 'EditDialog', 'EditColumn', 'Edit', 'List', 'View', 'Attachments', 'Relationships', 'Watchers'];

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
    $hData = parent::processCreateGetData();
    $hData['CreatorID'] = $this->getController()->user()->id;
    return $hData;
  }

  /**
   * Prepare the template for display based on the current action and current method
   */
  public function prepareTemplate()
  {
    if ($this->sCurrentAction == 'Process')
    {
      if ($this->sCurrentMethod == 'Attachments')
      {
        if (isset($_FILES['Attachment']))
        {
          try
          {
            $this->oItem->addAttachment($_FILES['Attachment']['tmp_name'], $_FILES['Attachment']['name']);
            $this->getController()->templateData('success', "Successfully added attachment.");
          }
          catch (\Exception $e)
          {
            $this->getController()->templateData('failure', "Failed to add attachment: " . $e->getMessage());
          }
        }
        elseif (isset($_GET['Delete']))
        {
          try
          {
            $this->oItem->removeAttachment($_GET['Delete']);
            $this->getController()->templateData('success', "Successfully removed attachment.");
          }
          catch (\Exception $e)
          {
            $this->getController()->templateData('failure', "Failed to remove attachment: " . $e->getMessage());
          }
        }

        $this->sCurrentAction = 'Display';
      }
      elseif ($this->sCurrentMethod == 'Relationships')
      {
        if (isset($_POST['SetParent']))
        {
          try
          {
            $oParent = $this->getController()->itemFromId('ticket', $_POST['SetParent']);
            $this->oItem->parentId = $oParent->id;
            $this->oItem->save();
            $this->getController()->templateData('success', "Successfully set parent ticket.");
          }
          catch (\Exception $e)
          {
            $this->getController()->templateData('failure', "Failed to set parent ticket: " . $e->getMessage());
          }
        }
        elseif (isset($_POST['AddChild']))
        {
          try
          {
            $oChild = $this->getController()->itemFromId('ticket', $_POST['AddChild']);
            $this->oItem->addChild($oChild);
            $this->oItem->save();
            $this->getController()->templateData('success', "Successfully add child ticket.");
          }
          catch (\Exception $e)
          {
            $this->getController()->templateData('failure', "Failed to add child ticket: " . $e->getMessage());
          }
        }
        elseif (isset($_GET['RemoveParent']))
        {
          try
          {
            $oParent = $this->getController()->itemFromId('ticket', $_GET['RemoveParent']);

            if ($this->oItem->parentId != $oParent->id)
            {
              throw new Exception("The parent id to be removed does not match the actual parent id.");
            }

            $this->oItem->parentId = 0;
            $this->oItem->save();
            $this->getController()->templateData('success', "Successfully removed parent ticket.");
          }
          catch (\Exception $e)
          {
            $this->getController()->templateData('failure', "Failed to remove parent ticket: " . $e->getMessage());
          }
        }
        elseif (isset($_GET['RemoveChild']))
        {
          try
          {
            $oChild = $this->getController()->itemFromId('ticket', $_GET['RemoveChild']);
            $this->oItem->removeChild($oChild);
            $this->oItem->save();
            $this->getController()->templateData('success', "Successfully removed child ticket.");
          }
          catch (\Exception $e)
          {
            $this->getController()->templateData('failure', "Failed to remove child ticket: " . $e->getMessage());
          }
        }

        $this->sCurrentAction = 'Display';
      }
      elseif ($this->sCurrentMethod == 'Watchers')
      {
        $hPost = $this->editGetData();
        $iUser = $this->getController()->user()->id;

        if (isset($hPost['submit']) && $hPost['submit'] == 'Watch this ticket')
        {
          $this->oItem->addWatcher($iUser);
        }
        elseif (isset($hPost['submit']) && $hPost['submit'] == 'Stop watching this ticket')
        {
          $this->oItem->removeWatcher($iUser);
        }

        $this->sCurrentAction = 'Display';
        $this->sCurrentMethod = 'View';
      }
    }

    return parent::prepareTemplate();
  }

  /**
   * Return the appropriate data for the current edit
   *
   * @return array
   */
  protected function editGetData()
  {
    $hPost = parent::editGetData();
    $hPost['UserID'] = $this->getController()->user()->id;
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
   * @param \Omniverse\Item $oItem
   * @param string $sColumn
   * @return mixed
   */
  public function getColumnValue(\Omniverse\Item $oItem, $sColumn)
  {
    if ($sColumn == 'ReleaseID')
    {
      return $oItem->releaseId == 0 ? 'None' : '<a target="_blank" href="?Admin=Display&Module=Software&Display=RoadMap&SoftwareID=' . $oItem->softwareId . '#' . $oItem->release->version . '">' . $oItem->release->version . '</a>';
    }

    if ($sColumn == 'CreatorID')
    {
      return $oItem->creatorId == 0 ? 'None' : '<a target="_blank" href="?Admin=Process&Module=User&Process=View&UserID=' . $oItem->creatorId . '">' . $oItem->creator->name . '</a>';
    }

    if ($sColumn == 'OwnerID')
    {
      return $oItem->ownerId == 0 ? 'None' : '<a target="_blank" href="?Admin=Process&Module=User&Process=View&UserID=' . $oItem->ownerId . '">' . $oItem->owner->name . '</a>';
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
      $oList = \Omniverse\Item::search('TicketCategory');
      $oSelect = $this->getController()->widgetFactory('Select', "$this->sModuleName[$sName]");
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

      if ($bInTable)
      {
        return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Category:</th><td class=\"OmnisysFieldValue\">" . $oSelect . "</td></tr>";
      }

      return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Category:</span><span class=\"OmnisysFieldValue\">" . $oSelect . "</span></div>";
    }

    if (in_array($sName, ['OwnerID', 'CreatorID']))
    {
      $sType = strtolower(preg_replace('/id$/i', '', $sName));
      $oUsers = \Omniverse\Item::search('User', ['Visible' => true, 'Active' => true]);
      $oSelect = $this->getController()->widgetFactory('Select', "$this->sModuleName[$sName]");
      $sEmptyItemLabel = $this->isSearch() ? 'None' : "Select an $sType";
      $oSelect->addOption($sEmptyItemLabel, '');

      foreach ($oUsers as $oUser)
      {
        $oSelect->addOption($oUser->name, $oUser->id);
      }

      $oSelect->setSelected($sValue);

      if ($bInTable)
      {
        return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Owner:</th><td class=\"OmnisysFieldValue\">" . $oSelect . "</td></tr>";
      }

      return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Owner:</span><span class=\"OmnisysFieldValue\">" . $oSelect . "</span></div>";
    }

    if ($sName == 'ParentID')
    {
      return null;
    }

    if ($sName == 'UpdateText')
    {
      $oText = $this->getController()->widgetFactory('Editor', "$this->sModuleName[$sName]");
      $oText->setToolBar('Basic');
      $oText->setText($sValue);

      if ($bInTable)
      {
        return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Update:</th><td class=\"OmnisysFieldValue\">" . $oText . "</td></tr>";
      }

      return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Update:</span><span class=\"OmnisysFieldValue\">" . $oText . "</span></div>";
    }

    if ($sName == 'UpdateType')
    {
      if (in_array($this->oItem->Type, ['internal', 'system']))
      {
        if ($bInTable)
        {
          return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Update Type:</th><td class=\"OmnisysFieldValue\">Private<input type=\"hidden\" name=\"UpdateType\" value=\"private\" /></td></tr>";
        }

        return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Update Type:</span><span class=\"OmnisysFieldValue\">Private<input type=\"hidden\" name=\"UpdateType\" value=\"private\" /></span></div>";
      }

      $oSelect = $this->getController()->widgetFactory('Select', "$this->sModuleName[$sName]");
      $oSelect->addOption('Public', 'public');
      $oSelect->addOption('Private', 'private');
      $oSelect->setSelected('public');

      if ($bInTable)
      {
        return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Update Type:</th><td class=\"OmnisysFieldValue\">" . $oSelect . "</td></tr>";
      }

      return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Update Type:</span><span class=\"OmnisysFieldValue\">" . $oSelect . "</span></div>";
    }

    if ($sName == 'TimeWorked')
    {
      if ($bInTable)
      {
        return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Time Worked:</th><td class=\"OmnisysFieldValue\"><input type=\"text\" name=\"$this->sModuleName[$sName]\" id=\"$this->sModuleName[$sName]\" value=\"$sValue\"></td></tr>";
      }

      return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Time Worked:</span><span class=\"OmnisysFieldValue\"><input type=\"text\" name=\"$this->sModuleName[$sName]\" id=\"$this->sModuleName[$sName]\" value=\"$sValue\"></span></div>";
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

      $oSoftwareWidget = $this->getController()->widgetFactory('Software', "$this->sModuleName[SoftwareID]");
      $sSoftwareID = $oSoftwareWidget->getID();

      $oReleaseWidget = $this->getController()->widgetFactory('Select', "$this->sModuleName[ReleaseID]");
      $sReleaseID = $oReleaseWidget->getID();

      $oElementWidget = $this->getController()->widgetFactory('Select', "$this->sModuleName[ElementID]");
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

      if ($bInTable)
      {
        $sFormField = "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Software:</th><td class=\"OmnisysFieldValue\">" . $oSoftwareWidget . "</td></tr>";
      }
      else
      {
        $sFormField = "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Software:</span><span class=\"OmnisysFieldValue\">" . $oSoftwareWidget . "</span></div>";
      }

      $oReleaseWidget->addOption('Select a version', '0');

      if ($bInTable)
      {
        $sFormField .= "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Version:</th><td class=\"OmnisysFieldValue\">" . $oReleaseWidget . "</td></tr>";
      }
      else
      {
        $sFormField .= "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Version:</span><span class=\"OmnisysFieldValue\">" . $oReleaseWidget . "</span></div>";
      }

      $oElementWidget->addOption('Select an element', '0');

      if ($bInTable)
      {
        $sFormField .= "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Element:</th><td class=\"OmnisysFieldValue\">" . $oElementWidget . "</td></tr>";
      }
      else
      {
        $sFormField .= "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Element:</span><span class=\"OmnisysFieldValue\">" . $oElementWidget . "</span></div>";
      }

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
        if ($oWatcher->id == $this->getController()->user()->id)
        {
          $bCurrentlyWatching = true;
        }

        $aWatcher[] = "<a href=\"?Admin=Process&Module=User&Process=View&UserID=$oWatcher->id\">$oWatcher->name</a>";
      }

      $sWatcherList = count($aWatcher) == 0 ? 'None' : implode(', ', $aWatcher);
      $sButtonValue = $bCurrentlyWatching ? 'Stop watching this ticket' : 'Watch this ticket';

      if ($bInTable)
      {
        return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Watchers:</th><td class=\"OmnisysFieldValue\">$sWatcherList<br /><form method=\"post\" action=\"?Admin=Process&Module=Ticket&Process=Watchers&TicketID={$this->oItem->id}\"><input type=\"submit\" name=\"$this->sModuleName[submit]\" value=\"$sButtonValue\"></form></td></tr>";
      }

      return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Watchers:</span><span class=\"OmnisysFieldValue\">$sWatcherList<br /><form method=\"post\" action=\"?Admin=Process&Module=Ticket&Process=Watchers&TicketID={$this->oItem->id}\"><input type=\"submit\" name=\"$this->sModuleName[submit]\" value=\"$sButtonValue\"></form></span></div>";
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