#!/bin/sh

folder1=../EVALUATE/system/subtrack1/baseline_radu_3/
folder2=../EVALUATE/system/subtrack1/radu_test_3/
fout1=../EVALUATE/system/subtrack1/c1_radu_3
fout2=../EVALUATE/system/subtrack1/c2_radu_3
fout3=../EVALUATE/system/subtrack1/c3_radu_3
fout4=../EVALUATE/system/subtrack1/c4_radu_3
fout5=../EVALUATE/system/subtrack1/c5_radu_3

mkdir $fout1
mkdir $fout2
mkdir $fout3
mkdir $fout4
mkdir $fout5

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
	-outFile:$fout2/$fname \
	-type:PRIO2

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder2/$fname \
	-outFile:$fout3/$fname \
	-type:SMALLER

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder2/$fname \
	-outFile:$fout4/$fname \
	-type:LARGER

    php ann_combine.php \
	-inFile2:$folder1/$fname \
	-inFile1:$folder2/$fname \
	-outFile:$fout5/$fname \
	-type:APPARITIONS

done
