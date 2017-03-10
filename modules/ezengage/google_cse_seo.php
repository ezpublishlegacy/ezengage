<?php
set_time_limit(180);
$ini = eZINI::instance('ezengage.ini');
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
if( $http->hasVariable( 'get_site_meta_overview' ) )
{
    $seoResults = array();
    $url = 'https://www.googleapis.com/customsearch/v1';
    $params = array();
    $params['key'] = $ini->variable('SocialInsights', 'GoogleWebKey');
    $params['cx'] = $ini->variable('SocialInsights', 'GoogleCSECX');
    $params['q'] = $http->variable( 'get_site_meta_overview' );

    $results = json_decode( EngageHelper::getData( $url, $params ), true );
    
    if( !isset($results['error']) && isset( $results['items'] ) )
    {
        foreach( $results['items'] as $item )
        {
            $htmlObj = false;
            
            // tries up to 3 times to get a valid $htmlObj
            for( $x = 0; $x < 3; $x++ )
            {
                $htmlData = EngageHelper::getData( $item['link'] );
                $htmlObj = new simple_html_dom( $htmlData );
                if( $htmlObj && $htmlData )
                {
                    $metaTags = array();
                    try
                    {
                        $metaKeywords = $htmlObj->find('meta[name=keywords]');
                        if( $metaKeywords && isset( $metaKeywords[0] ) )
                        {
                            $metaTags['keywords'] = $metaKeywords[0]->content;
                        }
                        $metaDescription = $htmlObj->find('meta[name=description]');
                        if( $metaDescription && isset( $metaDescription[0] ) )
                        {
                            $metaTags['description'] = $metaDescription[0]->content;
                        }
                        if( !empty( $metaTags ) )
                        {
                            $seoResults[] = $metaTags;
                        }
                        break;
                    }
                    catch (Exception $ex)
                    {
                        eZLog::write("Error - url({$item['link']}), data:\n{$htmlData}", 'ezengage.log');
                    }
                }
                else
                {
                    eZLog::write("Error - url({$item['link']}), data:\n{$htmlData}", 'ezengage.log');
                }
            }

        }
    }
    $keywords = array();
    foreach( $seoResults as $seoResult )
    {
        if( isset( $seoResult['keywords'] ) )
        {
            $keywordArray = array_map('trim', explode( ',', $seoResult['keywords'] ) );
            $keywords = array_merge( $keywords, $keywordArray );
        }
    }
    $totals = array_count_values( $keywords);
    arsort( $totals  );

    $tpl->setVariable( "seo_results", $seoResults );
    $tpl->setVariable( "totals", $totals );
}
$Result = array();
$Result['content'] = $tpl->fetch( "design:engage/google_cse_seo.tpl" );
$Result['left_menu'] = "design:engage/leftmenu.tpl";
$Result['path'] = array( 
  array( 'url' => false, 'text' => 'eZEngage' ),
  array( 'url' => '/ezengage/youtube_query', 'text' => 'Google CSE SEO' )
);

