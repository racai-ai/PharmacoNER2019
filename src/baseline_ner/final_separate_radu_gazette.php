<?php

foreach(explode("\n",file_get_contents("final-entities-full-dataset.txt")) as $line){
	$line=trim($line);
	if(strlen($line)==0)continue;

	$data=explode("\t",$line);
	$d1=explode(" ",trim($data[0]));
	file_put_contents("${d1[1]}.gazette",trim($data[1])."\n",FILE_APPEND);
}

