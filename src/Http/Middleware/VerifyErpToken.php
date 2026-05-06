<?php

namespace Webkul\ErpConnector\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyErpToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $erpConfig = app(\Webkul\ErpConnector\Helpers\Config::class);
        $expectedToken = $erpConfig->getErpToken();
        
        $providedToken = $request->bearerToken() ?? $request->header('X-ERP-TOKEN');

        if (empty($expectedToken) || $providedToken !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized. Invalid ERP token.'], 401);
        }

        return $next($request);
    }
}
