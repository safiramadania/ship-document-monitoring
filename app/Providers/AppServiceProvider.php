<?php

namespace App\Providers;

use App\Contracts\AiExtractionProviderInterface;
use App\Contracts\OcrProviderInterface;
use App\Services\Ocr\FakeAiExtractionProvider;
use App\Services\Ocr\FakeOcrProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OcrProviderInterface::class, FakeOcrProvider::class);
        $this->app->bind(AiExtractionProviderInterface::class, FakeAiExtractionProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
