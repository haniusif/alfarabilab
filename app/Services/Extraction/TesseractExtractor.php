<?php

namespace App\Services\Extraction;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * استخراج مجاني بالكامل (بدون اشتراك / بدون إنترنت) عبر Tesseract OCR:
 *   PDF رقمي  → pdftotext (طبقة النص)
 *   PDF مصوّر → pdftoppm ثم Tesseract
 *   صورة      → Tesseract مباشرة
 * ثم محلّل نصّي يملأ نفس الحقول التي تعيدها بقية المستخرِجات.
 */
class TesseractExtractor implements ReportExtractor
{
    /** الحقول التي يطابقها المحلّل سطر «التسمية : القيمة» (المفتاح => نمط التسمية) */
    private const LABELS = [
        'name'                => 'Name',
        'patient_external_id' => 'Patient\s*ID',
        'mobile'              => 'Mobile(?:\s*No\.?)?',
        'referral_doctor'     => 'Referr?al\s*Doctor',
        'age_gender'          => 'Age\s*/?\s*(?:Sex|Gender)',
        'accession_no'        => 'Accession\s*No\.?',
        'report_status'       => 'Report\s*Status',
        'patient_ref_no'      => 'Patient\s*Ref\.?(?:\s*No\.?)?',
    ];

    public function extract(string $storedPath, string $type): array
    {
        $absolute = Storage::path($storedPath);

        $text = $type === 'pdf'
            ? $this->readPdf($absolute)
            : $this->ocr($absolute);

        return $this->parse($text);
    }

    /* ---------------- OCR / text extraction ---------------- */

    private function readPdf(string $pdf): string
    {
        // 1) محاولة قراءة طبقة النص مباشرة (PDF رقمي)
        $text = $this->run(['pdftotext', '-layout', $pdf, '-']);
        if (mb_strlen(trim($text)) >= 30) {
            return $text;
        }

        // 2) PDF مصوّر: حوّل أول صفحة إلى صورة ثم OCR
        $prefix = sys_get_temp_dir() . '/ocr_' . uniqid();
        try {
            $this->run(['pdftoppm', '-png', '-r', '300', '-f', '1', '-l', '1', $pdf, $prefix]);
            $pages = glob($prefix . '*.png') ?: [];
            if ($pages === []) {
                throw new RuntimeException('تعذّر تحويل PDF إلى صورة للـOCR');
            }

            return $this->ocr($pages[0]);
        } finally {
            foreach (glob($prefix . '*.png') ?: [] as $tmp) {
                @unlink($tmp);
            }
        }
    }

    private function ocr(string $image): string
    {
        // psm 6: كتلة نصّ موحّدة — مناسبة لتقارير المختبر
        return $this->run(['tesseract', $image, 'stdout', '-l', 'ara+eng', '--psm', '6']);
    }

    private function run(array $cmd): string
    {
        $path = '/opt/homebrew/bin:/usr/local/bin:/usr/bin:/bin';

        $result = Process::env(['PATH' => $path . ':' . (getenv('PATH') ?: '')])
            ->timeout(120)
            ->run($cmd);

        if (! $result->successful()) {
            throw new RuntimeException('فشل تشغيل ' . $cmd[0] . ': ' . trim($result->errorOutput()));
        }

        return $result->output();
    }

    /* ---------------- parsing ---------------- */

    private function parse(string $text): array
    {
        $fields = array_fill_keys([
            'name', 'patient_external_id', 'mobile', 'referral_doctor', 'age_gender',
            'accession_no', 'report_status', 'patient_ref_no',
            'test_name', 'result', 'unit', 'reference_range',
        ], '');

        // مواضع كل التسميات (لتحديد نهاية القيمة عند بداية التسمية التالية)
        $labelStarts = [];
        foreach (self::LABELS as $pattern) {
            if (preg_match('#' . $pattern . '\s*[:：]#iu', $text, $m, PREG_OFFSET_CAPTURE)) {
                $labelStarts[] = $m[0][1];
            }
        }

        foreach (self::LABELS as $key => $pattern) {
            if (! preg_match('#' . $pattern . '\s*[:：]\s*#iu', $text, $m, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            $valueStart = $m[0][1] + strlen($m[0][0]);

            // نهاية القيمة = أقرب (تسمية تالية | نهاية السطر | نهاية النص)
            $end = strlen($text);
            foreach ($labelStarts as $pos) {
                if ($pos > $valueStart && $pos < $end) {
                    $end = $pos;
                }
            }
            $newline = strpos($text, "\n", $valueStart);
            if ($newline !== false && $newline < $end) {
                $end = $newline;
            }

            $fields[$key] = $this->clean(substr($text, $valueStart, $end - $valueStart));
        }

        if ($fields['mobile'] !== '') {
            $fields['mobile'] = preg_replace('/[^\d+]/', '', $fields['mobile']);
        }

        $this->parseTestRow($text, $fields);

        $fields['_raw_text'] = trim($text);

        return $fields;
    }

    /** أول صف بيانات تحت ترويسة الجدول: Test | Result | Unit | Reference Range */
    private function parseTestRow(string $text, array &$fields): void
    {
        $lines  = preg_split('/\r?\n/', $text) ?: [];
        $header = null;

        foreach ($lines as $i => $line) {
            if (preg_match('/\bTest\b/i', $line) && preg_match('/\bResult\b/i', $line)) {
                $header = $i;
                break;
            }
        }
        if ($header === null) {
            return;
        }

        for ($i = $header + 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '' || ! preg_match('/\d/', $line)) {
                continue;
            }

            // اسم الفحص … نتيجة(رقم) … وحدة … مدى مرجعي
            if (preg_match('/^(.*?)\s{2,}([\d.,]+)\s+(\S+)\s+(.*)$/u', $line, $m)
                || preg_match('/^(.+?)\s+([\d.,]+)\s+(\S+)\s+(.*)$/u', $line, $m)) {
                $fields['test_name']       = $this->clean($m[1]);
                $fields['result']          = $this->clean($m[2]);
                $fields['unit']            = $this->clean($m[3]);
                $fields['reference_range'] = $this->clean($m[4]);
            } else {
                $fields['test_name'] = $this->clean($line);
            }

            return;
        }
    }

    private function clean(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value));
    }
}
