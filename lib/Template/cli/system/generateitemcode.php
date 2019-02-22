<?php
try
{
  $sTable = $options['t'] ?? $options['table'] ?? null;
  die($module->generateItemCode($sTable));
}
catch (\Exception $e)
{
  die("Failed to generate stub Item class for $sTable: " . $e->getMessage());
}