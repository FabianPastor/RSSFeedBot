<?php 
require_once(__DIR__."/RSS/Client.class.php");

/* *
require_once(__DIR__."/Utils.class.php");
$feeds = new \FabianPastor\RSS\Client([
  
  // "ElPais-Portada" => "https://feeds.elpais.com/mrss-s/pages/ep/site/elpais.com/portada",
  // "ElPais-Valencia" => "https://elpais.com/rss/ccaa/valencia.xml",
   "ElPais-Ultimas" => "https://elpais.com/rss/tags/ultimas_noticias.xml",
   "ElPais-Videos" => "https://elpais.com/rss/tags/o_video.xml",
  
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
  "LasProvincias-ValenciaCiudad" => ["https://www.lasprovincias.es/rss/2.0/?section=valencia-ciudad",["enable_title_check"=>true,"enable_link_check"=>true,"lastupdate"=>time()-8000]],
  "LasProvincias-Valencia" => ["https://www.lasprovincias.es/rss/2.0/?section=valencia",["enable_title_check"=>true,"enable_link_check"=>true,"lastupdate"=>time()-8000]],
  "LasProvincias-TransportPublic" => ["https://www.lasprovincias.es/rss/2.0/?section=transporte-publico",["enable_title_check"=>true,"enable_link_check"=>true,"lastupdate"=>time()-8000]],
  "LasProvincias-UltimaHora" => ["https://www.lasprovincias.es/rss/2.0/?section=ultima-hora",["enable_title_check"=>true,"enable_link_check"=>true,"lastupdate"=>time()-8000]],
]);

$i = 0;
$running = true;
while($running){
  $results = $feeds->getUpdates();
  
  foreach($results as $feed){
    FabianPastor\Utils::print_cli($feed);
    echo "\n\n";
  }
  echo "\n\n\n\n";
  if($i++>1) $running = false;
  sleep(5);
}


/* */