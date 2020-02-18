<?php

if($argc!=3){
    echo "stanford_xml_to_tab.php <stanford_xml> <out.tab>\n";
    die();
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function emptyTok(){
    global $tok;

    $tok=[
	"id"=>"",
	"word"=>"",
	"lemma"=>"",
	"start"=>"",
	"end"=>"",
	"pos"=>"",
	"ner"=>"",
    ];
}

$fname_in=$argv[1];
$fname_out=$argv[2];

$fin=fopen($fname_in,"r");
$fout=fopen($fname_out,"w");
fwrite($fout,"#ID\tWORD\tLEMMA\tSTART\tEND\tPOS\tNER\n");
$tok=[];
emptyTok();
while(!feof($fin)){
    $line=fgets($fin,4096);
    if($line===false)break;

    $line=trim($line);
    if(startsWith($line,"<sentences")){
	;
    }else if(startsWith($line,"<sentence")){
	$id=substr($line,14);
	$pos=strpos($id,"\"");
	$id=substr($id,0,$pos);
	fwrite($fout,"\n# Sentence $id\n");
    }else if(startsWith($line,"<tokens")){
	;
    }else if(startsWith($line,"<token")){
	$id=substr($line,11);
	$pos=strpos($id,"\"");
	$id=substr($id,0,$pos);
	emptyTok();
	$tok["id"]=$id;
    }else if(startsWith($line,"<word>")){
	$d=substr($line,6);
	$pos=strpos($d,"</");
	$d=substr($d,0,$pos);
	$tok["word"]=$d;
    }else if(startsWith($line,"<lemma>")){
	$d=substr($line,7);
	$pos=strpos($d,"</");
	$d=substr($d,0,$pos);
	$tok["lemma"]=$d;
    }else if(startsWith($line,"<CharacterOffsetBegin>")){
	$d=substr($line,22);
	$pos=strpos($d,"</");
	$d=substr($d,0,$pos);
	$tok["start"]=$d;
    }else if(startsWith($line,"<CharacterOffsetEnd>")){
	$d=substr($line,20);
	$pos=strpos($d,"</");
	$d=substr($d,0,$pos);
	$tok["end"]=$d;
    }else if(startsWith($line,"<POS>")){
	$d=substr($line,5);
	$pos=strpos($d,"</");
	$d=substr($d,0,$pos);
	$tok["pos"]=$d;
    }else if(startsWith($line,"<NER>")){
	$d=substr($line,5);
	$pos=strpos($d,"</");
	$d=substr($d,0,$pos);
	$tok["ner"]=$d;
    }else if(startsWith($line,"</tokens")){
	;
    }else if(startsWith($line,"</token")){
	fwrite($fout,"${tok['id']}\t${tok['word']}\t${tok['lemma']}\t${tok['start']}\t${tok['end']}\t${tok['pos']}\t${tok['ner']}\n");
	//var_dump($tok);
    }
}

fclose($fin);
fclose($fout);

