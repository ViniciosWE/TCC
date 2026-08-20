<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarTipo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$tipos): Response
    {
        // verifica se o tipo do usuário possui permissão para acessar a rota
        if (!in_array(auth()->user()->tipo, $tipos)) {
            abort(403);
        }

        // permite que a requisição continue
        return $next($request);
    }
}
