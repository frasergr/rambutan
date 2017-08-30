<?php

namespace App\Http\Middleware;

use Closure;

class ValidateItn
{
    /**
     * Validates ITN signature
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $xmlSignature = $request->hasHeader(env('R_ITN_SIG')) ? $request->header(env('ITN_SIG')) : null;
        $contentSignature = base64_encode(hash_hmac('sha256', $request->getContent(), env('ITN_SECRET')));

        if ($xmlSignature !== $contentSignature) {
            return response('Not Authorized', 500);
        }

        return $next($request);
    }
}
