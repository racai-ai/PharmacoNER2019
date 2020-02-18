<?php

require_once "utils.string.php";
require_once "utils.ann.php";
require_once "command_line_options.php";

$cl=new CommandLineOptions();
$types=["PRIO1","PRIO2","SMALLER","LARGER","APPARITIONS"];
$cl->defineStringValue("inFile1","Input file name 1");
$cl->defineStringValue("inFile2","Input file name 2");
$cl->defineStringValue("outFile","Output file name");
$cl->defineStringValue("type","Combination type: ".implode(",",$types));
$cl->addValidation("type","ENUM",$types);
$cl->parse();

$entities1=readAnn($cl->getOption("inFile1"));
$entities2=readAnn($cl->getOption("inFile2"));
$type=$cl->getOption("type");


$eout=[];
foreach($entities1 as &$e1)$e1['ok']=true;
foreach($entities2 as &$e2)$e2['ok']=true;

$eCounts=[];
if(strcasecmp($type,"APPARITIONS")===0){
    foreach($entities1 as $e1){
        if(!isset($eCounts[$e1['value']]))$eCounts[$e1['value']]=0;
        $eCounts[$e1['value']]++;
    }
    
    foreach($entities2 as $e2){
        if(!isset($eCounts[$e2['value']]))$eCounts[$e2['value']]=0;
        $eCounts[$e2['value']]++;
    }

}

foreach($entities1 as &$e1){
    foreach($entities2 as &$e2){
        // e1 = e2
        if($e1['start']==$e2['start'] && $e1['end']==$e2['end']){
            $e2['ok']=false;
            continue;
        }
    
        // e2 incepe in interiorul lui e1
        if($e2['start']>=$e1['start'] && $e2['start']<=$e1['end'] || $e1['start']>=$e2['start'] && $e1['start']<=$e2['end']){
            $found=true;
            if(strcasecmp($type,"PRIO1")===0){$e2['ok']=false;
            }else if(strcasecmp($type,"PRIO2")===0){$e1['ok']=false;break;
            }else if(strcasecmp($type,"SMALLER")===0){
                $l1=$e1['end']-$e1['start'];
                $l2=$e2['end']-$e2['start'];
                
                if($l1<$l2)$e2['ok']=false;
                else{$e1['ok']=false;break;}
            }else if(strcasecmp($type,"LARGER")===0){
                $l1=$e1['end']-$e1['start'];
                $l2=$e2['end']-$e2['start'];
                
                if($l1>$l2)$e2['ok']=false;
                else{$e1['ok']=false;break;}
            }else if(strcasecmp($type,"APPARITIONS")===0){
                $c1=0;
                if(isset($eCounts[$e1['value']]))$c1=$eCounts[$e1['value']];
                $c2=0;
                if(isset($eCounts[$e2['value']]))$c2=$eCounts[$e2['value']];
                
                if($c1>$c2+2)$e2['ok']=false;
                else if($c2>$c1+2)$e1['ok']=false;
                else{
                        $l1=$e1['end']-$e1['start'];
                        $l2=$e2['end']-$e2['start'];
                        
                        if($l1>$l2)$e2['ok']=false;
                        else{$e1['ok']=false;break;}
                    
                    }
            }
            
        }
    }
    
    if($e1['ok']===true)$eout[]=$e1;
}

foreach($entities2 as &$e2){
    if($e2['ok']===true)$eout[]=$e2;
}

writeAnn($cl->getOption("outFile"),$eout);

?>