<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/sandbox/admin', function (Request $request) {
    abort_unless(app()->environment('local'), 404);
    $request->session()->regenerate();
    $request->session()->put('sandbox.design-admin', true);

    return redirect()->route('spinotek.design.edit')->header('Referrer-Policy', 'no-referrer');
})->middleware('signed')->name('sandbox.design-login');
