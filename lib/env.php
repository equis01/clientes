<?php
function env($key, $default=null){
  static $vars=null;
  if($vars===null){
    $vars=[];
    $path=__DIR__.'/../.env';
    if(is_file($path) && is_readable($path)){
      $lines=@file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      if($lines!==false){
        foreach($lines as $line){
          $t=trim($line);
          if($t==='' || $t[0]==='#' || $t[0]===';' ) continue;
          $eq=strpos($t,'=');
          if($eq===false) continue;
          $k=trim(substr($t,0,$eq));
          $v=trim(substr($t,$eq+1));
          if((strlen($v)>=2) && (($v[0]==='"' && substr($v,-1)=='"') || ($v[0]==="'" && substr($v,-1)==="'"))){
            $v=substr($v,1,-1);
          }
          $vars[$k]=$v;
        }
      }
    }
  }
  return array_key_exists($key,$vars)?$vars[$key]:$default;
}

