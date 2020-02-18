<?php

function readGazette($fname){
    if(!is_file($fname)){
        echo "WARN:readGazette: Gazette file not found [${fname}]\n";
        return [];
    }

    $entities=[];
    foreach(explode("\n",file_get_contents($fname)) as $line){
        $line=ltrim($line);
        $line=rtrim($line,"\n\r");
        if(strlen($line)==0 || $line[0]=='#')continue;
        $entities[$line]=true;
    }

    return array_keys($entities);
}

function writeGazette($fname,$entities){
    file_put_contents($fname,implode("\n",$entities));
}

?>