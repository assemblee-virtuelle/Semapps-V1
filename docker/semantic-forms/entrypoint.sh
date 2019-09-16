#!/usr/bin/env bash

cd /semantic_forms

rm RUNNING_PID

./start.sh

tail -f /dev/null