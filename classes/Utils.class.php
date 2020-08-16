<?php 
namespace FabianPastor;
class Utils{
  public static function print_cli($val, $return=false, $l=0){
    $return=($return!==false);
    
    $tab = str_repeat("  ", $l+1);
    $msg = "";
    
    switch(gettype($val)){
      
      case "boolean":
        $msg.="(bool) ".($val?"true":"false");
      break;
      case "integer":
        $msg.="(int) ".$val;
      break;
      case "double":
        $msg.="(double) ".$val;
      break;
      case "string":
        $msg.="(string) \"$val\"";
      break;
      case "NULL":
        $msg.="(null)";
      break;
      
      case "array":
        $msg.="(array) ".($val === []?"[empty]":"\n");
        foreach($val as $k => $v){
          $msg.= $tab."[\"$k\"] => ".SELF::print_cli($v,true,$l+1).PHP_EOL;
        }
        $msg = trim($msg).PHP_EOL;
      break;
      
      case "object":
        $object_vars = get_object_vars($val);
        $msg.="(object) ".get_class($val)." ".($object_vars === []?"[empty]":"\n");
        foreach($object_vars as $k => $v){
          $msg.= $tab."\"$k\"-> ".SELF::print_cli($v,true,$l+1).PHP_EOL;
        }
        $msg = trim($msg).PHP_EOL;
      break;
      
      case "resource":
        $msg.="(resource) ";
      break;
      case "resource (closed)":
        $msg.="(closed resource) ";
      break;
      case "unknown type":
        $msg.="(unknown) ";
      break;

    }

    if($l==0){
      if($return){
        return trim($msg);
      }else{
        echo trim($msg);
        return true;
      }
    }else{
      return $msg;
    }
  }
  
  public static function strip_tags($str){
    return strip_tags(html_entity_decode($str),"<br><b><strong><i><em><u><ins><s><strike><del><a><code><pre>");
  }
}