<?php
if (isset($failure))
{
  echo "<div class=\"methodFailure\">$failure</div>\n";
}

echo "Please enter the new password below:
<form name=\"Edit\" action=\"" . $module->generateUri($method) . "\" method=\"post\">
" . $module->getFormField('Password', '', ['Type' => 'password'], true) . "
" . \Limbonia\Module::field('<button type="submit" name="Update">Update</button>&nbsp;&nbsp;&nbsp;&nbsp;<a class="item" href="' . $module->generateUri() . '"><button name="No">No</button></a>') . "
</form>\n";