<?php


    namespace Ling5821\Lconsul\Commands;


    use Illuminate\Console\Command;
    use Ling5821\Lconsul\ServiceManager;

    class ConsulCmd extends Command
    {
        /**
         * The name and signature of the console command.
         * @var string
         */
        protected $signature = 'consul:do {argument}';
        /**
         * The console command description.
         * @var string
         */
        protected $description = 'consul';

        public function __construct()
        {
            parent::__construct();

        }

        public function handle()
        {
            $argument = $this->argument('argument');
            switch ($argument) {
                case 'register':
                    app(ServiceManager::class)->register();
                    break;
                case 'deregister':
                    app(ServiceManager::class)->deregister();
                    break;
                case 'refreshServices':
                    app(ServiceManager::class)->refreshServices();
                    break;
                default:
                    break;
            }
        }

    }