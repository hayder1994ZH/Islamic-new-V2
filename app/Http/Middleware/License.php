<?php

namespace App\Http\Middleware;

use App\Models\Companies;
use App\Models\Companies_ip;
use Carbon\Carbon;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Str;

class License extends Middleware
{
    /**
     * Handle an incoming request.
     * @param array $company
     * @param $next
     * @param $request
     * @return mixed
     * @throws JWTException
     */
    public function handle($request, Closure $next, ...$company)
    {
        $host_server = $request->server('SERVER_NAME');
        $host1 = $request->server('HTTP_HOST_NAME');
        $host = $request->server('HTTP_HOST_NAME');
        $ip = $request->server('HTTP_HOST_IP');
        $REMOTE_ADDR = $request->server('REMOTE_ADDR');
        $httpProtocol = $request->server('REQUEST_SCHEME');

        if (Str::contains($host, "int.vodu")) {
            //print_r($request->server());
        }

        /*

        $comapnyInfo = $this->CompanyInfo($REMOTE_ADDR);

        if ($comapnyInfo) {
            if ($comapnyInfo->http_host !== $host) {
                return response()->json(["message" => "your host is not allowed"], 404);
            }

            if (Str::contains($httpProtocol, $comapnyInfo->http_protocol)) {
                return response()->json(["message" => "you can't use it without SSL"], 404);
            }

            if ($comapnyInfo->expired_date < Carbon::today()->format("Y-m-d")) {
                return response()->json(["message" => "your license expired"], 404);
            }
        } else if ($host_server === 'local.vodu.store') {

            // $request->attributes->add([
            //     'host' =>  'https://dish.vodu.store',
            //     'domain' =>  'dish.vodu.store',
            //     'http_protocol' => 'https://',
            //     'REMOTE_ADDR' => $REMOTE_ADDR,
            //     'port' => '9999',
            //     'imageSize' => '?h=512',

            // ]);

            // return $next($request);
        } else {
            return response()->json(["message" => "you don't have license to use it"], 404);
        }



        $request->attributes->add([
            'host' =>  $comapnyInfo->http_protocol . $comapnyInfo->http_host,
            'domain' =>  $comapnyInfo->http_host,
            'http_protocol' => $comapnyInfo->http_protocol,
            'REMOTE_ADDR' => $REMOTE_ADDR,
            'imageSize' => '?h=512',
            'port' => $comapnyInfo->storage_port

        ]);


*/

        if (!$request->server->has('HTTP_HOST_NAME')) {

            $request->attributes->add([
                // 'host' =>  '',
                'host' =>  'https://islamic.vodu.me',
                // 'host' =>  'https://islamic.vodu.me/islamic',
                // 'domain' =>  'dish.vodu.store',
                'http_protocol' => 'https://',
                'REMOTE_ADDR' => $REMOTE_ADDR,
                'port' => '9999',
                'imageSize' => '?h=256',
            ]);
        } else {
            $request->attributes->add([
                'host' =>  $httpProtocol . "://" . $host,
                'domain' =>  $host,
                'http_protocol' => $httpProtocol . "://",
                'REMOTE_ADDR' => $REMOTE_ADDR,
                'imageSize' => '?h=256',
                'port' => "9999"

            ]);
        }


        //    return response()->json(["message" => "you can't use serve IP"], 404);
        return $next($request);
    }

    public function Registred($ipAddr, $httpProtocol)
    {
        $company = Companies::where('http_host', $ipAddr)->orWhere('http_protocol', $httpProtocol)->first();
        if (!$company) {
            return 404;
        }
        return 200;
    }


    public function CompanyInfo($ipAddr)
    {
        $comapny = Companies_ip::where('ip_address', $ipAddr . "/32");
        if (!$comapny->exists()) {
            return null;
        }
        $c = Companies::find($comapny->first()->id);
        return $c;
    }

    public function Expired($host)
    {
        $date = Carbon::today();
        $date = $date->toDateString();
        $company = Companies::where('http_host', $host)->where('expired_date', '<', Carbon::today()->toDateString())->first();
        if (!$company) {
            return 200;
        }
        return 404;
    }
}
