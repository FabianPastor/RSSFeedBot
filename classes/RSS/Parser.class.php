<?php
namespace FabianPastor\RSS;

require_once(__DIR__."/../DOM/Document.class.php");

use FabianPastor\DOM\Document;
class Parser{
  
  public static function normalize_xml($xml){
    $xml = str_replace([
      "<content:","</content:",
      "<atom:", "</atom:",
      "<dc:", "</dc:",
      "<sy:", "</sy:",
    ],
    [
      "<content-","</content-",
      "<atom-", "</atom-",
      "<", "</",
      "<", "</",
      
    ],$xml);
    
    
    return $xml;
  }
  
  public static function parseXML($xml){
    if(trim($xml)=="") return false;
    $xml = SELF::normalize_xml($xml);
    $obj = new \stdClass;
    $doc = new \FabianPastor\DOM\Document($xml);
    
    $obj->items = [];
    foreach( $doc->get("//item") as $itemNode ){
      $item = new \stdClass;
      $item->guid         = $doc->eval("string(./child::guid)", $itemNode);
      $item->title        = $doc->eval("string(./child::title)", $itemNode);
      $item->link         = $doc->eval("string(./child::link)", $itemNode);
      $item->description  = trim($doc->eval("string(./child::description)", $itemNode));
      if($item->description == ""){
        $item->description  = trim($doc->eval("string(./child::content-encoded)", $itemNode));
      }
      $item->tags         = array_map("trim" , $doc->getValues("./child::category", $itemNode));
      $item->pubDate      = strtotime($doc->eval("string(./child::pubDate)", $itemNode));
      $obj->items[] = $item;
    }
    
    return $obj;
  }
  
  
  public static function parseFeed_old($xml_string){    
    if(trim($xml_string)=="") return false;
    $xml = SELF::normalize_xml($xml);
    if($xml = simplexml_load_string($xml_string,"SimpleXMLElement",
      LIBXML_NOCDATA|
      LIBXML_BIGLINES|
      LIBXML_HTML_NOIMPLIED|
      LIBXML_PEDANTIC|
      LIBXML_NOERROR|
      LIBXML_NOWARNING|
      LIBXML_ERR_NONE
      
    )){
      $json = json_encode($xml);
      $obj = json_decode($json);
      
      return $obj;
      
    }else{
      //var_dump($xml_string);
      return false;
    }
  }
}
