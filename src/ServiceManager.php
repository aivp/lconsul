<?php


    namespace Ling5821\Lconsul;


    use DCarbone\PHPConsulAPI\Agent\AgentServiceCheck;
    use DCarbone\PHPConsulAPI\Agent\AgentServiceRegistration;
    use DCarbone\PHPConsulAPI\Consul;
    use DCarbone\PHPConsulAPI\QueryOptions;
    use Illuminate\Support\Facades\Log;
    use Ling5821\Lconsul\Common\RedisUtils;
    use Ling5821\Lconsul\Common\Utils;

    class ServiceManager
    {
        private $consul;
        private $appId;
        private $appName;
        private $serviceHost;
        private $servicePort;
        private $healthCheckUrl;
        private $healthCheckInterval;

        /**
         * ServiceNode constructor.
         */
        public function __construct()
        {
            $this->consul = new Consul();
            $this->appId = env('SERVICE_ID', 'my-service-01');
            $this->appName = env("SERVICE_NAME", 'my-service');
            $this->serviceHost = env("SERVER_ADDR", '127.0.0.1');
            $this->servicePort = (int)env('SERVICE_PORT', 80);
            $this->healthCheckUrl = env('SERVICE_HEALTH_CHECK_URL', "http://$this->serviceHost:$this->servicePort/api/health");
            $this->healthCheckInterval = env('SERVICE_HEALTH_CHECK_INTERVAL', 30);
        }

        public function register()
        {
            $registration = new AgentServiceRegistration();
            $registration->setID($this->appId);
            $registration->setName($this->appName);
            $registration->setAddress($this->serviceHost);
            $registration->setPort($this->servicePort);
            $check           = new AgentServiceCheck();
            $check->HTTP     = $this->healthCheckUrl;
            $check->Interval = $this->healthCheckInterval;
            $registration->setCheck($check);
            $err = $this->consul->Agent->serviceRegister($registration);
            if (NULL !== $err) {
                throw new \RuntimeException($err);
            }
        }

        public function deregister() {
            $err = $this->consul->Agent->serviceDeregister($this->appId);
            if (NULL !== $err) {
                throw new \RuntimeException($err);
            }
        }

        public function refreshServices()
        {
            if (Utils::isNotNullStr(env('CONSUL_SERVICES'))) {
                $serviceNames = explode(',', env('CONSUL_SERVICES'));
                foreach ($serviceNames as $serviceName) {
                    $servicesKey     = Utils::getServiceKey($serviceName);
                    $serviceInfosStr = RedisUtils::get(Utils::getServiceKey($servicesKey));
                    $waitIndex       = 0;
                    if (Utils::isNotNullStr($serviceInfosStr)) {
                        $serviceInfos = json_decode($serviceInfosStr);
                        $waitIndex    = $serviceInfos->waitIndex;
                    }
                    $queryOptions = new QueryOptions();
                    $queryOptions->setWaitIndex($waitIndex);
                    $queryOptions->setWaitTime(30 * 1000 * 1000 * 1000);
                    [$services, $qm, $err] = $this->consul->Health->service($serviceName, '', TRUE, $queryOptions);
                    if (NULL !== $err) {
                        throw new \RuntimeException($err);
                    }
                    if ($qm->getLastIndex() != $waitIndex) {
                        $addressList             = collect($services)->map(function ($item) {
                            return $item->Service->Address . ':' . $item->Service->Port;
                        })->unique()->values()->toArray();
                        $serviceInfos            = new \stdClass();
                        $serviceInfos->waitIndex = $qm->getLastIndex();
                        $serviceInfos->addresses = $addressList;
                        Log::info("handleRefresh:" . json_encode($serviceInfos));
                        RedisUtils::set($servicesKey, json_encode($serviceInfos));
                    } else {
                        Log::info("handleRefresh no update");
                    }
                }

            }


        }
    }