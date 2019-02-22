<?php
namespace Limbonia\Interfaces;

/**
 * Limbonia Tag Interface
 *
 * This interface to the basic HTML tag
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
interface Tag
{
  /**
  * Does all redundant bits of creating the actual tag.
  *
  * @return boolean
  */
  public function create();

  /**
   * Magic method for generating a string representation of an object
   *
   * @return string
   */
  public function __toString();

  /**
   * Add an event to the tag parameters
   *
   * @param string $sEvent
   * @param string $sHandler
   */
  public function addEvent($sEvent, $sHandler=null);

  /**
   * Return the HTML for the specified event
   *
   * @param string $sEvent
   * @return string
   */
  public function getEvent($sEvent=null);

  /**
   * Set the specified parameter with the specified data
   *
   * @param string $sName
   * @param string $sParam
   */
  public function setRawParam($sName, $sParam = '');

  /**
   * Return the raw (non-HTML) data associated with the specified parameter
   *
   * @param string $sName
   * @return string
   */
  public function getRawParam($sName=null);

  /**
   * Set the parameter with clean text
   *
   * @param string $sName
   * @param string $sParam
   */
  public function setParam($sName, $sParam=null);

  /**
   * Set all parameters at once using the specified array
   *
   * @param array $hParam
   */
  public function setAll(array $hParam = []);

  /**
   * Generate and return the HTML for the specified parameter, if it exists
   * If no parameter is specified then the whole list of parameters will be returned
   *
   * @param string $sName
   * @return string
   */
  public function getParam($sName = '');

  /**
   * Add a CSS style to this tag
   *
   * @param string $sName
   * @param string $sSetting
   */
  public function addStyle($sName, $sSetting=null);

  /**
   * Remove the specified CSS style from this tag
   *
   * @param string $sName
   */
  public function removeStyle($sName);

  /**
   * Add a class to this tag
   *
   * @param string $sName
   */
  public function addClass($sName);

  /**
   * Remove the specified class from this tag
   *
   * @param string $sName
   */
  public function removeClass($sName);


  /**
   * Add some content to this tag
   *
   * @param string|Tag $xItem - Either a string of data or another tag
   */
  public function addContent($xItem);

  /**
   * Remove the specified content from this tag
   *
   * @param string|Tag $xItem - Either a string of data or another tag
   * @return integer The index of the removed content or false if there is none
   */
  public function removeContent($xItem);

  /**
   * Add the specified tag to this tag's content
   *
   * @param Tag $oTag
   */
  public function addTag(\Limbonia\Interfaces\Tag $oTag);

  /**
   * Remove the specified tag from the tag's content
   *
   * @param Tag $oTag
   */
  public function removeTag(\Limbonia\Interfaces\Tag $oTag);

  /**
   * Add the specified text to the tag's content
   *
   * @param string $sText
   */
  public function addText($sText);

  /**
   * Remove the specified text from the tag's content
   *
   * @param string $sText
   */
  public function removeText($sText);

  /**
   * If the parameter is empty then return the current value otherwise attempt to set to the specified value
   *
   * @param string $bXHTML
   * @return boolean
   */
  public function isXHTML($bXHTML = null);

  /**
   * If a collapse variable is specified update the this tag's collapse variable to match.
   * If it is not specified then return the current value
   *
   * @param boolean $bCollapse
   * @return boolean
   */
  public function allowCollapse($bCollapse=null);
}
