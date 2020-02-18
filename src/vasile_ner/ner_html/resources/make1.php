<?php

ini_set('default_charset', 'utf-8');

$words=[];

foreach(explode("\n",file_get_contents("body_parts.txt")) as $line){
    $line=trim($line);
    if(strlen($line)==0)continue;
    
    foreach(explode(";",$line) as $part){
        $part=trim($part);
        if(strlen($part)==0)continue;
        
        $data=explode("=",$part);
        foreach(explode(" ",$data[0]) as $w){
            $w=trim($w);
            if(strlen($w)==0)continue;
            $words[$w]=true;
        }
    }
}

echo implode("|",array_keys($words));

?>