<?php
namespace Omniverse\Lib\Exception;

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