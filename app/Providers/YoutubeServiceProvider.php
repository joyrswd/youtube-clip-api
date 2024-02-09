<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Google\Client;
use Google\Service\YouTube;

class YoutubeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(YouTube::class, function ($app) {
            $client = new Client();
            $client->setApplicationName("Youtube Crawler");
            $client->setDeveloperKey(env('YOUTUBE_API_KEY'));
            return new YouTube($client);
        });
    }
}
