<?php

function createRandomImage( $width = 800, $height = 600, $path = 'extension/ezengage/test/random_image.jpg' )
{
    if( eZINI::instance( 'image.ini' )->variable( 'ImageMagick', 'IsEnabled' ) != 'true' )
    {
        return false;
    }
    $imageMagick = eZINI::instance( 'image.ini' )->variable( 'ImageMagick', 'ExecutablePath' ) . eZSys::fileSeparator() . eZINI::instance( 'image.ini' )->variable( 'ImageMagick', 'Executable' ) ;
    $randomEffects = array(
            '-paint ' . rand(1, 10)
            , '-emboss ' . rand(1, 10)
            , '-sharpen 0x' . rand(1, 20)
            , '-sharpen 0x' . rand(1, 20)
            , '-swirl '. rand(0, 360) . '   -shave ' . rand(1, 20) . 'x' . rand(1, 20)
            , '-ordered-dither threshold,' . rand(2, 4)
            , '-channel G +noise random   -auto-level  -solarize ' . rand(0, 100) . '%'
            , '-sigmoidal-contrast ' . rand(10, 100) . 'x'. rand(0, 100) .'% -solarize ' . rand(0, 100) . '% -auto-level'
            , '-function Sinusoid ' . rand(1, 5) . ','. rand(0, 300)
            , '-function Sinusoid ' . rand(1, 5) . ','. rand(0, 300)
            , '+noise Random -virtual-pixel Tile -blur 0x5 -auto-level -separate -background white -compose ModulusAdd -flatten -channel R -combine +channel -set colorspace HSB -colorspace RGB'
        );

        $params = array();
        $params[] = "-size {$width}x{$height}";
        $params[] = 'plasma:  ';
        $params[] =  '-blur ' . rand(2, 10) . 'x' . rand(2, 10);
        $params[] = rand( 0, 2 ) ? '' : '-colorspace Gray';
        $params[] = $randomEffects[ rand(0, count($randomEffects) -1 ) ];
        $params[] =  $path;
        exec( $imageMagick . ' ' . implode(' ', $params ) );
    
    return $path;
    
}


$lipsum = new joshtronic\LoremIpsum();

// Generates folders

$folderObjects= array();

for( $x = 0; $x < 6; $x ++ )
{
    $folder = new ezpObject('folder', 2);
    $attributes = [
        'name' => 'Category ' . ($x+1)
        , 'short_description' => '<p>' . $lipsum->sentence( rand(2, 5) ) . '</p>'
        , 'description' => $lipsum->paragraphs( rand(4, 6), 'p' )
    ];
    foreach( $attributes as $identifier => $value )
    {
        $folder->__set($identifier, $value);
    }
    
    $folderObjects[] = $folder->publish();
}

// Generate articles
$articleIDs = [];

for( $x = 0; $x < 200; $x ++ )
{
    $parentFolder =  $folderObjects[ rand(0, count($folderObjects) -1 ) ];
    $article = new ezpObject( 'article', $parentFolder->attribute('main_node_id') );
    $attributes = [
        'title' => $lipsum->words( rand(4, 10) )
        , 'intro' => '<p>' . $lipsum->sentence( rand(2, 5) ) . '</p>'
        , 'body' => $lipsum->paragraphs( rand(5, 10), 'p' )
        , 'image' => createRandomImage()
        , 'caption' => '<p>' . $lipsum->sentence( rand(2, 5) ) . '</p>'
    ];
    foreach( $attributes as $identifier => $value )
    {
        $article->__set($identifier, $value);
    }
    $article->object->setAttribute( 'published', rand( strtotime( '-12 months' ), time() ) );
    $article->object->store();
    $article->publish();
}