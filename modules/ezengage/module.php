<?php
$Module = array( 'name'            => 'ezengage',
                 'variable_params' => true );

$ViewList = array();
$ViewList[ 'message' ] = array(
    'script'    => 'message.php',
    'functions' => array()
);
$ViewList[ 'get_objects' ] = array(
    'script'    => 'get_objects.php',
    'functions' => array()
);

$ViewList[ 'social_insights' ] = array(
    'functions' => array( 'social_insights' )
    , 'default_navigation_part' => 'ezengagenavigationpart'
    , 'script'    => 'social_insights.php'
);

$ViewList[ 'youtube_query' ] = array(
    'functions' => array( 'social_media_query' )
    , 'default_navigation_part' => 'ezengagenavigationpart'
    , 'script'    => 'youtube_query.php'
);

$ViewList[ 'dashboard' ] = array(
    'functions' => array( 'dashboard' )
    , 'default_navigation_part' => 'ezengagenavigationpart'
    , 'script'    => 'dashboard.php'
);

$ViewList[ 'chart_js_test' ] = array(
    'functions' => array( 'dashboard' )
    , 'default_navigation_part' => 'ezengagenavigationpart'
    , 'script'    => 'chart_js_test.php'
);

$ViewList[ 'google_cse_seo' ] = array(
    'functions' => array( 'google_cse_seo' )
    , 'default_navigation_part' => 'ezengagenavigationpart'
    , 'script'    => 'google_cse_seo.php'
);

$FunctionList = array();
$FunctionList[ 'social_insights' ] = array();
$FunctionList[ 'social_media_query' ] = array();
$FunctionList[ 'dashboard' ] = array();
$FunctionList[ 'google_cse_seo' ] = array();
?>