#!/bin/bash
./rssfeedbot.php 2>&1 |tee ./logs/log-$(date +%s).txt