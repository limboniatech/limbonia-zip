<?php
namespace Omniverse\Lib;

class Database extends \PDO
{
  protected static $hDateTimeFormat =
  [
    'date' => '%Y-%m-%d',
    'time' => '%T',
    'timestamp' => '%Y-%m-%d %T',
    'datetime' => '%Y-%m-%d %T',
    'year' => '%Y'
  ];
  private static $hDatabaseObjects = [];

  protected $aTableList = [];

  protected $hColumnList = [];

  static public function arrayToDSN(array $hConfig = [])
  {
  	$hDSN =
  	[
  		'dsn' => strtolower($hConfig['Driver']) . ':',
  		'username' => '',
  		'password' => '',
  		'options' => []
  	];
  	unset($hConfig['Driver']);

  	if (isset($hConfig['Host']))
  	{
  	  $hDSN['dsn'] .= 'host=';
  	  $hDSN['dsn'] .= $hConfig['Host'] == 'localhost' ? '127.0.0.1' : $hConfig['Host'];
  	  unset($hConfig['Host']);

  	  if (isset($hConfig['Port']))
  	  {
  	    $hDSN['dsn'] .= ";port={$hConfig['Port']}";
  	    unset($hConfig['Port']);
  	  }
  	}
  	elseif (isset($hConfig['UnixSocket']))
  	{
  	  $hDSN['dsn'] .= "unix_socket={$hConfig['UnixSocket']}";
  	  unset($hConfig['UnixSocket']);
  	}

  	if (isset($hConfig['Database']))
  	{
  	  $hDSN['dsn'] .= ";dbname={$hConfig['Database']}";
  	  unset($hConfig['Database']);
  	}

  	if (isset($hConfig['CharSet']))
  	{
  	  $hDSN['dsn'] .= ";charset={$hConfig['CharSet']}";
  	  unset($hConfig['CharSet']);
  	}

  	if (isset($hConfig['User']))
  	{
  	  $hDSN['username'] = $hConfig['User'];
  	  unset($hConfig['User']);
  	}

  	if (isset($hConfig['Password']))
  	{
  	  $hDSN['password'] = $hConfig['Password'];
  	  unset($hConfig['Password']);
  	}

  	$hDSN['options'] = $hConfig;
  	return $hDSN;
  }

  /**
   * Generate an instance of the Omniverse\Lib Database object based on the specified configuration
   *
   * @param array $hConfig
   * @throws \Omniverse\Lib\Exception\Database
   * @return \Omniverse\Lib\Database
   */
  static public function factory(array $hConfig = [])
  {
  	if (empty($hConfig['Driver']))
  	{
  		throw new Exception\Database('A valid SQL driver name was not found!');
  	}

  	ksort($hConfig);
    $sConfigHash = md5(serialize($hConfig));

    if (isset(self::$hDatabaseObjects[$sConfigHash]))
    {
      return self::$hDatabaseObjects[$sConfigHash];
    }

    $hDSN = self::arrayToDSN($hConfig);
    self::$hDatabaseObjects[$sConfigHash] = new self($hDSN['dsn'], $hDSN['username'], $hDSN['password'], $hDSN['options']);
    return self::$hDatabaseObjects[$sConfigHash];
  }

