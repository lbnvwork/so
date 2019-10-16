#!/usr/bin/env bash
php vendor/bin/doctrine orm:convert-mapping --force --from-database annotation  data/
