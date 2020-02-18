<?php

$gazette=[];

foreach(explode("\n",file_get_contents($argv[1])) as $line){

    $line=trim($line);
    if(strlen($line)==0 || $line[0]=='#')continue;

    $data=explode(" ",$line,2);

    if(count($data)!=2 || strlen($data[1])<2 || is_numeric($data[1]))continue;

    $gazette[$data[1]]=$data[0];


}

//echo "gazette=".count($gazette);

$text=file_get_contents($argv[2]);

$char_brk=" ,.?!()-";
$char_brk_arr=[];
for($i=0;$i<strlen($char_brk);$i++)$char_brk_arr[$char_brk[$i]]=true;

$ent=0;
foreach($gazette as $k=>$v){
    $last=0;
    while(true){
        $pos=mb_strpos($text,$k,$last);
	if($pos!==false){
	    $c_a=mb_substr($text,$pos-1,1);
	    $c_n=mb_substr($text,$pos+mb_strlen($k),1);
	    if(($pos==0 || isset($char_brk_arr[$c_a])) && ($pos==mb_strlen($text)-1 || isset($char_brk_arr[$c_n]))){
		$ent++;
		echo "T${ent}\t${v} $pos ".($pos+mb_strlen($k))."\t${k}\n";
	    }
	    $last=$pos+mb_strlen($k);
	}else break;
    }
}
