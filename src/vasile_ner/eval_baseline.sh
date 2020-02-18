#!/bin/sh

if [ $# -eq 0 ]; then
    echo "Syntax:"
    echo "    annotate_folder.sh <folder>"
    exit
fi

folder=$1
for f in $folder/*.txt; do
    echo $f
    fname=${f##*/}
    xpref=${fname%.*}
    php annotate_with_gazette.php gazette.stanford "$folder/$fname" > "../EVALUATE/system/subtrack1/baseline/$xpref.ann"
done
