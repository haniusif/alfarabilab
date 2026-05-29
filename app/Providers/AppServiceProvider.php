<?php

namespace App\Providers;

use App\Services\Extraction\GeminiExtractor;
use App\Services\Extraction\ReportExtractor;
use App\Services\Extraction\TesseractExtractor;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ReportExtractor::class, function () {
            return match (config('services.ocr.driver', 'tesseract')) {
                'gemini' => new GeminiExtractor(),
                default  => new TesseractExtractor(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
