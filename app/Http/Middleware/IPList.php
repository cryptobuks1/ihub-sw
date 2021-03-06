<?php


namespace App\Http\Middleware;

use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;

class IPList
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string                   $integration
     *
     * @return mixed
     */
    public function handle($request, \Closure $next, string $integration)
    {
        $ip = $request->ip();

        if($this->isValidIP($ip, $integration)){
            return $next($request);
        }

        throw new ApiHttpException(400, $ip .' '. trans('IP address is not permitted'));
    }

    public function isValidIP($ip, $integration)
    {
        $whitelist = config("integrations.{$integration}.allowed_ips");

        if (empty($whitelist)) {
            return true;
        }

        if(is_string($whitelist)){
            return $ip == $whitelist;
        }

        if (is_array($whitelist)){
            return in_array($ip, $whitelist);
        }

        return false;
    }
}