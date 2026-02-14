<?php

namespace App\Support;

use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\Mime\MimeTypes;

/**
 * Fallback MIME type guesser using file extensions.
 * Used when fileinfo extension is not available.
 */
class ExtensionMimeTypeGuesser implements MimeTypeGuesserInterface
{
    public function isGuesserSupported(): bool
    {
        // Always supported as it only uses file extensions
        return true;
    }

    public function guessMimeType(string $path): ?string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        if (empty($extension)) {
            return null;
        }

        // Use Symfony's built-in extension-to-MIME mapping
        $mimeTypes = MimeTypes::getDefault()->getMimeTypes($extension);
        
        return $mimeTypes[0] ?? null;
    }
}
