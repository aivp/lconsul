<?php
    namespace Cloud\Common;

    class Utils
    {
        /**
         * 此方法判断是否不为空字符串除null 未定义 '' 外都会返回true
         *
         * @param $str
         *
         * @return bool
         */
        public static function isNotNullStr($str)
        {
            return !self::isNullStr($str);
        }

        /**
         * 此方法判断是否为空字符串包括null 未定义 '' 都会返回true
         *
         * @param $str
         *
         * @return bool
         */
        public static function isNullStr($str)
        {
            $str = trim($str);
            if (!isset($str) || $str === '' || $str === NULL) {
                return TRUE;
            }
            return FALSE;
        }

        public static function getServiceKey($serviceName) {
            return env('SERVICE_LIST_CACHE_PREFIX', 'service_list') . ':' . $serviceName;
        }
    }