<?php

namespace App\Services\Extraction;

interface ReportExtractor
{
    /**
     * Read a lab report and return the structured fields.
     *
     * @param  string  $storedPath  path within the storage disk
     * @param  string  $type        image | pdf
     * @return array<string, mixed>
     */
    public function extract(string $storedPath, string $type): array;
}
