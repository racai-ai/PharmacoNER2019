#!/bin/sh

/data/vasile/ner/jdk1.8.0_191/bin/java \
    -cp "../stanford-ner-2018-10-16-pvf/classes:../stanford-ner-2018-10-16-pvf/lib/*" \
    -mx20g edu.stanford.nlp.ie.crf.CRFClassifier \
    -prop prop_run_pharmaconer.txt \
    -testFile $1
