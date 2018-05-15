<?php
if (isset($success))
{
  echo "<div class=\"methodSuccess\">$success</div>\n";
}

if (isset($failure))
{
  echo "<div class=\"methodFailure\">$failure</div>\n";
}

if ($controller->api->subAction == 'edit' && isset($category))
{
  if ($category->id == $currentItem->topCategory->id)
  {
    $sNameField = $module->getField('Name', $category->name, ['Type' => 'varchar']);
  }
  else
  {
    $sNameField = $module->getFormField('Name', $category->name, ['Type' => 'varchar']);
  }

  echo "<h2>Edit Project Category</h2>
<form action=\"" . $module->generateUri($currentItem->id, $method, $category->id, 'edit') . "\" method=\"post\">
" . $sNameField . "
<div class=\"field\">
  <span class=\"label\">User</span>
  <span class=\"data\">
    <select name=\"" . $module->getType() . "[UserID]\">
      <option value=\"0\">None</option>\n";

  foreach ($internalUserList as $user)
  {
    $sSelected = $category->userID == $user->id ? ' selected="selected"' : '';
    echo "      <option value=\"$user->id\"$sSelected>$user->name</option>\n";
  }

  echo "    </select>
    </span>
  </div>
<div class=\"field\"><span class=\"blankLabel\"></span><span class=\"data\"><button type=\"submit\" name=\"Update\">Update</button>&nbsp;&nbsp;&nbsp;&nbsp;<a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method) . "\"><button name=\"No\">No</button></a></span></div>
</form>\n";
}
elseif ($controller->api->subAction == 'delete' && isset($category))
{
  echo "<h2>Delete Project Category</h2>
<form action=\"" . $module->generateUri($currentItem->id, $method, $category->id, 'delete') . "\" method=\"post\">
<input type=\"hidden\" name=\"havePosted\" value=\"1\">
Are you sure you want to delete $currentItem->name's category \"$category->name\"?
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

  foreach ($currentItem->getCategoryList() as $category)
  {
    $sDelete = $category->id == $currentItem->topCategory->id ? '' : " | <a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method, $category->id, 'delete') . "\">Delete</a>";
    echo "  <div>
    <span>$category->name [<a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method, $category->id, 'edit') . "\">Edit</a>$sDelete]</span>
    <span>{$category->user->name}</span>
  </div>\n";
  }

  echo "  <footer>
    <span>
      <input type=\"text\" name=\"Name\" placeholder=\"New Category Name\" />
      <select name=\"UserID\">
        <option value=\"0\">Select User</option>\n";

  foreach ($internalUserList as $user)
  {
    echo "        <option value=\"$user->id\">$user->name</option>\n";
  }

  echo "      </select>
      <button type=\"submit\">Create Category</button>
    </span>
  </footer>
</div>
</form>\n";
}