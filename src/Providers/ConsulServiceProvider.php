<?php

    namespace Ling5821\Lconsul\Providers;

    use Carbon\Laravel\ServiceProvider;
    use Ling5821\Lconsul\Commands\ConsulCmd;
    use Ling5821\Lconsul\Commands\ConsulDeregister;
    use Ling5821\Lconsul\Commands\ConsulRegister;
    use Ling5821\Lconsul\Commands\ConsulServicesRefresh;
    use Ling5821\Lconsul\ServiceNode;

    class ConsulServiceProvider extends ServiceProvider
    {
        public function boot()
        {
            if ($this->app->runningInConsole()) {
                $this->commands([
                                    ConsulCmd::class,
                                    ConsulRegister::class,
                                    ConsulDeregister::class,
                                    ConsulServicesRefresh::class,
                                ]);
            }
        }

        public function register()
        {
            $this->app->singleton(ServiceNode::class, function ($app) {
                return new ServiceNode();
            });
        }


    }