#!/usr/bin/env bash
php vendor/bin/doctrine orm:schema-tool:update --dump-sql $1
