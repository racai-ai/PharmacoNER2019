#!/bin/sh

folder=subtask1
for f in $folder/*.ann; do
    echo $f
    php /data/vasile/spanish/ann_combine/ann_keep_only.php \
	-inFile:$f \
	 -outFile:$f \
	 -keep:NORMALIZABLES -keep:PROTEINAS -keep:NO_NORMALIZABLES
done
