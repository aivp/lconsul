<?php


    namespace Ling5821\Lconsul\Commands;


    use Illuminate\Console\Command;
    use Ling5821\Lconsul\ServiceManager;

    class ConsulRefreshServices extends Command
    {
        /**
         * The name and signature of the console command.
         * @var string
         */
        protected $signature = 'consul:refreshServices';
        /**
         * The console command description.
         * @var string
         */
        protected $description = 'consul refreshServices';

        public function __construct()
        {
            parent::__construct();
        }

        public function handle()
        {
            app(ServiceManager::class)->refreshServices();
        }



    }