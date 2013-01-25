<?php

/**
 * Gather and record information of what's needed to load and show requested page or to
 * execute called function (when through ajax)
 * Basically, we need to know what page we will load, what module it belongs to, and which
 * elements belong to that module's menu.
 *
 * We check that this page exists and that the user has enough privileges to see it. If any
 * of those fail, we fall back to the default page and alert the user.
 *
 * Once we know the module, we need to include the right scripts. For ajax calls this is the
 * pages script (pages.php), the common functions script (functions.php) and the ajax-related
 * script (ajax.php) that contains ajax functions and returns a list of functions to register
 * to xajax.
 * For regular calls that will load the whole page, we also need to include the JScripts and
 * CSS stylesheets, as well as to configure PageCfg parameters and debugger (if devMode).
 *
 *
 */

# Current page and input attributes
$cPag = oNav()->getCurrentPage();

# Make sure user has permission to access current page, or fall back to default page
if (!isXajax() && oPermits()->cant($cPag))
{
	$cPag = DEFAULT_PAGE;
}

# Module the current page belongs to (be it the page the user wanted or the default)
$cMod = oPermits()->getModuleFromPage($cPag);

if (!isXajax())
{
	# List of available modules and list of pages in current module (for menu)
	$mods = oPermits()->getModules();
	$pags = oPermits()->getPagesFromModule( $cMod );

	# Register current coordinates info in PageCfg for template use
	oPageCfg()->add_develMsgs("Módulo: {$mods[$cMod]['name']} ({$cMod})");
	oPageCfg()->add_develMsgs("Página: {$pags[$cPag]['name']} ({$cPag})");

	oPageCfg()->add_modules($mods);
	oPageCfg()->set_module($cMod);

	# Highlight current menu item (and store it in case next page is not in menu)
	$menuItem = $pags[$cPag]['id_area'] ? $cPag : getSes('menuItem');

	oPageCfg()->add_jsOnLoad("flagMenuItem('{$menuItem}');");

	regSes('menuItem', $menuItem);
}

/**
* Include basic module files, shared for both regular and xajax calls
*/
require_once MODS_PATH . '/pages.php';
require_once MODS_PATH . '/funcs.php';

foreach (['_common', $cMod] as $code)
{
	$path = MODS_PATH . "/{$code}/ajax.php";

	# Include module's xajax script and register all returned functions
	$fList = is_file($path) ? (require_once $path) : array();

	foreach ((array)$fList as $f)
	{
		function_exists($f) && oXajax()->registerFunction($f);
	}
}


# Xajax calls have all scripts they need at hand [back in index it calls processRequests()]
if (isXajax())
{
	return;
}


/**
* From here on, only regular calls apply. We include styles, jScripts, PageCfg options, etc.
*/

# Add module button to navigation menu
oPageCfg()->add_pageNav($mods[$cMod]['name'], $cMod);
oPageCfg()->add_pageNav($pags[$cPag]['name'], $cPag);

$js = SCRIPTS_PATH . "/{$cMod}.js";
is_file($js) && oPageCfg()->add_jScripts($js);

# Build Menu (non-developed pages don't have an ID, and they're grayed out by PageCfg)
foreach ($pags as $code => $page)
{
	if (!function_exists("page_{$code}") && $page['id_area'] != 'global')
	{
		$code = NULL;
	}

	oPageCfg()->add_menuItems($page['area'], $page['name'], $code);
}

# Tell the page to load current page as soon as the DOM is ready for it
oPageCfg()->add_jsOnLoad("loadContent('".oNav()->getCode()."');");


/**
* Final arrangements for the page: these are common to all modules
* Here we build the modules list for navigation bar, and add common entries to the menu
*/

# Navigation bar (modules)
foreach( $mods as $key => $val ) oPageCfg()->add_navButtons( $key );

# Add logout button to navigation bar
oPageCfg()->add_navButtons('logout', false, 'Cerrar sesión');

# Display menu (it's hidden when no session -not the case, since we're here)
showMenu();