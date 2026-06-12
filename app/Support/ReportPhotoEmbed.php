<?php

namespace App\Support;

class ReportPhotoEmbed
{
    /**
     * Embed foto sebagai data URI agar dompdf bisa render tanpa URL eksternal.
     */
    public static function dataUri(?string $storagePath): ?string
    {
        if ($storagePath === null || $storagePath === '') {
            return null;
        }

        $fullPath = storage_path('app/public/' . ltrim($storagePath, '/'));
        if (! is_file($fullPath)) {
            return null;
        }

        $mime = @mime_content_type($fullPath) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($fullPath));
    }
}
