<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ExportsCsv
{
    /**
     * يبثّ ملف CSV متوافق مع Excel العربي (مع BOM لـ UTF-8).
     *
     * @param  iterable<array<int,string|int|null>>  $rows
     * @param  array<int,string>  $headers
     */
    protected function streamCsv(string $name, array $headers, iterable $rows): StreamedResponse
    {
        $rows = $rows instanceof Collection ? $rows : collect($rows);
        $filename = $name.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM ليقرأ Excel العربية بشكل صحيح
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
