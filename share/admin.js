/**
 * The current module name
 *
 * @type String
 */
var sCurrentModule = '';

/**
 * The ID of the current Item
 *
 * @type Number
 */
var iCurrentItemId = 0;

/**
 * Generate and return the module name from the specified URL
 *
 * @param {String} sUrl - The URL to extract the module name from, if there is one
 * @returns {String}
 */
function urlModule(sUrl)
{
  var aMatches = sUrl.match(/^.*admin\/(.*?)(\/|$)/);
  return aMatches && aMatches.length > 0 ? aMatches[1] : '';
}

/**
 * Generate and return the item ID from the specified URL
 *
 * @param {String} sUrl - The URL to extract the item ID from, if there is one
 * @returns {String}
 */
function urlItemId(sUrl)
{
  var aMatches = sUrl.match(/^.*admin\/.*?\/(\d+?)/);
  return aMatches && aMatches.length > 0 ? parseInt(aMatches[1]) : 0;
}

/**
 * Generate the item section from the specified data, then insert it into the DOM
 *
 * @param {Object} oData
 */
function buildItem(oData)
{
  var sLowerModule = oData.moduleType.toLowerCase();
  var sItemNav = '';
  var sPageTitle = oData.moduleType + ' #' + oData.id + ' > ' + oData.itemTitle + ' > ' + oData.action.charAt(0).toUpperCase() + oData.action.slice(1);
  $(document).prop('title', sPageTitle);

  for (var sAction in oData.subMenu)
  {
    var sCurrent = oData.action === sAction ? 'current ' : '';
    sItemNav += '  <a class="item ' + sCurrent + 'tab ' + sLowerModule + ' ' + sAction + '" href="' + oData.itemUri + '/' + sAction + '">' + oData.subMenu[sAction] + '</a>\n';
  }

  if (iCurrentItemId !== oData.id)
  {
    iCurrentItemId = oData.id;
    $('#content > nav.tabSet > span').remove();
    $('#content > nav.tabSet').append('<span class="current tab ' + sLowerModule + ' ' + oData.action + ' noLink">' + oData.moduleType + ' #' + oData.id + '</span>').siblings().removeClass('current');
  }

  $('#moduleOutput').html('      <div id="item">\
      <h2 class="title">' + oData.itemTitle + '</h2>\
      <div class="tabSet">\
' + sItemNav + '\
      </div>\
      <div id="page">\
      </div>\
    </div>\n');
}

/**
 * Update the primary admin navigation based on the specified new module name
 *
 * @param {String} sModuleName - The name of the module to display
 */
function updateAdminNav(sModuleName)
{
  sCurrentModule = sModuleName.toLowerCase();
  $('#admin > nav > a').show();
  $('#admin > nav > .module').hide();
  $('#content > .tabSet > a').hide();

  if (sCurrentModule)
  {
    $('#content > .tabSet > span').not('.' + sCurrentModule).remove();
    $('#admin > nav > a.' + sCurrentModule).hide();
    $('#admin > nav > .module.' + sCurrentModule).show();
    $('#content > .tabSet > .' + sCurrentModule).show();
  }
}

/**
 * Generate and insert data from the specified URL
 *
 * @param {String} sUrl - The URL to generate and insert data from
 * @param {String} sType (optional) - The type of data being inserted (defaults to 'module')
 * @param {String} sFormData (optional) - The form data to submit
 * @param {Boolean} bHasFiles (optional) - The form data contains files to upload...
 */
