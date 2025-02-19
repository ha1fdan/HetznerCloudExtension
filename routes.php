<?php
use Illuminate\Support\Facades\Route;
Route::post('/hetzner/status/{product}', [App\Extensions\Servers\HetznerCloud\HetznerCloud::class, 'status'])->name('extensions.hetzner.status');
Route::post('/hetzner/revdns/{product}', [App\Extensions\Servers\HetznerCloud\HetznerCloud::class, 'revdns'])->name('extensions.hetzner.revdns');
Route::post('/hetzner/firewall/{product}', [App\Extensions\Servers\HetznerCloud\HetznerCloud::class, 'handleFirewallAction'])->name('extensions.hetzner.firewall.action');