<?php

$ini = eZINI::instance('ezengage.ini');

$tpl = eZTemplate::factory();


$youtubeTokenData = json_decode( @file_get_contents( eZSys::storageDirectory() . eZSys::instance()->FileSeparator . 'youtube_tokens' ), true );
if( !$youtubeTokenData || !isset( $youtubeTokenData['access_token'] ) )
{
    $tpl->setVariable( "setup_youtube", true );
    $tpl->setVariable( "youtube_redirect", eZSys::serverURL() . '/ezengage/youtube_query' );
}

$Result = array();
$Result['content'] = $tpl->fetch( "design:engage/dashboard.tpl" );
$Result['left_menu'] = "design:engage/leftmenu.tpl";
$Result['path'] = array( 
  array( 'url' => false, 'text' => 'eZEngage' ),
  array( 'url' => '/ezengage/social_insights', 'text' => 'eZEngage Dashboard' )
);
