<?php

// stanford:
//ID, WORD, LEMMA, START, END, POS, NER
// complete:
// WORD, LEMMA, POS, 

function processStanford(&$tokData,&$tokDataComplete,$stanford){
    foreach(explode("\n",$tokDataComplete) as $tok){
        $tok=trim($tok);
        if(strlen($tok)==0)continue;
        
        $t=explode("\t",$tok);
        if(count($t)<5)continue;
    
       	$tokData.="${t[0]}\t${t[1]}\t${t[4]}\n";
    }

}
