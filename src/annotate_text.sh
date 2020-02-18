#!/bin/sh

if [ $# -eq 0 ]; then
    echo "Syntax:"
    echo "    annotate_text.sh <text_file>"
    exit
fi

/data/vasile/spanish/jdk1.8.0_191/bin/java -mx5g \
    -cp stanford-corenlp-full-2018-02-27/*:stanford-corenlp-3.9.1.jar:stanford-spanish-corenlp-2018-02-27-models.jar \
    edu.stanford.nlp.pipeline.StanfordCoreNLP \
    -properties StanfordCoreNLP-spanish.properties \
    -outputFormat xml \
    -file $1
