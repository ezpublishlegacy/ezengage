<?php

function checkMessage( $message, $uid, $memcache )
{
    // TODO specify a reasonable maximum message length
    if( !$message || strlen( $message ) > 20000 )
    {
        return false;
    }
    $parsedMessages = json_decode($message, true);
    if( !is_array($parsedMessages) || count($parsedMessages) > 20  )
    {
        return false;
    }
    foreach( $parsedMessages as $parsedMessage )
    {
        if( !is_array( $parsedMessage ) || !isset( $parsedMessage['id'] ) )
        {
            return false;
        }
    }
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    if ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER ) ) {
        $ipAddress = array_pop( explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
    }
    // avoiding noise from the same ip address
    // each ip address can create only up to 2 messages each 15 minutes
    $ipMap = json_decode( $memcache->get( '/ezengage/ipmap_' . $ipAddress ) );
    if(  !is_array( $ipMap ))
    {
        $ipMap = [];
    }
    if( !in_array( $uid, $ipMap ) && count( $ipMap ) >= 2 )
    {
        return false;
    }
    else if( !in_array( $uid, $ipMap ) )
    {
        $ipMap[] = $uid;
    }
    $jsonIpMap = json_encode( $ipMap );
    $memcache->set( '/ezengage/ipmap_' . $ipAddress, $jsonIpMap, 900 );

    // uid info can only be set by an unique ip
    $uidMap = $memcache->get( '/ezengage/uidmap_' . $uid );
    if(  !$uidMap)
    {
        $uidMap = $ipAddress;
    }
    if( $uidMap !=  $ipAddress )
    {
        return false;
    }
    $memcache->set( '/ezengage/uidmap_' . $uid, $uidMap, 900 );

    return true;
}

$memcache = new Memcached();
$cacheAvailable = $memcache->addServer( '127.0.0.1', 11211 );
 
if ($cacheAvailable == true)
{
    $http = eZHTTPTool::instance();
    $uid = $http->hasVariable( 'uid' ) ? $http->variable( 'uid' ) : false;
    $message = $http->hasVariable( 'message' ) ? $http->variable( 'message' ) : false;
    $key = '/ezengage/messages';
    $cachedMessages = $memcache->get($key);

    if ($cachedMessages) {
        $cachedMessages = json_decode($cachedMessages, true);

    }
    if(  !is_array( $cachedMessages ))
    {
        $cachedMessages = [];
    }
    
    if ($uid && checkMessage( $message, $uid, $memcache ) )
    {
        if( isset($cachedMessages[$uid]) )
        {
            unset($cachedMessages[$uid]);
        }
        $cachedMessages = array_merge( array( $uid => $message ), $cachedMessages );
        if( count( $cachedMessages ) > 100 )
        {
            $cachedMessages = array_slice(0, 100); 
        }
    }
    $jsonCacheMessages = json_encode( $cachedMessages );
    $memcache->set($key, $jsonCacheMessages, 86400);
    $result = array();
    
    foreach( $cachedMessages as $key => $cachedMessage )
    {
        if( $key == $uid )
        {
            $result[$key] = $cachedMessage;
        }
        else
        {
            $result[ 'u' . count($result) ] = $cachedMessage;
        }
    }
    echo json_encode($result);
    eZExecution::cleanExit();
}
echo json_encode( false );
eZExecution::cleanExit();


?>