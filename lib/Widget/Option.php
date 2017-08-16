<?php
namespace Omniverse\Widget;

class Option extends \Omniverse\Tag
{
  protected function toString()
  {
    $sContent = $this->getContent();
    $sParam = $this->getParam();
    return "<option$sParam>$sContent</option>\n";
  }
}
