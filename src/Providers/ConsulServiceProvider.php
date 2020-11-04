<?php

    namespace Cloud\Providers;

    use Carbon\Laravel\ServiceProvider;
    use Illuminate\Console\Command;

    class ConsulServiceProvider extends ServiceProvider
    {
        public function boot()
        {
            if ($this->app->runningInConsole()) {
                $this->commands([
                                    Command::class,
                                ]);
            }
        }

        public function register()
        {
            parent::register();
        }


    }