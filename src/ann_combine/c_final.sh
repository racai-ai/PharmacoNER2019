#!/bin/sh

folder1=../RUN/run_baseline/
folder2=../RUN/radu/
fout1=../RUN/c1
fout4=../RUN/c4

mkdir $fout1
mkdir $fout4

#folder=test
#fout=test

#if [ $# -eq 0 ]; then
#    echo "Syntax:"
#    echo "    annotate_folder.sh <folder>"
#    exit
#fi

#folder=$1
for f in $folder1/*.ann; do
    echo $f
    fname=${f##*/}
    xpref=${fname%.*}
    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder2/$fname \
	-outFile:$fout1/$fname \
	-type:PRIO1


    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder2/$fname \
	-outFile:$fout4/$fname \
	-type:LARGER

done
