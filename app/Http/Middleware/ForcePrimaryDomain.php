<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePrimaryDomain
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        if (in_array($host, ['ovievent.com','www.ovievent.com'])) {
            $to = 'https://eventib.com'.$request->getRequestUri();
            return redirect()->to($to, 301);
        }
        return $next($request);
    }
}
