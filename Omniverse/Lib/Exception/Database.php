<?php
namespace \Omniverse\Lib\Exception;

class Database extends Exception
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