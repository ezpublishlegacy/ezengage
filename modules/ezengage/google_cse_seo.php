<?php
/*
$ini = eZINI::instance('ezengage.ini');

$url = 'https://www.googleapis.com/customsearch/v1';
$params = array();
$params['key'] = $ini->variable('SocialInsights', 'GoogleWebKey');
$params['cx'] = $ini->variable('SocialInsights', 'GoogleCSECX');
$params['q'] = 'cancer';

$results = json_decode( EngageHelper::getData( $url, $params ), true );

var_dump( $results );*/
$seoResults = array();
$results = json_decode( file_get_contents('extension/ezengage/test/response.json'), true );
if( !isset($results['error']) && isset( $results['items'] ) )
{
    foreach( $results['items'] as $item )
    {
        //$metaTags = get_meta_tags( $item['link'] );
        $htmlObj = new simple_html_dom( EngageHelper::getData($item['link']) );
        $metaTags = array();
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

$tpl = eZTemplate::factory();
$tpl->setVariable( "seo_results", $seoResults );
$tpl->setVariable( "totals", $totals );
$Result = array();
$Result['content'] = $tpl->fetch( "design:engage/google_cse_seo.tpl" );
$Result['left_menu'] = "design:engage/leftmenu.tpl";
$Result['path'] = array( 
  array( 'url' => false, 'text' => 'eZEngage' ),
  array( 'url' => '/ezengage/youtube_query', 'text' => 'Google CSE SEO' )
);

