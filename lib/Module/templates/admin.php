<?php
$sAdminNav = "No modules were found!\n";
$sModuleNav = '';
$iGroup = count($_SESSION['ModuleGroups']);

if ($iGroup > 0)
{
  $sAdminNav = '';
  $iMinGroups = array_key_exists('Hidden', $_SESSION['ModuleGroups']) ? 2 : 1;

  foreach ($_SESSION['ModuleGroups'] as $sGroup => $hModuleList)
  {
    if ($iGroup > $iMinGroups && $sGroup !== 'Hidden')
    {
      $sAdminNav .= "      <div class=\"group\">$sGroup</div>\n";
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

if (isset($moduleOutput))
{
  if (isset($currentItem) && $currentItem->id > 0)
  {
    $sTemp = $moduleOutput;
    $sJsonData = json_encode
    ([
      'moduleType' => $module->getType(),
      'itemTitle' => $module->getCurrentItemTitle(),
      'action' => $method,
      'subMenu' => $module->getSubMenuItems(true),
      'id' => $currentItem->id,
      'itemUri' => $module->generateUri($currentItem->id)
    ]);
    $moduleOutput = "<script type=\"text/javascript\">
   updateAdminNav('" . $module->getType() . "');
   buildItem($sJsonData);
   $('#item > #page').html(" . json_encode($sTemp) . ");
</script>\n";
  }
  else
  {
    $moduleOutput .= "<script type=\"text/javascript\">updateAdminNav('" . $module->getType() . "');</script>\n";
  }
}
else
{
  $moduleOutput = '';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Omniverse Admin</title>
  <link rel="stylesheet" type="text/css" href="<?= $controller->domain->uri . '/' . $controller->getDir('share') ?>/admin.css" />
  <script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
  <script type="text/javascript" src="<?= $controller->domain->uri . '/' . $controller->getDir('share') ?>/admin.js"></script>
  <script type="text/javascript" src="<?= $controller->domain->uri . '/' . $controller->getDir('share') ?>/slideout-1.0.1/dist/slideout.min.js"></script>
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
  <header><span class="hamburger">☰</span><span>User: <?= $controller->oUser->name ?></span><span class="tools"><a class="item" href="<?= $controller->generateUri('profile') ?>">Profile</a> | <a href="<?= $controller->generateUri('logout') ?>" target="_top">Logout</a></span></header>
  <section id="admin">
    <nav class="moduleList" id="menu">
<?= $sAdminNav ?>
    </nav>
    <section id="content">
      <nav class="tabSet">
<?= $sModuleNav ?>
      </nav>
      <div id="moduleOutput">
<?= $moduleOutput ?>
      </div>
    </section>
  </section>
</body>
</html>