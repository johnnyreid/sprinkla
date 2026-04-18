#!/bin/bash

DEFAULT_PATH="/var/www/sprinkla"
CRON_FILE="/etc/cron.d/sprinkla"
FORCE=false

usage() {
    cat <<EOF
Usage: install-cron.sh [OPTIONS]

Install the Sprinkla cron job into /etc/cron.d/sprinkla.

The cron job runs every minute and performs two checks:
  1. Turns off sprinklers whose auto-off timer has expired.
  2. Turns off any sprinkler that has been running longer than the
     configured safety timeout (max_run_minutes, default 60 min).

Options:
  -h, --help     Show this help message and exit
  -f, --force    Overwrite an existing cron entry

The script will prompt for the Sprinkla project root directory
(default: $DEFAULT_PATH).
EOF
    exit 0
}

for arg in "$@"; do
    case "$arg" in
        -h|--help)  usage ;;
        -f|--force) FORCE=true ;;
        *)
            echo "Unknown option: $arg"
            echo "Run with --help for usage information."
            exit 1
            ;;
    esac
done

read -p "Enter the path to the Sprinkla project root [$DEFAULT_PATH]: " PROJECT_PATH
PROJECT_PATH="${PROJECT_PATH:-$DEFAULT_PATH}"

# Remove trailing slash
PROJECT_PATH="${PROJECT_PATH%/}"

CRON_SCRIPT="$PROJECT_PATH/src/scripts/timer/cron.php"

if [ ! -f "$CRON_SCRIPT" ]; then
    echo "Error: $CRON_SCRIPT not found."
    exit 1
fi

PHP_BIN=$(which php)
if [ -z "$PHP_BIN" ]; then
    echo "Error: php not found in PATH."
    exit 1
fi

CRON_COMMENT="# Sprinkla: check for expired sprinkler timers and turn off GPIO pins"
CRON_LINE="* * * * * sprinkla $PHP_BIN $CRON_SCRIPT"

if [ -f "$CRON_FILE" ] && grep -qF "$CRON_SCRIPT" "$CRON_FILE"; then
    if [ "$FORCE" = true ]; then
        echo "Overwriting existing cron entry in $CRON_FILE"
    else
        echo "Cron entry already exists in $CRON_FILE (use -f to overwrite)."
        exit 0
    fi
fi

printf "%s\n%s\n" "$CRON_COMMENT" "$CRON_LINE" | sudo tee "$CRON_FILE" > /dev/null
sudo chmod 644 "$CRON_FILE"

echo "Cron entry installed at $CRON_FILE:"
echo "  $CRON_LINE"
