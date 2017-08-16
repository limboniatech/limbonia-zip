<?php
namespace Omniverse\Exception;

/**
 * Omniverse DBResult Exception Class
 *
 * Handles exceptions thrown by the DBResult class(es)
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class DBResult extends Database
{
	protected $sQuery = null;

	public function __construct($sMessage, $sSQLType, $sQuery, $iCode = 0)
	{
		$this->sQuery = $sQuery;

		parent::__construct($sMessage, $sSQLType, $iCode);
	}

	public function getquery()
	{
		return $this->sQuery;
	}
}