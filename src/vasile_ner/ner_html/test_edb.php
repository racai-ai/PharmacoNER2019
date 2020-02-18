<?php

require "EntityDB.php";

$tokens=[];
foreach(explode("\n",file_get_contents("test_edb.txt")) as $line){
    $line=trim($line);
    $data=explode("\t",$line);
    if(count($data)!=6)continue;
    $tokens[]=["word"=>$data[0],"lemma"=>$data[1],"msd"=>$data[2],"msd2"=>$data[3],"ctag"=>$data[4],"ner"=>$data[5]];
}

$EntityDB=new EntityDB();

$EntityDB->load("test_edb.json");
$EntityDB->addEntities($tokens);
$EntityDB->correctEntities($tokens);
$EntityDB->save("test_edb.json");
