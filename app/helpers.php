<?php

if (! function_exists('number_to_string')) {
    /**
     * @param $number
     * @return string
     */
    function number_to_string($number)
    {
        if (!is_numeric($number)) {
            throw new \InvalidArgumentException($number . ' is not number');
        }
        return number_format((float)$number, 2, '.', '');
    }
}

if (! function_exists('get_formatted_date')) {
    /**
     * @param $value
     * @param string $format
     * @return false|string
     */
    function get_formatted_date($value, $format = 'Y-m-d H:i:s')
    {
        if (is_numeric($value)) {
            $date = date_create_from_format('U', $value);
        } else {
            $date = date_create_from_format('Y-m-d H:i:s', $value);
        }
        if ($date && $date = date_format($date, $format)) {
            return $date;
        }
        throw new \InvalidArgumentException;
    }
}

if (! function_exists('integration_config')) {

    /**
     * @param \Illuminate\Foundation\Application $app
     * @param string $environment
     */
    function integration_config(\Illuminate\Foundation\Application $app, $environment){
        if($environment && $app){
            $basePath = $app->basePath().DIRECTORY_SEPARATOR.'integrations'.DIRECTORY_SEPARATOR;
            $environmentConfig = $basePath . $environment . '.php';

            if(!file_exists($environmentConfig)){
                $environmentConfig = $basePath . 'default.php';
            }

            $integrations = require $environmentConfig;

            Illuminate\Container\Container::getInstance()->make('config')->set('integrations', $integrations);
        }
    }

}

if (! function_exists('gen_uid')) {

    /**
     * @return string
     */
    function gen_uid()
    {
        return bin2hex(random_bytes(16));
    }

}

if (! function_exists('get_client_ip')) {

    /**
     * @return string
     */
    function get_client_ip()
    {
        if(request()->headers->has('x-forwarded-for')){
            $ips = request()->headers->get('x-forwarded-for');
            if(is_array($ips))
            {
                foreach ($ips as $ip){
                    if(filter_var($ip, FILTER_VALIDATE_IP)){
                        return $ip;
                    }
                }
            } elseif (filter_var($ips, FILTER_VALIDATE_IP)) {
                return $ips;
            }
        }

        return request()->header('X-Real-IP', request()->getClientIp());
    }

}