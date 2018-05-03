<?php
if (isset($failure))
{
  echo "<div class=\"LimboniaMethodFailure\">$failure</div>\n";
}

echo "Please enter the new password below:\n";
echo "<form name=\"Edit\" action=\"" . $module->generateUri($method) . "\" method=\"post\">\n";
echo $module->getFormField('Password', '', ['Type' => 'password'], true) . "\n";
echo '<div class="field"><span class="blankLabel"></span><span class="data"><input type="submit" name="Update" value="Update">&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="No" value="No" onClick="history.go(-1)"></span></div>' . "\n";
echo '</form>';