#!/bin/bash
# ponytail: simple mysql wrapper for remote railway db
mysql -h hayabusa.proxy.rlwy.net -P 14839 -u root -pwefoYsoyPQlGHGhpNUeIvjJGpEYcJJYD railway "$@"
