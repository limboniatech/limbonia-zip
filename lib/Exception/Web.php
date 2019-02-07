<?php
namespace Limbonia\Exception;

/**
 * Limbonia Web Exception Class
 *
 * Extends the default exception class for use in object constructors.
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Web extends \Exception
{
  /**
   * The context for this exception
   *
   * @var array
   */
  protected $responseCode = 0;

  /**
   * Constructor
   *
   * @param string $sError - the error message
   * @param integer $iCode - the error code number
   * @param integer $iReponseCode
   */
  public function __construct($sError, $iCode, $iReponseCode)
  {
    parent::__construct($sError, $iCode);
    $this->responseCode = empty($iReponseCode) || !is_numeric($iReponseCode) || $iReponseCode < 400 ? 400 : (int)$iReponseCode;
  }

  /**
   * Return the HTTP Response Code for this exception
   *
   * @return integer
   */
  public function getResponseCode()
  {
    return $this->responseCode;
  }
}