function updateNav(sUrl, sType, sFormData, bHasFiles)
{
  if (arguments.length < 2) { sType = 'module'; }
  if (arguments.length < 3) { sFormData = false; }
  if (arguments.length < 4) { bHasFiles = false; }

  var sUrlModule = urlModule(sUrl);

  if (sCurrentModule !== sUrlModule)
  {
    updateAdminNav(sUrlModule);
    sType = 'module';
  }

  var iUrlItemId = urlItemId(sUrl);
  var sOverlayId = sUrlModule + iUrlItemId + '-' + Math.floor(1000 * Math.random());

  //if the type is 'item' *and* '#item > #page' exists then use it... otherwise use moduleOutput
  var sOverlayTarget = sType === 'item' && $('#item > #page').length ? '#item > #page' : '#moduleOutput';

  history.pushState(null, '', sUrl);
  sUrlJoinCharacter = sUrl.match(/\?/) ? '&' : '?';

  var hAjaxConfig =
  {
    beforeSend: function()
    {
      var oTarget = $(sOverlayTarget);
      var oTargetPosition = oTarget.position();
      oTarget.append('<div class="overlay" id="' + sOverlayId + '" style="height: ' + oTarget.height() + 'px; left: ' + oTargetPosition.left + 'px; top: ' + oTargetPosition.top + 'px; width: ' + oTarget.width() + 'px;' + '"></div>');
    },
    method: 'GET',
    dataType: 'json',
    url: sUrl + sUrlJoinCharacter + 'ajax=click'
  };

  if (sFormData)
  {
    hAjaxConfig['method'] = 'POST';
    hAjaxConfig['data'] = sFormData;

    if (bHasFiles)
    {
      hAjaxConfig['cache'] = false;
      hAjaxConfig['contentType'] = false;
      hAjaxConfig['processData'] = false;
    }
  }

  $.ajax(hAjaxConfig)
  .done(function(oData, sStatus, oRequest)
  {
    if (oData.replacePage)
    {
      document.open('text/html');
      document.write(oData.replacePage);
      document.close();
    }
    else if (oData.error)
    {
      $('#moduleOutput').html(oData.error);
    }
    else if (oData.action)
    {
      var bQuick = sUrl.match(/search\/quick$/);

      if (bQuick || (oData.id > 0 && oData.subMenu[oData.action]))
      {
        sType = 'item';

        if (bQuick && oData.itemUri && oData.itemUri !== sUrl)
        {
          history.pushState(null, '', oData.itemUri);
        }
      }

      switch (sType)
      {
        case 'item':
          if (oData.id > 0)
          {
            buildItem(oData);
          }

          $('#item > #page').html(oData.moduleOutput);
          $('#item > .tabSet > a.' + oData.moduleType.toLowerCase() + '.' + oData.action).addClass('current').siblings().removeClass('current');
          break;

        default:
          var sPageTitle = oData.moduleType + ' > ' + oData.action.charAt(0).toUpperCase() + oData.action.slice(1);
          $(document).prop('title', sPageTitle);

          $('#moduleOutput').html(oData.moduleOutput);
          $('#content > .tabSet > span').remove();
          $('#content > .tabSet > a.' + oData.moduleType.toLowerCase() + '.' + oData.action).addClass('current').siblings().removeClass('current');
      }
    }
  })
  .always(function()
  {
    $(sOverlayId).remove();
  });
}

$(function()
{
  /**
   * Handle clicks on the module list with AJAX instead of the default URL
   */
  $('#admin').on('click', 'nav.moduleList > a', function(e)
  {
    updateAdminNav($(this).attr('class'));
    updateNav($(this).attr('href'));
    e.preventDefault();
  });

  /**
   * Handle clicks on the current module's tabs with AJAX instead of the default URL
   */
  $('#content').on('click', 'nav.tabSet > a', function(e)
  {
    updateNav($(this).attr('href'));
    e.preventDefault();
  });

  /**
   * Handle clicks on the specified URLs in the moduleOutput with AJAX instead of the default URL
   */
  $('#moduleOutput').on('click', 'a.module', function(e)
  {
    updateNav($(this).attr('href'), 'module');
    e.preventDefault();
  });

  /**
   * Handle clicks on the specified URLs in the moduleOutput with AJAX instead of the default URL
   */
  $('#moduleOutput').on('click', 'a.item', function(e)
  {
    updateNav($(this).attr('href'), 'item');
    e.preventDefault();
  });

  /**
   * Make options that only work if boxes are checked only visible when they are checked
   */
  $('#moduleOutput').on('click', '.OmnisysSortGridCellCheckbox', function()
  {
    var bChecked = $('.OmnisysSortGridCellCheckbox:checked').length > 0;
    $('.OmnisysSortGridDelete').toggle(bChecked);
    $('.OmnisysSortGridEdit').toggle(bChecked);
  });

  /**
   * Handle clicks on the specified URLs in the top header with AJAX instead of the default URL
   */
  $('body > header').on('click', 'a.item', function(e)
  {
    updateNav($(this).attr('href'), 'item');
    e.preventDefault();
  });

  /**
   * Handle form submission with AJAX instead of the default URL
   */
  $('#admin').on('submit', 'form', function(e)
  {
    var sUri = $(this).prop('action');
    var sType = urlItemId(sUri) > 0 ? 'item' : 'module';
    var oFormData = null;
    var bHasFiles = false;

    //Look for file input fields
    var aFileInput = $(this).children('input[type=file]');

    //if there any file input fields
    if (aFileInput.length > 0)
    {
      //if the FormData class doesn't exist
      if (!window.FormData)
      {
        //then do nothing else and process this post normally instead of using AJAX
        return;
      }

      //get the form object
      var oForm = $(this);

      //use the form object to generate the the FormData object
      oFormData = new FormData(oForm[0]);
      bHasFiles = true;
    }
    else
    {
      oFormData = $(this).serialize();
    }

    updateNav(sUri, sType, oFormData, bHasFiles);
    e.preventDefault();
  });
});