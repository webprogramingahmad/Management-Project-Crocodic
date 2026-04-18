<?php

namespace App\Support;

class TaskDescriptionFormatter
{
    /**
     * Teks biasa di-escape; URL http/https menjadi tautan (target _blank, rel aman).
     */
    public static function toHtml(?string $raw): string
    {
        if ($raw === null || trim($raw) === '') {
            return '';
        }

        $raw = (string) $raw;
        $parts = preg_split('#(https?://\S+)#iu', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            if (preg_match('#^https?://#iu', $part)) {
                $url = rtrim($part, ',.;:!?@\'"]');
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $e = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                    $out .= '<a href="'.$e.'" target="_blank" rel="noopener noreferrer">'.$e.'</a>';

                    continue;
                }
            }

            $out .= htmlspecialchars($part, ENT_QUOTES, 'UTF-8');
        }

        return nl2br($out, false);
    }
}
