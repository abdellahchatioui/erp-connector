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

        // Read the static ERP token from the X-ERP-TOKEN header only
        // (the Authorization: Bearer header carries the Keycloak JWT, which is separate)
        $providedToken = $request->header('X-ERP-TOKEN');

        \Log::info('VerifyErpToken middleware', [
            'provided'  => $providedToken ? substr($providedToken, 0, 15) . '...' : 'NULL',
            'expected'  => $expectedToken ? substr($expectedToken, 0, 15) . '...' : 'NULL',
        ]);

        if (empty($expectedToken) || $providedToken !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized. Invalid ERP token.'], 401);
        }

        return $next($request);
    }
}
