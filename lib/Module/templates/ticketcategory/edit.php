<?php
if (isset($close))
{
  echo "<script type=\"text/javascript\">history.go(-2);</script>\n";
}
else
{
  $sModuleType = $module->getType();
  echo "<script type=\"text/javascript\" src=\"" . $controller->domain->uri . '/' . $controller->getDir('share') . "/admin_ticketcategory.js\"></script>\n";
  echo "<form name=\"Edit\" action=\"" . $module->generateUri($currentItem->id, $method) . "\" method=\"post\">\n";
  echo "<div class=\"field\"><span class=\"label\">Name</span><span class=\"data\"><input type=\"text\" name=\"{$sModuleType}[Name]\" id=\"{$sModuleType}[Name]\" value=\"" . ucwords($currentItem->name) . "\"></span></div>\n";
  echo "<div class=\"field\">\n";
  echo "  <span class=\"label\">Assignment Method</span>\n";
  echo "  <span class=\"data\">\n";
  echo "    <select name=\"{$sModuleType}[AssignmentMethod]\" id=\"{$sModuleType}AssignmentMethod\" onchange=\"toggleMethod(this.value);\">\n";

  foreach ($currentItem->assignmentMethodList as $assignmentMethod)
  {
    $sSelected = $assignmentMethod == $currentItem->assignmentMethod ? ' selected="selected"' : '';
    echo "      <option value=\"$assignmentMethod\"{$sSelected}>" . ucwords($assignmentMethod) . "</option>\n";
  }

  echo "</select>
    </span>
  </div>
  <div id=\"user\" class=\"field\" style=\"display: none;\">
    <span class=\"label\">User</span>
    <span class=\"data\">
      <select name=\"{$sModuleType}[UserID]\" id=\"{$sModuleType}UserID\">
        <option value=\"\">Select a user</option>\n";

  foreach (\Omniverse\Item::search('User', ['Active' => true, 'Visible' => true]) as $user)
  {
    $sSelected = $user->id == $currentItem->userID ? ' selected="selected"' : '';
    echo "      <option value=\"$user->id\"$sSelected>" . ucwords($user->name) . "</option>\n";
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
    $sSelected = $key->id == $currentItem->keyID ? ' selected="selected"' : '';
    echo "      <option value=\"$key->id\"$sSelected>" . ucwords($key->name) . "</option>\n";
  }

  echo "    </select>
    </span>
  </div>
  <div id=\"level\" class=\"field\" style=\"display: none;\">
    <span class=\"label\">Level</span><span class=\"data\"><input type=\"text\" name=\"{$sModuleType}[Level]\" id=\"{$sModuleType}[Level]\" value=\"$currentItem->level\"></span>
  </div>
  <div class=\"field\"><span class=\"blankLabel\"></span><span class=\"data\"><input type=\"submit\" name=\"Update\" value=\"Update\">&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"submit\" name=\"No\" value=\"No\" onClick=\"history.go(-1)\"></span></div>
  </form>
  <script type=\"text/javascript\">toggleMethod('$currentItem->assignmentMethod');</script>\n";
}