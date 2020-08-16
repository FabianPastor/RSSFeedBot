#!/usr/bin/php
<?php
$config = json_decode(file_get_contents("config.json"));

require_once("classes/rss.class.php");
require_once("classes/Curl.class.php");
require_once("classes/Utils.class.php");

use FabianPastor\Utils as U;

register_shutdown_function(function(){
  global $config;
  $url = "https://api.telegram.org/bot{$config->bottoken}/sendMessage";

  
  $opts[CURLOPT_HTTPHEADER] = ['Content-Type:application/json'];
  $opts[CURLOPT_POSTFIELDS] = json_encode([
    "chat_id"=> $config->root
   ,"text" => "RSS Bot: I am DED :("
   ,"parse_mode" => "html"
   //,"reply_markup" => ((isset($inline_keyboard))?$inline_keyboard:false)
   //,"disable_notification" => true
   
  ]);
  $returned = \FabianPastor\Curl::get($url,$opts);
  if($returned->info["http_code"]!==200){
    $returned->json = json_encode(json_decode($returned->data),128);
    U::print_cli($returned);
  }
});

$feeds = new \FabianPastor\RSS\Client([
  
  // "ElPais-Portada" => "https://feeds.elpais.com/mrss-s/pages/ep/site/elpais.com/portada",
  // "ElPais-Valencia" => "https://elpais.com/rss/ccaa/valencia.xml",
  // "ElPais-Ultimas" => "https://elpais.com/rss/tags/ultimas_noticias.xml",
  // "ElPais-Videos" => "https://elpais.com/rss/tags/o_video.xml",
  
  // "MundoToday" => "https://www.elmundotoday.com/rss",
  // "Meneame-Ciencia"=> "https://www.meneame.net/m/ciencia/rss",
  
  // "Maldita-Portada" => "https://maldita.es/rss",
  // "Maldita-Ciencia" => "https://maldita.es/malditaciencia/rss",
  //"Maldita-Bulo" => ["https://maldita.es/malditobulo/rss",["enable_title_check"=>true,"lastupdate"=>1589813842]],
  //"Meneame-Retuit" => ["https://www.meneame.net/m/Retuit/rss",["disable_check_pubDate"=>true,"lastupdate"=>1589813842]],
  //"ElMundo-Portada" => ["https://e00-elmundo.uecdn.es/elmundo/rss/portada.xml",["enable_title_check"=>true,"enable_link_check"=>true,"lastupdate"=>1590072270]],
  //"ElMundo-Valencia" => ["https://e00-elmundo.uecdn.es/elmundo/rss/valencia.xml",["enable_title_check"=>true,"enable_link_check"=>true,"lastupdate"=>1590072270]],
 // "LasProvincias-Portada" => ["https://www.lasprovincias.es/rss/2.0/portada",["enable_title_check"=>true,"enable_link_check"=>true,"lastupdate"=>time()]],
  //"LasProvincias-Comunitat" => ["https://www.lasprovincias.es/rss/2.0/?section=comunitat",["enable_title_check"=>true,"enable_link_check"=>true,"lastupdate"=>time()]],
  //"LasProvincias-ValenciaCiudad" => ["https://www.lasprovincias.es/rss/2.0/?section=valencia-ciudad",["enable_title_check"=>true,"enable_link_check"=>true,"lastupdate"=>time()]],
  //"LasProvincias-Valencia" => ["https://www.lasprovincias.es/rss/2.0/?section=valencia",["enable_title_check"=>true,"enable_link_check"=>true,"lastupdate"=>time()]],
  //"LasProvincias-TransportPublic" => ["https://www.lasprovincias.es/rss/2.0/?section=transporte-publico",["enable_title_check"=>true,"enable_link_check"=>true,"lastupdate"=>time()]],
  //"LasProvincias-UltimaHora" => ["https://www.lasprovincias.es/rss/2.0/?section=ultima-hora",["enable_title_check"=>true,"enable_link_check"=>true,"lastupdate"=>time()]],


  // 
  // 

]);
$retry = 15;
$debug = false;
function d($text){
  global $debug;
  if($debug){
    U::print_cli($text);
  }
}

$n_requests = 0;


