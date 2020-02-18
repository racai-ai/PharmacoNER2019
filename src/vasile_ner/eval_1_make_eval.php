<?php

$fname_in="../dev/subtrack1";

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

$dh = opendir($fname_in);
while (($file = readdir($dh)) !== false) {
    if(endsWith($file,".tab")){
	echo "$file\n";
	$first_sentence=true;
	$fout=fopen("${fname_in}/${file}.eval","w");
	foreach(explode("\n",file_get_contents($fname_in."/".$file)) as $line){
	    $line=trim($line);
	    if(startsWith($line,"# Sentence")){
		if(!$first_sentence)
		    fwrite($fout,"</s>\t</s>\t</s>\t</s>\t</s>\n");
		fwrite($fout,"<s>\t<s>\t<s>\t<s>\t<s>\n");
		$first_sentence=false;
	    }else if(startsWith($line,"#") || strlen($line)<3)continue;
	    else{
		$data=explode("\t",$line);
		fwrite($fout,"${data[1]}\t${data[2]}\t${data[5]}\t-\t${data[6]}\n");
	    }
	}
	fwrite($fout,"</s>\t</s>\t</s>\t</s>\t</s>\n");
	fclose($fout);
    }
}
closedir($dh);
