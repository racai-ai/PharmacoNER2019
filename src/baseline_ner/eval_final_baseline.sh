#!/bin/sh

folder=../RUN/background
fout=../RUN/run_baseline

mkdir $fout

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
	-gUNCLEAR=UNCLEAR.gazette \
	-gNORMALIZABLES=NORMALIZABLES.gazette \
	-gPROTEINAS=PROTEINAS.gazette \
	-gPROTEINAS=proteinas_SNOMED.txt \
	-gNO_NORMALIZABLES=NO_NORMALIZABLES.gazette \
	-text:$folder/$fname \
	-saveEntities:$fout/$xpref.ann
#	-gPROTEINAS=genes.txt
#	\
#	-saveRegexGazette:$fout/regex.gazette \
#	-saveOverlap:$fout/overlap.data \
#	-saveRawEntities:$fout/raw.data
done
