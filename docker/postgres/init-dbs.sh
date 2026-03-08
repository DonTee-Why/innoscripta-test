#!/bin/bash
set -e
# Create hr_db for hr-service (ignore error if it already exists)
psql -v ON_ERROR_STOP=0 --username "$POSTGRES_USER" --dbname "postgres" -c "CREATE DATABASE hr_db;" || true
