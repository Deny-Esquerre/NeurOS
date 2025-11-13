<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfCannotAccessPanel
{
    public function handle(Request $request, Closure $next)
    {
        // Only act if there is an active Filament panel and the user is logged in.
        if (!Filament::getCurrentPanel() || !Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $currentPanel = Filament::getCurrentPanel();

        // If the user can access the current panel, do nothing.
        if (method_exists($user, 'canAccessPanel') && $user->canAccessPanel($currentPanel)) {
            return $next($request);
        }

        // If the user cannot access the current panel, find a panel they can access.
        $role = $user->getRoleNames()->first();
        if ($role) {
            $panelId = strtolower($role);
            
            // Check if a panel with that ID exists.
            if (Filament::getPanel($panelId, false)) {
                 return redirect()->to("/{$panelId}");
            }
        }

        // If no alternative panel is found, log the user out and redirect to the current login page.
        Filament::auth()->logout();

        return redirect()->to($currentPanel->getLoginUrl());
    }
}
