<?php

$http = eZHTTPTool::instance();
$ids = $http->hasVariable( 'ids' ) ? $http->variable( 'ids' ) : false;

$result = [];

if( $ids  )
{
    $objectIDs = explode( ',', $ids );
    if( count($objectIDs) < 10 )
    {
        foreach( $objectIDs as $objectID )
        {
            $oid = intval( trim( $objectID ) );
            if( is_int($oid) && $oid > 0 )
            {
                $object = eZContentObject::fetch($oid);
                if( $object->canRead() )
                {
                    $node = $object->attribute('main_node');
                    if( $node->canRead() )
                    {
                        //articlethumbnail
                        $dataMap = $object->attribute('data_map');
                        $data = [];
                        $data['title'] = $object->attribute('name');
                        $url = $node->attribute('url_alias');
                        eZURI::transformURI( $url );
                        $data[ 'link' ] = $url;
                        if( isset($dataMap['image']) && $dataMap['image']->attribute('has_content') )
                        {
                            $imageContent = $dataMap['image']->attribute('content');
                            $thumb = $imageContent->imageAlias('articlethumbnail');
                            $imageURL = $thumb['full_path'];
                            eZURI::transformURI( $imageURL, true );
                            $data[ 'img' ] = $imageURL;
                        }
                        else
                        {
                            $data[ 'img' ] = "";
                        }
                        $result[] = $data;
                    }
                }
            }
        }
    }
}

echo json_encode( $result );
eZExecution::cleanExit();


?>