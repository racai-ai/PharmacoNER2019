<?php

require_once "utils.string.php";
require_once "utils.ann.php";
require_once "command_line_options.php";

$cl=new CommandLineOptions();
$cl->defineStringValue("inFile","Input file name");
$cl->defineMultiStringValue("keep","Values to keep");
$cl->defineStringValue("outFile","Output file name");
$cl->parse();

$entities1=readAnn($cl->getOption("inFile"));
$keep=array_flip($cl->getOption("keep"));


$eout=[];
foreach($entities1 as $e1){
    if(isset($keep[$e1["type"]]))$eout[]=$e1;
}

writeAnn($cl->getOption("outFile"),$eout);

?>