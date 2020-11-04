<?php


    namespace Ling5821\Lconsul\Common;


    use Illuminate\Support\Facades\Redis;

    class RedisUtils
    {
        public static function increase($key)
        {
            $value = Redis::incr($key);
            if ($value >= 9223372036854775807) {
                Redis::set($key, 0);
                return 0;
            }

            return $value;
        }

        public static function set($key, $value) {
            Redis::set($key, $value);
        }

        public static function get($key) {
            return Redis::get($key);
        }


    }