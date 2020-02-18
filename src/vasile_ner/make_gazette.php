<?php

file_put_contents("UNCLEAR.gazette","");
file_put_contents("NO_NORMALIZABLES.gazette","");
file_put_contents("NORMALIZABLES.gazette","");
file_put_contents("PROTEINAS.gazette","");

foreach(explode("\n",file_get_contents("labels-counted.txt")) as $line){
	$line=trim($line);
	if(strlen($line)==0)continue;

	$data=explode("\t",$line);
	$d1=explode(" ",$data[0]);
	file_put_contents("${d1[1]}.gazette",$data[1]."\n",FILE_APPEND);
}

if(is_file("UNCLEAR.manual"))file_put_contents("UNCLEAR.gazette",file_get_contents("UNCLEAR.manual"),FILE_APPEND);
if(is_file("NO_NORMALIZABLES.manual"))file_put_contents("NO_NORMALIZABLES.gazette",file_get_contents("NO_NORMALIZABLES.manual"),FILE_APPEND);
if(is_file("NORMALIZABLES.manual"))file_put_contents("NORMALIZABLES.gazette",file_get_contents("NORMALIZABLES.manual"),FILE_APPEND);
if(is_file("PROTEINAS.manual"))file_put_contents("PROTEINAS.gazette",file_get_contents("PROTEINAS.manual"),FILE_APPEND);

$fname_out="gazette.stanford";

$fout=fopen($fname_out,"w");

function makeGazette($type,$fout){
    foreach(explode("\n",file_get_contents($type.".gazette")) as $line){
	$line=trim($line);
	if(strlen($line)==0 || $line[0]=='#')continue;

	fwrite($fout,"$type $line\n");
    }
}

makeGazette("PROTEINAS",$fout);
makeGazette("UNCLEAR",$fout);
makeGazette("NORMALIZABLES",$fout);
makeGazette("NO_NORMALIZABLES",$fout);

fclose($fout);

