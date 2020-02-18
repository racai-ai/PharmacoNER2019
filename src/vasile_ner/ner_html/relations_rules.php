<?php


$rules=json_decode(file_get_contents("rules/RELATIONS_rules.json"),true);

$fout_edges="";

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<graphml xmlns="http://graphml.graphdrawing.org/xmlns">'."\n";
echo '<key attr.name="label" attr.type="string" for="node" id="label"/>'."\n";
echo '<key attr.name="Edge Label" attr.type="string" for="edge" id="edgelabel"/>'."\n";
echo '<key id="color" for="node" attr.name="color" attr.type="string"><default>yellow</default></key>';
echo '<graph edgedefault="directed">'."\n";

$id=0;
$eid=0;
$currentSent=[];
$currentEntType="O";
$currentEnt="";
$words=[];

//var_dump(isWPart("!PER"));
//var_dump(isWildcard([],["word"=>"*","ner"=>"!PER"]));
//die();

$nodes=[];
$edges=[];
$colors=["PER"=>"green","ORG"=>"red","LOC"=>"yellow","TIME"=>"blue"];

function matchPart($match,$c){
      $is_match=true;
      
      if(!is_array($match)){
          if($match[0]=='!'){
              if(substr($match,1)==$c){$is_match=false;}
          }else if($match!='*' && $match!=$c)$is_match=false;
      }else{
          $found=false;
          foreach($match as $w){
              if($w[0]=='!'){
                  if(substr($w,1)==$c){$is_match=false;break;}
              }else if($w=='*' || $w==$c){$found=true;break;}
          }
          if(!$found)$is_match=false;
      }

      //var_dump($match);var_dump($c);var_dump($is_match);

      return $is_match;
}

function isWPart($match){
      $is_match=true;
      
      if(!is_array($match)){
          if($match!='*' && $match[0]!='!')$is_match=false;
      }else{
          $found=false;
          foreach($match as $w){
              if($w=='*' || $w[0]=='!'){$found=true;break;}
          }
          if(!$found)$is_match=false;
      }

      return $is_match;
}

function isMatch($c,$match){
      $is_match=true;
      
      if($is_match && isset($match['ner']))$is_match=matchPart($match['ner'],$c['ner']);
      if($is_match && isset($match['word']))$is_match=matchPart($match['word'],$c['word']);
      if($is_match && isset($match['lemma']))$is_match=matchPart($match['lemma'],$c['lemma']);
      if($is_match && isset($match['msd']))$is_match=matchPart($match['msd'],$c['msd']);
      if($is_match && isset($match['msd2']))$is_match=matchPart($match['msd2'],$c['msd2']);
      if($is_match && isset($match['ctag']))$is_match=matchPart($match['ctag'],$c['ctag']);
      
      return $is_match;
}

function isWildcard($c,$match){
      $isw=true;
      
      if($isw && isset($match['ner']))$isw=isWPart($match['ner']);
      if($isw && isset($match['word']))$isw=isWPart($match['word']);
      if($isw && isset($match['lemma']))$isw=isWPart($match['lemma']);
      if($isw && isset($match['msd']))$isw=isWPart($match['msd']);
      if($isw && isset($match['msd2']))$isw=isWPart($match['msd2']);
      if($isw && isset($match['ctag']))$isw=isWPart($match['ctag']);

      return $isw;
}


function displayMatch($sent,$rule,$start){

    $ent1Id=$rule['result']['ent1'];
    $ent2Id=$rule['result']['ent2'];
    $type=$rule['result']['type'];
    
    $ent1="";
    $ent1Type="";
    $ent2="";
    $ent2Type="";
    
    //var_dump($rule);
    
    foreach($rule['match'] as $m){
        if(isset($m['id'])){
            if($m['id']==$ent1Id){
                $data=array_slice($sent,$start,$m['n_matches']);
                foreach($data as $str)$ent1.=" ".$str['word'];
                $ent1=trim($ent1);
                $ent1Type=$data[0]['ner'];
            }else if($m['id']==$ent2Id){
                $data=array_slice($sent,$start,$m['n_matches']);
                foreach($data as $str)$ent2.=" ".$str['word'];
                $ent2=trim($ent2);
                $ent2Type=$data[0]['ner'];
            }
        }
        $start+=$m['n_matches'];
    }
    
    //echo "[$ent1Type/$ent1] ====($type)====> [$ent2Type/$ent2]\n";
    global $nodes,$id,$edges,$eid,$colors,$fout_edges;
    if(!isset($nodes[$ent1])){
        $nodes[$ent1]=$id;
        $color=$colors[$ent1Type];
        $label=htmlspecialchars($ent1);
    	  echo "<node id=\"$id\"><data key=\"label\">$label</data><data key=\"color\">$color</data></node>\n";
        $id++;
    }
    if(!isset($nodes[$ent2])){
        $nodes[$ent2]=$id;
        $label=htmlspecialchars($ent2);
        $color=$colors[$ent2Type];
    	  echo "<node id=\"$id\"><data key=\"label\">$label</data><data key=\"color\">$color</data></node>\n";
        $id++;
    }
    
    $rel_id=$nodes[$ent1]." ".$nodes[$ent2]." ".$type;
    if(!isset($edges[$rel_id])){
        $edges[$rel_id]=$eid;
        $fout_edges.="<edge id=\"$eid\" source=\"${nodes[$ent1]}\" target=\"${nodes[$ent2]}\">";
        $fout_edges.="<data key=\"edgelabel\">$type</data>";
        $fout_edges.="</edge>\n";
        $eid++;
    }
    

}

