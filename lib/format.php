<?php
function fmt_date($str,$tz='America/Mexico_City'){
  if(!$str) return '';
  $ts=strtotime($str);
  if(!$ts){
    $c1=preg_replace('/\s*\([^)]*\)\s*/','',$str);
    $c1=preg_replace('/GMT([+-]\d{4})/','$1',$c1);
    $ts=strtotime($c1);
  }
  if(!$ts) return $str;
  if(function_exists('date_default_timezone_set') && $tz){ @date_default_timezone_set($tz); }
  return date('d/m/Y',$ts);
}
function fmt_decimal($v,$dec=1){
  if($v===null || $v==='') return '';
  if(is_string($v)) $v=str_replace(',','.', $v);
  if(!is_numeric($v)) return $v;
  return number_format((float)$v,$dec,'.','');
}

