<?php

if (! function_exists('sanitizeStoragePath')) {
    /**
     * Ensure the given path is relative to storage/app.
     */
    function sanitizeStoragePath(string $rawPath): string
    {
        $normalized = str_replace('\\', '/', $rawPath);
        $storageApp = str_replace('\\', '/', storage_path('app'));

        if (str_starts_with($normalized, $storageApp.'/')) {
            $normalized = substr($normalized, strlen($storageApp)+1);
        } elseif (($pos = strpos($normalized, 'storage/app/')) !== false) {
            $normalized = substr($normalized, $pos + strlen('storage/app/'));
        }

        return ltrim($normalized, '/');
    }
}
