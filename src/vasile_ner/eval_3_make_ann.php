<?php


#$fname_in="../dev/test/subtrack1";
#$fname_out="../EVALUATE/system/subtrack1/test";


$fname_in="../dev/subtrack1";
$fname_out="../EVALUATE/system/subtrack1/vasile";

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function writeEntity(){
    global $currentEntityType;
    global $eid,$tokens,$currentStart,$tid,$text,$fout;

		    if($currentEntityType!="O"){

			$eid++;
			$s=intval($tokens[$currentStart][3]);
			$e=intval($tokens[$tid-1][4]);
			$t=mb_substr($text,$s,$e-$s);
			fwrite($fout,"T${eid}\t${currentEntityType} ${s} ${e}\t${t}\n");
			echo "T${eid}\t${currentEntityType} ${s} ${e}\t${t}\n";
		    }

    $currentEntityType="O";
    $currentStart=$tid+1;
}


$dh = opendir($fname_in);
$tokens=[];
while (($file = readdir($dh)) !== false) {
    if(endsWith($file,".txt.tab")){
	$fbase=substr($file,0,strlen($file)-8);
	if(!is_file("${fname_in}/${fbase}.txt.tab.eval.vasilener")){
	    echo "ERROR ************* $file\n";
	    continue;
	}
	echo "$file\n";

	$text=file_get_contents("${fname_in}/${fbase}.txt");

	$fout=fopen("${fname_out}/${fbase}.ann","w");

	// Read initial tokens
	// ID,Word,Lemma,Start,End,POS,StanfordNER
	$tokens=[];
	foreach(explode("\n",file_get_contents($fname_in."/".$file)) as $line){
	    $line=trim($line);
	    if(startsWith($line,"# Sentence"))continue;
	    else if(startsWith($line,"#") || strlen($line)<3)continue;
	    else{
		$data=explode("\t",$line);
		$tokens[]=$data;
	    }
	}

	// Read annotated tokens
	// Word,Lemma,POS,-,StanfordNER,NER
	$tid=0;
	$eid=0;
	$currentEntityType="O";
	$currentStart=0;
	foreach(explode("\n",file_get_contents("${fname_in}/${fbase}.txt.tab.eval.vasilener")) as $line){
	    $line=trim($line);
	    if(startsWith($line,"<s>") || startsWith($line,"</s>")){writeEntity();continue;
	    }else if(startsWith($line,"#") || strlen($line)<3)continue;
	    else{
		$data=explode("\t",$line);
		if($data[5]!=$currentEntityType){
		    writeEntity();
		    $currentEntityType=$data[5];
		    $currentStart=$tid;	
		}
		$tid++;
	    }
	}


	fclose($fout);

    }

}
closedir($dh);
