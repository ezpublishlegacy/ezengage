<?php

$ini = eZINI::instance('ezengage.ini');

$youtubeQuery = YoutubeQuery::instance();

$tpl = eZTemplate::factory();

$http = eZHTTPTool::instance();

if( $http->hasVariable( 'run_test') )
{
    $tpl->setVariable( "activities", $youtubeQuery->query('activities', 'list', ['channelId'=> 'UC0M0rxSz3IF0CsSour1iWmw'] ) );
    $tpl->setVariable( "videos", $youtubeQuery->query('videos', 'list', ['id'=> 'oLWnl2fBy3Q'] ) );
    $tpl->setVariable( "commentThreads", $youtubeQuery->query('commentThreads', 'list', ['allThreadsRelatedToChannelId'=> $ini->variable('SocialInsights', 'YoutubeChannel')] ) );
    $tpl->setVariable( "subscriptions", $youtubeQuery->query('subscriptions', 'list', ['mySubscribers'=> 'true'] ) );
    $tpl->setVariable( "run_test", true );
}

if( $http->hasVariable( 'get_channel_keywords_id' ) )
{
     $result = $youtubeQuery->query('channels', 'list', [ 'part' => 'brandingSettings', 'id'=>  $http->variable( 'get_channel_keywords_id' ) ] );
     if( isset( $result['items'] ) && !empty($result['items']) )
     {
         $tpl->setVariable( "get_channel_keywords_result", $result['items'][0] );
     }
     else
     {
         $tpl->setVariable( "get_channel_keywords_result", false );
     }
}

if( $http->hasVariable( 'get_video_tags_id' ) )
{
     $result = $youtubeQuery->query('videos', 'list', [ 'part' => 'snippet', 'id'=>  $http->variable( 'get_video_tags_id' ) ] );
     if( isset( $result['items'] ) && !empty($result['items']) )
     {
         $tpl->setVariable( "get_video_tags_result", $result['items'][0] );
     }
     else
     {
         $tpl->setVariable( "get_video_tags_result", false );
     }
}


$Result = array();
$Result['content'] = $tpl->fetch( "design:engage/youtube_query.tpl" );
$Result['left_menu'] = "design:engage/leftmenu.tpl";
$Result['path'] = array( 
  array( 'url' => false, 'text' => 'eZEngage' ),
  array( 'url' => '/ezengage/youtube_query', 'text' => 'Youtube Query' )
);
