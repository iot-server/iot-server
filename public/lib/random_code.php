<?php
function random_code($length = 8,$type = 'alpha-number'){
  $code_arr = array(
    't' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
    'n'=> '0123456789',
    's'  => '#$%@*-_',
  );
 
  $type_arr = explode(',',$type);
 
  foreach($type_arr as $t){
    if( ! array_key_exists($t,$code_arr)){
      trigger_error("Can not generate type ($t) code");
    }
  }
 
  $chars = '';
 
  foreach($type_arr as $t){
    $chars .= $code_arr[$t];
  }
  $chars = str_shuffle($chars);
  $number = $length > strlen($chars) - 1 ? strlen($chars) - 1:$length;
  return substr($chars,0,$number);
}
#echo random_code(32,"t,n,s");
?>
