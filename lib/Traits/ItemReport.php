<?php
namespace Limbonia\Traits;

trait ItemReport
{
  /**
   * Generate and return the result data for the current configuration of this report
   *
   *
   * @return \Limbonia\Interfaces\Result
   * @throws \Limbonia\Exception
   */
  protected function generateResult(): \Limbonia\Interfaces\Result
  {
    $sItemDriver = \Limbonia\Item::driver($this->getType());

    if (empty($sItemDriver))
    {
      throw new \Limbonia\Exception("Driver for type ($this->sType) not found");
    }

    $oItem = $this->oController->itemFactory($this->sType);
    $sTable = $oItem->getTable();
    $oDatabase = $this->oController->getDB();
    $aFields = empty($this->aFields) ? array_keys($this->hHeaders) : array_intersect($oDatabase->verifyColumns($sTable, array_merge(['id'], $this->aFields)), array_keys($this->hHeaders));

    //default order is according to the ID column of this item
    $aOrder = empty($this->aOrder) ? ['id'] : $this->aOrder;
    return $oDatabase->query($oDatabase->makeSearchQuery($sTable, $aFields, $this->hSearch, $aOrder));
  }
}
