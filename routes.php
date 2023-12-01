<?php
use Illuminate\Support\Facades\Route;
Route::post('/hetzner/status/{product}', [App\Extensions\Servers\HetznerCloud\HetznerCloud::class, 'status'])->name('extensions.hetzner.status');