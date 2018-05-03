<?php
if ($controller->post['reset'] == 'Yes')
{
  echo "<h3>Are you sure you wan to reset this user's password?</h3>\n";
  echo "<form name=\"ResetPassword\" action=\"" . $module->generateUri($currentItem->id, $method) . "\" method=\"post\">\n";
  echo "<div class=\"field\"><span class=\"blankLabel\"><span class=\"data\"><button type=\"submit\">Yes</button>&nbsp;&nbsp;&nbsp;&nbsp;<a class=\"item\" href=\"" . $module->generateUri($currentItem->id) . "\"><button name=\"No\">No</button></a></span></div>\n";
  echo "</form>\n";
}
else
{
  echo "<h3>Reset this user's password?</h3>\n";
  echo "<form name=\"ResetPassword\" action=\"" . $module->generateUri($currentItem->id, $method) . "\" method=\"post\">\n";
  echo "<div class=\"field\"><span class=\"blankLabel\"><span class=\"data\"><button type=\"submit\">Yes</button>&nbsp;&nbsp;&nbsp;&nbsp;<a class=\"item\" href=\"" . $module->generateUri($currentItem->id) . "\"><button name=\"No\">No</button></a></span></div>\n";
  echo "</form>\n";
}
