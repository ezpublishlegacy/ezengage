<?php

class YoutubeQuery
{
    private $tokenData = null;
    private $ini;
    private $http;
    private static $instance = null;
    /**
     * Call this method to get singleton
     *
     * @return YoutubeQuery
     */
    public static function instance()
    {
        if ( !isset( static::$instance ) ) {
            static::$instance = new static;
        }
        return static::$instance;
    }
    /**
     * Private constructor so nobody else can clone it
     *
     */
    private function __clone(){}

    /**
     * Private constructor so nobody else can instance it
     *
     */
    private function __construct()
    {
        $this->ini = eZINI::instance('ezengage.ini');
        $this->http = eZHTTPTool::instance();
        $this->setUpTokens();
    }
    /**
     * 
     * @return type
     */
    public function setUpTokens()
    {
        
        if( !$this->tokenData )
        {
            $this->tokenData = json_decode( @file_get_contents( eZSys::storageDirectory() . eZSys::instance()->FileSeparator . 'youtube_tokens' ), true );
            if( $this->tokenData && isset( $this->tokenData['access_token'] ) )
            {
                $url = 'https://www.googleapis.com/oauth2/v1/tokeninfo';
                $params = [
                    'access_token' => $this->tokenData['access_token'],
                ];
                $result = json_decode( EngageHelper::postData( $url, $params ), true );
                if( $result && !isset( $result['error'] ) )
                {
                    $this->tokenData ['expires_in'] = time() + $result['expires_in'];
                }
                else if( isset( $result['error'] ) && isset( $this->tokenData['refresh_token'] ) )
                {
                    $this->tokenData ['expires_in'] = 0;
                }
                else if( !isset( $this->tokenData['refresh_token'] ) )
                {
                    $this->tokenData = null;
                }
            }
        }
        if( !$this->tokenData  || $this->tokenData ['expires_in'] < time() + 60 )
        {
            if( !isset( $this->tokenData['refresh_token'] ) )
            {
                
                $redirectURI = eZSys::serverURL() . '/ezengage/youtube_query';
                if( !$this->http->hasVariable( 'code' ) )
                {
                    $clientId = $this->ini->variable('SocialInsights', 'YoutubeClientId');
                    $url = "https://accounts.google.com/o/oauth2/auth";
                    $scope = 'https://www.googleapis.com/auth/youtube https://www.googleapis.com/auth/youtube.force-ssl https://www.googleapis.com/auth/youtube.readonly https://www.googleapis.com/auth/youtubepartner';
                    eZHTTPTool::redirect( "{$url}?client_id={$clientId}&redirect_uri={$redirectURI}&scope={$scope}&response_type=code&access_type=offline" );
                    eZExecution::cleanExit();
                }
                else
                {
                    $url = "https://www.googleapis.com/oauth2/v4/token";
                    $params = [
                        'code' => $this->http->variable( 'code' ),
                        'client_id' => $this->ini->variable('SocialInsights', 'YoutubeClientId'),
                        'client_secret' => $this->ini->variable('SocialInsights', 'YoutubeClientSecret'),
                        'redirect_uri' => $redirectURI,
                        'grant_type' => 'authorization_code'
                    ];
                    $result = json_decode( EngageHelper::postData( $url, $params ), true );
                    if( isset( $result['access_token'] ) )
                    {
                        $result['expires_in'] = intval( $result['expires_in'] ) + time();
                        file_put_contents( eZSys::storageDirectory() . eZSys::instance()->FileSeparator . 'youtube_tokens',  json_encode( $result ) );
                        $this->tokenData = $result;
                    }
                }
            }
            else
            {
                $url = 'https://www.googleapis.com/oauth2/v4/token';
                $params = [
                    'refresh_token' => $this->tokenData['refresh_token'],
                    'client_id' => $this->ini->variable('SocialInsights', 'YoutubeClientId'),
                    'client_secret' => $this->ini->variable('SocialInsights', 'YoutubeClientSecret'),
                    'grant_type' => 'refresh_token'
                ];
                $result = json_decode( EngageHelper::postData( $url, $params ), true );
                if( isset( $result['access_token'] ) && isset( $result['expires_in'] ) )
                {
                    $this->tokenData['access_token'] = $result['access_token'];
                    $this->tokenData['expires_in'] = intval( $result['expires_in'] ) + time();
                    file_put_contents( eZSys::storageDirectory() . eZSys::instance()->FileSeparator . 'youtube_tokens',  json_encode( $this->tokenData ) );
                }
                else
                {
                    unlink( eZSys::storageDirectory() . eZSys::instance()->FileSeparator . 'youtube_tokens' );
                }
                
            }
        }
    }