// Prepare rules
foreach($rules['rules'] as &$rule){
    $rule['is_match']=true;
    $rule['current_match']=0;
    $rule['last_match']=0;
    foreach($rule['match'] as &$m){
        $m['n_matches']=0;
        if(!isset($m['min']))$m['min']=1;
        if(isset($m['word']) && is_string($m['word']) && strpos($m['word'],"|")!==false)
            $m['word']=explode("|",$m['word']);
        if(isset($m['ner']) && is_string($m['ner']) && strpos($m['ner'],"|")!==false)
            $m['ner']=explode("|",$m['ner']);
        if(isset($m['lemma']) && is_string($m['lemma']) && strpos($m['lemma'],"|")!==false)
            $m['lemma']=explode("|",$m['lemma']);
        if(isset($m['msd']) && is_string($m['msd']) && strpos($m['msd'],"|")!==false)
            $m['msd']=explode("|",$m['msd']);
        if(isset($m['msd2']) && is_string($m['msd2']) && strpos($m['msd2'],"|")!==false)
            $m['msd2']=explode("|",$m['msd2']);
        if(isset($m['ctag']) && is_string($m['ctag']) && strpos($m['ctag'],"|")!==false)
            $m['ctag']=explode("|",$m['ctag']);
            
    }
}

//var_dump($rules);
                    
foreach(explode("\n",$_REQUEST['text']) as $line){

    $line=trim($line);
    if(strlen($line)===0)continue;

    $data=explode("\t",$line);
    $str=["word"=>$data[0],"lemma"=>$data[1],"msd"=>$data[2],"msd2"=>$data[3],"ctag"=>$data[4],"ner"=>$data[5]];
    $currentSent[]=$str;
    
    if($str["word"]=="</s>"){
            foreach($rules['rules'] as &$rule){
                $rule['is_match']=true;
                $rule['current_match']=0;
                $rule['last_match']=0;
                foreach($rule['match'] as &$m){
                    $m['n_matches']=0;
                    if(!isset($m['min']))$m['min']=1;
                }
            }

        //var_dump($currentSent);

        // Try to match in current sentence
        for($i=0;$i<count($currentSent)-1;$i++){
            foreach($rules['rules'] as &$rule){
                $rule['is_match']=true;
                $rule['current_match']=0;
                if(!isset($rule['last_match']))$rule['last_match']=0;
                foreach($rule['match'] as &$m){
                    $m['n_matches']=0;
                    if(!isset($m['min']))$m['min']=1;
                }
            }
            
            //var_dump($i);
            
            for($j=$i;$j<count($currentSent);$j++){
                foreach($rules['rules'] as &$rule){
                    if(!$rule['is_match'] || $rule['last_match']>$i)continue;
                    
                    $repeat=true;
                    while($repeat && $rule['is_match']){
                        $repeat=false;
                        $match=$rule['match'][$rule['current_match']];
                        
                        $c=$currentSent[$j];
                        
                        $is_match=isMatch($c,$match);
    
                        if(!$is_match){
                            if($match['min']<=$match['n_matches']){
                                $rule['current_match']++;
                                $repeat=true;
                                if($rule['current_match']>=count($rule['match'])){
                                    //echo "Match\n";
                                    $rule['is_match']=false;
                                    $rule['last_match']=$j;
                                    $repeat=false;
                                    //var_dump($rule);
                                    displayMatch($currentSent,$rule,$i);
                                }
                            }else $rule['is_match']=false;
                        }else{
                            if(isWildcard($c,$match) && $rule['current_match']<count($rule['match'])-1 && isMatch($c,$rule['match'][$rule['current_match']+1])){
                                $rule['current_match']++;
                                $repeat=true;
                            }else if(isset($match['max']) && $match['max']<=$match['n_matches']){
                                $rule['current_match']++;
                                $repeat=true;
                                if($rule['current_match']>=count($rule['match'])){
                                    $rule['last_match']=$j;
                                    $repeat=false;
                                    displayMatch($currentSent,$rule,$i);
                                }
                            }else{
                                $rule['match'][$rule['current_match']]['n_matches']++;
                            }
                        }
                     
                       //echo "$i => $j => ${rule['current_match']} R:[$repeat] M:[$is_match]\n";
   
                    }
                    
                }
            }
        }

        $currentSent=[];


    }else{
        //$currentSent[]=$str;
     }            

}

echo $fout_edges;
echo "</graph>\n</graphml>\n";


?>