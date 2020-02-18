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
    echo $fname
    ./annotate_text.sh $f
    mv "$fname.xml" "$folder/"
    php stanford_xml_to_tab.php "$folder/$fname.xml" "$folder/$fname.tab"
    php tab_ann.php "$folder/$fname.tab" "$folder/$xpref.ann" "$folder/$fname.tab.ann"
done
