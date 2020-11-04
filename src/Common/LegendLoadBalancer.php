<?php


    namespace Ling5821\Lconsul\Common;


    class LegendLoadBalancer
    {
        private $serviceName;
        private $increaseKey;

        /**
         * @param string $serviceName
         */
        public function __construct($serviceName)
        {
            $this->serviceName = $serviceName;
            $this->increaseKey = "LegendLoadBalance:increase:$serviceName";
        }

        public function choose($services)
        {
            if ($services == NULL || !is_array($services) || count($services) == 0) {
                return NULL;
            }
            $serviceCount    = count($services);
            $increasedNumber = RedisUtils::increase($this->increaseKey);
            $chooseIndex     = $increasedNumber % $serviceCount;

            return $services[ $chooseIndex ];
        }
    }
