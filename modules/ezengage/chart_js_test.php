<?php

$ini = eZINI::instance('ezengage.ini');

$tpl = eZTemplate::factory();

$Result = array();
$Result['content'] = $tpl->fetch( "design:engage/chart_js_test.tpl" );
$Result['left_menu'] = "design:engage/leftmenu.tpl";
$Result['path'] = array( 
  array( 'url' => false, 'text' => 'eZEngage' ),
  array( 'url' => '/ezengage/chart_js_test', 'text' => 'eZEngage ChartJS' )
);
