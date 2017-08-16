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
	protected $sSQLType = null;

	public function __construct($sMessage, $sSQLType = "Unknown", $iCode = 0)
	{
		$this->sSQLType = $sSQLType;
		parent::__construct($sMessage, $iCode);
	}

	public function getType()
	{
		return $this->sSQLType;
	}
}