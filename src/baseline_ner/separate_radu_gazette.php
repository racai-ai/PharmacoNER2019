<?php

foreach(explode("\n",file_get_contents("radu_train_2_entities.txt")) as $line){
	$line=trim($line);
	if(strlen($line)==0)continue;

	$data=explode("\t",$line);
	$d1=explode(" ",$data[1]);
	file_put_contents("${d1[0]}.gazette",$data[2]."\n",FILE_APPEND);
}

