<?php

namespace App\Services\Extraction;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * يقرأ تقرير المختبر (صورة أو PDF) عبر Google Gemini API ويعيد الحقول المنظّمة.
 */
class GeminiExtractor implements ReportExtractor
{
    private const PROMPT = <<<'TXT'
أنت نظام استخراج بيانات من تقارير المختبرات الطبية. حلّل الملف المرفق واستخرج الحقول التالية بدقة. أعد JSON فقط بدون أي نص إضافي أو علامات Markdown.

{
  "name": "اسم المريض",
  "patient_external_id": "Patient ID",
  "mobile": "Mobile No.",
  "referral_doctor": "Referral Doctor",
  "age_gender": "Age/Gender",
  "accession_no": "Accession No.",
  "report_status": "Report Status",
  "patient_ref_no": "Patient Ref. No.",
  "test_name": "اسم الفحص (Test)",
  "result": "النتيجة (Result)",
  "unit": "الوحدة (Unit)",
  "reference_range": "المدى المرجعي كاملاً في نص واحد"
}

ملاحظات: استخدم "" لأي حقل غير موجود. إن كان الحقل مموّهاً للخصوصية أعد "—محجوب—". إن وُجد أكثر من فحص ركّز على الأول. أعد JSON صالحاً فقط.
TXT;

    private const MODEL = 'gemini-2.0-flash';

    private readonly string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?: (string) config('services.gemini.key');
    }

    public function extract(string $storedPath, string $type): array
    {
        $bytes  = Storage::get($storedPath);
        $base64 = base64_encode($bytes);
        $mime   = $type === 'pdf' ? 'application/pdf' : $this->imageMime($storedPath);

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . self::MODEL . ':generateContent';

        $response = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
            ->timeout(60)
            ->post($endpoint, [
                'contents' => [[
                    'parts' => [
                        ['inline_data' => ['mime_type' => $mime, 'data' => $base64]],
                        ['text' => self::PROMPT],
                    ],
                ]],
                'generationConfig' => [
                    'temperature'      => 0,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('فشل استدعاء Gemini API: ' . $response->status() . ' ' . $response->body());
        }

        $text  = (string) $response->json('candidates.0.content.parts.0.text', '');
        $clean = trim(str_replace(['```json', '```'], '', $text));

        $data = json_decode($clean, true);
        if (! is_array($data)) {
            throw new RuntimeException('تعذّر تحليل ناتج الاستخراج كـ JSON');
        }

        return $data;
    }

    private function imageMime(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png'   => 'image/png',
            'webp'  => 'image/webp',
            'gif'   => 'image/gif',
            default => 'image/jpeg',
        };
    }
}