    /**
     * 
     * @param string $resourceType
     * @param string $method
     * @param array $params
     * @return string json array containing the youtube response
     * @throws Exception
     */
    public function query( $resourceType, $method, $params )
    {
        $this->setUpTokens();
        $definition = self::definition();
        if( isset( $definition[$resourceType] ) && isset( $definition[$resourceType]['methods'][$method] ) )
        {
            $params += [ 'access_token' => $this->tokenData['access_token'] ];
            foreach( $definition[$resourceType]['methods'][$method]['fields'] as $fieldKey => $fieldValue )
            {
                if( !isset( $params[$fieldKey] ) && $fieldValue['default'] !== false )
                {
                    $params[$fieldKey] = $fieldValue['default'];
                }
            }
            if( $definition[$resourceType]['methods'][$method]['type'] == 'GET' )
            {
                return json_decode( EngageHelper::getData( $definition[$resourceType]['url'], $params ), true );
            }
        }
        else
        {
            throw new Exception('Resource type or method are not supported');
        }
    }
    /**
     * Check if a certain channel is subscribed to another
     * @param string $subscriberChannel
     * @param string $targetChannel
     * @return boolean
     */
    function isChannelSubscribedTo( $subscriberChannel, $targetChannel )
    {
        $result = $this->query('subscriptions', 'list', ['channelId'=> $subscriberChannel, 'forChannelId' => $targetChannel ] );
        if( isset( $result['pageInfo'] ) && $result['pageInfo']['totalResults'] != 0 )
        {
            return true;
        }
        return false;
    }