$running=true;
while($running){
  echo "Ejecutando checkeador de RSS ".time()."\n";
  
  $results = $feeds->getUpdates();
  
  foreach($results as $feed){
    if(!empty($feed->items)){
      //U::print_cli($feed);
      foreach($feed->items as $item){
        
          //If something is not set.
          if(!is_object($item)){             echo "\n###### Item Not An Object\n";U::print_cli($item);continue;}
          if(!isset($item->link)) {          echo "\n###### Item link not setted\n"; U::print_cli($item); continue;}
          if(!is_string($item->description)){echo "\n###### Description not a string.\n"; U::print_cli($item); continue;}
          
          $description = format_description($item->description);
          $link = is_meneame_link($item->link, $description);
          $ivlink = checkIvLink($link);
          
          
          if( strlen($description) > 4000 ){
            $description = strip_tags(substr($description,0,1000))."[...]";
          }

          $msg = "";
          $msg .= "$ivlink <a href='$link'><b>$item->title</b></a>\n\n";
          $msg .= "$description\n\n";
          
          if(!empty($item->tags) && is_array($item->tags) ){
            $msg .= "Tags: <code>".implode("</code>, <code>",$item->tags)."</code>\n";
          }
          $msg .= "Publicado: <code>$item->pubDate</code>\n";
          $msg .= "Fuente: <b>$feed->source</b>\n";
          
          
          try{
            $Message = botSendMessage($msg);
          }catch(\Throwable $e){
            U::print_cli($Message);
            Error_Print($e);
          }
          echo "\n\n".$msg."\n\n";
      }
      
    }else{
      //Just an empty feed
      //U::print_cli($feed);
    }
  }
  
  echo "\nEsperando {$retry}minutos...\n";
  $n_requests++;
  if($n_requests >= 20){
    echo "#### Purgando  cache\n";
    $feeds->purgeCache();
    $n_requests = 0;
  }
  sleep($retry*60);
  
}
exit();
/* */

function botSendMessage($msg,$inline_keyboard=null){
  global $config;
  $url = "https://api.telegram.org/bot{$config->bottoken}/sendMessage";
    
    $opts[CURLOPT_HTTPHEADER] = ['Content-Type:application/json'];
    $opts[CURLOPT_POSTFIELDS] = json_encode([
      "chat_id"=> $config->news_channel
     ,"text" => $msg
     ,"parse_mode" => "html"
     //,"reply_markup" => ((isset($inline_keyboard))?$inline_keyboard:false)
     //,"disable_notification" => true
     
    ]);
    $returned = \FabianPastor\Curl::get($url, $opts);
    if($returned->info["http_code"]!==200){
      $returned->json = json_encode(json_decode($returned->data),128);
      U::print_cli($returned);
    }
    sleep(5);
    return $returned;
}

function is_meneame_link($link, $description){
  $matches = [];
  if(preg_match("/<a href=\"(https?:\/\/www\.meneame\.net\/.*\/go\?id=[0-9]+)\" >\nnoticia original<\/a>/", $description, $matches)){
    return $matches[1];
  }
  else return $link;
}

function format_description($str){
  $description = U::strip_tags($str);
  
  // $description = str_replace(
  //   [
  //     "<strong>etiquetas</strong>:",
  //     "noticia original"
  //   ],[
  //     "\n<strong>etiquetas</strong>:",
  //     "\nnoticia original"
  //   ],$description
  // );
  
  $preg_patterns = [
    '|<strong>etiquetas</strong>: .* (<a href="http://www\.meneame\.net/m/mnm/go\?id=.*" >noticia original</a> \(.*\))|',
    '|La entrada .* se publicó primero en <a rel="nofollow" href="https://maldita\.es">Maldita\.es — Periodismo para que no te la cuelen</a>\.|',
    '|<a href="https://www\.elmundo\.es/.*">Leer</a>|',
  ];
  $patterns_replace = [
    "\n$1",
    '',
    '',
  ];
  
  foreach($preg_patterns as $i => $pattern){
    $description = preg_replace($pattern, $patterns_replace[$i], $description);
  }
  
  return trim($description);
}


function checkIvLink($link){
  $ivlink = "";
  
  if(strpos($link,"lasprovincias.es/")!==false){
    $ivlink = '<a href="https://t.me/iv?url='.urlencode($link).'&rhash=e1fc25a35dc1fa">'."\u{200B}".'</a>';
  }
  
  if(strpos($link,"www.lasprovincias.es/")!==false){
    $ivlink = '<a href="https://t.me/iv?url='.urlencode($link).'&rhash=b9ef2fd24ddb37">'."\u{200B}".'</a>';
  }

  
  if(strpos($link,"comerybeber.lasprovincias.es/")!==false){
    $ivlink = '<a href="https://t.me/iv?url='.urlencode($link).'&rhash=2618e8f2b987c3">'."\u{200B}".'</a>';
  }
  
  if(strpos($link,"maldita.es/")!==false){
    $ivlink = '<a href="https://t.me/iv?url='.urlencode($link).'&rhash=94b63e70ff8cf6">'."\u{200B}".'</a>';
  }
  
  if(strpos($link,"www.elmundotoday.com/")!==false){
    $ivlink = '<a href="https://t.me/iv?url='.urlencode($link).'&rhash=6b51d158a81433">'."\u{200B}".'</a>';
  }
 
  if(strpos($link,"www.elmundo.es/")!==false){
    $ivlink = '<a href="https://t.me/iv?url='.urlencode($link).'&rhash=39f8599e0b12a7">'."\u{200B}".'</a>';
  }
  
  
  return $ivlink;
}


function Error_Print(\Throwable $e){
  echo $e->getCode().": ".$e->getMessage()."\n";
  echo "In File: ".$e->getFile()." Line: ".$e->getLine()."\n\n";
  echo "Trace:\n";
  echo $e->getTraceAsString();
}