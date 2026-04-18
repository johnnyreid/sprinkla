<?php declare(strict_types=1);

function sprinkla_log_dir(): string
{
    return $GLOBALS['config']['log_dir'] ?? '/var/log/sprinkla';
}

function sprinkla_log_access(string $message): void
{
    $logFile = sprinkla_log_dir() . '/access.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

function sprinkla_log_operation(string $message): void
{
    $logFile = sprinkla_log_dir() . '/operations.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}
