<?php

$ini = eZINI::instance('ezengage.ini');

$sentiment = new \PHPInsight\Sentiment();
$socialInsights = array();


$url = 'https://disqus.com/api/3.0/posts/list.json';
$params = array();
$params['api_secret'] = $ini->variable('SocialInsights', 'DisqusKey');
$params['forum'] = $ini->variable('SocialInsights', 'DisqusForum');
$params['limit'] = '100';
$params['related[]'] = 'thread';

$socialInsights += array( 'Disqus' => array( 
                                'neg' => array()
                                , 'neu' => array()
                                , 'pos' => array() ) );

$disqusComments = json_decode( EngageHelper::getData( $url, $params ), true );


foreach( $disqusComments['response'] as $disqusComment )
{
    $scores = $sentiment->score($disqusComment['raw_message']);
    $class = $sentiment->categorise($disqusComment['raw_message']);
    $socialInsights['Disqus'][$class][] = array( 
                                'username' => $disqusComment['author']['username']
                                , 'name' => $disqusComment['author']['name']
                                , 'comment' => $disqusComment['raw_message']
                                , 'link' => $disqusComment['thread']['link']
                                , 'scores' => $scores
    );
}

$socialInsights += array( 'Youtube' => array( 
                                'neg' => array()
                                , 'neu' => array()
                                , 'pos' => array() ) );
$params = array();
$params['allThreadsRelatedToChannelId'] = $ini->variable('SocialInsights', 'YoutubeChannel');
$params['part'] = 'id,snippet,replies';
 $youtubeQuery = YoutubeQuery::instance();
$youtubeComments = $youtubeQuery->query('commentThreads', 'list', $params );

foreach( $youtubeComments['items'] as $youtubeComment )
{
    $scores = $sentiment->score($youtubeComment['snippet']['topLevelComment']['snippet']['textDisplay']);
    $class = $sentiment->categorise($youtubeComment['snippet']['topLevelComment']['snippet']['textDisplay']);
    $videoID = $youtubeComment['snippet']['videoId'];
    $socialInsights['Youtube'][$class][] = array( 
                                'username' => $youtubeComment['snippet']['topLevelComment']['snippet']['authorDisplayName']
                                , 'name' => $youtubeComment['snippet']['topLevelComment']['snippet']['authorDisplayName']
                                , 'comment' => $youtubeComment['snippet']['topLevelComment']['snippet']['textDisplay']
                                , 'link' => 'https://www.youtube.com/watch?v=' . $videoID . '&google_comment_id=' . $youtubeComment['snippet']['topLevelComment']['id']
                                , 'scores' => $scores
    );
    if( isset( $youtubeComment['replies'] ) )
    {
        foreach( $youtubeComment['replies']['comments'] as $youtubeReply )
        {
            $scores = $sentiment->score($youtubeReply['snippet']['textDisplay']);
            $class = $sentiment->categorise($youtubeReply['snippet']['textDisplay']);
            $videoID = $youtubeComment['snippet']['videoId'];
            $socialInsights['Youtube'][$class][] = array( 
                                        'username' => $youtubeReply['snippet']['authorDisplayName']
                                        , 'name' => $youtubeReply['snippet']['authorDisplayName']
                                        , 'comment' => $youtubeReply['snippet']['textDisplay']
                                        , 'link' => 'https://www.youtube.com/watch?v=' . $videoID . '&google_comment_id=' . $youtubeReply['id']
                                        , 'scores' => $scores
            );
        }
    }
}

$consumer_key = $ini->variable('SocialInsights', 'TwitterKey');
$consumer_secret = $ini->variable('SocialInsights', 'TwitterSecret');
$token = $ini->variable('SocialInsights', 'TwitterTokenKey');
$secret = $ini->variable('SocialInsights', 'TwitterTokenSecret');

$socialInsights += array( 'Twitter' => array( 
                                'neg' => array()
                                , 'neu' => array()
                                , 'pos' => array() ) );

$connection = new Abraham\TwitterOAuth\TwitterOAuth($consumer_key, $consumer_secret, $token, $secret);  
$twitterComments = $connection->get('/search/tweets', array( 'q' => $ini->variable('SocialInsights', 'TwitterQuery'), 'lang' => 'en' ) );
$twitterComments = json_decode(json_encode($twitterComments), true);
foreach($twitterComments['statuses'] as $twitterComment)
{
    $scores = $sentiment->score($twitterComment['text']);
    $class = $sentiment->categorise($twitterComment['text']);
    $socialInsights['Twitter'][$class][] = array( 
                                'username' => $twitterComment['user']['screen_name']
                                , 'name' => $twitterComment['user']['name']
                                , 'comment' => $twitterComment['text']
                                , 'link' => 'https://www.twitter.com/' . $twitterComment['user']['screen_name'] . '/status/' . $twitterComment['id']
                                , 'scores' => $scores
    );
}

$tpl = eZTemplate::factory();
$tpl->setVariable( "socialInsights", $socialInsights );

$Result = array();
$Result['content'] = $tpl->fetch( "design:engage/social_insights.tpl" );
$Result['left_menu'] = "design:engage/leftmenu.tpl";
$Result['path'] = array( 
  array( 'url' => false, 'text' => 'eZEngage' ),
  array( 'url' => '/ezengage/social_insights', 'text' => 'Social Insights' )
);
