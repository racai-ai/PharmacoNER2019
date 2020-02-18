<?php

if(!isset($argv) || count($argv)!=2){
  die("Command line only");
}

$text=file_get_contents($argv[1]);

require "../php_text_lib/lib.php";
require "utils.TTL.php";
require "utils.NER.php";
require "tag_rules.php";
require "subentity.rule.php";

//echo "<pre>\n";
$rules=[];
loadRules($rules,"rules/1_general_rules.json");
loadRules($rules,"rules/1_TIME_rules.json");
loadRules($rules,"rules/1_ORG_rules.json");
loadRules($rules,"rules/1_PER_rules.json");
loadRules($rules,"rules/1_LOC_rules.json");



$tokData="";
$tokDataComplete="";

$originalNER=[];
//processTTL($text,$tokData,$tokDataComplete);
foreach(explode("\n",$text) as $line){
    $data=explode("\t",trim($line));
    if(count($data)!=6)continue;
    $t=["word"=>$data[0],"lemma"=>$data[1],"pos"=>$data[2],"msd2"=>$data[3],"ctag"=>$data[4],"ner"=>$data[5]];
   	$tokData.="${t['word']}\t${t['lemma']}\t${t['ctag']}\n";
   	$tokDataComplete.="${t['word']}\t${t['lemma']}\t${t['pos']}\t".substr($t['pos'],0,2)."\t${t['ctag']}\n";
    $originalNER[]=$data[5];
}

$ner=processNER($tokData);
$data=getCompleteData($ner,$tokDataComplete);

prepareRules($rules);
$ret=applyRules($data,$rules);

$rules=[];
loadRules($rules,"rules/2_general_rules.json");
loadRules($rules,"rules/2_TIME_rules.json");
loadRules($rules,"rules/2_ORG_rules.json");
loadRules($rules,"rules/2_PER_rules.json");
loadRules($rules,"rules/2_LOC_rules.json");
if(count($rules)>0){
    prepareRules($rules);
    $data=$ret;
    $ret=applyRules($data,$rules);
}

$rules=[];
loadRules($rules,"rules/2_general_rules.json");
if(count($rules)>0){
    prepareRules($rules);
    $data=$ret;
    $ret=applyRules($data,$rules);
}


//var_dump($rules);
//var_dump($data);

$ret=findSubEntities($ret);

//var_dump($entities);

//echo "WORD\tLEMMA\tMSD\tMSD2\tCTAG\tNER\n";
for($i=0;$i<count($ret);$i++) {
    $str=$ret[$i];
    $orig=$originalNER[$i];
    //echo htmlspecialchars("${str['word']}\t${str['lemma']}\t${str['msd']}\t${str['msd2']}\t${str['ctag']}\t${str['ner']}")."\n";
    echo "${str['word']} $orig ${str['ner']}\n";
    if($str['word']=="</s>")echo "\n";
}


//$response=$tokData."\n".$response;
//$ret=str_replace("null","",$response);
//$ret=htmlspecialchars($ret);
//echo $ret;
//echo "</pre>\n";

