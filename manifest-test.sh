#!/bin/bash
set -xe
cd $(dirname $0)
exec php $(basename $0 .sh).php
