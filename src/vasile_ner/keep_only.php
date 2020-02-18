<?php

if(count($argv)<5){
echo <<<EOT
Usage:
    keep_only <input_file> <column> <values> <output_file>

input_file = input file name tab delimited
column = column number to filter (0 based)
values = comma separated list of values to keep (others will be replaced with O)
output_file = output file name
EOT;
die("\n");
}

$sep="\t";

$fname=$argv[1];
$col=intval($argv[2]);
$keep=array_flip(explode(",",$argv[3]));
$fnameout=$argv[4];

$fin=fopen($fname,"r");
$fout=fopen($fnameout,"w");

while(!feof($fin)){

    $line=fgets($fin);
    if($line===false)break;
    $oline=trim($line);

    $line=explode($sep,trim($line));
    if(count($line)<=$col){fwrite($fout,"$oline\n");continue;}

    if(!isset($keep[$line[$col]]))
	$line[$col]='O';
    fwrite($fout,implode($sep,$line)."\n");
}

fclose($fin);
fclose($fout);
