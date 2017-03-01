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

$FunctionList = array();
$FunctionList[ 'social_insights' ] = array();
?>