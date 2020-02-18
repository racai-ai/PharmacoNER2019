<?php

if($argc!=4){
    echo "tab_ann.php <in.tab> <in.ann> <out.tab.ann>\n";
    die();
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

$fname_in=$argv[1];
$fname_ann=$argv[2];
$fname_out=$argv[3];

$ann=[];
foreach(explode("\n",file_get_contents($fname_ann)) as $line){
    $line=trim($line);
    $d=explode("\t",$line);
    if(count($d)!=3)continue;
    $ent=["id"=>$d[0]];
    $entD=explode(" ",$d[1]);
    $ent["type"]=$entD[0];
    $ent["start"]=intval($entD[1]);
    $ent["end"]=intval($entD[2]);
    $ann[]=$ent;
}


$fin=fopen($fname_in,"r");
$fout=fopen($fname_out,"w");
$lnum=0;
while(!feof($fin)){
    $line=fgets($fin,4096);
    if($line===false)break;

    $lnum++;
    $line=trim($line);
    if($lnum==1){
	fwrite($fout,"$line\tPHARMACONER\n");
	continue;
    }
    
    if(strlen($line)==0 || $line[0]=="#"){
	fwrite($fout,"$line\n");
	continue;
    }

    $data=explode("\t",$line);
    $start=intval($data[3]);
    $end=intval($data[4]);

    $found=false;
    foreach($ann as $a){
	if($start>=$a['start'] && $end<=$a['end']){
	    $found=true;
	    fwrite($fout,"$line\t${a['type']}\n");
	}
    }

    if(!$found)fwrite($fout,"$line\tO\n");
}

fclose($fin);
fclose($fout);

