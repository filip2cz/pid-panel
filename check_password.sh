#!/bin/bash
username=$1
password=$2

echo "$password" | su -c "exit" "$username" > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "OK"
else
    echo "FAIL"
fi