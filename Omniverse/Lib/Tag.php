<?php
namespace Omniverse\Lib;

/**
 * Omnisys tag base class
 *
 * This defines all the
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.11 $
 * @package Omniverse\Lib
 */
class Tag
{
  /**
   * @var string $sType - name of the basic HTML tag represented by the widget
   */
  protected $sType = null;

  /**
   * @var boolean $bInit - has this widget been initialized yet?
   */
  protected $bInit = false;

  /**
   * @var boolean $bXHTML - is this tag XHTML compliant?
   */
  protected $bXHTML = true;

  /**
   * @var string $hStyle - a hash containing the tag's style(s) if there are any
   */
  protected $hStyle = [];

  /**
   * @var string $aClass - an array containing the tag's class(es) if there are any
   */
  protected $aClass = [];

  /**
   * @var string $hParam - a hash containing the tags parameters if there are any, including events and their handlers
   */
  protected $hParam = [];

  /**
   * @var array $aContent - array of registerd objects
   */
  protected $aContent = [];

  /**
   *
   * @var boolean
   */
  protected $bCollapse = true;

  /**
   * Constructor
   *
   * It generates the type for further use later...
   */
  public function __construct($sType=null)
  {
    $this->sType = strtolower(empty($sType) ? str_replace(__CLASS__.'_', '', get_class($this)) : $sType);

    if (empty($this->sType))
    {
      throw new Exception\Object(__CLASS__ . " couldn't find a valid type!");
    }
  }

  /**
   * factory method that creates an instance of a specific type of widget.
   * It must be called statically.
   *
   * @param string $sType - The type of widget to instanciate
   * @return "mixed" - The object requested on success, otherwise false.
   */
  static public function factory($sType)
  {
     $sTypeClass = __NAMESPACE__ . '\\Tag\\' . ucfirst(strtolower(trim($sType)));

    if (\class_exists($sTypeClass, true))
    {
      return new $sTypeClass();
    }

    return new Tag($sType);
  }

  /**
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
        $sContent .= $xData->toString();
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
   *
   *
   * @return string
   */
  public function toString()
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
   *
   * @param string $sEvent
   * @return string
   */
  protected function makeEvent($sEvent)
  {
    return 'on' . ucfirst(strtolower($sEvent));
  }

  /**
   *
   * @param string$sEvent
   * @param string $sHandler
   */
  public function addEvent($sEvent, $sHandler=null)
  {
    $this->setRawParam($this->makeEvent($this->cleanString($sEvent)), $sHandler);
  }

  /**
   *
   * @param string $sEvent
   * @return string
   */
  public function getEvent($sEvent=null)
  {
    return $this->getParam($this->makeEvent($sEvent));
  }

  /**
   *
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
   *
   *
   * @param string $sName
   * @param string $sParam
   */
  public function setRawParam($sName, $sParam=null)
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
   *
   *
   * @param string $sName
   * @return string
   */
  public function getRawParam($sName=null)
  {
    return empty($sName) ? $this->hParam : (isset($this->hParam[$sName]) ? $this->hParam[$sName] : '');
  }

  /**
   *
   *
   * @param string $sName
   * @param string $sParam
   */
  public function setParam($sName, $sParam=null)
  {
    $this->setRawParam(strtolower($this->cleanString($sName)), $sParam);
  }

  /**
   *
   *
   * @param array $hParam
   */
  public function setAll($hParam=[])
  {
    if (is_array($hParam) && count($hParam) > 0)
    {
      foreach ($hParam as $sName => $sValue)
      {
        $this->setParam($sName, $sValue);
      }
    }
  }

  /**
   *
   *
   * @param string $sName
   * @return string
   */
  public function getParam($sName=null)
  {
    if (empty($sName))
    {
      $sParamList = '';
      foreach ($this->hParam as $sName => $sParam)
      {
        $sParamList .= $this->getParam($sName);
      }
      return $sParamList;
    }

    return is_null($this->hParam[$sName]) ? '' : " $sName=\"{$this->hParam[$sName]}\"";
  }

  /**
   *
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
   *
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
   *
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
   *
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
   *
   *
   * @param mixed $xItem
   */
  protected function addContent($xItem)
  {
    $this->aContent[] = $xItem;
  }

  /**
   *
   *
   * @param mixed $xItem
   */
  protected function removeContent($xItem)
  {
    if ($iKey = array_search($xItem, $this->aContent))
    {
      unset($this->aContent[$iKey]);
    }
  }

  /**
   *
   *
   * @param Tag $oTag
   */
  public function addTag(Tag $oTag)
  {
    $this->addContent($oTag);
  }

  /**
   *
   *
   * @param Tag $oTag
   */
  public function removeTag(Tag $oTag)
  {
    $this->removeContent($oTag);
  }

  /**
   *
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
   *
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
   *
   *
   * @param string $bXHTML
   * @return boolean
   */
  public function isXHTML($bXHTML=null)
  {
    if (is_null($bXHTML))
    {
      return $this->bXHTML;
    }

    $this->bXHTML = (boolean)$bXHTML;
  }

  /**
   *
   *
   * @param string $bCollapse
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
?>