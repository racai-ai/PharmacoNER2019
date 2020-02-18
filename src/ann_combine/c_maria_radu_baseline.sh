#!/bin/sh

folder1=../EVALUATE/system/subtrack1/baseline_radu_2/
folder21=../EVALUATE/system/subtrack1/c1_maria_2/
folder22=../EVALUATE/system/subtrack1/c2_maria_2/
folder23=../EVALUATE/system/subtrack1/c3_maria_2/
folder24=../EVALUATE/system/subtrack1/c4_maria_2/
fout1=../EVALUATE/system/subtrack1/c1_c1_brmaria_2
fout2=../EVALUATE/system/subtrack1/c2_c1_brmaria_2
fout3=../EVALUATE/system/subtrack1/c3_c1_brmaria_2
fout4=../EVALUATE/system/subtrack1/c4_c1_brmaria_2
fout5=../EVALUATE/system/subtrack1/c1_c2_brmaria_2
fout6=../EVALUATE/system/subtrack1/c2_c2_brmaria_2
fout7=../EVALUATE/system/subtrack1/c3_c2_brmaria_2
fout8=../EVALUATE/system/subtrack1/c4_c2_brmaria_2
fout9=../EVALUATE/system/subtrack1/c1_c3_brmaria_2
fout10=../EVALUATE/system/subtrack1/c2_c3_brmaria_2
fout11=../EVALUATE/system/subtrack1/c3_c3_brmaria_2
fout12=../EVALUATE/system/subtrack1/c4_c3_brmaria_2
fout13=../EVALUATE/system/subtrack1/c1_c4_brmaria_2
fout14=../EVALUATE/system/subtrack1/c2_c4_brmaria_2
fout15=../EVALUATE/system/subtrack1/c3_c4_brmaria_2
fout16=../EVALUATE/system/subtrack1/c4_c4_brmaria_2

mkdir $fout1
mkdir $fout2
mkdir $fout3
mkdir $fout4
mkdir $fout5
mkdir $fout6
mkdir $fout7
mkdir $fout8
mkdir $fout9
mkdir $fout10
mkdir $fout11
mkdir $fout12
mkdir $fout13
mkdir $fout14
mkdir $fout15
mkdir $fout16

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
	-inFile2:$folder21/$fname \
	-outFile:$fout1/$fname \
	-type:PRIO1

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder21/$fname \
	-outFile:$fout2/$fname \
	-type:PRIO2

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder21/$fname \
	-outFile:$fout3/$fname \
	-type:SMALLER

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder21/$fname \
	-outFile:$fout4/$fname \
	-type:LARGER


    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder22/$fname \
	-outFile:$fout5/$fname \
	-type:PRIO1

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder22/$fname \
	-outFile:$fout6/$fname \
	-type:PRIO2

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder22/$fname \
	-outFile:$fout7/$fname \
	-type:SMALLER

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder22/$fname \
	-outFile:$fout8/$fname \
	-type:LARGER


    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder23/$fname \
	-outFile:$fout9/$fname \
	-type:PRIO1

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder23/$fname \
	-outFile:$fout10/$fname \
	-type:PRIO2

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder23/$fname \
	-outFile:$fout11/$fname \
	-type:SMALLER

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder23/$fname \
	-outFile:$fout12/$fname \
	-type:LARGER


    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder24/$fname \
	-outFile:$fout13/$fname \
	-type:PRIO1

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder24/$fname \
	-outFile:$fout14/$fname \
	-type:PRIO2

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder24/$fname \
	-outFile:$fout15/$fname \
	-type:SMALLER

    php ann_combine.php \
	-inFile1:$folder1/$fname \
	-inFile2:$folder24/$fname \
	-outFile:$fout16/$fname \
	-type:LARGER


done
