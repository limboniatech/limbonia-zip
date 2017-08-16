<?php
namespace Omniverse;

/**
 * Omniverse Database Class
 *
 * This is an extension to PHP's PDO system for accessing databases
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Database extends \PDO
{
  /**
   * List of existing database objects
   *
   * @var array
   */
  private static $hDatabaseObjects = [];

  /**
   * List of time formats for use in filtering time values
   *
   * @var array
   */
  protected static $hDateTimeFormat =
  [
    'date' => '%Y-%m-%d',
    'time' => '%T',
    'timestamp' => '%Y-%m-%d %T',
    'datetime' => '%Y-%m-%d %T',
    'year' => '%Y'
  ];

  /**
   * List of tables
   *
   * @var array
   */
  protected $aTableList = [];

  /**
   * List of columns per table
   *
   * @var array
   */
  protected $hColumnList = [];

  /**
   * Return the existing column type from the data passed in
   *
   * @param string|array $xData - Either and array of column data or the actual column type
   * @return string
   */
  public static function columnType($xData)
  {
    return isset($xData['Type']) ? $xData['Type'] : $xData;
  }

  /**
   * Determine of the specified column data matches the specified column type
   *
   * @param string|array $xData - Either and array of column data or the actual column type
   * @param string $sType
   * @return boolean
   */
  public static function columnIs($xData, $sType)
  {
    return preg_match("/$sType/i", self::columnType($xData));
  }

  /**
   * Determine of the specified column is an integer
   *
   * @param string|array $xData - Either and array of column data or the actual column type
   * @return boolen
   */
  public static function columnIsInteger($xData)
  {
    return self::columnIs($xData, 'int');
  }

  /**
   * Determine of the specified column is a float
   *
   * @param string|array $xData - Either and array of column data or the actual column type
   * @return boolen
   */
  public static function columnIsFloat($xData)
  {
    return self::columnIs($xData, 'real|double|float|decimal|numeric');
  }

  /**
   * Determine of the specified column is numeric
   *
   * @param string|array $xData - Either and array of column data or the actual column type
   * @return boolen
   */
  public static function columnIsNumeric($xData)
  {
    return self::columnIsInteger($xData) || self::columnIsFloat($xData);
  }

  /**
   * Determine of the specified column is a date
   *
   * @param string|array $xData - Either and array of column data or the actual column type
   * @return boolen
   */
  public static function columnIsDate($xData)
  {
    return self::columnIs($xData, 'date|time|year');
  }

  /**
   * Determine of the specified column is a string
   *
   * @param string|array $xData - Either and array of column data or the actual column type
   * @return boolen
   */
  public static function columnIsString($xData)
  {
    return self::columnIs($xData, 'char|binary|blob|text|string|enum') || self::columnIsDate($xData);
  }

  /**
   * Filter the specified value to be compatible with the specified type
   *
   * @param string|array $xType - Either and array of column data or the actual column type
   * @param mixed $xValue
   * @return type
   */
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

      case self::columnIsInteger($xType):
        return (integer)$xValue;

      case self::columnIsFloat($xType):
        return (float)$xValue;

      case self::columnIsDate($xType):
        if (empty($xValue) || $xValue == 'CURRENT_TIMESTAMP' || in_array($xValue, ['0000-00-00', '0000-00-00 00:00:00']))
        {
          return null;
        }

        // if the value is numeric and appears to be an integer assume it's a timestamp, otherwise evaluate it as a string
        $iValue = (is_numeric($xValue) && $xValue == (integer)$xValue) ? (integer)$xValue : strtotime($xValue);
        $sTimeFormat = isset(self::$hDateTimeFormat[$sType]) ? self::$hDateTimeFormat[$sType] : '%F %T';
        return strftime($sTimeFormat, $iValue);

      default:
        settype($xValue, 'string');
        // if there is any "extra" and it was on a string, it should be the max length of the string
        return !empty($sExtra) && self::columnIsString($xType) ? substr($xValue, 0, $sExtra) : $xValue;
    }
  }

  /**
   * Prepare the specified value for use in an SQL statement
   *
   * @param string|array $xType - Either and array of column data or the actual column type
   * @param mixed $xValue
   * @return string
   */
  public static function prepareValue($xType, $xValue)
  {
    if (is_null($xValue))
    {
      return 'null';
    }

    $xFilteredValue = self::filterValue($xType, $xValue);

    if (is_null($xFilteredValue))
    {
      return 'null';
    }

    return self::columnIsString($xType) ? "'" . addslashes($xFilteredValue) . "'" : $xFilteredValue;
  }

  /**
   * Generate the "select" part of an SQL statement from the specified column name(s), if any
   *
   * @param string|array $xColumns (optional) - specify either a single column string or an array of column names
   * @return string
   */
  public static function makeSelect($xColumns = null)
  {
    return empty($xColumns) ? '*' : implode(', ', (array)$xColumns);
  }

  /**
   * Generate the "where" part of an SQL statement from the specified name(s), if any
   *
   * @param string|array $xWhere (optional) - specify either a single string or an array of string
   * @return string
   */
  public static function makeWhere($xWhere = null)
  {
    return empty($xWhere) ? '' : ' WHERE ' . implode(' AND ', (array)$xWhere);
  }

  /**
   * Generate the "order" part of an SQL statement from the specified name(s), if any
   *
   * @param string|array $xOrder (optional) - specify either a single string or an array of string
   * @return string
   */
  public static function makeOrder($xOrder = null)
  {
    return empty($xOrder) ? '' : " ORDER BY " . implode(', ', (array)$xOrder);
  }

  /**
   * Generate an "ID" column name from the specified table name
   *
   * @param string $sTable
   * @return string
   */
  public static function makeIdColumn($sTable)
  {
    return strtolower(preg_replace("/.*_/", '', $sTable)) . 'ID';
  }

  /**
   * Change a flat hash of database options into a DSN string, username, password, and an array with the remaining options
   *
   * @param array $hConfig
   * @return array
   */
  static public function arrayToDSN(array $hConfig = [])
  {
  	$hDSN =
  	[
  		'dsn' => strtolower($hConfig['driver']) . ':',
  		'username' => '',
  		'password' => '',
  		'options' => []
  	];
  	unset($hConfig['driver']);

  	if (isset($hConfig['host']))
  	{
  	  $hDSN['dsn'] .= 'host=';
  	  $hDSN['dsn'] .= $hConfig['host'] == 'localhost' ? '127.0.0.1' : $hConfig['host'];
  	  unset($hConfig['host']);

  	  if (isset($hConfig['port']))
  	  {
  	    $hDSN['dsn'] .= ";port={$hConfig['port']}";
  	    unset($hConfig['port']);
  	  }
  	}
  	elseif (isset($hConfig['unixsocket']))
  	{
  	  $hDSN['dsn'] .= "unix_socket={$hConfig['unixsocket']}";
  	  unset($hConfig['unixsocket']);
  	}

  	if (isset($hConfig['database']))
  	{
  	  $hDSN['dsn'] .= ";dbname={$hConfig['database']}";
  	  unset($hConfig['database']);
  	}

  	if (isset($hConfig['charset']))
  	{
  	  $hDSN['dsn'] .= ";charset={$hConfig['charset']}";
  	  unset($hConfig['charset']);
  	}

  	if (isset($hConfig['user']))
  	{
  	  $hDSN['username'] = $hConfig['user'];
  	  unset($hConfig['user']);
  	}

  	if (isset($hConfig['password']))
  	{
  	  $hDSN['password'] = $hConfig['password'];
  	  unset($hConfig['password']);
  	}

  	$hDSN['options'] = $hConfig;
  	return $hDSN;
  }

  /**
   * Generate an instance of the Omniverse Database object based on the specified configuration
   *
   * @param array $hConfig
   * @throws \Omniverse\Exception\Database
   * @return \Omniverse\Database
   */
  static public function factory(array $hConfig = [])
  {
    $hLowercaseConfig = array_change_key_case($hConfig, CASE_LOWER);

    if (empty($hLowercaseConfig['driver']))
  	{
  		throw new Exception\Database('A valid SQL driver name was not found!');
  	}

  	ksort($hLowercaseConfig);
    $sConfigHash = md5(serialize($hLowercaseConfig));

    if (isset(self::$hDatabaseObjects[$sConfigHash]))
    {
      return self::$hDatabaseObjects[$sConfigHash];
    }

    $hDSN = self::arrayToDSN($hLowercaseConfig);
    self::$hDatabaseObjects[$sConfigHash] = new self($hDSN['dsn'], $hDSN['username'], $hDSN['password'], $hDSN['options']);
    return self::$hDatabaseObjects[$sConfigHash];
  }

  /**
   * Creates a Database instance representing a connection to a database
   *
   * @param string $sDSN
   * @param string $sUsername (optional)
   * @param string $sPassword (optional)
   * @param array $hOptions (optional)
   */
  public function __construct($sDSN, $sUsername = null, $sPassword = null, $hOptions = null)
  {
    parent::__construct($sDSN, $sUsername, $sPassword, $hOptions);
    $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, ['\Omniverse\DBResult', [$this]]);
    $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
  }

  /**
   * Return the driver type for this object
   *
   * @return string
   */
  public function getType()
  {
    return $this->getAttribute(\PDO::ATTR_DRIVER_NAME);
  }

  /**
   * Create a new table based on the specified parameters
   *
   * @param string $sName - name for the table to create
   * @param array $hColumns - list of column data for the table to create
   * @param string $sDatabase (optional) - name of the database to create the table in, defaults to the "current" database for the connection
   * @throws Exception\Database
   */
  public function createTable($sName, array $hColumns, $sDatabase = '')
  {
    if (empty($sName))
    {
      throw new Exception\Database(__METHOD__ . ": Name is invalid!", $this->getType());
    }

    if (count($hColumns) == 0)
    {
      throw new Exception\Database(__METHOD__ . ": Columns is invalid!", $this->getType());
    }

    switch ($this->getType())
    {
      case 'mysql':
        $aColumns = [];
        foreach ($hColumns as $sColumn => $aColumnConfig)
        {
          $aColumns[] = $sColumn . ' ' . implode(' ', $aColumnConfig);
        }

        if (!empty($sDatabase))
        {
          $sDatabase .= '.';
        }

        $sCreateSQL = "CREATE TABLE IF NOT EXISTS $sDatabase$sName (" . implode(', ', $aColumns) . ")";
        break;

      default:
        throw new Exception\Database(__METHOD__ . ": Can not create tables of this type, yet.", $this->getType());
    }

    $this->query($sCreateSQL);
    $this->aTableList[] = $sName;
  }

  /**
   * Return the list of databases that the current connection has access to
   *
   * @return array
   * @throws Exception\Database
   */
  public function getDatabases()
  {
    switch ($this->getType())
    {
      case 'mysql':
        $sDatabasesSQL = 'SHOW DATABASES';
        break;

      default:
        throw new Exception\Database(__METHOD__ . ": Can not list databases of this type, yet.", $this->getType());
    }

    $oDatabases = $this->query($sDatabasesSQL);
    $aDatabases = [];

    foreach ($oDatabases as $hRow)
    {
      $aDatabases[] = array_shift($hRow);
    }

    return $aDatabases;
  }

  /**
   * Determine of the specified table is in the specified database
   *
   * @param string $sTable - name of the table to check
   * @param string $sDatabase (optional) - the database used to check for the table (if none is specified then use the "current" database)
   * @return boolean
   */
  public function hasTable($sTable, $sDatabase = '')
  {
    $aTable = $this->getTables($sDatabase);
    return in_array($sTable, $aTable);
  }

  /**
   * Generate the array of table names in the specified database
   *
   * @param string $sDatabase (optional) - the database used to get the table list (if none is specified then use the "current" database)
   * @return array
   * @throws Exception\Database
   */
  public function getTables($sDatabase = '')
  {
    if (count($this->aTableList) == 0)
    {
      switch ($this->getType())
      {
        case 'mysql':
          $sTableSQL = empty($sDatabase) ? 'SHOW TABLES' : "SHOW TABLES FROM $sDatabase";
          break;

        default:
          throw new Exception\Database(__METHOD__ . ": Can not list tables from this type, yet.", $this->getType());
      }

      $oGetTables = $this->query($sTableSQL);

      foreach ($oGetTables as $aRow)
      {
        $this->aTableList[] = array_shift($aRow);
      }
    }

    return $this->aTableList;
  }

  /**
   * Generate the list of column data from the specified table
   *
   * @param string $sTable - name of the table to get column data from
   * @param boolean $bUseTableName (optional) - Should the table name be prepended to each column name (defaults to false)
   * @return type
   * @throws \Omniverse\Exception\Database
   */
  public function getColumns($sTable, $bUseTableName = false)
  {
    if (!isset($this->hColumnList[$sTable]))
    {
      switch ($this->getType())
      {
        case 'mysql':
          $sColumnSQL = "DESC $sTable";
          break;

        default:
          throw new Exception\Database(__METHOD__ . ": Can not list columns from this type, yet.", $this->getType());
      }

      try
      {
        $oGetColumns = $this->query($sColumnSQL);
      }
      catch (\PDOException $e)
      {
        return [];
      }

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

  /**
   * Get a row of data from the specified table using the specified id,
   * if column(s) are specified then data from only those columns will be returned
   *
   * @param string $sTable - name of the table to get the data from
   * @param integer $iID - the ID of the the row to get the data from
   * @param string|array $xColumns (optional) - specify either a single column string or an array of column names
   * @return array
   */
  public function getRowById($sTable, $iID, $xColumns)
  {
    return $this->getRow("SELECT " . self::makeSelect($xColumns) . " FROM $sTable WHERE " . self::makeIdColumn($sTable) . " = ?", [$iID]);
  }

  /**
   * Insert the specified data into the specified table
   *
   * @param string $sTable
   * @param array $hData
   * @return integer - returns the last inserted id on success and false on failure
   */
  public function insert($sTable, $hData)
  {
    $sSQL = "INSERT INTO $sTable (" . implode(', ', array_keys($hData)) . ") VALUES (" . implode(', ', array_fill(0, count($hData), '?')) . ")";

    if (false == $this->query($sSQL, array_values($hData)))
    {
      return false;
    }

    return $this->lastInsertId();
  }

  /**
   *
   *
   * @param string $sTable
   * @param array $hData
   */
  public function update($sTable, $hData)
  {
  }

  /**
   *
   *
   * @param string $sTable
   * @param integer $iID
   * @return boolean
   */
  public function delete($sTable, $iID)
  {
    return $this->query("DELETE FROM $sTable WHERE " . self::makeIdColumn($sTable) . " = ?", [$iID]);
  }
}