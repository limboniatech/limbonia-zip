<?php
namespace Limbonia\Exception;

/**
 * Limbonia Error Exception Class
 *
 * Extends the default exception class for use as a substitute for errors.
 *
 * This is only needed for PHP *before* version 7.0
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
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
   * @param string $sFileName - the name of the file that the exception occurred in
   * @param integer $iLine - the line number that the exception occurred
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
