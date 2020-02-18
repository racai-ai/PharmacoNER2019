#!/bin/sh

folder=../EVALUATE/gold/subtrack1/radu_dev_2/
fout=../EVALUATE/system/subtrack1/combine4_baseline_radu_2/

#folder=test
#fout=test

#if [ $# -eq 0 ]; then
#    echo "Syntax:"
#    echo "    annotate_folder.sh <folder>"
#    exit
#fi

#folder=$1
for f in $folder/*.txt; do
    echo $f
    fname=${f##*/}
    xpref=${fname%.*}
    php annotate_with_gazette.php \
	-stopWords:stopwords-es.txt \
	-gUNCLEAR=UNCLEARC.gazette \
	-gNORMALIZABLES=NORMALIZABLESC.gazette \
	-gPROTEINAS=PROTEINASC.gazette \
	-gNO_NORMALIZABLES=NO_NORMALIZABLESC.gazette \
	-text:$folder/$fname \
	-saveEntities:$fout/$xpref.ann 
#	-gPROTEINAS=genes.txt \
#	\
#	-saveRegexGazette:$fout/regex.gazette \
#	-saveOverlap:$fout/overlap.data \
#	-saveRawEntities:$fout/raw.data
done
