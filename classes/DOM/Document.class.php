<?php 
namespace FabianPastor\DOM;

class Document{
  public $doc;
  public $xpath;
  
  public function __construct($xml){
    $this->init($xml);
  }
  
  public function init($xml){
    $this->doc = new \DOMDocument();
    $this->doc->loadXML($xml);
    $this->xpath = new \DOMXPath($this->doc);
  }
  
  public function eval($xpathquery, $context = false){
    $query = self::checkQuery($xpathquery);
    
    if($context == false){
      $result =  $this->xpath->evaluate($query);
    }else{
      $result =  $this->xpath->evaluate($query, $context);
    }
    return $result;
  }
  
  public function get($xpathquery, $context = false){
    $query = self::checkQuery($xpathquery);
    
    if($context == false){
      $result =  $this->xpath->query($query);
    }else{
      $result =  $this->xpath->query($query, $context);
    }
    return $result;
  }
  
  public function getValue($xpathquery, $context = false){
    $nodeList = self::get($xpathquery, $context);
    return $nodeList->item(0)->nodeValue;
  }
  
  public function getValues($xpathquery, $context = false){
    $nodeList = self::get($xpathquery, $context);
    $values = [];
    foreach($nodeList as $node){
      $values[] = $node->nodeValue;
    }
    return $values;
  }
  
  private function checkQuery($query){
    $query = preg_replace("/\[has-class\([\"'](.*)[\"']\)\]/", "[contains(concat(' ',normalize-space(@class),' '),' $1 ')]", $query);
    
    return $query;
  }
}