<?php
namespace FabianPastor\RSS;

require_once(__DIR__."/Parser.class.php");
require_once(__DIR__."/../Curl.class.php");

class Client{
  private $configFile = "saved/feeds_data.json";
  private $cacheFile = "saved/cache.json";
  private $feeds = [];
  private $cache = [];
  
  public function __construct($feeds=false){
    if(!file_exists("saved/")){
      mkdir("saved/");
    }
    $this->loadConfig();
    
    $this->updateFeeds($feeds);
    $this->initFeeds();
    
    $this->saveConfig();
  }
  
  public function initFeeds(){
    foreach($this->feeds as $id => &$feed){
      if(!isset($feed->enabled)){
        $feed->enabled = true;
      }
      if(!isset($feed->disable_check_pubDate)){
        $feed->disable_check_pubDate = false;
      }
      if(!isset($feed->lastupdate)){
        $feed->lastupdate = 0;
      }
      if(!isset($feed->count_errors)){
        $feed->count_errors = 0;
      }
      if(!isset($feed->enable_title_check)){
        $feed->enable_title_check = false;
      }
      if(!isset($feed->enable_link_check)){
        $feed->enable_link_check = false;
      }
      // if(!isset($feed->cache)){
      //   $feed->cache = new \stdClass;
      // }
    }
    
    $this->loadCache();
  }
  public function loadConfig(){
    if(file_exists($this->configFile)){
      $this->feeds =(array) json_decode(file_get_contents($this->configFile));
    }
  }
  public function saveConfig(){
    $feeds = $this->feeds;
    // foreach($feeds as $id => &$feed){
    //   if(isset($feed->cache)){
    //     unset($feed->cache);
    //   }
    // }
    file_put_contents($this->configFile, json_encode($feeds,128));
  }
  
  
  public function loadCache(){
    // if(file_exists($this->cacheFile)){
    //   $cache = (array) json_decode(file_get_contents($this->cacheFile));
    //   foreach($cache as $id => $data){
    //     $this->feeds[$id]->cache = $data;
    //   }
    // }
    if(file_exists($this->cacheFile)){
      $this->cache = json_decode(file_get_contents($this->cacheFile),true);
    }
    
  }
  public function saveCache(){
    // foreach($this->feeds as $id => $feed){
    //   if(!empty($feed->cache)){
    //     $cache[$id] = $feed->cache;
    //   }
    // }
    // if(!empty($cache)){
    //   file_put_contents($this->cacheFile,json_encode($cache,128));
    // }
    file_put_contents($this->cacheFile,json_encode($this->cache,128));
    
  }
  
  public function updateFeeds($feeds, $source=false, $options=[]){
    if($feeds===false) return false;
    
    if(is_array($feeds)){
      foreach($feeds as $source_index => $feed){
          if(is_array($feed)){
            $this->updateFeeds($feed[0], $source_index,$feed[1]);
          }else{
            $this->updateFeeds($feed, $source_index);
          }
          
      }
      return true;
    }
    
    if(is_string($feeds)){
      var_dump($feeds);
      $md5 = md5($feeds);
      
      if( !isset( $this->feeds[$md5] ) ){
        $this->feeds[$md5] = new \stdClass;
        $this->feeds[$md5]->url = $feeds;
        $this->feeds[$md5]->lastupdate = 0;
        $this->feeds[$md5]->enabled = true;
        if($source===false){
          $this->feeds[$md5]->source = "";
        }else{
          $this->feeds[$md5]->source = $source;
        } 
        
      }
      if(!empty($options)){
        foreach($options as $key => $value){
          $this->feeds[$md5]->$key = $value;
        }
      }
      return true;
    }
    if(is_object($feeds)){
      return false;
    }
    return false;
  }
  
  public function checkTitle($title, $source){
    $md5 = md5($title);
    
    
    $source = explode("-",$source);
    $source = $source[0];
    
    if(isset($this->cache[$source])){
      if(isset($this->cache[$source][$md5])){
        return true;
      }else{
        $this->cache[$source][$md5] = $md5;
      }
    }else{
      $this->cache[$source] = [];
      $this->cache[$source][$md5] = $md5;
    }
    
    
    return false;
  }
  
