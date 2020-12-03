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
            $this->consul              = new Consul();
            $this->appId               = config('consul.service.id');
            $this->appName             = config('consul.service.name');
            $this->serviceHost         = config('consul.service.addr');
            $this->servicePort         = (int)config('consul.service.port');
            $this->healthCheckUrl      = config('consul.service.health_check_url');
            $this->healthCheckInterval = (int)config('consul.service.health_check_interval');
        }

        public function register()
        {
            $registration = new AgentServiceRegistration();
            $registration->setID($this->appId);
            $registration->setName($this->appName);
            $registration->setAddress($this->serviceHost);
            $registration->setPort($this->servicePort);
            $check = new AgentServiceCheck();
            $check->setHTTP($this->healthCheckUrl);
            $check->setInterval($this->healthCheckInterval);
            $registration->setCheck($check);
            $err = $this->consul->Agent->serviceRegister($registration);
            if (NULL !== $err) {
                throw new \RuntimeException($err);
            }
        }

        public function deregister()
        {
            $err = $this->consul->Agent->serviceDeregister($this->appId);
            if (NULL !== $err) {
                throw new \RuntimeException($err);
            }
        }

        public function refreshServices()
        {
            $serviceNames = config('consul.consul_services');
            if (sizeof($serviceNames)) {
                foreach ($serviceNames as $serviceName) {
                    $servicesKey     = Utils::getServiceKey($serviceName);
                    $serviceInfosStr = RedisUtils::get($servicesKey);
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