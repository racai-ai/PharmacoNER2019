#!/bin/sh

folder=../EVALUATE/system/subtrack1/combine4_radu_2/

#folder=test
#fout=test

#if [ $# -eq 0 ]; then
#    echo "Syntax:"
#    echo "    annotate_folder.sh <folder>"
#    exit
#fi

#folder=$1
for f in $folder/*.ann; do
    echo $f
    fname=${f##*/}
    xpref=${fname%.*}
    php make_gazette_from_ann.php \
	-inAnn:$folder/$fname \
	-categ:PROTEINAS=PROTEINASC.gazette \
	-categ:NORMALIZABLES=NORMALIZABLESC.gazette \
	-categ:NO_NORMALIZABLES=NO_NORMALIZABLESC.gazette \
	-categ:UNCLEAR=UNCLEARC.gazette

done
