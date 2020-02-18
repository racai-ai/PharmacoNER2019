<?php

$fp=fopen("/data/vasile/spanish/cc.es.300.vec","r");
$data=[];
while(($line=fgets($fp,10000))!==false){
    $vec=explode(" ",trim($line));
    if(count($vec)<10)continue;
    $word=$vec[0];
    $str=implode(" ",array_slice($vec,1));
    $data[$word]=$str;
}
fclose($fp);

$f_words=fopen("words.spanish","w");
$f_vec=fopen("vectors.spanish","w");

foreach($data as $w=>$vec){
	fwrite($f_words,$w."\n");
	fwrite($f_vec,$data[$w]."\n");
}

fclose($f_words);
fclose($f_vec);

?>