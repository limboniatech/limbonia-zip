<?php
if (isset($success))
{
  echo "<div class=\"methodSuccess\">$success</div>\n";
}

if (isset($failure))
{
  echo "<div class=\"methodFailure\">$failure</div>\n";
}

if ($controller->api->subAction == 'edit' && isset($element))
{
  echo "<h2>Edit Software Element</h2>
<form action=\"" . $module->generateUri($currentItem->id, $method, $element->id, 'edit') . "\" method=\"post\">
" . $module->getFormField('Name', $element->name, ['Type' => 'varchar']) . "
<div class=\"field\">
  <span class=\"label\">User</span>
  <span class=\"data\">
    <select name=\"" . $module->getType() . "[UserID]\">
      <option value=\"0\">None</option>\n";

  foreach ($internalUserList as $user)
  {
    $sSelected = $element->userID == $user->id ? ' selected="selected"' : '';
    echo "      <option value=\"$user->id\"$sSelected>$user->name</option>\n";
  }

  echo "    </select>
    </span>
  </div>
<div class=\"field\"><span class=\"blankLabel\"></span><span class=\"data\"><button type=\"submit\" name=\"Update\">Update</button>&nbsp;&nbsp;&nbsp;&nbsp;<a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method) . "\"><button name=\"No\">No</button></a></span></div>
</form>\n";
}
elseif ($controller->api->subAction == 'delete' && isset($element))
{
  echo "<h2>Delete Software Element</h2>
<form action=\"" . $module->generateUri($currentItem->id, $method, $element->id, 'delete') . "\" method=\"post\">
Are you sure you want to delete $currentItem->name's element \"$element->name\"?
<button type=\"submit\" name=\"Yes\">Yes</button>&nbsp;&nbsp;&nbsp;&nbsp;<a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method) . "\"><button name=\"No\">No</button></a></form>\n";
}
else
{
  echo "<form action=\"" . $module->generateUri($currentItem->id, $method, 'create') . "\" method=\"post\">
<div class=\"display\">
  <header>
    <span>Name</span>
    <span>User</span>
  </header>\n";

  foreach ($currentItem->getElementList() as $element)
  {
    echo "  <div>
    <span>$element->name [<a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method, $element->id, 'edit') . "\">Edit</a> | <a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method, $element->id, 'delete') . "\">Delete</a>]</span>
    <span>{$element->user->name}</span>
  </div>\n";
  }

  echo "  <footer>
    <span>
      <input type=\"text\" name=\"Name\" placeholder=\"New Element Name\" />
      <select name=\"UserID\">
        <option value=\"0\">Select User</option>\n";

  foreach ($internalUserList as $user)
  {
    echo "        <option value=\"$user->id\">$user->name</option>\n";
  }

  echo "      </select>
      <button type=\"submit\">Create Element</button>
    </span>
  </footer>
</div>
</form>\n";
}