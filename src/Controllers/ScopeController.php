<?php

namespace Lyignore\LaravelOauth2\Http\Controllers;

use Laravel\Passport\Passport;

class ScopeController
{
    /**
     * Get all of the available scopes for the application.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return Passport::scopes();
    }
}
