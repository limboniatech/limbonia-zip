<?php
namespace Limbonia\Module;

/**
 * Limbonia System Module class
 *
 * Admin module for handling all the basic system configuration and management
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class System extends \Limbonia\Module
{
  /**
   * The admin group that this module belongs to
   *
   * @var string
   */
  protected static $sGroup = 'Hidden';

  /**
   * List of actions that are allowed to run
   *
   * @var array
   */
  protected $aAllowedActions = ['generateitemcode', 'managemodules', 'config', 'description'];
  /**
   * The default method for this module
   *
   * @var string
   */
  protected $sDefaultAction = 'description';

  /**
   * The current method being used by this module
   *
   * @var string
   */
  protected $sCurrentAction = 'description';

  /**
   * List of components that this module contains along with their descriptions
   *
   * @var array
   */
  protected static $hComponent =
  [
    'description' => 'Explain what the system module is used for...',
    'managemodules' => 'This is the ability to activate and deactivate modules.',
    'config' => 'The ability to configure the system.'
  ];

  /**
   * List of menu items that this module should display
   *
   * @var array
   */
  protected $hMenuItems =
  [
    'description' => 'Description',
    'managemodules' => 'Manage Modules',
    'config' => 'Config'
  ];

  /**
   * List of sub-menu options
   *
   * @var array
   */
  protected $hSubMenuItems =
  [
  ];

  /**
   * Deactivate this module then return a list of types that were deactivated
   *
   * @param array $hActiveModule - the active module list
   * @return array
   * @throws Exception on failure
   */
  public function deactivate(array $hActiveModule)
  {
    throw new \Limbonia\Exception('The System module can not be deactivated');
  }

  /**
   * Perform the base "GET" code then return null on success
   *
   * @return null
   * @throws \Exception
   */
  protected function processApiHead()
  {
    return null;
  }

  /**
   * Perform and return the default "GET" code
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGet()
  {
    switch ($this->oRouter->action)
    {
      case 'item-module-base':
        break;

      default:
        throw new \Limbonia\Exception\Web('No action specified for GET System');
    }
  }

  public function generateItemCode($sTable)
  {
    if (empty($sTable))
    {
      throw new \Limbonia\Exception("Table not specified");
    }

    $oDatabase = $this->oController->getDB();

    if (!$oDatabase->hasTable($sTable))
    {
      throw new \Limbonia\Exception("Table not found: $sTable");
    }


    $hColumns = $oDatabase->getColumns($sTable);
    $sColumns = '';
    $sDefaultData = '';

    foreach ($hColumns as $sName => $hColumn)
    {
      $sDefault = 'null';

      if (isset($hColumn['Default']))
      {
        if (is_null($hColumn['Default']))
        {
          $sDefault = 'null';
        }
        elseif (\Limbonia\Database::columnIsString($hColumn))
        {
          $sDefault = "'" . addslashes($hColumn['Default']) . "'";
        }
        elseif (\Limbonia\Database::columnIsInteger($hColumn))
        {
          $sDefault = (integer)$hColumn['Default'];
        }
        elseif (\Limbonia\Database::columnIsFloat($hColumn))
        {
          $sDefault = (float)$hColumn['Default'];
        }
      }

      $sDefaultData .= "    '$sName' => $sDefault,\n";
      $sColumns .= "\n    '$sName' =>\n    [\n";

      foreach ($hColumn as $sSubName => $sValue)
      {
        if ($sSubName == 'Default')
        {
          if (is_null($sValue))
          {
            $sValue = 'null';
          }
          elseif (\Limbonia\Database::columnIsString($hColumn))
          {
            $sValue = "'$sValue'";
          }
          elseif (\Limbonia\Database::columnIsInteger($hColumn))
          {
            $sValue = (integer)$sValue;
          }
          elseif (\Limbonia\Database::columnIsFloat($hColumn))
          {
            $sValue = (float)$sValue;
          }
        }
        else
        {
          $sValue = "'$sValue'";
        }

        $sColumns .= "      '$sSubName' => $sValue,\n";
      }

      $sColumns = rtrim(rtrim($sColumns), ',') . "
    ],";
    }

    $sColumns = rtrim(rtrim($sColumns), ',');
    $hColumnAlias = \Limbonia\Database::aliasColumns($hColumns);
    $sColumnAlias = '';

    foreach ($hColumnAlias as $sAlias => $sColumn)
    {
      $sColumnAlias .= "    '$sAlias' => '$sColumn',\n";
    }

    $sColumnAlias = rtrim(rtrim($sColumnAlias), ',');
    $sDefaultData = rtrim(rtrim($sDefaultData), ',');
    $sIdColumn = isset($hColumnAlias['id']) ? "'{$hColumnAlias['id']}'" : 'false';

    return "<?php
namespace Limbonia\Item;

/**
 * Limbonia $sTable Item Class
 *
 * Item based wrapper around the User table
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class $sTable extends \Limbonia\Item
{
  /**
   * The database schema for creating this item's table in the database
   *
   * @var string
   */
  protected static \$sSchema = \"" . $oDatabase->getSchema($sTable) . "\";

  /**
   * The columns for this item's tables
   *
   * @var array
   */
  protected static \$hColumns =
  [
$sColumns
  ];

  /**
   * The aliases for this item's columns
   *
   * @var array
   */
  protected static \$hColumnAlias =
  [
$sColumnAlias
  ];

  /**
   * The default data used for \"blank\" or \"empty\" items
   *
   * @var array
   */
  protected static \$hDefaultData =
  [
$sDefaultData
  ];

  /**
   * This object's data
   *
   * @var array
   */
  protected \$hData =
  [
$sDefaultData
  ];

  /**
   * List of columns that shouldn't be updated after the data has been created
   *
   * @var array
   */
  protected \$aNoUpdate = [$sIdColumn];

  /**
   * The table that this object is referencing
   *
   * @var string
   */
  protected \$sTable = '$sTable';

  /**
   * The name of the \"ID\" column associated with this object's table
   *
   * @var string
   */
  protected \$sIdColumn = $sIdColumn;
}";
  }

  /**
   * Prepare the generateitemcode template for use
   */
  protected function prepareTemplateGenerateitemcode()
  {
    if ($this->oController->type == 'cli')
    {
      $this->oController->setDescription('Generate a stub php file for an Item class based on an existing database table');
      $this->oController->addOption
      ([
        'short' => 't',
        'long' => 'table',
        'desc' => 'The table to base the Item code on',
        'value' => \Limbonia\Controller\Cli::OPTION_VALUE_REQUIRE
      ]);
    }
  }

  protected function prepareTemplatePostManagemodules()
  {
    $oPost = \Limbonia\Input::singleton('post');
    $aCurrentActiveModule = empty($oPost->activemodule) ? [] : array_keys($oPost->activemodule);
    $aPriorActiveModule = array_keys($this->oController->activeModules());
    $aActivate = array_diff($aCurrentActiveModule, $aPriorActiveModule);
    $aDeactivate = array_diff($aPriorActiveModule, $aCurrentActiveModule);
    $aError = [];

    foreach ($aActivate as $sModule)
    {
      try
      {
        $this->oController->activateModule($sModule);
      }
      catch (\Limbonia\Exception $e)
      {
        $aError[] = $e->getMessage();
      }
    }

    foreach ($aDeactivate as $sModule)
    {
      try
      {
        $this->oController->deactivateModule($sModule);
      }
      catch (\Limbonia\Exception $e)
      {
        $aError[] = $e->getMessage();
      }
    }

    $this->oController->templateData('error', $aError);
  }
}