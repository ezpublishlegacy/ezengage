<?php

$ini = eZINI::instance('ezengage.ini');

$youtubeQuery = YoutubeQuery::instance();

$tpl = eZTemplate::factory();

$tpl->setVariable( "activities", $youtubeQuery->query('activities', 'list', ['channelId'=> 'UC0M0rxSz3IF0CsSour1iWmw'] ) );
$tpl->setVariable( "videos", $youtubeQuery->query('videos', 'list', ['id'=> 'oLWnl2fBy3Q'] ) );
$tpl->setVariable( "commentThreads", $youtubeQuery->query('commentThreads', 'list', ['allThreadsRelatedToChannelId'=> $ini->variable('SocialInsights', 'YoutubeChannel')] ) );
$tpl->setVariable( "subscriptions", $youtubeQuery->query('subscriptions', 'list', ['mySubscribers'=> 'true'] ) );

$Result = array();
$Result['content'] = $tpl->fetch( "design:engage/youtube_query.tpl" );
$Result['left_menu'] = "design:engage/leftmenu.tpl";
$Result['path'] = array( 
  array( 'url' => false, 'text' => 'eZEngage' ),
  array( 'url' => '/ezengage/youtube_query', 'text' => 'Youtube Query' )
);
