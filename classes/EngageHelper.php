<?php
class EngageHelper
{

    public static function postData($url, $postParams = array() ) {
        $params = '';
        if( is_array( $postParams ) )
        {
            foreach($postParams as $key=>$value)
            {
                $params .= $key.'='. urlencode( $value ) . '&';
            }
            $params = trim($params, '&');
        }
        else
        {
            $params = $postParams;
        }
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.54 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
    public static function getData($url, $postParams = array() ) {
        $params = '';
        foreach($postParams as $key=>$value)
        {
            $params .= $key.'='. urlencode( $value ) . '&';
        }
        $params = trim($params, '&');
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url .'?' . $params );
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.54 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

}


?>
