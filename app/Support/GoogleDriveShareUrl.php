<?php

namespace App\Support;

class GoogleDriveShareUrl
{
    public static function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $parts = parse_url($value);
        if (! is_array($parts)) {
            return false;
        }

        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        $query = $parts['query'] ?? '';
        $isDriveHost = in_array($host, ['drive.google.com', 'docs.google.com'], true);
        $hasDriveResourcePath = $host === 'drive.google.com'
            && preg_match('#^/file/d/[^/]+(?:/|$)#', $path) === 1;
        $hasDriveResourceId = $host === 'drive.google.com'
            && in_array($path, ['/open', '/uc'], true)
            && preg_match('/(?:^|&)id=[^&]+/', $query) === 1;
        $hasDocsResourcePath = $host === 'docs.google.com'
            && preg_match('#^/(?:document|spreadsheets|presentation|forms)/d/[^/]+(?:/|$)#', $path) === 1;

        return ($parts['scheme'] ?? '') === 'https'
            && $isDriveHost
            && ($hasDriveResourcePath || $hasDriveResourceId || $hasDocsResourcePath);
    }
}