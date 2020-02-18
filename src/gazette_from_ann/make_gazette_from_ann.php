<?php

require_once "utils.string.php";
require_once "utils.ann.php";
require_once "utils.gazette.php";
require_once "command_line_options.php";

$cl=new CommandLineOptions();
$cl->defineStringValue("inAnn","Input file name");
$cl->defineMultiStringValue("categ","Category accepted");
$cl->parse();

$types=$cl->getOption("categ");
$entities=[];
foreach($types as $type){
    list($name,$fname)=explode("=",$type);
    $entities[$name]=array_flip(readGazette($fname));
}

$entities1=readAnn($cl->getOption("inAnn"));
foreach($entities1 as $e1){
    if(!isset($entities[$e1['type']]))continue;
    $entities[$e1['type']][$e1['value']]=true;
}

foreach($types as $type){
    list($name,$fname)=explode("=",$type);
    writeGazette($fname,array_keys($entities[$name]));
}

?>