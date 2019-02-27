<?php
$sAdminNav = '';
$sModuleNav = '';
$iGroup = count($_SESSION['ModuleGroups']);
$sPageTitle = 'Limbonia Admin';
$system_menu = '';
$profile_menu = '';

if ($controller->user()->isAdmin())
{
  $system_menu = '<a class="item" href="' . $controller->generateUri('system')  . '">System</a> | ';
}

if (isset($_SESSION['ResourceList']['Profile']) && isset($controller->activeModules()['profile']))
{
  $profile_menu = '<a class="item" href="' . $controller->generateUri('profile') . '">Profile</a> | ';
}

if ($iGroup > 0)
{
  $iMinGroups = array_key_exists('Hidden', $_SESSION['ModuleGroups']) ? 2 : 1;

  foreach ($_SESSION['ModuleGroups'] as $sGroup => $hModuleList)
  {
    if ($iGroup > $iMinGroups && $sGroup !== 'Hidden')
    {
      $sAdminNav .= "      <div class=\"moduleGroup\">$sGroup</div>\n";
    }

    foreach ($hModuleList as $sLabel => $sModuleName)
    {
      $sLowerModule = strtolower($sModuleName);
      $oModule = $controller->moduleFactory($sModuleName);

      if ($sGroup !== 'Hidden')
      {
        $sAdminNav .= "      <div class=\"module $sLowerModule\" style=\"display: none\">\n";
        $sAdminNav .= "        <div class=\"title\">" . preg_replace("/([A-Z])/", " $1", $oModule->getType()) . "</div>\n";

        $hQuickSearch = $oModule->getQuickSearch();

        if (!empty($hQuickSearch) && $oModule->allow('search'))
        {
          $sModuleType = $oModule->getType();

          foreach ($hQuickSearch as $sColumn => $sTitle)
          {
            $sAdminNav .= "        <form name=\"QuickSearch\" action=\"" . $oModule->generateUri('search', 'quick') . "\" method=\"post\">$sTitle:<input type=\"text\" name=\"{$sModuleType}[{$sColumn}]\" id=\"{$sModuleType}{$sColumn}\"></form>\n";
          }
        }

        $sAdminNav .= "      </div>\n";
        $sAdminNav .= "      <a class=\"$sLowerModule\" href=\"" . $controller->generateUri($sLabel) . "\">" . preg_replace("/([A-Z])/", " $1", $sModuleName) . "</a>\n";
      }

      foreach ($oModule->getMenuItems() as $sMenuAction => $sMenuTitle)
      {
        if (!$oModule->allow($sMenuAction))
        {
          continue;
        }

        if ($sMenuAction !== 'item')
        {
          $sCurrent = isset($method) && $method == $sMenuAction ? 'current ' : '';
          $sDisplay = isset($module) && $oModule->getType() == $module->getType() ? '' : ' style="display: none"';
          $sModuleNav .= "        <a class=\"item {$sCurrent}tab $sLowerModule $sMenuAction\"$sDisplay href=\"" . $oModule->generateUri($sMenuAction) . "\">$sMenuTitle</a>\n";
        }
      }
    }
  }
}

if (empty($sAdminNav))
{
  $sAdminNav = "No modules were found!<br>Try either: <a href=\"" . $controller->generateUri('setup') . "\">Setup</a><a href=\"" . $controller->generateUri('system', 'managemodules') . "\">Manage Modules</a>";
}

if (!empty($content))
{
  $sTemp = $content;
  $content = "<script type=\"text/javascript\">
 updateAdminNav('" . $module->getType() . "');\n";

  if (method_exists($module, 'getItem') && $module->getItem()->id > 0)
  {
    $content .= "   buildItem(" . json_encode($module->getAdminOutput()) . ");
 $('#item > #page').html(" . json_encode($sTemp) . ");\n";
  }
  else
  {
    $sPageTitle = ucwords($module->getType() . " > $method");
    $content .= "   $('#moduleOutput').html(" . json_encode($sTemp) . ");\n";
  }

  $content .= "\n</script>\n";
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="Lonnie Blansett">
  <meta name="generator" content="Limbonia <?= \Limbonia\Controller::version() ?>">
  <meta name="build-date" content="<?= \Limbonia\Controller::buildDate() ?>">
  <title><?= $sPageTitle ?></title>
  <link rel="stylesheet" type="text/css" href="<?= $controller->domain->uri . '/' . $controller->getDir('share') ?>/admin.css" />
<?php
if ($controller->debug)
{
  echo "  <script type=\"text/javascript\" src=\"" . $controller->domain->uri . '/' . $controller->getDir('share') . "/node_modules/jquery/dist/jquery.js\"></script>\n";
  echo "  <script type=\"text/javascript\" src=\"" . $controller->domain->uri . '/' . $controller->getDir('share') . "/node_modules/slideout/dist/slideout.js\"></script>\n";
  echo "  <script type=\"text/javascript\" src=\"" . $controller->domain->uri . '/' . $controller->getDir('share') . "/admin.js\"></script>\n";
  echo "  <script type=\"text/javascript\" src=\"" . $controller->domain->uri . '/' . $controller->getDir('share') . "/ajax.js\"></script>\n";
  echo "  <script type=\"text/javascript\" src=\"" . $controller->domain->uri . '/' . $controller->getDir('share') . "/select.js\"></script>\n";
  echo "  <script type=\"text/javascript\" src=\"" . $controller->domain->uri . '/' . $controller->getDir('share') . "/sorttable.js\"></script>\n";
  echo "  <script type=\"text/javascript\" src=\"" . $controller->domain->uri . '/' . $controller->getDir('share') . "/window.js\"></script>\n";
}
else
{
  echo "  <script type=\"text/javascript\" src=\"" . $controller->domain->uri . '/' . $controller->getDir('share') . "/admin-all-min.js\"></script>\n";
}
?>
  <script type="text/javascript">
  $(function()
  {
    var slideout = new Slideout
    ({
      'panel': document.getElementById('content'),
      'menu': document.getElementById('menu'),
      'padding': 1,
      'tolerance': 70
    });
    $('.hamburger').on('click', function()
    {
      slideout.toggle();
    });
  });
  </script>
</head>
<body>

  <header>
    <span class="hamburger">☰</span>
    <span>User: <?= $controller->oUser->name ?></span>
    <span class="tools"><?= $system_menu ?><?= $profile_menu ?><a href="<?= $controller->generateUri('logout') ?>" target="_top">Logout</a></span>
  </header>

  <section id="admin">
    <nav class="moduleList" id="menu">
<?= $sAdminNav ?>
    </nav>
    <section id="content">
      <nav class="tabSet">
<?= $sModuleNav ?>
      </nav>
      <main id="moduleOutput">
<?= $content ?>
      </main>
    </section>
  </section>

</body>
</html>