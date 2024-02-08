<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use ReflectionClass;
use App\Console\Commands\CrawlerBatch;

class CrawlerBatchTest extends TestCase
{

    private function getMethod(string $name): \ReflectionMethod
    {
        $class = new ReflectionClass(CrawlerBatch::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @test
     */
    public function バッチがコマンドで起動すること(): void
    {
        $this->artisan('batch:crawler')
            ->expectsOutput('Start batch:crawler')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function googleApiPhpClientのインストール確認(): void
    {
        $this->assertTrue(class_exists('Google_Client'));
    }

    /**
     * @test
     */
    public function googleApiPhpClientインスタンス化確認(): void
    {
        $result = $this->getMethod('connectYoutubeAPI')->invoke(new CrawlerBatch());
        $this->assertInstanceOf('Google_Service_YouTube', $result);
    }

    /**
     * @test
     */
    public function 取得対象データ読込確認(): void
    {
        $result = $this->getMethod('loadTarget')->invoke(new CrawlerBatch());
        $this->assertIsArray($result);
    }
}