  public function __construct($sDSN, $sUsername = null, $sPassword = null, $hOptions = null)
  {
    parent::__construct($sDSN, $sUsername, $sPassword, $hOptions);
    $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, ['\Omniverse\Lib\DBResult', [$this]]);
  }

  public function getType()
  {
    return $this->getAttribute(\PDO::ATTR_DRIVER_NAME);
  }

  protected function _createTableSQL($sName, $hColumns, $sDatabase = null)
  {
    if (!is_array($hColumns) || count($hColumns) == 0)
    {
      throw new Exception\Database("Create table columns is invalid!", $this->getType());
    }

    $aColumns = [];
    foreach ($hColumns as $sColumn => $aColumnConfig)
    {
      $aColumns[] = $sColumn . ' ' . implode(' ', $aColumnConfig);
    }

    $sDatabase = empty($sDatabase) ? $sDatabase : "$sDatabase.";

    return "CREATE TABLE IF NOT EXISTS $sDatabase$sName (" . implode(', ', $aColumns) . ")";
  }

  public function createTable($sName, $hColumns, $sDatabase = null)
  {
    $this->query($this->_createTableSQL($sName, $hColumns));
    $this->aTableList[] = $sName;
  }

  protected function _getDatabasesSQL()
  {
    return 'SHOW DATABASES';
  }

  public function getDatabases()
  {
    $oDatabases = $this->query($this->_getDatabasesSQL());
    $aDatabases = [];

    foreach ($oDatabases as $hRow)
    {
      $aDatabases[] = array_shift($hRow);
    }

    return $aDatabases;
  }

  protected function _getTablesSQL($sDatabase = null)
  {
    return empty($sDatabase) ? 'SHOW TABLES' : "SHOW TABLES FROM $sDatabase";
  }

  public function hasTable($sTable, $sDatabase = null)
  {
    $aTable = $this->getTables($sDatabase);
    return in_array($sTable, $aTable);
  }

  public function getTables($sDatabase = null)
  {
    if (count($this->aTableList) == 0)
    {
      $oGetTables = $this->query($this->_getTablesSQL($sDatabase));

      foreach ($oGetTables as $aRow)
      {
        $this->aTableList[] = array_shift($aRow);
      }
    }

    return $this->aTableList;
  }

  protected function _getColumnsSQL($sTable)
  {
    return "DESC $sTable";
  }

  public function getColumns($sTable, $bUseTableName = false)
  {
    if (!isset($this->hColumnList[$sTable]))
    {
      $oGetColumns = $this->query($this->_getColumnsSQL($sTable));
      $this->hColumnList[$sTable] = [];

      foreach ($oGetColumns as $hRow)
      {
        $sName = $bUseTableName ? "$sTable.{$hRow['Field']}" : $hRow['Field'];
        $this->hColumnList[$sTable][$sName]['Type'] = $hRow['Type'];
        $hRow['Key'] = trim($hRow['Key']);

        if (!empty($hRow['Key']))
        {
          $this->hColumnList[$sTable][$sName]['Key'] = str_replace('PRI', 'Primary', $hRow['Key']);
          $this->hColumnList[$sTable][$sName]['Key'] = str_replace('MUL', 'Multi', $this->hColumnList[$sTable][$sName]['Key']);
        }

        $this->hColumnList[$sTable][$sName]['Default'] = trim($hRow['Default']);

        $hRow['Extra'] = trim($hRow['Extra']);
        if (!empty($hRow['Extra']))
        {
          $this->hColumnList[$sTable][$sName]['Extra'] = $hRow['Extra'];
        }
      }
    }

    return $this->hColumnList[$sTable];
  }

  public static function columnType($xData)
  {
    return isset($xData['Type']) ? $xData['Type'] : $xData;
  }

  // $xData can be either a column array or a column type
  public static function columnIs($xData, $sType)
  {
    return preg_match("/$sType/", self::columnType($xData));
  }

  // $xData can be either a column array or a column type
  public static function columnIsInteger($xData)
  {
    return self::columnIs($xData, "int");
  }

  // $xData can be either a column array or a column type
  public static function columnIsFloat($xData)
  {
    return self::columnIs($xData, "real|double|float|decimal|numeric");
  }

  // $xData can be either a column array or a column type
  public static function columnIsNumeric($xData)
  {
    return self::columnIsInteger() || self::columnIsFloat();
  }

  // $xData can be either a column array or a column type
  public static function columnIsDate($xData)
  {
    return self::columnIs($xData, "date|time|year");
  }

  // $xData can be either a column array or a column type
  public static function columnIsString($xData)
  {
    return self::columnIs($xData, "char|binary|blob|text|string|enum") || self::columnIsDate($xData);
  }

  // $xData can be either a column array or a column type
  public static function filterValue($xType, $xValue)
  {
    $sExtra = null;
    $sType = strtolower(self::columnType($xType));

    if (preg_match("#(.*?)\((.*?)\)#", $sType, $aMatch))
    {
      $sType = $aMatch[1];
      $sExtra = $aMatch[2];
    }

    switch (true)
    {
      case ($sType == 'enum'):
        $aExtra = explode(',', strtolower(preg_replace("/','/", ',', trim($sExtra, "'"))));
        settype($xValue, 'string');
        return in_array(strtolower($xValue), $aExtra) ? $xValue : null;
        break;

      case $sType == 'set':
        if (is_array($xValue))
        {
          // this will allow us to lower case the whole thing in the next step
          $xValue = implode(',', $xValue);
        }

        $aSet = explode(',', strtolower($xValue));
        $aRange = explode(',', strtolower(preg_replace("/','/", ',', trim($sExtra, "'"))));
        $aTemp = [];

        foreach ($aSet as $sItem)
        {
          if (in_array(strtolower($sItem), $aRange))
          {
            $aTemp[] = self::quote($sItem);
          }
        }

        return implode(',', array_unique($aTemp));
        break;

      case self::columnIsInteger($xType):
        return (integer)$xValue;
        break;

      case self::columnIsFloat($xType):
        return (float)$xValue;
        break;

      case self::columnIsDate($xType):
        if (empty($xValue) || $xValue == 'CURRENT_TIMESTAMP' || in_array($xValue, array(
            '0000-00-00',
            '0000-00-00 00:00:00'
        )))
        {
          return null;
        }

        // if the value is numeric and appears to be an integer assume it's a timestamp, otherwise evaluate it as a string
        $iValue = (is_numeric($xValue) && $xValue == (integer)$xValue) ? (integer)$xValue : strtotime($xValue);
        $sTimeFormat = isset(self::$hDateTimeFormat[$sType]) ? self::$hDateTimeFormat[$sType] : '%F %T';
        return strftime($sTimeFormat, $iValue);
        break;

      default:
        settype($xValue, 'string');
        // if there is any "extra" and it was on a string, it should be the max length of the string
        return !empty($sExtra) && self::columnIsString($xType) ? substr($xValue, 0, $sExtra) : $xValue;
    }
  }

  public static function prepareValue($xType, $xValue)
  {
    if (is_null($xValue))
    {
      return 'null';
    }

    $xValue = self::filterValue($xType, $xValue);
    return self::columnIsString($xType) ? "'" . addslashes($xValue) . "'" : $xValue;
  }

  public static function makeSelect($xColumns = null)
  {
    return empty($xColumns) ? '*' : implode(', ', (array)$xColumns);
  }

  public static function makeWhere($xWhere = null)
  {
    return empty($xWhere) ? '' : ' WHERE ' . implode(' AND ', (array)$xWhere);
  }

  public static function makeOrder($xOrder = null)
  {
    return empty($xOrder) ? '' : " ORDER BY " . implode(', ', (array)$xOrder);
  }

  public static function makeIdColumn($sTable)
  {
    return strtolower(preg_replace("/.*_/", '', $sType)) . 'ID';
  }

  public function getRowById($sTable, $iID, $xColumns)
  {
    $sID = self::makeIdColumn($sTable);
    $sColumns = self::makeSelect($xColumns);
    return $this->getRow("SELECT $sColumns FROM $sTable WHERE $sID = ?", array($iID));
  }

  public function insert($sTable, $hData)
  {
    $sSQL = "INSERT INTO $sTable (" . implode(', ', array_keys($hData)) . ") VALUES (" . implode(', ', array_fill(0, count($hData), '?')) . ")";
    return $this->query($sSQL, array_values($hData));
  }

  public static function update($sTable, $hData)
  {
  }

  public function delete($sTable, $iID)
  {
    $sID = self::makeIdColumn($sTable);
    return $this->query("DELETE FROM $sTable WHERE $sID = ?", array($iID));
  }
}