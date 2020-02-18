#!/bin/sh

/data/vasile/ner/jdk1.8.0_191/bin/java \
    -cp "../stanford-ner-2018-10-16-pvf/classes:../stanford-ner-2018-10-16-pvf/lib/*" \
    -mx15g  -XX:+UseStringDeduplication -XX:+UseG1GC ner_server \
    -prop prop_server_pharmaconer.txt
