<?php
namespace Omniverse\Exception;

/**
 * Omniverse Database Exception Class
 *
 * Handles exceptions thrown by the Database class(es)
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Database extends \Exception
{
  /**
   * The SQL server type
   *
   * @var string
   */
	protected $sSQLType = null;

  /**
   * Construct a new exception
   *
   * @param string $sMessage
   * @param string $sSQLType
   * @param integer $iCode
   */
	public function __construct($sMessage, $sSQLType = "Unknown", $iCode = 0)
	{
		$this->sSQLType = $sSQLType;
		parent::__construct($sMessage, $iCode);
	}

  /**
   * Return the SQL server type
   *
   * @return string
   */
	public function getType()
	{
		return $this->sSQLType;
	}
}