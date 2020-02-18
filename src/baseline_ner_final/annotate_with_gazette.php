<?php

require_once "utils.string.php";

$gazette=[];

$saveCombinedGazette=false;
$saveRegexGazette=false;
$saveRawEntities=false;
$saveEntities=false;
$text_fname=false;
$saveOverlap=false;
$stopWords=false;

function mylog($type,$msg){
    echo "[$type] $msg\n";
}

$total_gazette=0;
foreach($argv as $k=>$a){
    if($k==0)continue;
    if(startsWith($a,"-g") && strpos($a,"=")!==false){
        list($c,$fname)=explode("=",substr($a,2),2);
        if(!isset($gazette[$c]))$gazette[$c]=array();

        foreach(explode("\n",file_get_contents($fname)) as $line){
        
            $line=trim($line);
            if(strlen($line)<3 || $line[0]=='#')continue;
            if($stopWords!==false && isset($stopWords[$line]))continue;
            if($stopWords===false && strlen($line)<3)continue; 
        
            $gazette[$c][]=$line;
            $total_gazette++;
        }
    }else if(startsWith($a,"-saveCombinedGazette:")){
        $saveCombinedGazette=substr($a,21);
    }else if(startsWith($a,"-saveRegexGazette:")){
        $saveRegexGazette=substr($a,18);
    }else if(startsWith($a,"-saveRAWEntities:")){
        $saveRawEntities=substr($a,17);
    }else if(startsWith($a,"-saveEntities:")){
        $saveEntities=substr($a,14);
    }else if(startsWith($a,"-saveOverlap:")){
        $saveOverlap=substr($a,13);
    }else if(startsWith($a,"-text:")){
        $text_fname=substr($a,6);
    }else if(startsWith($a,"-stopWords:")){
        $stopWords=[];
        foreach(explode("\n",file_get_contents(substr($a,11))) as $line){
            $line=trim($line);
            if(strlen($line)==0)continue;
            $stopWords[$line]=true;
        }
    }else{
        mylog("WARNING","Unknown argument: [$a] ; will be ignored");
    }
}

if(count($gazette)===0){
    echo "Syntax:\n";
    echo "   annotate_with_gazette -gCATEGORY=FNAME -gCATEGORY=FNAME.... [-saveCombinedGazette:FNAME] [-saveRegexGazette:FNAME] [-saveRAWEntities:FNAME] [-saveEntities:FNAME] [-saveOverlap:FNAME] [-stopWords:FNAME] [-text:FNAME]\n\n";
    echo "      CATEGORY = gazette category\n";
    echo "      FNAME = filename used for input or output\n";
    die();
}

if($saveEntities===false){
    mylog("WARNING","-saveEntities parameter not used => entities will not be saved");
}
if($text_fname===false){
    mylog("WARNING","-text parameter not used => matching will not be performed");
    $saveEntities=false;
    $saveRawEntities=false;
}

mylog("INFO","Loaded ".count($gazette)." gazette categories");
mylog("INFO","Total gazette entries ${total_gazette}");

if($saveCombinedGazette!==false){
    mylog("INFO", "Saving combined gazette to [$saveCombinedGazette]");

    $fp=fopen($saveCombinedGazette,"w");
    foreach($gazette as $gtype=>$cg){
        foreach($cg as $g){
            fwrite($fp,"$gtype $g\n");
        }
    }
    fclose($fp);
}

// Make REGEX
mylog("INFO", "Creating REGEX");
foreach($gazette as $gtype => &$currentGazette){
    foreach($currentGazette as &$g){
        $g=mb_strtolower($g);
        $g=preg_replace("/[- .<>^()+]+/i"," ",$g);
        $g=trim($g);
        $g=preg_replace("/([^0-9 ]+)([0-9]+)/i",'${1} ${2}',$g);
        $g=str_replace("[","[[]",$g);
        $g=str_replace(" ","[- .<>^\"]*",$g);
        $g=str_replace("\\","[\\]",$g);
        $g=str_replace("/","[\\/]",$g);
    }
}

if($saveRegexGazette!==false){
    mylog("INFO", "Saving REGEX gazette to [$saveRegexGazette]");
    $fp=fopen($saveRegexGazette,"w");
    foreach($gazette as $gtype=>$cg){
        foreach($cg as $g){
            fwrite($fp,"$gtype $g\n");
        }
    }
    fclose($fp);
}


//echo "gazette=".count($gazette);
$text="";
if($text_fname!==false){
    mylog("INFO", "Load text from [${text_fname}]");
    $text=file_get_contents($text_fname);
    mylog("INFO", "Begin annotation");
}else {
    die("DONE");
}

$char_brk=" ,.?!()-:";
$char_brk_arr=[];
for($i=0;$i<strlen($char_brk);$i++)$char_brk_arr[$char_brk[$i]]=true;

