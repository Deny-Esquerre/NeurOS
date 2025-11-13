<?php

namespace App\Http\Responses\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class CustomPanelRedirectResponse implements LoginResponse
{
    public function __construct(private string $url)
    {
    }

    public function toResponse($request): RedirectResponse
    {
        return Redirect::to($this->url);
    }
}
