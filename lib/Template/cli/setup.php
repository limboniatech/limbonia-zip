<?php
try
{
  echo "\nStart set-up:\n";
  $controller->setup();
  die("\nFinish set-up\n");
}
catch (\Exception $e)
{
  die("Failed to generate stub Item class for $sTable: " . $e->getMessage());
}