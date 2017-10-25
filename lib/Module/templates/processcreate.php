<?php
if ($failure)
{
  echo "<h3>Failed creating new " . $module->getType() . ": $failure</h3>";
}
else
{
  echo "<h3>Successfully created new " . $module->getType() . " called:  " . $module->getCurrentItemTitle() . "</h3><a class=\"item\" href=\"" . $module->generateUri('create') . "\">Create another?</a><br />";
}