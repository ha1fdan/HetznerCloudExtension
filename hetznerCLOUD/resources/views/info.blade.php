{{-- Hetzner Cloud Server Information Component --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Server Overview Stats Cards --}}
<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5 lg:gap-7.5 mb-7">
    <!-- Connection Status Card -->
    <div class="kt-card">
        <div class="kt-card-content lg:py-7.5">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="relative size-[50px] shrink-0">
                        <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48"
                            viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z"
                                fill=""></path>
                            <path
                                d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z"
                                stroke=""></path>
                        </svg>
                        <div
                            class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
                            <i class="ki-filled ki-wifi text-xl"></i>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-medium text-mono">Connection</span>
                        <span class="text-sm text-secondary-foreground">Server connectivity status</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                @php
                    $status = $status ?? 'unknown';
                    $statusInfo = match ($status) {
                        'running' => ['class' => 'kt-badge-success', 'text' => 'Online'],
                        'starting' => ['class' => 'kt-badge-warning', 'text' => 'Starting'],
                        'stopping' => ['class' => 'kt-badge-warning', 'text' => 'Stopping'],
                        'off' => ['class' => 'kt-badge-danger', 'text' => 'Offline'],
                        'initializing' => ['class' => 'kt-badge-info', 'text' => 'Initializing'],
                        default => ['class' => 'kt-badge-secondary', 'text' => ucfirst($status)],
                    };
                @endphp
                <span class="text-3xl font-semibold text-mono">{{ $server_ipv4 ?? 'Pending' }}</span>
                <span class="kt-badge {{ $statusInfo['class'] }} kt-badge-outline">
                    {{ $statusInfo['text'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- Memory Card -->
    <div class="kt-card">
        <div class="kt-card-content lg:py-7.5">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="relative size-[50px] shrink-0">
                        <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48"
                            viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z"
                                fill=""></path>
                            <path
                                d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z"
                                stroke=""></path>
                        </svg>
                        <div
                            class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
                            <i class="ki-filled ki-technology-4 text-xl text-info"></i>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-medium text-mono">Memory</span>
                        <span class="text-sm text-secondary-foreground">System RAM allocation</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-3xl font-semibold text-mono">
                    @if (isset($memory) && $memory !== 'N/A')
                        {{ $memory }}
                    @else
                        N/A
                    @endif
                </span>
                <span class="kt-badge kt-badge-secondary kt-badge-outline">
                    GB RAM
                </span>
            </div>
        </div>
    </div>

    <!-- Storage Card -->
    <div class="kt-card">
        <div class="kt-card-content lg:py-7.5">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="relative size-[50px] shrink-0">
                        <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48"
                            viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z"
                                fill=""></path>
                            <path
                                d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z"
                                stroke=""></path>
                        </svg>
                        <div
                            class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
                            <i class="ki-filled ki-archive text-xl text-warning"></i>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-medium text-mono">Storage</span>
                        <span class="text-sm text-secondary-foreground">Disk space allocation</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-3xl font-semibold text-mono">{{ $disk ?? 'N/A' }}</span>
                <span class="kt-badge kt-badge-secondary kt-badge-outline">
                    GB SSD
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Server Management Grid --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-7.5">
    <div class="col-span-2">
        <div class="flex flex-col gap-5 lg:gap-7.5">
            {{-- Server Details Card --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">
                        <i class="ki-filled ki-server me-2"></i>
                        Server Details
                    </h3>
                    <div class="flex items-center gap-2">
                        <button onclick="refreshServerInfo()" class="kt-btn kt-btn-sm kt-btn-outline">
                            <i class="ki-filled ki-arrows-circle"></i>
                            Refresh
                        </button>
                    </div>
                </div>
                <div class="kt-card-table kt-scrollable-x-auto pb-3">
                    <table class="kt-table align-middle text-sm text-muted-foreground">
                        <tr>
                            <td class="min-w-56 text-secondary-foreground font-normal">
                                <div class="flex items-center gap-3">
                                    <i class="ki-filled ki-code text-base text-muted-foreground"></i>
                                    Server ID
                                </div>
                            </td>
                            <td class="min-w-48 w-full text-foreground font-normal">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono">{{ $server_id ?? 'N/A' }}</span>
                                    @if (isset($server_id) && $server_id !== 'N/A')
                                        <button onclick="copyToClipboard('{{ $server_id }}', 'Server ID copied!')"
                                            class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                            <i class="ki-filled ki-copy text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary-foreground font-normal">
                                <div class="flex items-center gap-3">
                                    <i class="ki-filled ki-abstract-26 text-base text-muted-foreground"></i>
                                    Provider
                                </div>
                            </td>
                            <td class="text-foreground font-normal">
                                <div class="flex items-center gap-2">
                                    <span>Numblio Cloud</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary-foreground font-normal">
                                <div class="flex items-center gap-3">
                                    <i class="ki-filled ki-wifi text-base text-muted-foreground"></i>
                                    IPv4 Address
                                </div>
                            </td>
                            <td class="text-foreground font-normal">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono">{{ $server_ipv4 ?? 'N/A' }}</span>
                                    @if (isset($server_ipv4) && $server_ipv4 !== 'N/A')
                                        <button onclick="copyToClipboard('{{ $server_ipv4 }}', 'IPv4 copied!')"
                                            class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                            <i class="ki-filled ki-copy text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary-foreground font-normal">
                                <div class="flex items-center gap-3">
                                    <i class="ki-filled ki-wifi text-base text-muted-foreground"></i>
                                    IPv6 Address
                                </div>
                            </td>
                            <td class="text-foreground font-normal">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs">
                                        @if (isset($server_ipv6) && $server_ipv6 !== 'N/A')
                                            {{ Str::limit($server_ipv6, 30) }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                    @if (isset($server_ipv6) && $server_ipv6 !== 'N/A')
                                        <button onclick="copyToClipboard('{{ $server_ipv6 }}', 'IPv6 copied!')"
                                            class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                            <i class="ki-filled ki-copy text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary-foreground font-normal">
                                <div class="flex items-center gap-3">
                                    <i class="ki-filled ki-key text-base text-muted-foreground"></i>
                                    Root Password
                                </div>
                            </td>
                            <td class="text-foreground font-normal">
                                <div class="flex items-center gap-2">
                                    <span class="password-field font-mono"
                                        data-password="{{ $server_root_passwd ?? 'N/A' }}">
                                        ••••••••••••••••
                                    </span>
                                    @if (isset($server_root_passwd) && $server_root_passwd !== 'N/A')
                                        <button onclick="togglePassword(this)"
                                            class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" title="Show Password">
                                            <i class="ki-filled ki-eye text-xs"></i>
                                        </button>
                                        <button
                                            onclick="copyToClipboard('{{ $server_root_passwd }}', 'Password copied!')"
                                            class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" title="Copy Password">
                                            <i class="ki-filled ki-copy text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary-foreground font-normal">
                                <div class="flex items-center gap-3">
                                    <i class="ki-filled ki-technology-4 text-base text-muted-foreground"></i>
                                    Specifications
                                </div>
                            </td>
                            <td class="text-foreground font-normal">
                                <div class="flex flex-wrap gap-2">
                                    @if (isset($memory) && $memory !== 'N/A')
                                        <span class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">
                                            {{ $memory }} GB RAM
                                        </span>
                                    @endif
                                    @if (isset($disk) && $disk !== 'N/A')
                                        <span class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">
                                            {{ $disk }} GB SSD
                                        </span>
                                    @endif
                                    @if (isset($cpu) && $cpu !== 'N/A')
                                        <span class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">
                                            {{ $cpu }} vCPU
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if (isset($server_location) && $server_location !== 'N/A')
                            <tr>
                                <td class="text-secondary-foreground font-normal">
                                    <div class="flex items-center gap-3">
                                        <i class="ki-filled ki-geolocation text-base text-muted-foreground"></i>
                                        Location
                                    </div>
                                </td>
                                <td class="text-foreground font-normal">
                                    {{ $server_location }}
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
            {{-- Server Activity Timeline Card --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">
                        <i class="ki-filled ki-time me-2"></i>
                        Server Activity
                    </h3>
                    <div class="flex items-center gap-2">
                        <button onclick="refreshServerActivity()" class="kt-btn kt-btn-sm kt-btn-outline">
                            <i class="ki-filled ki-arrows-circle"></i>
                            Refresh
                        </button>
                    </div>
                </div>
                <div class="kt-card-content lg:py-7.5">
                    @if (isset($server_actions) && !empty($server_actions))
                        <div class="space-y-5">
                            @foreach (array_slice(array_reverse($server_actions), 0, 5) as $action)
                                @php
                                    $statusInfo = match ($action['status']) {
                                        'success' => [
                                            'class' => 'kt-badge-success',
                                            'icon' => 'ki-check-circle',
                                            'text' => 'Completed',
                                        ],
                                        'error' => [
                                            'class' => 'kt-badge-danger',
                                            'icon' => 'ki-cross-circle',
                                            'text' => 'Failed',
                                        ],
                                        'running' => [
                                            'class' => 'kt-badge-warning',
                                            'icon' => 'ki-loading',
                                            'text' => 'Running',
                                        ],
                                        default => [
                                            'class' => 'kt-badge-secondary',
                                            'icon' => 'ki-time',
                                            'text' => ucfirst($action['status']),
                                        ],
                                    };

                                    $commandInfo = match ($action['command']) {
                                        'create_server' => [
                                            'icon' => 'ki-plus-circle',
                                            'name' => 'Create Server',
                                            'description' => 'Server was created',
                                        ],
                                        'start_server' => [
                                            'icon' => 'ki-play-circle',
                                            'name' => 'Start Server',
                                            'description' => 'Server was started',
                                        ],
                                        'stop_server' => [
                                            'icon' => 'ki-stop-circle',
                                            'name' => 'Stop Server',
                                            'description' => 'Server was stopped',
                                        ],
                                        'reboot_server' => [
                                            'icon' => 'ki-arrows-circle',
                                            'name' => 'Reboot Server',
                                            'description' => 'Server was rebooted',
                                        ],
                                        'reset_server' => [
                                            'icon' => 'ki-arrow-circle-left',
                                            'name' => 'Reset Server',
                                            'description' => 'Server was reset',
                                        ],
                                        'rebuild_server' => [
                                            'icon' => 'ki-arrows-circle',
                                            'name' => 'Rebuild Server',
                                            'description' => 'Server was rebuilt',
                                        ],
                                        'power_off_server' => [
                                            'icon' => 'ki-power',
                                            'name' => 'Power Off',
                                            'description' => 'Server was powered off',
                                        ],
                                        'power_on_server' => [
                                            'icon' => 'ki-power',
                                            'name' => 'Power On',
                                            'description' => 'Server was powered on',
                                        ],
                                        'shutdown_server' => [
                                            'icon' => 'ki-stop-circle',
                                            'name' => 'Shutdown Server',
                                            'description' => 'Server was shut down',
                                        ],
                                        'create_image' => [
                                            'icon' => 'ki-save-2',
                                            'name' => 'Create Image',
                                            'description' => 'Server image was created',
                                        ],
                                        'attach_iso' => [
                                            'icon' => 'ki-cd',
                                            'name' => 'Attach ISO',
                                            'description' => 'ISO was attached',
                                        ],
                                        'detach_iso' => [
                                            'icon' => 'ki-cd',
                                            'name' => 'Detach ISO',
                                            'description' => 'ISO was detached',
                                        ],
                                        'enable_rescue' => [
                                            'icon' => 'ki-shield',
                                            'name' => 'Enable Rescue',
                                            'description' => 'Rescue mode was enabled',
                                        ],
                                        'disable_rescue' => [
                                            'icon' => 'ki-shield',
                                            'name' => 'Disable Rescue',
                                            'description' => 'Rescue mode was disabled',
                                        ],
                                        'change_protection' => [
                                            'icon' => 'ki-lock',
                                            'name' => 'Change Protection',
                                            'description' => 'Protection settings were changed',
                                        ],
                                        'request_console' => [
                                            'icon' => 'ki-terminal',
                                            'name' => 'Request Console',
                                            'description' => 'Console access was requested',
                                        ],
                                        default => [
                                            'icon' => 'ki-gear',
                                            'name' => ucfirst(str_replace('_', ' ', $action['command'])),
                                            'description' => 'Action was performed',
                                        ],
                                    };
                                @endphp

                                {{-- Timeline Item --}}
                                <div class="flex items-start gap-3.5">
                                    {{-- Timeline Icon --}}
                                    <div class="relative">
                                        <div
                                            class="flex size-9 shrink-0 items-center justify-center rounded-full border-2 border-transparent bg-{{ $statusInfo['class'] === 'kt-badge-success' ? 'success' : ($statusInfo['class'] === 'kt-badge-danger' ? 'danger' : ($statusInfo['class'] === 'kt-badge-warning' ? 'warning' : 'secondary')) }}-clarity">
                                            <i
                                                class="ki-filled {{ $commandInfo['icon'] }} text-lg text-{{ $statusInfo['class'] === 'kt-badge-success' ? 'success' : ($statusInfo['class'] === 'kt-badge-danger' ? 'danger' : ($statusInfo['class'] === 'kt-badge-warning' ? 'warning' : 'secondary')) }}"></i>
                                        </div>
                                        @if (!$loop->last)
                                            <div
                                                class="absolute start-1/2 top-9 h-12 w-0.5 -translate-x-1/2 bg-border">
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Timeline Content --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="text-sm font-semibold text-foreground">{{ $commandInfo['name'] }}</span>
                                                <span
                                                    class="kt-badge kt-badge-sm {{ $statusInfo['class'] }} kt-badge-outline">
                                                    <i class="ki-filled {{ $statusInfo['icon'] }} text-3xs me-1"></i>
                                                    {{ $statusInfo['text'] }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-muted-foreground">
                                                {{ \Carbon\Carbon::parse($action['started'])->diffForHumans() }}
                                            </div>
                                        </div>

                                        <p class="text-sm text-secondary-foreground mb-2">
                                            {{ $commandInfo['description'] }}
                                        </p>

                                        {{-- Action Details --}}
                                        <div class="flex flex-wrap gap-2 text-xs text-muted-foreground">
                                            <span>ID: {{ $action['id'] }}</span>
                                            @if ($action['status'] === 'running' && isset($action['progress']))
                                                <span>Progress: {{ $action['progress'] }}%</span>
                                            @endif
                                            @if (isset($action['finished']) && $action['finished'])
                                                <span>Duration:
                                                    {{ \Carbon\Carbon::parse($action['started'])->diffInSeconds(\Carbon\Carbon::parse($action['finished'])) }}s</span>
                                            @endif
                                        </div>

                                        {{-- Progress Bar for Running Actions --}}
                                        @if ($action['status'] === 'running' && isset($action['progress']))
                                            <div class="mt-2">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="text-xs text-muted-foreground">Progress</span>
                                                    <span
                                                        class="text-xs text-muted-foreground">{{ $action['progress'] }}%</span>
                                                </div>
                                                <div class="w-full bg-secondary-clarity rounded-full h-1.5">
                                                    <div class="bg-warning h-1.5 rounded-full transition-all duration-300"
                                                        style="width: {{ $action['progress'] }}%"></div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Error Message --}}
                                        @if ($action['status'] === 'error' && isset($action['error']['message']))
                                            <div class="mt-2 kt-alert kt-alert-destructive">
                                                <div class="kt-alert-icon">
                                                    <i class="ki-filled ki-information text-lg"></i>
                                                </div>
                                                <div class="kt-alert-content">
                                                    <div class="kt-alert-text text-xs">
                                                        {{ $action['error']['message'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Security Message --}}
                        @if (count($server_actions) > 5)
                            <div class="mt-5 text-center">
                                <p class="text-sm text-muted-foreground">
                                    For security reasons, only the 5 most recent server activities are displayed.

                                </p>
                            </div>
                        @endif
                    @else
                        {{-- Empty State --}}
                        <div class="text-center py-8">
                            <div class="flex flex-col items-center gap-4">
                                <div
                                    class="flex size-16 items-center justify-center rounded-full bg-secondary-clarity">
                                    <i class="ki-filled ki-time text-2xl text-muted-foreground"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-foreground mb-1">No Activity Found</h4>
                                    <p class="text-sm text-muted-foreground">No recent server activities to display.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1">
        <div class="flex flex-col gap-5 lg:gap-7.5">
            {{-- Power Management Card --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">
                        Power Management
                    </h3>
                </div>
                <div class="kt-card-content lg:py-7.5">
                    {{-- Power Controls Section --}}
                    <div class="mb-7">
                        <div class="flex items-center gap-2 mb-4">
                            <h4 class="text-sm font-semibold text-secondary-foreground">Power Controls</h4>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            {{-- Power On --}}
                            <button class="kt-btn kt-btn-success" wire:click="goto('powerOn')"
                                wire:loading.attr="disabled" wire:target="goto('powerOn')"
                                title="Power on the server">
                                <i class="ki-filled ki-toggle-on me-2" wire:loading.remove
                                    wire:target="goto('powerOn')"></i>
                                <i class="ki-filled ki-loading animate-spin me-2" wire:loading
                                    wire:target="goto('powerOn')"></i>
                                <span wire:loading.remove wire:target="goto('powerOn')">Power On</span>
                                <span wire:loading wire:target="goto('powerOn')">Starting...</span>
                            </button>

                            {{-- Power Off --}}
                            <button class="kt-btn kt-btn-outline kt-btn-outline-danger" wire:click="goto('powerOff')"
                                wire:loading.attr="disabled" wire:target="goto('powerOff')"
                                title="Power off the server">
                                <i class="ki-filled ki-toggle-off me-2" wire:loading.remove
                                    wire:target="goto('powerOff')"></i>
                                <i class="ki-filled ki-loading animate-spin me-2" wire:loading
                                    wire:target="goto('powerOff')"></i>
                                <span wire:loading.remove wire:target="goto('powerOff')">Power Off</span>
                                <span wire:loading wire:target="goto('powerOff')">Stopping...</span>
                            </button>
                        </div>
                    </div>

                    {{-- System Actions Section --}}
                    <div class="mb-7">
                        <div class="flex items-center gap-2 mb-4">
                            <h4 class="text-sm font-semibold text-secondary-foreground">System Actions</h4>
                        </div>
                        <div class="flex flex-col gap-3">
                            {{-- Reboot --}}
                            <button class="kt-btn kt-btn-outline kt-btn-outline-info" wire:click="goto('reboot')"
                                wire:loading.attr="disabled" wire:target="goto('reboot')" title="Restart the server">
                                <i class="ki-filled ki-arrows-circle me-2" wire:loading.remove
                                    wire:target="goto('reboot')"></i>
                                <i class="ki-filled ki-loading animate-spin me-2" wire:loading
                                    wire:target="goto('reboot')"></i>
                                <span wire:loading.remove wire:target="goto('reboot')">Restart Server</span>
                                <span wire:loading wire:target="goto('reboot')">Restarting...</span>
                            </button>

                            {{-- Reset Password --}}
                            <button class="kt-btn kt-btn-outline" wire:click="goto('resetPassword')"
                                wire:loading.attr="disabled" wire:target="goto('resetPassword')"
                                title="Generate new root password">
                                <i class="ki-filled ki-key me-2" wire:loading.remove
                                    wire:target="goto('resetPassword')"></i>
                                <i class="ki-filled ki-loading animate-spin me-2" wire:loading
                                    wire:target="goto('resetPassword')"></i>
                                <span wire:loading.remove wire:target="goto('resetPassword')">Reset Root
                                    Password</span>
                                <span wire:loading wire:target="goto('resetPassword')">Resetting...</span>
                            </button>
                        </div>
                    </div>

                    {{-- Destructive Actions Section --}}
                    <div class="mb-0">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="ki-filled ki-information text-base text-warning"></i>
                            <h4 class="text-sm font-semibold text-secondary-foreground">Destructive Actions</h4>
                        </div>
                        <div class="kt-alert kt-alert-destructive mb-5" id="alert_5">
                            <div class="kt-alert-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info"
                                    aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M12 16v-4"></path>
                                    <path d="M12 8h.01"></path>
                                </svg>
                            </div>
                            <div class="kt-alert-title">The following action will permanently destroy data and cannot
                                be undone.</div>
                        </div>

                        <button class="kt-btn kt-btn-outline kt-btn-outline-danger w-full"
                            data-kt-modal-toggle="#rebuild_server_modal" title="Rebuild server with custom image">
                            <i class="ki-filled ki-arrows-circle me-2"></i>
                            Rebuild Server
                        </button>


                        {{-- Hidden button for modal rebuild functionality --}}
                        <button id="hidden_rebuild_button" class="hidden" wire:click="goto('rebuild')"
                            wire:loading.attr="disabled" wire:target="goto('rebuild')">
                        </button>






                        {{-- Rebuild Server Modal --}}
                        <div class="kt-modal" data-kt-modal="true" id="rebuild_server_modal">
                            <div class="kt-modal-content max-w-[600px] top-5 lg:top-[10%]">
                                <div class="kt-modal-header">
                                    <h3 class="kt-modal-title">
                                        <i class="ki-filled ki-arrows-circle me-2 text-danger"></i>
                                        Rebuild Server
                                    </h3>
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0"
                                        data-kt-modal-dismiss="true">
                                        <i class="ki-filled ki-cross"></i>
                                    </button>
                                </div>
                                <div class="kt-modal-body grid gap-5 px-0 py-5">
                                    {{-- Warning Section --}}
                                    <div class="px-5">
                                        <div class="kt-alert kt-alert-destructive">
                                            <div class="kt-alert-icon">
                                                <i class="ki-filled ki-information text-lg"></i>
                                            </div>
                                            <div class="kt-alert-content">
                                                <div class="kt-alert-title">Destructive Action Warning</div>
                                                <div class="kt-alert-text">
                                                    This will <strong>completely wipe your server</strong> and reinstall
                                                    with the selected image.
                                                    <br><strong>All data, configurations, and installed software will be
                                                        permanently lost.</strong>
                                                    <br>This action cannot be undone.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Current Server Info --}}
                                    <div class="px-5">
                                        <div class="flex flex-col gap-2.5">
                                            <label class="text-mono font-semibold text-sm">Current Server
                                                Information</label>
                                            <div class="kt-card shadow-none bg-muted/30 p-4">
                                                <div class="grid grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <span class="text-secondary-foreground">Current Image:</span>
                                                        <br><span
                                                            class="font-mono text-foreground">{{ $description ?? 'N/A' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-secondary-foreground">Server Type:</span>
                                                        <br><span
                                                            class="font-mono text-foreground">{{ $server_type ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Image Selection --}}
                                    <div class="px-5">
                                        <div class="flex flex-col gap-2.5">
                                            <label class="text-mono font-semibold text-sm">Select New Operating System
                                                Image</label>
                                            <select id="rebuild_image_select" class="kt-select w-full"
                                                data-kt-select="true">
                                                <option value="">Choose an image...</option>
                                                @if (isset($available_images) && is_array($available_images))
                                                    @foreach ($available_images as $imageId => $imageName)
                                                        <option value="{{ $imageId }}"
                                                            {{ $imageId === ($description ?? '') ? 'selected' : '' }}>
                                                            {{ $imageName }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="kt-form-description">
                                                Select the operating system image to install on your server.
                                                The current image is pre-selected for safety.
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Confirmation Section --}}
                                    <div class="px-5">
                                        <div class="flex flex-col gap-2.5">
                                            <label class="text-mono font-semibold text-sm">Confirmation</label>
                                            <label class="kt-label">
                                                <input type="checkbox" id="rebuild_confirm_checkbox"
                                                    class="kt-checkbox">
                                                <span class="text-sm">
                                                    I understand that this action will <strong>permanently destroy all
                                                        data</strong>
                                                    on my server and cannot be undone.
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="px-5">
                                        <div class="flex gap-3 justify-end">
                                            <button class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">
                                                Cancel
                                            </button>
                                            <button id="rebuild_confirm_button" class="kt-btn kt-btn-danger" disabled
                                                onclick="performServerRebuild()">
                                                <i class="ki-filled ki-arrows-circle me-2"></i>
                                                Rebuild Server
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content py-10 flex flex-col gap-5 lg:gap-7.5">
                    <div class="flex flex-col items-start gap-2.5">
                        <div class="mb-2.5">
                            <div class="relative size-[50px] shrink-0">
                                <svg class="w-full h-full stroke-primary/10 fill-primary-soft" fill="none"
                                    height="48" viewbox="0 0 44 48" width="44"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506
   18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937
   39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z"
                                        fill="">
                                    </path>
                                    <path
                                        d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506
   18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937
   39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z"
                                        stroke="">
                                    </path>
                                </svg>
                                <div
                                    class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
                                    <i class="ki-filled ki-cloud-add text-xl ps-px text-primary">
                                    </i>
                                </div>
                            </div>
                        </div>
                        <a class="text-base font-semibold text-mono hover:text-primary" href="#">
                            Firewall setup assistance
                        </a>
                        <p class="text-sm text-secondary-foreground">
                            Firewall creation isn’t available in Numblio Console yet. If you need a firewall for your
                            Space, please contact our support team and we’ll set it up for you.
                        </p>
                        <a class="kt-link kt-link-underlined kt-link-dashed" href="https://support.numblio.com">
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>



    {{-- Enhanced JavaScript for basic functionality --}}
    <script>
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            updateLastRefresh();
            console.log('Hetzner Cloud server info loaded');
            initializeRebuildModal();
        });

        // Initialize rebuild modal functionality
        function initializeRebuildModal() {
            const checkbox = document.getElementById('rebuild_confirm_checkbox');
            const button = document.getElementById('rebuild_confirm_button');
            const imageSelect = document.getElementById('rebuild_image_select');

            if (checkbox && button) {
                // Enable/disable button based on checkbox state and image selection
                function updateButtonState() {
                    const isChecked = checkbox.checked;
                    const hasImageSelected = imageSelect && imageSelect.value !== '';
                    button.disabled = !(isChecked && hasImageSelected);
                }

                checkbox.addEventListener('change', updateButtonState);
                if (imageSelect) {
                    imageSelect.addEventListener('change', updateButtonState);
                }

                // Initial state check
                updateButtonState();
            }
        }

        // Perform server rebuild with selected image
        function performServerRebuild() {
            const imageSelect = document.getElementById('rebuild_image_select');
            const selectedImage = imageSelect ? imageSelect.value : '';

            if (!selectedImage) {
                showToast('Please select an image first.', 'error');
                return;
            }

            // Store selected image in a cookie for the backend to pick up
            document.cookie = `rebuild_image=${selectedImage}; path=/; max-age=300`; // 5 minutes expiry

            // Close modal first
            const modal = document.getElementById('rebuild_server_modal');
            if (modal) {
                const modalInstance = KTModal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }

            // Show loading toast
            showToast('Starting server rebuild...', 'info');

            // Click the hidden rebuild button that has wire:click
            const hiddenButton = document.getElementById('hidden_rebuild_button');
            if (hiddenButton) {
                hiddenButton.click();
            } else {
                showToast('Rebuild function not available', 'error');
            }

            // Reset form
            document.getElementById('rebuild_confirm_checkbox').checked = false;
            document.getElementById('rebuild_image_select').value = '';
        }

        // Call server action functions via AJAX
        function callServerAction(action) {
            const button = event.target;
            const originalContent = button.innerHTML;

            // Show loading state
            button.disabled = true;
            button.innerHTML = '<i class="ki-filled ki-loading animate-spin me-1"></i>Processing...';

            showToast(`Executing ${action}...`, 'info');

            // Make AJAX request to the current page with the action
            fetch(window.location.pathname, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: new URLSearchParams({
                        'action': action,
                        'service_id': '{{ $service->id ?? '' }}'
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showToast(data.message || `${action} completed successfully!`, 'success');

                        // Refresh page after a short delay for certain actions
                        if (['powerOn', 'powerOff', 'reboot'].includes(action)) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        }
                    } else {
                        throw new Error(data.message || `${action} failed`);
                    }
                })
                .catch(error => {
                    console.error(`${action} error:`, error);
                    showToast(`${action} failed: ${error.message}`, 'error');
                })
                .finally(() => {
                    // Restore button state
                    button.disabled = false;
                    button.innerHTML = originalContent;
                });
        }

        // Confirm action before executing (for dangerous actions)
        function confirmAndCallAction(action) {
            const actionNames = {
                'rebuild': 'rebuild the server with the current OS'
            };

            const actionDescription = actionNames[action] || action;

            if (confirm(`Are you sure you want to ${actionDescription}? This action cannot be undone.`)) {
                callServerAction(action);
            }
        }

        // Enhanced copy to clipboard with notifications
        function copyToClipboard(text, successMessage = 'Copied to clipboard!') {
            navigator.clipboard.writeText(text).then(function() {
                showToast(successMessage, 'success');
            }).catch(function() {
                // Fallback for older browsers
                const textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showToast(successMessage, 'success');
            });
        }

        // Password toggle functionality
        function togglePassword(button) {
            const passwordField = button.closest('.flex').querySelector('.password-field');
            const icon = button.querySelector('i');
            const password = passwordField.getAttribute('data-password');

            if (passwordField.textContent.includes('••••')) {
                passwordField.textContent = password;
                icon.className = 'ki-filled ki-eye-slash text-xs';
                button.title = 'Hide Password';
            } else {
                passwordField.textContent = '••••••••••••••••';
                icon.className = 'ki-filled ki-eye text-xs';
                button.title = 'Show Password';
            }
        }

        // Refresh server information
        function refreshServerInfo() {
            showToast('Refreshing server information...', 'info');
            // Trigger Livewire refresh
            if (window.Livewire) {
                // Find the parent service component and refresh
                const component = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                if (component) {
                    component.call('$refresh');
                } else {
                    // Fallback: emit refresh to all components
                    Livewire.emit('$refresh');
                }
            } else {
                window.location.reload();
            }
        }

        // Refresh server activity timeline
        function refreshServerActivity() {
            showToast('Refreshing server activity...', 'info');
            // Trigger Livewire refresh - activity data will be updated with server info
            refreshServerInfo();
        } // Update last refresh timestamp
        function updateLastRefresh() {
            const lastUpdated = document.getElementById('last-updated');
            if (lastUpdated) {
                lastUpdated.textContent = `Updated: ${new Date().toLocaleTimeString()}`;
            }
        }

        // Open terminal (if supported)
        function openTerminal() {
            const ip = '{{ $server_ipv4 ?? '' }}';
            if (ip && ip !== 'N/A') {
                // Try to open SSH protocol (may not work in all browsers)
                const sshUrl = `ssh://root@${ip}`;
                window.open(sshUrl, '_blank');

                showToast(
                    'SSH connection attempted. If not supported by your browser, use the copied command in your terminal.',
                    'info');
                copyToClipboard(`ssh root@${ip}`, 'SSH command copied to clipboard!');
            }
        }

        // Ping server functionality
        function pingServer() {
            const ip = '{{ $server_ipv4 ?? '' }}';
            if (ip && ip !== 'N/A') {
                showToast('Testing connection to your server...', 'info');

                // Simple connectivity test using image loading
                const img = new Image();
                const timeout = setTimeout(() => {
                    showToast('Connection test timed out. Server may be unreachable.', 'warning');
                }, 5000);

                img.onload = img.onerror = function() {
                    clearTimeout(timeout);
                    showToast('Server is reachable!', 'success');
                };

                img.src = `http://${ip}/favicon.ico?t=${Date.now()}`;
            }
        }

        // Enhanced toast notification system for better UX
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-4 py-2 rounded-md shadow-lg z-50 ${type === 'success' ? 'bg-success text-white' :
                    type === 'error' ? 'bg-danger text-white' :
                        type === 'warning' ? 'bg-warning text-white' :
                            'bg-info text-white'
                }`;
            toast.textContent = message;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