  public function checkLink($guid, $source){
    if(is_string($guid)){
      $link = $guid;
    }
    if(is_object($guid)){
      if(is_string($guid->{"0"})){
        $link = $guid->{"0"};
      }else{
        return false;
      }
    }
    
    $md5 = md5($link);
    
    
    $source = explode("-",$source);
    $source = $source[0]."-links";
    
    if(isset($this->cache[$source])){
      if(isset($this->cache[$source][$md5])){
        return true;
      }else{
        $this->cache[$source][$md5] = $md5;
      }
    }else{
      $this->cache[$source] = [];
      $this->cache[$source][$md5] = $md5;
    }
    
    
    return false;
  }
  
  public static function fetch(&$feed){
    $result = \FabianPastor\Curl::get($feed->url,[
       CURLOPT_TIMEVALUE => $feed->lastupdate
      ,CURLOPT_TIMECONDITION => CURL_TIMECOND_IFMODSINCE
      ,CURLOPT_HEADER => true
    ]);
    return $result;
  }
  
  public function purgeCache(){
    $done = false;
    
    foreach($this->cache as $source => &$history){
      if(is_array($history)){
        if(count($history) > 150){
          $history = array_slice($history, -150,null, true);
          $done = true;
        }
      }
    }
    if($done){
      $this->saveCache();
    }
    return $done;
  }
  
  
  public function getUpdates(){
    $results = [];
    
    foreach($this->feeds as &$feed){
      if($feed->enabled){
        echo "Checking feed: ".str_pad($feed->source,  35, " ");;
        $feedUpdate = $feed->lastupdate;
        
        $RSSConnection = SELF::fetch($feed);
        echo " | Error Code: ".str_pad($RSSConnection->info["http_code"],  3, " ");
        if($RSSConnection->info["http_code"]!==304){
          $xml_string = $RSSConnection->data;
          if($result = Parser::parseXML($xml_string)){
            
            if(isset($result->items)){
              
              echo " | New Items Before: ".str_pad(count($result->items),  3, " ");
              
              foreach($result->items as $i => &$item){
                
                $pubDate = $item->pubDate;
                $item->pubDate = date("d/m/Y H:i:s", $item->pubDate);
                
                $feedNotDeleted = true;
                
                if($feedNotDeleted){
                  if(!$feed->disable_check_pubDate){
                    if( $pubDate < $feedUpdate){
                      unset($result->items[$i]);
                      $feedNotDeleted = false;
                    }
                  }
                }
                
                if($feedNotDeleted){
                  if($feed->enable_link_check){
                    if($this->checkLink($item->guid, $feed->source)){
                      unset($result->items[$i]);
                      $feedNotDeleted = false;
                    }
                  }
                }
                
                if($feedNotDeleted){
                  if($feed->enable_title_check){
                    if($this->checkTitle($item->title, $feed->source)){
                      unset($result->items[$i]);
                      $feedNotDeleted = false;
                    }
                  }
                }
                
                
                
                
              }
              
              //TODO: Reorder items by pubdate
              $result->items = array_reverse($result->items);
              
            }
            echo " | New Items After: ".str_pad(count($result->items),  3, " ")."\n";
            
            $feed->lastupdate = time();
            $result->source = $feed->source;
            
            $results[] = $result;
            $feed->count_errors = 0;
          }else{
            if($feed->count_errors>10){
              $feed->enabled = false;
            }
            $feed->count_errors++;
            //echo "HTTP Error Code: ".$RSSConnection->info["http_code"].PHP_EOL;
            
            echo " | Feed no parseable $feed->count_errors | Error: {$RSSConnection->error}".PHP_EOL;
            //var_dump($RSSConnection);
          }
        }else{
          echo " | Nothing new.\n";
          $feed->count_errors = 0;
        }
      }
    }
    $this->saveConfig();
    $this->saveCache();
    return $results;
  }
  
  
}