    /**
     * 
     * @staticvar array $definition
     * @return array The metainfo about all the possible queries
     */
    static function definition()
    {
        static $definition = array( 
                                    'activities' => array(
                                        'url' => 'https://www.googleapis.com/youtube/v3/activities'
                                        , 'methods' => array(
                                                'list' => array(
                                                    'type' => 'GET'
                                                    , 'fields' => array(
                                                        'part' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'id,contentDetails,snippet'
                                                            , 'default' => 'id,contentDetails'
                                                            , 'notes' => 'required'
                                                        ),
                                                        'channelId' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'this or mine must be present in the query'
                                                        ),
                                                        'mine' => array(
                                                            'type' => 'boolean'
                                                            , 'values' => 'true|false'
                                                            , 'default' => false
                                                            , 'notes' => 'this or channelId must be present in the query'
                                                        ),
                                                        'maxResults' => array(
                                                            'type' => 'integer'
                                                            , 'values' => '0 to 50'
                                                            , 'default' => false
                                                            , 'notes' => 'Acceptable values are 0 to 50, inclusive. The default value is 5'
                                                        ),
                                                        'pageToken' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'nextPageToken or prevPageToken'
                                                            , 'default' => false
                                                            , 'notes' => 'The pageToken parameter identifies a specific page in the result set that should be returned'
                                                        ),
                                                        'publishedAfter' => array(
                                                            'type' => 'datetime'
                                                            , 'values' => 'ISO 8601 (YYYY-MM-DDThh:mm:ss.sZ) format'
                                                            , 'default' => false
                                                            , 'notes' => 'The publishedAfter parameter specifies the earliest date and time that an activity could have occurred for that activity to be included in the API response'
                                                        ),
                                                        'publishedBefore' => array(
                                                            'type' => 'datetime'
                                                            , 'values' => 'ISO 8601 (YYYY-MM-DDThh:mm:ss.sZ) format'
                                                            , 'default' => false
                                                            , 'notes' => 'The publishedBefore parameter specifies the date and time before which an activity must have occurred for that activity to be included in the API response'
                                                        ),
                                                        'regionCode' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2'
                                                            , 'default' => false
                                                            , 'notes' => 'The regionCode parameter instructs the API to return results for the specified country.'
                                                        )
                                                    )
                                                ),
                                                'insert' => array(
                                                     'type' => 'POST'
                                                    , 'fields' => array(
                                                        'part' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'id,contentDetails,snippet'
                                                            , 'default' => 'id,contentDetails'
                                                            , 'notes' => 'required'
                                                        ),
                                                        'request' => array(
                                                            'type' => 'json'
                                                            , 'values' => 'json encoded string'
                                                            , 'default' => false
                                                            , 'notes' => 'Provide an activity resource in the request body'
                                                        )
                                                    )
                                                )
                                        )
                                    ),
                                    'videos' => array(
                                        'url' => 'https://www.googleapis.com/youtube/v3/videos'
                                        , 'methods' => array(
                                                'list' => array(
                                                    'type' => 'GET'
                                                    , 'fields' => array(
                                                        'part' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'id,contentDetails,liveStreamingDetails,localizations,status,statistics,player,snippet,topicDetails,fileDetails,processingDetails,suggestions,recordingDetails'
                                                            , 'default' => 'contentDetails,statistics,snippet,topicDetails'
                                                            , 'notes' => 'required'
                                                        ),
                                                        'id' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'this or chart or myRating must be present in the query'
                                                        ),
                                                        'chart' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'empty or mostPopular'
                                                            , 'default' => false
                                                            , 'notes' => 'this or id or myRating must be present in the query'
                                                        ),
                                                        'myRating' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'like or dislike'
                                                            , 'default' => false
                                                            , 'notes' => 'this or id or chart must be present in the query'
                                                        ),
                                                        'hl' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'The hl parameter instructs the API to retrieve localized resource metadata for a specific application language that the YouTube website supports'
                                                        ),
                                                        'maxHeight' => array(
                                                            'type' => 'integer'
                                                            , 'values' => '72 to 8192'
                                                            , 'default' => false
                                                            , 'notes' => 'The maxHeight parameter specifies the maximum height of the embedded player returned in the player.embedHtml property'
                                                        ),
                                                        'maxResults' => array(
                                                            'type' => 'integer'
                                                            , 'values' => '0 to 50'
                                                            , 'default' => false
                                                            , 'notes' => 'Acceptable values are 0 to 50, inclusive. The default value is 5'
                                                        ),
                                                        'maxWidth' => array(
                                                            'type' => 'integer'
                                                            , 'values' => '72 to 8192'
                                                            , 'default' => false
                                                            , 'notes' => 'The maxWidth parameter specifies the maximum width of the embedded player returned in the player.embedHtml property'
                                                        ),
                                                        'pageToken' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'nextPageToken or prevPageToken'
                                                            , 'default' => false
                                                            , 'notes' => 'The pageToken parameter identifies a specific page in the result set that should be returned'
                                                        ),
                                                        'regionCode' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2'
                                                            , 'default' => false
                                                            , 'notes' => 'The regionCode parameter instructs the API to return results for the specified country'
                                                        ),
                                                        'videoCategoryId' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'The videoCategoryId parameter identifies the video category for which the chart should be retrieved'
                                                        ),
                                                        'onBehalfOfContentOwner' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'The onBehalfOfContentOwner parameter indicates that the request s authorization credentials identify a YouTube CMS user who is acting on behalf of the content owner specified in the parameter value'
                                                        ),
                                                    )
                                                ),
                                                'insert' => array(
                                                     'type' => 'POST'
                                                    , 'fields' => array()
                                                )
                                        )
                                    ),
                                    'commentThreads' => array(
                                        'url' => 'https://www.googleapis.com/youtube/v3/commentThreads'
                                        , 'methods' => array(
                                                'list' => array(
                                                    'type' => 'GET'
                                                    , 'fields' => array(
                                                        'part' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'id,snippet,replies'
                                                            , 'default' => 'id,snippet,replies'
                                                            , 'notes' => 'required'
                                                        ),
                                                        'allThreadsRelatedToChannelId' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either allThreadsRelatedToChannelId or channelId or id or videoId'
                                                        ),
                                                        'channelId' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'empty or mostPopular'
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either allThreadsRelatedToChannelId or channelId or id or videoId'
                                                        ),
                                                        'id' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'like or dislike'
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either allThreadsRelatedToChannelId or channelId or id or videoId'
                                                        ),
                                                        'videoId' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'like or dislike'
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either allThreadsRelatedToChannelId or channelId or id or videoId'
                                                        ),
                                                        'order' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'time|relevance'
                                                            , 'default' => false
                                                            , 'notes' => 'The order parameter specifies the order in which the API response should list comment threads'
                                                        ),
                                                        'moderationStatus' => array(
                                                            'type' => 'integer'
                                                            , 'values' => 'heldForReview|likelySpam|published'
                                                            , 'default' => false
                                                            , 'notes' => 'Set this parameter to limit the returned comment threads to a particular moderation state'
                                                        ),
                                                        'maxResults' => array(
                                                            'type' => 'integer'
                                                            , 'values' => '0 to 50'
                                                            , 'default' => false
                                                            , 'notes' => 'Acceptable values are 0 to 50, inclusive. The default value is 5'
                                                        ),
                                                        'searchTerms' => array(
                                                            'type' => 'integer'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'The searchTerms parameter instructs the API to limit the API response to only contain comments that contain the specified search terms'
                                                        ),
                                                        'pageToken' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'nextPageToken or prevPageToken'
                                                            , 'default' => false
                                                            , 'notes' => 'The pageToken parameter identifies a specific page in the result set that should be returned'
                                                        ),
                                                        'textFormat' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'html|plainText'
                                                            , 'default' => false
                                                            , 'notes' => 'Set this parameter s value to html or plainText to instruct the API to return the comments left by users in html formatted or in plain text'
                                                        ),
                                                    )
                                                ),
                                                'insert' => array(
                                                     'type' => 'POST'
                                                    , 'fields' => array()
                                                )
                                        )
                                    ),
                                    'channels' => array(
                                        'url' => 'https://www.googleapis.com/youtube/v3/channels'
                                        , 'methods' => array(
                                                'list' => array(
                                                    'type' => 'GET'
                                                    , 'fields' => array(
                                                        'part' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'auditDetails,brandingSettings,contentDetails,contentOwnerDetails,id,invideoPromotion,localizations,snippet,statistics,status,topicDetails'
                                                            , 'default' => 'statistics,snippet'
                                                            , 'notes' => 'required'
                                                        ),
                                                        'categoryId' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either categoryId or forUsername or id or managedByMe or mine or mySubscribers'
                                                        ),
                                                        'forUsername' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either categoryId or forUsername or id or managedByMe or mine or mySubscribers'
                                                        ),
                                                        'id' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either categoryId or forUsername or id or managedByMe or mine or mySubscribers'
                                                        ),
                                                        'managedByMe' => array(
                                                            'type' => 'boolean'
                                                            , 'values' => 'true|false'
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either categoryId or forUsername or id or managedByMe or mine or mySubscribers'
                                                        ),
                                                        'mine' => array(
                                                            'type' => 'boolean'
                                                            , 'values' => 'true|false'
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either categoryId or forUsername or id or managedByMe or mine or mySubscribers'
                                                        ),
                                                        'hl' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'The hl parameter instructs the API to retrieve localized resource metadata for a specific application language that the YouTube website supports'
                                                        ),
                                                        'maxResults' => array(
                                                            'type' => 'integer'
                                                            , 'values' => '0 to 50'
                                                            , 'default' => false
                                                            , 'notes' => 'Acceptable values are 0 to 50, inclusive. The default value is 5'
                                                        ),
                                                        'pageToken' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'nextPageToken or prevPageToken'
                                                            , 'default' => false
                                                            , 'notes' => 'The pageToken parameter identifies a specific page in the result set that should be returned'
                                                        ),
                                                        'onBehalfOfContentOwner' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'The onBehalfOfContentOwner parameter indicates that the request s authorization credentials identify a YouTube CMS user who is acting on behalf of the content owner specified in the parameter value'
                                                        ),
                                                    )
                                                ),
                                                'update' => array(
                                                     'type' => 'POST'
                                                    , 'fields' => array()
                                                )
                                        )
                                    ),
                                    'subscriptions' => array(
                                        'url' => 'https://www.googleapis.com/youtube/v3/subscriptions'
                                        , 'methods' => array(
                                                'list' => array(
                                                    'type' => 'GET'
                                                    , 'fields' => array(
                                                        'part' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'contentDetails,id,snippet,subscriberSnippet'
                                                            , 'default' => 'id'
                                                            , 'notes' => 'required'
                                                        ),
                                                        'channelId' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either channelId or id or mine or myRecentSubscribers or mySubscribers'
                                                        ),
                                                        'id' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either channelId or id or mine or myRecentSubscribers or mySubscribers'
                                                        ),
                                                        'mine' => array(
                                                            'type' => 'boolean'
                                                            , 'values' => 'true|false'
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either channelId or id or mine or myRecentSubscribers or mySubscribers'
                                                        ),
                                                        'myRecentSubscribers' => array(
                                                            'type' => 'boolean'
                                                            , 'values' => 'true|false'
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either channelId or id or mine or myRecentSubscribers or mySubscribers'
                                                        ),
                                                        'mySubscribers' => array(
                                                            'type' => 'boolean'
                                                            , 'values' => 'true|false'
                                                            , 'default' => false
                                                            , 'notes' => 'you must provide either channelId or id or mine or myRecentSubscribers or mySubscribers'
                                                        ),
                                                        'forChannelId' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'The forChannelId parameter specifies a comma-separated list of channel IDs'
                                                        ),
                                                        'order' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'relevance|alphabetical|unread'
                                                            , 'default' => false
                                                            , 'notes' => 'The order parameter specifies the method that will be used to sort resources in the API response'
                                                        ),
                                                        'maxResults' => array(
                                                            'type' => 'integer'
                                                            , 'values' => '0 to 50'
                                                            , 'default' => false
                                                            , 'notes' => 'Acceptable values are 0 to 50, inclusive. The default value is 5'
                                                        ),
                                                        'pageToken' => array(
                                                            'type' => 'string'
                                                            , 'values' => 'nextPageToken or prevPageToken'
                                                            , 'default' => false
                                                            , 'notes' => 'The pageToken parameter identifies a specific page in the result set that should be returned'
                                                        ),
                                                        'onBehalfOfContentOwner' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'The onBehalfOfContentOwner parameter indicates that the request s authorization credentials identify a YouTube CMS user who is acting on behalf of the content owner specified in the parameter value'
                                                        ),
                                                        'onBehalfOfContentOwnerChannel' => array(
                                                            'type' => 'string'
                                                            , 'values' => ''
                                                            , 'default' => false
                                                            , 'notes' => 'The onBehalfOfContentOwnerChannel parameter specifies the YouTube channel ID of the channel to which a video is being added'
                                                        ),
                                                    )
                                                ),
                                                'insert' => array(
                                                     'type' => 'POST'
                                                    , 'fields' => array()
                                                )
                                        )
                                    ),
        );
        return $definition;
    }
    
}