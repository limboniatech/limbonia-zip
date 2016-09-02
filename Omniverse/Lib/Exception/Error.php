<?php
namespace Omniverse\Lib\Exception;

/**
 * Omniverse Error Exception Class
 *
 * Extends the default exception class for use as a substitute for errors.
 */
class Error extends Exception
{
  /**
   * The context for this exception
   *
   * @var array
   */
  protected $context;

  /**
   * Constructor
   *
   * @param string $sError - the error message
   * @param integer $iCode - the error code number
   * @param string $sFileName - the name of the file that the exceptipon occured in
   * @param integer $iLine - the line number that the exception occured
   */
  public function __construct($sError, $iCode, $sFileName, $iLine)
  {
    parent::__construct($sError, $iCode);
    $this->file = $sFileName;
    $this->line = $iLine;
  }

  /**
   * Return the context for this exception
   *
   * @return array
   */
  public function getContext()
  {
    return $this->context;
  }
}
