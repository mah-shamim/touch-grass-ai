#!/usr/bin/env bash
set -e
curl -fsS http://localhost:8081/api/health | sed 's/,/,&\n/g'
