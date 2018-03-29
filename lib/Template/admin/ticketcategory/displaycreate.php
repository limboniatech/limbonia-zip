<?php
$sModuleType = $module->getType();
echo "<script type=\"text/javascript\" src=\"" . $controller->domain->uri . '/' . $controller->getDir('share') . "/admin_ticketcategory.js\"></script>
<form name=\"Create\" action=\"" . $module->generateUri('create') . "\" method=\"post\" enctype=\"multipart/form-data\">
  <div class=\"field\"><span class=\"label\">Name</span><span class=\"data\"><input type=\"text\" name=\"{$sModuleType}[Name]\" id=\"{$sModuleType}[Name]\" value=\"\"></span></div>
  <div class=\"field\">
  <span class=\"label\">Assignment Method</span>
  <span class=\"data\">
    <select name=\"{$sModuleType}[AssignmentMethod]\" id=\"{$sModuleType}AssignmentMethod\" onchange=\"toggleMethod(this.value);\">\n";

foreach ($currentItem->assignmentMethodList as $assignmentMethod)
{
  $sSelected = $assignmentMethod == 'unassigned' ? ' selected="selected"' : '';
  echo "      <option value=\"$assignmentMethod\"{$sSelected}>" . ucwords($assignmentMethod) . "</option>\n";
}

echo "    </select>
  </span>
</div>
<div id=\"user\" class=\"field\" style=\"display: none;\">
  <span class=\"label\">User</span>
  <span class=\"data\">
    <select name=\"{$sModuleType}[UserID]\" id=\"{$sModuleType}UserID\">
      <option value=\"\">Select a user</option>\n";

foreach (\Limbonia\Item::search('User', ['Active' => true, 'Visible' => true]) as $user)
{
  echo "      <option value=\"$user->id\">" . ucwords($user->name) . "</option>\n";
}

echo "    </select>
  </span>
</div>
<div id=\"key\" class=\"field\" style=\"display: none;\">
  <span class=\"label\">Required resource</span>
  <span class=\"data\">
    <select name=\"{$sModuleType}[KeyID]\" id=\"{$sModuleType}KeyID\">
      <option value=\"\">None</option>\n";

foreach ($currentItem->search('ResourceKey') as $key)
{
  echo "      <option value=\"$key->id\">" . ucwords($key->name) . "</option>\n";
}

echo "    </select>
  </span>
</div>
<div id=\"level\" class=\"field\" style=\"display: none;\">
  <span class=\"label\">Level</span><span class=\"data\"><input type=\"text\" name=\"{$sModuleType}[Level]\" id=\"{$sModuleType}[Level]\" value=\"0\"></span>
</div>
<div class=\"field\"><span class=\"blankLabel\"></span><span class=\"data\"><input type=\"reset\" value=\"Reset\">&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Create!\"></span></div>
</form>\n";