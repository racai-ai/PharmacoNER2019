<?php

function readAnn($fname){
    if(!is_file($fname)){
        echo "WARN:readAnn: ANN file not found [${fname}]\n";
        return [];
    }

    $entities=[];
    foreach(explode("\n",file_get_contents($fname)) as $line){
        $line=ltrim($line);
        if(strlen($line)==0 || $line[0]=='#')continue;
        
	      //echo "T${ent}\t${v} $pos ".($pos+mb_strlen($k))."\t${k}\n";
        $data=explode("\t",$line);
        if(count($data)!=3){
            echo "WARN: Malformed line [$line] in [$fname]\n";
            continue;
        }
        
        $data[1]=explode(" ",$data[1]);
        if(count($data[1])!=3){
            echo "WARN: Malformed line [${line}] in [${fname}]\n";
            continue;
        }
        
        $entities[]=[
            "id"=>$data[0],
            "type"=>$data[1][0],
            "start"=>$data[1][1],
            "end"=>$data[1][2],
            "value"=>$data[2]
        ];
    }
    return $entities;

}

function writeAnn($fname,$entities,$generateIds=true){
    $fout=fopen($fname,"w");
    $cid=1;
    foreach($entities as $e){
        $id=$e['id'];
        if($generateIds){
            $id="T${cid}";
            $cid++;
        }
        
	      fwrite($fout,"${id}\t${e['type']} ${e['start']} ${e['end']}\t${e['value']}\n");
        
    }
    fclose($fout);
}

?>