<?php

devMode() || die();

ob_start();

Template::one()->display('_debug/statsHead.tpl');

echo "<h3 style='width:200px;'>QUEUED MSG</h3>";
print_r( $_SESSION['queuedMsg'] );

echo "<h3 style='width:200px;'>SESSION</h3>";
print_r( $_SESSION['crm'] );

echo "<h3>NAV</h3>";
print_r( $_SESSION['nav'] );

Template::one()->display('_debug/statsFoot.tpl');

ob_flush();

die();