<?php 
namespace FabianPastor;

class Curl{
  public static function get($url,$opts=[])
  {
    $headers = [];
    $headers_len=0;
    $options = array(
       CURLOPT_URL => $url
      ,CURLOPT_RETURNTRANSFER => true
      ,CURLOPT_FOLLOWLOCATION => true
      ,CURLOPT_TIMEOUT => 5
      ,CURLOPT_SSL_VERIFYHOST => 0
      ,CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$headers,&$headers_len)
        {
          $len = strlen($header);
          $headers_len += $len;
          $header = explode(':', $header, 2);
          if (count($header) < 2) // ignore invalid headers
            return $len;
            
          $headers[strtolower(trim($header[0]))][] = trim($header[1]);
          return $len;
        }
    );
    
    if(is_array($opts)){
      $options = $opts + $options;
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, $options);
    
    $result = new \stdClass();
    $result->data   = curl_exec ($ch);
    $result->info   = curl_getinfo($ch);
    $result->nerror = curl_errno ($ch );
    $result->error  = curl_error ($ch );
    curl_close($ch);
    $result->headers = $headers;
    
    if(isset($options[CURLOPT_HEADER]) && $options[CURLOPT_HEADER]){
      $result->data = trim(substr($result->data,$headers_len));
      //echo json_encode(substr($result->data,0,300));
    }
    
    
    // $result->json = new \stdClass;
    // $result->json->obj = json_decode(trim($result->data));
    // $result->json->error = json_last_error();
    
    return $result;
  }
}