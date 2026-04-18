#!/bin/bash

set -e

DEFAULT_PATH="/var/www/sprinkla"
LOG_DIR="/var/log/sprinkla"
SPRINKLA_USER="sprinkla"
SPRINKLA_GROUP="sprinkla"
WEB_USER="www-data"

usage() {
    cat <<EOF
Usage: install.sh [OPTIONS]

Set up system users, groups, permissions, and log rotation for Sprinkla.

This script will:
  1. Create a '$SPRINKLA_GROUP' system group
  2. Create a '$SPRINKLA_USER' system user (member of the group, no login)
  3. Add '$WEB_USER' to the '$SPRINKLA_GROUP' group
  4. Create the log directory at $LOG_DIR with correct ownership
  5. Set ownership and permissions on the project directory
  6. Install the logrotate configuration for daily log rotation

Options:
  -h, --help     Show this help message and exit
  -p, --path     Path to the Sprinkla project root (default: $DEFAULT_PATH)

Examples:
  sudo ./install.sh
  sudo ./install.sh -p /opt/sprinkla
EOF
    exit 0
}

PROJECT_PATH="$DEFAULT_PATH"

while [[ $# -gt 0 ]]; do
    case "$1" in
        -h|--help)  usage ;;
        -p|--path)
            PROJECT_PATH="$2"
            shift 2
            ;;
        *)
            echo "Unknown option: $1"
            echo "Run with --help for usage information."
            exit 1
            ;;
    esac
done

# Remove trailing slash
PROJECT_PATH="${PROJECT_PATH%/}"

if [ "$(id -u)" -ne 0 ]; then
    echo "Error: this script must be run as root (use sudo)."
    exit 1
fi

if [ ! -d "$PROJECT_PATH" ]; then
    echo "Error: project directory $PROJECT_PATH does not exist."
    exit 1
fi

echo "=== Sprinkla System Setup ==="
echo "Project path: $PROJECT_PATH"
echo ""

# 1. Create group
if getent group "$SPRINKLA_GROUP" > /dev/null 2>&1; then
    echo "Group '$SPRINKLA_GROUP' already exists."
else
    groupadd --system "$SPRINKLA_GROUP"
    echo "Created system group '$SPRINKLA_GROUP'."
fi

# 2. Create user
if id "$SPRINKLA_USER" > /dev/null 2>&1; then
    echo "User '$SPRINKLA_USER' already exists."
else
    useradd --system --gid "$SPRINKLA_GROUP" --shell /usr/sbin/nologin --no-create-home "$SPRINKLA_USER"
    echo "Created system user '$SPRINKLA_USER'."
fi

# 3. Add web server user to sprinkla group
if id -nG "$WEB_USER" 2>/dev/null | grep -qw "$SPRINKLA_GROUP"; then
    echo "User '$WEB_USER' is already a member of '$SPRINKLA_GROUP'."
else
    usermod -a -G "$SPRINKLA_GROUP" "$WEB_USER"
    echo "Added '$WEB_USER' to group '$SPRINKLA_GROUP'."
    echo "  Note: restart Apache/nginx for the group change to take effect."
fi

# 4. Create log directory
if [ -d "$LOG_DIR" ]; then
    echo "Log directory $LOG_DIR already exists."
else
    mkdir -p "$LOG_DIR"
    echo "Created log directory $LOG_DIR."
fi
chown "$SPRINKLA_USER:$SPRINKLA_GROUP" "$LOG_DIR"
chmod 775 "$LOG_DIR"
echo "Set ownership on $LOG_DIR to $SPRINKLA_USER:$SPRINKLA_GROUP (775)."

# 5. Set project directory permissions
chown -R "$SPRINKLA_USER:$SPRINKLA_GROUP" "$PROJECT_PATH"
find "$PROJECT_PATH" -type d -exec chmod 755 {} \;
find "$PROJECT_PATH" -type f -exec chmod 644 {} \;

# Make shell scripts executable
find "$PROJECT_PATH" -name "*.sh" -exec chmod 755 {} \;

# Directories that need group write access (web server writes here)
mkdir -p "$PROJECT_PATH/data"
chown "$SPRINKLA_USER:$SPRINKLA_GROUP" "$PROJECT_PATH/data"
chmod 775 "$PROJECT_PATH/data"

echo "Set ownership on $PROJECT_PATH to $SPRINKLA_USER:$SPRINKLA_GROUP."

# 6. Install logrotate configuration
LOGROTATE_SRC="$PROJECT_PATH/config/logrotate.conf"
LOGROTATE_DST="/etc/logrotate.d/sprinkla"

if [ -f "$LOGROTATE_SRC" ]; then
    cp "$LOGROTATE_SRC" "$LOGROTATE_DST"
    chmod 644 "$LOGROTATE_DST"
    echo "Installed logrotate config to $LOGROTATE_DST."
else
    echo "Warning: $LOGROTATE_SRC not found, skipping logrotate setup."
fi

echo ""
echo "=== Setup Complete ==="
echo ""
echo "Next steps:"
echo "  1. Restart your web server and PHP-FPM for the group change to take effect."
echo "     Both services must be restarted — Apache/nginx alone is not enough when"
echo "     using PHP-FPM, as it runs as a separate process with its own group list."
echo ""
echo "       sudo systemctl restart apache2"
echo "       sudo systemctl restart php8.4-fpm    # adjust version as needed"
echo ""
echo "     Verify PHP-FPM picked up the new group:"
echo "       cat /proc/\$(pgrep -f 'php-fpm: pool' | head -1)/status | grep Groups"
echo ""
echo "  2. Install the cron job:"
echo "       sudo ./install-cron.sh"
echo "  3. Verify log directory is writable:"
echo "       sudo -u $WEB_USER touch $LOG_DIR/test && rm $LOG_DIR/test && echo OK"
