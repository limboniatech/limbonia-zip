<?php
$ticketList = $currentItem->getTickets();

if (count($ticketList) == 0)
{
  echo "Sorry!  This user has no tickets at this time!<br>\n";
}
else
{
  $table = $controller->widgetFactory('Table');
  $ticketModule = $controller->moduleFactory('Ticket');
  $columnList = $ticketModule->getColumns('userTickets');
  $table->makeSortable();
  $table->startHeader();
  $table->addCell('&nbsp;', false);

  foreach ($columnList as $column)
  {
    $ticketModule->processSearchGridHeader($table, $column);
  }

  $table->endRow();

  foreach ($ticketList as $item)
  {
    $oRow = $table->startRow();
    $table->addCell('<a class="item" href="' . $controller->generateUri('ticket', $item->id) . '">View</a>');

    foreach ($columnList as $column)
    {
      $table->addCell($ticketModule->getColumnValue($item, $column));
    }

    $table->endRow();
  }

  echo $table->toString();
}