#!/bin/sh

if [ $# -eq 0 ]; then
    echo "Syntax:"
    echo "    annotate_folder.sh <folder>"
    exit
fi

folder=$1
for f in $folder/*.tab.ann; do
    echo $f
    fname=${f##*/}
    xpref=${fname%.*}
    cd ner_html ; php ner.php "../$folder/$fname" "read" > "../$folder/$fname.vasilener" ; cd ..
done
