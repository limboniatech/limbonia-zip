<?php
try
{
  echo "Checking zipcode database: ";

  if ($controller->getDB()->hasTable('ZipCode'))
  {
    die("yes!\nNothong more to do...\n");
  }

//  $module

  output('Finished initializing zipcode database');
}
catch (\Exception $e)
{
  output('Failed to initialize zipcode database: ' . $e->getMessage());
}
