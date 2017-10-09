<?php
namespace Omniverse;

/**
 * Omniverse Widget base class
 *
 * This defines all the basic parts of a basic HTML tag
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Tag
{
  use \Omniverse\Traits\DriverList;

  /**
   * Name of the basic HTML tag represented by the widget
   *
   * @var string $sType
   */
  protected $sType = null;

  /**
   * Has this widget been initialized yet?
   *
   * @var boolean $bInit
   */
  protected $bInit = false;

  /**
   * Is this tag XHTML compliant?
   *
   * @var boolean $bXHTML
   */
  protected $bXHTML = true;

  /**
   * Hash containing the tag's style(s), if there are any
   *
   * @var string $hStyle
   */
  protected $hStyle = [];

  /**
   * Array containing the tag's class(es), if there are any
   *
   * @var string $aClass
   */
  protected $aClass = [];

  /**
   * Hash containing the tags parameters if there are any, including events and their handlers
   *
   * @var string $hParam
   */
  protected $hParam = [];

  /**
   * Array of registered objects
   *
   * @var array $aContent
   */
  protected $aContent = [];

  /**
   * Is the tag allowed to "collapse" to a single tag with no closing tag?
   *
   * @var boolean
   */
  protected $bCollapse = true;

  /**
   * factory method that creates an instance of a specific type of widget.
   * It must be called statically.
   *
   * @param string $sType - The type of widget to instanciate
   * @return "mixed" - The object requested on success, otherwise false.
   */
  static public function factory($sType)
  {
     $sTypeClass = __CLASS__ . '\\' . $sType;

    if (\class_exists($sTypeClass, true))
    {
      return new $sTypeClass();
    }

    return new Tag($sType);
  }

  /**
   * Constructor
   *
   * It generates the type for further use later...
   */
  public function __construct($sType=null)
  {
    $this->sType = strtolower(empty($sType) ? str_replace(__CLASS__ . '_', '', get_class($this)) : $sType);

    if (empty($this->sType))
    {
      throw new Exception\Object(__CLASS__ . " couldn't find a valid type!");
    }
  }

  /**
   * Generate and return the content of this tag
   *
   * @return string
   */
  protected function getContent()
  {
    if (count($this->aContent) == 0)
    {
      return '';
    }

    $sContent = '';

    foreach ($this->aContent as $xData)
    {
      if (is_string($xData))
      {
        $sContent .= $xData;
      }
      elseif ($xData instanceof Tag)
      {
        $sContent .= $xData;
      }
    }

    return $sContent;
  }

  /**
   * Return the tag type
   *
   * @return string
   */
  public function getType()
  {
    return $this->sType;
  }

  /**
  * Does all redundant bits of creating the actual tag.
  *
  * @return boolean
  */
  public function create()
  {
    if (!$this->bInit)
    {
      $this->bInit = true;
      echo $this->toString();
    }

    return true;
  }

  /**
   * Return the HTML representation of this tag
   *
   * @return string
   */
  protected function toString()
  {
    $sParamList = $this->getParam();
    $sContent = $this->getContent();

    if (empty($sContent))
    {
      $sClose = $this->bXHTML ? ' /' : '';
      return "<$this->sType$sParamList$sClose>\n";
    }

    return "<$this->sType$sParamList>\n$sContent</$this->sType>\n";
  }

  /**
   * Magic method for generating a string representation of an object
   *
   * @return string
   */
  public function __toString()
  {
    return $this->toString();
  }

  /**
   * Generate and return the name of an event based on the specified name
   *
   * @param string $sEvent
   * @return string
   */
  protected function makeEvent($sEvent)
  {
    return 'on' . ucfirst(strtolower($sEvent));
  }

  /**
   * Add an event to the tag paramaters
   *
   * @param string $sEvent
   * @param string $sHandler
   */
  public function addEvent($sEvent, $sHandler=null)
  {
    $this->setRawParam($this->makeEvent($this->cleanString($sEvent)), $sHandler);
  }

  /**
   * Return the HTML for the specified event
   *
   * @param string $sEvent
   * @return string
   */
  public function getEvent($sEvent=null)
  {
    return $this->getParam($this->makeEvent($sEvent));
  }

  /**
   * Return the specified string with the spaces and quotation marks removed
   *
   * @param string $sString
   * @return mixed
   */
  protected function cleanString($sString)
  {
    //html tags and parameters shouldn't have spaces or quotes in it...
    return preg_replace("/\s|\"|'/", '', (string)$sString);
  }

  /**
   * Set the specified parameter with the specified data
   *
   * @param string $sName
   * @param string $sParam
   */
  public function setRawParam($sName, $sParam = '')
  {
    if (empty($sParam) && isset($this->hParam[$sName]))
    {
      unset($this->hParam[$sName]);
    }
    else
    {
      $this->hParam[$sName] = $sParam;
    }
  }

  /**
   * Return the raw (non-HTML) data associated with the specified parameter
   *
   * @param string $sName
   * @return string
   */
  public function getRawParam($sName=null)
  {
    return empty($sName) ? $this->hParam : (isset($this->hParam[$sName]) ? $this->hParam[$sName] : '');
  }

  /**
   * Set the parameter with clean text
   *
   * @param string $sName
   * @param string $sParam
   */
  public function setParam($sName, $sParam=null)
  {
    $this->setRawParam(strtolower($this->cleanString($sName)), $sParam);
  }

  /**
   * Set all parameters at once using the specified array
   *
   * @param array $hParam
   */
  public function setAll(array $hParam = [])
  {
    if (count($hParam) > 0)
    {
      foreach ($hParam as $sName => $sValue)
      {
        $this->setParam($sName, $sValue);
      }
    }
  }

  /**
   * Generate and return the HTML for the specified parameter, if it exists
   * If no parameter is specified then the whole list of parameters will be returned
   *
   * @param string $sName
   * @return string
   */
  public function getParam($sName = '')
  {
    if (empty($sName))
    {
      $sParamList = '';

      foreach (array_keys($this->hParam) as $sName)
      {
        $sParamList .= $this->getParam($sName);
      }

      return $sParamList;
    }

    return is_null($this->hParam[$sName]) ? '' : " $sName=\"{$this->hParam[$sName]}\"";
  }

  /**
   * Add a CSS style to this tag
   *
   * @param string $sName
   * @param string $sSetting
   */
  public function addStyle($sName, $sSetting=null)
  {
    $sName = strtolower($this->cleanString($sName));
    $sSetting = strtolower($this->cleanString($sSetting));

    if (!empty($sSetting))
    {
      $this->hStyle[$sName] = $sSetting;

      $sStyle = '';

      if (count($this->hStyle) > 0)
      {
        foreach ($this->hStyle as $sName => $sSetting)
        {
          $sStyle .= "$sName: $sSetting; ";
        }

        $sStyle = trim($sStyle);
      }

      $this->setRawParam('style', $sStyle);
    }
  }

  /**
   * Remove the specified CSS style from this tag
   *
   * @param string $sName
   */
  public function removeStyle($sName)
  {
    $sName = strtolower($this->cleanString($sName));

    if (isset($this->hStyle[$sName]))
    {
      unset($this->hStyle[$sName]);

      $sStyle = '';

      if (count($this->hStyle) > 0)
      {
        foreach ($this->hStyle as $sName => $sSetting)
        {
          $sStyle .= "$sName: $sSetting; ";
        }

        $sStyle = trim($sStyle);
      }

      $this->setRawParam('style', $sStyle);
    }
  }

  /**
   * Add a class to this tag
   *
   * @param string $sName
   */
  public function addClass($sName)
  {
    $this->aClass[] = $this->cleanString($sName);
    $this->aClass = array_unique($this->aClass);
    $this->setRawParam('class', trim(implode(' ', $this->aClass)));
  }

  /**
   * Remove the specified class from this tag
   *
   * @param string $sName
   */
  public function removeClass($sName)
  {
    if ($iKey = array_search($this->cleanString($sName), $this->aClass))
    {
      unset($this->aClass[$iKey]);
      $this->setRawParam('class', trim(implode(' ', $this->aClass)));
    }
  }

  /**
   * Add some content to this tag
   *
   * @param string|Tag $xItem - Either a string of data or another tag
   */
  protected function addContent($xItem)
  {
    $this->aContent[] = $xItem;
  }

  /**
   * Remove the specified content from this tag
   *
   * @param string|Tag $xItem - Either a string of data or another tag
   * @return integer The index of the removed content or false if there is none
   */
  protected function removeContent($xItem)
  {
    $iKey = array_search($xItem, $this->aContent);

    if ($iKey === false)
    {
      return false;
    }

    unset($this->aContent[$iKey]);
    return $iKey;
  }

  /**
   * Add the specified tag to this tag's content
   *
   * @param Tag $oTag
   */
  public function addTag(Tag $oTag)
  {
    $this->addContent($oTag);
  }

  /**
   * Remove the specified tage from the tag's content
   *
   * @param Tag $oTag
   */
  public function removeTag(Tag $oTag)
  {
    $this->removeContent($oTag);
  }

  /**
   * Add the specified text to the tag's content
   *
   * @param string $sText
   */
  public function addText($sText)
  {
    if (is_string($sText))
    {
      $this->addContent($sText);
    }
  }

  /**
   * Remove the specified text from the tag's content
   *
   * @param string $sText
   */
  public function removeText($sText)
  {
    if (is_string($sText))
    {
      $this->removeContent($sText);
    }
  }

  /**
   * If the parameter is empty then return the current value otherwise attempt to set to the specified value
   *
   * @param string $bXHTML
   * @return boolean
   */
  public function isXHTML($bXHTML = null)
  {
    if (is_null($bXHTML))
    {
      return $this->bXHTML;
    }

    $this->bXHTML = (boolean)$bXHTML;
  }

  /**
   * If a collapse variable is specified update the this tag's collapse variable to match.
   * If it is not specified then return the current value
   *
   * @param boolean $bCollapse
   * @return boolean
   */
  public function allowCollapse($bCollapse=null)
  {
    if (is_null($bCollapse))
    {
      return $this->bCollapse;
    }

    $this->bCollapse = (boolean)$bCollapse;
  }
}