<?php

$fout_edges="";

$word_col=0;
$ner_col=5;

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<graphml xmlns="http://graphml.graphdrawing.org/xmlns">'."\n";
echo '<key attr.name="label" attr.type="string" for="node" id="label"/>'."\n";
echo '<key attr.name="Edge Label" attr.type="string" for="edge" id="edgelabel"/>'."\n";
echo '<graph edgedefault="undirected">'."\n";

$id=0;
$eid=0;
$currentSent=[];
$currentEntType="O";
$currentEnt="";
$words=[];
                    
foreach(explode("\n",$_REQUEST['text']) as $line){

    $line=trim($line);
    if(strlen($line)===0)continue;

    $data=explode("\t",$line);
    $word=$data[$word_col];
    $ner=$data[$ner_col];
    
    if($word=="<s>"){
        $currentSent=[];
        $currentEntType="O";
        $currentEnt="";
    }else if($word=="</s>"){
        for($i=0;$i<count($currentSent)-1;$i++){
    	    for($j=$i+1;$j<count($currentSent);$j++){
                $fout_edges.="<edge id=\"$eid\" source=\"${currentSent[$i]}\" target=\"${currentSent[$j]}\">";
                $fout_edges.="<data key=\"edgelabel\"></data>";
                $fout_edges.="</edge>\n";
                $eid++;
            }
        }
    }else{
        if($currentEntType==$ner){
            if($ner!="O")$currentEnt.=" ".$word;
        }else{
            if($currentEntType!="O"){
        	if(!isset($words[$currentEnt])){
    	    	    $words[$currentEnt]=$id;
    	    	    $label=htmlspecialchars($currentEnt);
    	    	    echo "<node id=\"$id\"><data key=\"label\">$label</data></node>\n";
                    $id++;
		}
        	$currentSent[]=$words[$currentEnt];
    	    }
            $currentEntType=$ner;
            $currentEnt=$word;
        }
     }            

}

echo $fout_edges;
echo "</graph>\n</graphml>\n";


?>