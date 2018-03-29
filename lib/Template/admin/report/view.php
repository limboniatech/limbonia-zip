<?php
if (isset($success))
{
  echo "<div class=\"methodSuccess\">$success</div>\n";
}

if (isset($failure))
{
  echo "<div class=\"methodFailure\">$failure</div>\n";
}

$hHeaders = $report->getHeaders();
$hOptions = $report->getOptionList();

if (!empty($hOptions))
{
  echo "<form name=\"reportOptions\" action=\"\" method=\"post\">\n";

  //$module->getFormFields($hOptions, $formValues);

  foreach ($hOptions as $sField => $hData)
  {
    $sValue = $formValues[$sField] ?? null;
    echo $module->getFormField($hHeaders[$sField], $sValue, $hData);
  }

  echo "\n<div class=\"field\"><span class=\"blankLabel\"></span><span><button type=\"submit\">Run Report</button></span></div>
</form>\n";
}

$oResult = $report->run();

$table->makeSortable();
$table->startHeader();

foreach ($hHeaders as $sField => $sHeader)
{
  $module->processSearchGridHeader($table, $sHeader);
}

$table->endRow();

foreach ($oResult as $iKey => $hRow)
{
  $table->startRow();

  foreach ($hRow as $sName => $sValue)
  {
    $table->addCell($sValue);
  }

  $table->endRow();
}

echo $table->toString();
