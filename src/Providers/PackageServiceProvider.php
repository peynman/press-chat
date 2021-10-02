<?php

namespace Larapress\Chat\Providers;

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider;
use Larapress\Chat\Services\Chat\ChatRepository;
use Larapress\Chat\Services\Chat\ChatService;
use Larapress\Chat\Services\Chat\IChatRepository;
use Larapress\Chat\Services\Chat\IChatService;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(IChatService::class, ChatService::class);
        $this->app->bind(IChatRepository::class, ChatRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @param  BroadcastManager $broadcastManager
     * @return void
     */
    public function boot(BroadcastManager $broadcastManager)
    {
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'larapress');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../../migrations');

        $this->publishes([
            __DIR__.'/../../config/chat.php' => config_path('larapress/chat.php'),
        ], ['config', 'larapress', 'larapress-chat']);
    }
}