$entities=[];

$ent=0;
foreach($gazette as $gtype=>&$currentGazette){
    foreach($currentGazette as &$g){
        $last=0;
        while(true){
            $matches=[];
            $q="/$g/iu";
            $ret=preg_match($q,$text,$matches,PREG_OFFSET_CAPTURE,$last);
            if($ret==1){
                $t=$matches[0][0];
                $start=$matches[0][1];
                
                $temp=mb_strcut($text,$start);
                $start=mb_strlen($text)-mb_strlen($temp);
                
                $end=$start+mb_strlen($t);

          	    $c_a=mb_substr($text,$start-1,1);
          	    $c_n=mb_substr($text,$end,1);


          	    if(($start==0 || isset($char_brk_arr[$c_a])) && ($end==mb_strlen($text)-1 || isset($char_brk_arr[$c_n]))){
                    $entities[]=["type"=>$gtype,"start"=>$start,"end"=>$end,"text"=>$t,"len"=>mb_strlen($t)];
          		      //echo "T${ent}\t${v} $pos ".($pos+mb_strlen($k))."\t${k}\n";
          	    }
          	    $last=$matches[0][1]+strlen($t);
          	}else{
                if($ret===false)var_dump($g);
                break;
            }
        }
    }
}

mylog("INFO","Found ".count($entities)." raw matches");

if($saveRawEntities!==false){
    mylog("INFO", "Saving RAW entities to [$saveRawEntities]");
    $fp=fopen($saveRawEntities,"w");
    $c=0;
    foreach($entities as $e){
        $c++;
        fwrite($fp,"T${c}\t${e['type']} ${e['start']} ${e['end']}\t${e['text']}\n");
    }
    fclose($fp);
}

mylog("INFO","Checking found raw entities");
$overlapfp=false;
if($saveOverlap!==false){
    $overlapfp=fopen($saveOverlap,"w");
    mylog("INFO","Saving overlapping entities in [$saveOverlap]");
}

for($i=0;$i<count($entities);$i++){
    $e1=$entities[$i];
    if($e1===false)continue;
    for($j=$i+1;$j<count($entities);$j++){
        $e2=$entities[$j];
        if($e2===false)continue;
        
        if($e1['start']<=$e2['start'] && $e1['end']>=$e2['end'] && $e1['type']==$e2['type']){
            // sub-entity
            $entities[$j]=false;
        }else if($e2['start']<=$e1['start'] && $e2['end']>=$e1['end'] && $e1['type']==$e2['type']){
            // sub-entity
            $entities[$i]=false;
            break;
        }else if($e1['start']<=$e2['start'] && $e1['end']>=$e2['end']){
            // sub-entity
            // OVERLAP
            if($saveOverlap!==false){
                fwrite($overlapfp,"${e1['type']} ${e1['start']} ${e1['end']}\t${e1['text']}  <===\n");
                fwrite($overlapfp,"${e2['type']} ${e2['start']} ${e2['end']}\t${e2['text']}\n");
                fwrite($overlapfp,"\n");
            }
            $entities[$j]=false;
        }else if($e2['start']<=$e1['start'] && $e2['end']>=$e1['end']){
            // sub-entity
            // OVERLAP
            if($saveOverlap!==false){
                fwrite($overlapfp,"${e1['type']} ${e1['start']} ${e1['end']}\t${e1['text']}\n");
                fwrite($overlapfp,"${e2['type']} ${e2['start']} ${e2['end']}\t${e2['text']}  <===\n");
                fwrite($overlapfp,"\n");
            }
            $entities[$i]=false;
            break;
        }else if(!($e1['end']<$e2['start'] || $e2['end']<$e1['start'])){
            // OVERLAP
            $e1e="";$e2e="";
            if($e1['len']>$e2['len']){
                $e2e="  <===";
                $entities[$j]=false;
            }else{
                $e1e="  <===";
                $entities[$i]=false;
                break;
            }
            if($saveOverlap!==false){
                fwrite($overlapfp,"${e1['type']} ${e1['start']} ${e1['end']}\t${e1['text']}${e1e}\n");
                fwrite($overlapfp,"${e2['type']} ${e2['start']} ${e2['end']}\t${e2['text']}${e2e}\n");
                fwrite($overlapfp,"\n");
            }
        }
        
    }
}
if($saveOverlap!==false){
    fclose($overlapfp);
}

if($saveEntities!==false){
    mylog("INFO", "Saving entities to [$saveEntities]");
    $fp=fopen($saveEntities,"w");
    $c=0;
    foreach($entities as $e){
        if($e===false)continue;
        $c++;
        fwrite($fp,"T${c}\t${e['type']} ${e['start']} ${e['end']}\t${e['text']}\n");
    }
    fclose($fp);
}

echo "DONE";
