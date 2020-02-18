<?php

require "/var/www/html/php_text_lib/lib.php";
require "utils.TTL.php";
require "utils.NER.php";
require "utils.string.php";
require "tag_rules.php";
require "subentity.rule.php";
require "EntityDB.php";
require "log.php";

if(count($argv)<2){
    die("Syntax: ner.php <input_spanish_file> [read]");
}

$_REQUEST=[];

if(count($argv)==2){
    $tokData="";
    $tokDataComplete="";

    $tokDataComplete=file_get_contents($argv[1]);
    logNer($tokDataComplete);
    processTTL_alreadyTokenized($tokData,$tokDataComplete);

    $ner=processNER($tokData);
    $data=getCompleteData($ner,$tokDataComplete);
}else{
    $data=[];
    foreach(explode("\n",file_get_contents($argv[1])) as $tok){
        $tok=trim($tok);
        if(strlen($tok)==0)continue;
        
        if(startsWith($tok,"#Sentence")){
            if(count($data)>0){
           	$data[]=[
                "word"=>"</s>",
                "lemma"=>"</s>",
                "msd"=>"</s>",
                "msd2"=>"</s>",
                "ctag"=>"</s>",
                "ner"=>"</s>",
            ];
            }
           	$data[]=[
                "word"=>"<s>",
                "lemma"=>"<s>",
                "msd"=>"<s>",
                "msd2"=>"<s>",
                "ctag"=>"<s>",
                "ner"=>"<s>",
            ];
        }
        
        if($tok[0]=='#')continue;
        
        $t=explode("\t",$tok);
        if(count($t)<8)continue;
    
       	$data[]=[
            "word"=>$t[1],
            "lemma"=>$t[2],
            "msd"=>$t[5],
            "msd2"=>$t[5],
            "ctag"=>$t[6],
            "ner"=>$t[7]
        ];
    }
    
               	$data[]=[
                "word"=>"</s>",
                "lemma"=>"</s>",
                "msd"=>"</s>",
                "msd2"=>"</s>",
                "ctag"=>"</s>",
                "ner"=>"</s>",
            ];

    
}

//var_dump($data);

$ret=$data;

$rules=[];
loadRules($rules,"rules/1_general_rules.json");
loadRules($rules,"rules/1_PROTEINAS.json");
loadRules($rules,"rules/1_UNCLEAR.json");
loadRules($rules,"rules/1_NORMALIZABLES.json");
if(count($rules)>0){
    for($i=0;$i<2;$i++){
      prepareRules($rules);
      $data=$ret;
      $ret=applyRules($data,$rules);
    }
}

$rules=[];
loadRules($rules,"rules/2_general_rules.json");
loadRules($rules,"rules/2_PROTEINAS.json");
loadRules($rules,"rules/2_UNCLEAR.json");
loadRules($rules,"rules/2_NORMALIZABLES.json");
if(count($rules)>0){
    for($i=0;$i<2;$i++){
      prepareRules($rules);
      $data=$ret;
      $ret=applyRules($data,$rules);
    }
}

$rules=[];
loadRules($rules,"rules/3_general_rules.json");
loadRules($rules,"rules/3_PROTEINAS.json");
loadRules($rules,"rules/3_UNCLEAR.json");
loadRules($rules,"rules/3_NORMALIZABLES.json");
if(count($rules)>0){
    for($i=0;$i<2;$i++){
      prepareRules($rules);
      $data=$ret;
      $ret=applyRules($data,$rules);
    }
}

$rules=[];
loadRules($rules,"rules/4_general_rules.json");
loadRules($rules,"rules/4_PROTEINAS.json");
loadRules($rules,"rules/4_UNCLEAR.json");
loadRules($rules,"rules/4_NORMALIZABLES.json");
if(count($rules)>0){
    for($i=0;$i<2;$i++){
      prepareRules($rules);
      $data=$ret;
      $ret=applyRules($data,$rules);
    }
}

$ret=findSubEntities($ret);

$EntityDB=new EntityDB();
$EntityDB->load("db/entities.db.json");
$EntityDB->addEntities($ret);
@mkdir("db");
$EntityDB->save("db/entities.db.json");

//var_dump($entities);
if(isset($_REQUEST['text'])){
    echo "WORD\tLEMMA\tMSD\tMSD2\tCTAG\tNER\n";
}

foreach($ret as $str){
    //echo htmlspecialchars("${str['word']}\t${str['lemma']}\t${str['msd']}\t${str['msd2']}\t${str['ctag']}\t${str['ner']}")."\n";
    echo "${str['word']}\t${str['lemma']}\t${str['msd']}\t${str['msd2']}\t${str['ctag']}\t${str['ner']}\n";
}


//$response=$tokData."\n".$response;
//$ret=str_replace("null","",$response);
//$ret=htmlspecialchars($ret);
//echo $ret;
//echo "</pre>\n";

