<?php

    namespace Ling5821\Lconsul\Providers;

    use Carbon\Laravel\ServiceProvider;
    use Ling5821\Lconsul\Commands\ConsulCmd;
    use Ling5821\Lconsul\Commands\ConsulDeregister;
    use Ling5821\Lconsul\Commands\ConsulRefreshServices;
    use Ling5821\Lconsul\Commands\ConsulRegister;
    use Ling5821\Lconsul\Common\ConsulRpc;
    use Ling5821\Lconsul\ServiceManager;

    class ConsulServiceProvider extends ServiceProvider
    {
        public function boot()
        {
            if ($this->app->runningInConsole()) {
                $this->commands([
                                    ConsulCmd::class,
                                    ConsulRegister::class,
                                    ConsulDeregister::class,
                                    ConsulRefreshServices::class,
                                ]);
            }
        }

        public function register()
        {
            $this->app->singleton(ServiceManager::class, function ($app) {
                return new ServiceManager();
            });
            $this->app->singleton(ConsulRpc::class, function ($app) {
                return new ConsulRpc();
            });
        }


    }