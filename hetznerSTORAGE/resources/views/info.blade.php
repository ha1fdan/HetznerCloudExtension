{{-- Hetzner Storage Box Information Component --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Storage Box Overview Stats Cards --}}
<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-2 gap-5 lg:gap-7.5 mb-7">
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
                            <i class="ki-filled ki-cloud text-xl"></i>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-medium text-mono">Status</span>
                        <span class="text-sm text-secondary-foreground">Storage box status</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                @php
                    $status = $status ?? 'unknown';
                    $statusInfo = match ($status) {
                        'active' => ['class' => 'kt-badge-success', 'text' => 'Active'],
                        'creating' => ['class' => 'kt-badge-warning', 'text' => 'Creating'],
                        'deleting' => ['class' => 'kt-badge-warning', 'text' => 'Deleting'],
                        'inactive' => ['class' => 'kt-badge-danger', 'text' => 'Inactive'],
                        default => ['class' => 'kt-badge-secondary', 'text' => ucfirst($status)],
                    };
                @endphp
                <span class="text-3xl font-semibold text-mono">{{ $server ?? 'Pending' }}</span>
                <span class="kt-badge {{ $statusInfo['class'] }} kt-badge-outline">
                    {{ $statusInfo['text'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- Storage Size Card -->
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
                            <i class="ki-filled ki-archive text-xl text-info"></i>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-medium text-mono">Storage</span>
                        <span class="text-sm text-secondary-foreground">Total storage capacity</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-3xl font-semibold text-mono">
                    @if (isset($size) && $size !== 'N/A')
                        {{ round($size / 1024 / 1024 / 1024, 0) }}
                    @else
                        N/A
                    @endif
                </span>
                <span class="kt-badge kt-badge-secondary kt-badge-outline">
                    GB
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Storage Box Management Grid --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-7.5">
    <div class="col-span-2">
        <div class="flex flex-col gap-5 lg:gap-7.5">
            {{-- Storage Box Details Card --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">
                        <i class="ki-filled ki-cloud me-2"></i>
                        Storage Box Details
                    </h3>
                    <div class="flex items-center gap-2">
                        <button onclick="refreshStorageInfo()" class="kt-btn kt-btn-sm kt-btn-outline">
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
                                    Storage Box ID
                                </div>
                            </td>
                            <td class="min-w-48 w-full text-foreground font-normal">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono">{{ $storage_box_id ?? 'N/A' }}</span>
                                    @if (isset($storage_box_id) && $storage_box_id !== 'N/A')
                                        <button onclick="copyToClipboard('{{ $storage_box_id }}', 'Storage Box ID copied!')"
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
                                    <i class="ki-filled ki-user text-base text-muted-foreground"></i>
                                    Username
                                </div>
                            </td>
                            <td class="text-foreground font-normal">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono">{{ $username ?? 'N/A' }}</span>
                                    @if (isset($username) && $username !== 'N/A')
                                        <button onclick="copyToClipboard('{{ $username }}', 'Username copied!')"
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
                                    <i class="ki-filled ki-server text-base text-muted-foreground"></i>
                                    Server Address
                                </div>
                            </td>
                            <td class="text-foreground font-normal">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono">{{ $server ?? 'N/A' }}</span>
                                    @if (isset($server) && $server !== 'N/A')
                                        <button onclick="copyToClipboard('{{ $server }}', 'Server address copied!')"
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
                                    Password
                                </div>
                            </td>
                            <td class="text-foreground font-normal">
                                <div class="flex items-center gap-2">
                                    <span class="password-field font-mono"
                                        data-password="{{ $password ?? 'N/A' }}">
                                        ••••••••••••••••
                                    </span>
                                    @if (isset($password) && $password !== 'N/A')
                                        <button onclick="togglePassword(this)"
                                            class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" title="Show Password">
                                            <i class="ki-filled ki-eye text-xs"></i>
                                        </button>
                                        <button
                                            onclick="copyToClipboard('{{ $password }}', 'Password copied!')"
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
                                    @if (isset($size) && $size !== 'N/A')
                                        <span class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">
                                            {{ round($size / 1024 / 1024 / 1024, 0) }} GB Storage
                                        </span>
                                    @endif
                                    @if (isset($snapshot_limit) && $snapshot_limit !== 'N/A')
                                        <span class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">
                                            {{ $snapshot_limit }} Snapshots
                                        </span>
                                    @endif
                                    @if (isset($subaccounts_limit) && $subaccounts_limit !== 'N/A')
                                        <span class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">
                                            {{ $subaccounts_limit }} Subaccounts
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if (isset($location) && $location !== 'N/A')
                            <tr>
                                <td class="text-secondary-foreground font-normal">
                                    <div class="flex items-center gap-3">
                                        <i class="ki-filled ki-geolocation text-base text-muted-foreground"></i>
                                        Location
                                    </div>
                                </td>
                                <td class="text-foreground font-normal">
                                    {{ $location }}
                                </td>
                            </tr>
                        @endif
                        @if (isset($access_settings) && !empty($access_settings))
                            <tr>
                                <td class="text-secondary-foreground font-normal">
                                    <div class="flex items-center gap-3">
                                        <i class="ki-filled ki-setting-2 text-base text-muted-foreground"></i>
                                        Access Settings
                                    </div>
                                </td>
                                <td class="text-foreground font-normal">
                                    <div class="flex flex-wrap gap-2">
                                        @if ($access_settings['ssh_enabled'] ?? false)
                                            <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">SSH</span>
                                        @endif
                                        @if ($access_settings['samba_enabled'] ?? false)
                                            <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Samba</span>
                                        @endif
                                        @if ($access_settings['webdav_enabled'] ?? false)
                                            <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">WebDAV</span>
                                        @endif
                                        @if ($access_settings['zfs_enabled'] ?? false)
                                            <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">ZFS</span>
                                        @endif
                                        @if ($access_settings['reachable_externally'] ?? false)
                                            <span class="kt-badge kt-badge-sm kt-badge-info kt-badge-outline">External</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Storage Box Actions Panel --}}
    <div class="flex flex-col gap-5 lg:gap-7.5">
        {{-- Quick Actions Card --}}
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">
                    <i class="ki-filled ki-gear me-2"></i>
                    Storage Box Actions
                </h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-col gap-3">
                    <button class="kt-btn kt-btn-warning kt-btn-sm w-full" data-kt-modal-toggle="#reset_password_modal"
                        title="Reset storage box password">
                        <i class="ki-filled ki-key me-2"></i>
                        Reset Password
                    </button>
                    <button class="kt-btn kt-btn-info kt-btn-sm w-full" data-kt-modal-toggle="#create_snapshot_modal"
                        title="Create a new snapshot">
                        <i class="ki-filled ki-save-2 me-2"></i>
                        Create Snapshot
                    </button>
                    <button class="kt-btn kt-btn-secondary kt-btn-sm w-full" data-kt-modal-toggle="#access_settings_modal"
                        title="Update access settings">
                        <i class="ki-filled ki-setting-2 me-2"></i>
                        Update Access
                    </button>
                </div>
            </div>
        </div>

        {{-- Storage Stats Card --}}
        @if (isset($stats) && !empty($stats))
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">
                    <i class="ki-filled ki-chart-simple me-2"></i>
                    Storage Statistics
                </h3>
            </div>
            <div class="kt-card-content">
                <div class="space-y-4">
                    @if (isset($stats['size']))
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Total Size</span>
                            <span class="font-mono text-sm">{{ round($stats['size'] / 1024 / 1024 / 1024, 2) }} GB</span>
                        </div>
                    @endif
                    @if (isset($stats['size_data']))
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Data Size</span>
                            <span class="font-mono text-sm">{{ round($stats['size_data'] / 1024 / 1024 / 1024, 2) }} GB</span>
                        </div>
                    @endif
                    @if (isset($stats['size_snapshots']))
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Snapshots</span>
                            <span class="font-mono text-sm">{{ round($stats['size_snapshots'] / 1024 / 1024 / 1024, 2) }} GB</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Reset Password Modal --}}
<div class="kt-modal" data-kt-modal="true" id="reset_password_modal">
    <div class="kt-modal-content max-w-[500px] top-5 lg:top-[10%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">
                <i class="ki-filled ki-key me-2 text-warning"></i>
                Reset Storage Box Password
            </h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body grid gap-5 px-0 py-5">
            {{-- Warning Section --}}
            <div class="px-5">
                <div class="kt-alert kt-alert-warning">
                    <div class="kt-alert-icon">
                        <i class="ki-filled ki-information text-lg"></i>
                    </div>
                    <div class="kt-alert-content">
                        <div class="kt-alert-title">Password Reset</div>
                        <div class="kt-alert-text">
                            This will generate a new random password for your storage box.
                            <br><strong>The current password will no longer work after this action.</strong>
                            <br>Make sure to save the new password after the reset.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Current Storage Box Info --}}
            <div class="px-5">
                <div class="flex flex-col gap-2.5">
                    <label class="text-mono font-semibold text-sm">Storage Box Information</label>
                    <div class="kt-card shadow-none bg-muted/30 p-4">
                        <div class="grid grid-cols-1 gap-2 text-sm">
                            <div>
                                <span class="text-secondary-foreground">Storage Box ID:</span>
                                <span class="font-mono text-foreground ml-2">{{ $storage_box_id ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-secondary-foreground">Username:</span>
                                <span class="font-mono text-foreground ml-2">{{ $username ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-secondary-foreground">Server:</span>
                                <span class="font-mono text-foreground ml-2">{{ $server ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="px-5">
                <div class="flex items-center gap-2.5">
                    <button class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <button class="kt-btn kt-btn-warning flex-1" wire:click="goto('resetPassword')"
                        wire:loading.attr="disabled" wire:target="goto('resetPassword')"
                        onclick="document.querySelector('[data-kt-modal-dismiss]').click()">
                        <i class="ki-filled ki-key me-2" wire:loading.remove wire:target="goto('resetPassword')"></i>
                        <i class="ki-duotone ki-loading me-2" wire:loading wire:target="goto('resetPassword')"></i>
                        <span wire:loading.remove wire:target="goto('resetPassword')">Reset Password</span>
                        <span wire:loading wire:target="goto('resetPassword')">Resetting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Snapshot Modal --}}
<div class="kt-modal" data-kt-modal="true" id="create_snapshot_modal">
    <div class="kt-modal-content max-w-[500px] top-5 lg:top-[10%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">
                <i class="ki-filled ki-save-2 me-2 text-info"></i>
                Create Storage Box Snapshot
            </h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body grid gap-5 px-0 py-5">
            {{-- Info Section --}}
            <div class="px-5">
                <div class="kt-alert kt-alert-info">
                    <div class="kt-alert-icon">
                        <i class="ki-filled ki-information text-lg"></i>
                    </div>
                    <div class="kt-alert-content">
                        <div class="kt-alert-title">Snapshot Creation</div>
                        <div class="kt-alert-text">
                            This will create a snapshot of your current storage box data.
                            <br>Snapshots are useful for backing up your data before making changes.
                            <br>You can restore from this snapshot later if needed.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Snapshot Configuration --}}
            <div class="px-5">
                <div class="flex flex-col gap-2.5">
                    <label class="text-mono font-semibold text-sm">Snapshot Description (Optional)</label>
                    <input type="text" class="kt-input" id="snapshot_description" 
                        placeholder="e.g., Before update - {{ date('Y-m-d H:i') }}"
                        value="Manual snapshot - {{ date('Y-m-d H:i:s') }}">
                    <span class="text-xs text-secondary-foreground">
                        Provide a description to help identify this snapshot later
                    </span>
                </div>
            </div>

            {{-- Current Storage Info --}}
            <div class="px-5">
                <div class="flex flex-col gap-2.5">
                    <label class="text-mono font-semibold text-sm">Storage Box Information</label>
                    <div class="kt-card shadow-none bg-muted/30 p-4">
                        <div class="grid grid-cols-1 gap-2 text-sm">
                            <div>
                                <span class="text-secondary-foreground">Total Size:</span>
                                <span class="font-mono text-foreground ml-2">
                                    @if (isset($size) && $size !== 'N/A')
                                        {{ round($size / 1024 / 1024 / 1024, 0) }} GB
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-secondary-foreground">Snapshot Limit:</span>
                                <span class="font-mono text-foreground ml-2">{{ $snapshot_limit ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="px-5">
                <div class="flex items-center gap-2.5">
                    <button class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <button class="kt-btn kt-btn-info flex-1" wire:click="goto('createSnapshot')"
                        wire:loading.attr="disabled" wire:target="goto('createSnapshot')"
                        onclick="document.querySelector('#create_snapshot_modal [data-kt-modal-dismiss]').click()">
                        <i class="ki-filled ki-save-2 me-2" wire:loading.remove wire:target="goto('createSnapshot')"></i>
                        <i class="ki-duotone ki-loading me-2" wire:loading wire:target="goto('createSnapshot')"></i>
                        <span wire:loading.remove wire:target="goto('createSnapshot')">Create Snapshot</span>
                        <span wire:loading wire:target="goto('createSnapshot')">Creating...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Access Settings Modal --}}
<div class="kt-modal" data-kt-modal="true" id="access_settings_modal">
    <div class="kt-modal-content max-w-[600px] top-5 lg:top-[10%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">
                <i class="ki-filled ki-setting-2 me-2 text-secondary"></i>
                Update Access Settings
            </h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body grid gap-5 px-0 py-5">
            {{-- Info Section --}}
            <div class="px-5">
                <div class="kt-alert">
                    <div class="kt-alert-content">
                        <div class="kt-alert-title">Access Settings</div>
                        <div class="kt-alert-text">
                            These settings are read-only for privacy and security reasons.
                            <br>If you need to change any access settings, please contact our support team for assistance.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Access Methods Configuration --}}
            <div class="px-5">
                <div class="flex flex-col gap-4">
                    <label class="text-mono font-semibold text-sm">Access Methods</label>
                    
                    <div class="grid grid-cols-1 gap-3">
                        <label class="kt-checkbox-group">
                            <input type="checkbox" class="kt-checkbox" id="ssh_enabled" disabled
                                {{ (isset($access_settings['ssh_enabled']) && $access_settings['ssh_enabled']) ? 'checked' : '' }}>
                            <span class="kt-checkbox-indicator"></span>
                            <div class="kt-checkbox-label">
                                <span class="font-medium">SSH Access</span>
                                <span class="text-sm text-secondary-foreground block">Enable SSH/SFTP access to your storage box</span>
                            </div>
                        </label>

                        <label class="kt-checkbox-group">
                            <input type="checkbox" class="kt-checkbox" id="samba_enabled" disabled
                                {{ (isset($access_settings['samba_enabled']) && $access_settings['samba_enabled']) ? 'checked' : '' }}>
                            <span class="kt-checkbox-indicator"></span>
                            <div class="kt-checkbox-label">
                                <span class="font-medium">Samba/CIFS Access</span>
                                <span class="text-sm text-secondary-foreground block">Enable Windows/SMB network drive access</span>
                            </div>
                        </label>

                        <label class="kt-checkbox-group">
                            <input type="checkbox" class="kt-checkbox" id="webdav_enabled" disabled
                                {{ (isset($access_settings['webdav_enabled']) && $access_settings['webdav_enabled']) ? 'checked' : '' }}>
                            <span class="kt-checkbox-indicator"></span>
                            <div class="kt-checkbox-label">
                                <span class="font-medium">WebDAV Access</span>
                                <span class="text-sm text-secondary-foreground block">Enable WebDAV protocol for file access</span>
                            </div>
                        </label>

                        <label class="kt-checkbox-group">
                            <input type="checkbox" class="kt-checkbox" id="zfs_enabled" disabled
                                {{ (isset($access_settings['zfs_enabled']) && $access_settings['zfs_enabled']) ? 'checked' : '' }}>
                            <span class="kt-checkbox-indicator"></span>
                            <div class="kt-checkbox-label">
                                <span class="font-medium">ZFS Features</span>
                                <span class="text-sm text-secondary-foreground block">Enable advanced ZFS filesystem features</span>
                            </div>
                        </label>

                        <label class="kt-checkbox-group">
                            <input type="checkbox" class="kt-checkbox" id="reachable_externally" disabled
                                {{ (isset($access_settings['reachable_externally']) && $access_settings['reachable_externally']) ? 'checked' : '' }}>
                            <span class="kt-checkbox-indicator"></span>
                            <div class="kt-checkbox-label">
                                <span class="font-medium">External Reachability</span>
                                <span class="text-sm text-secondary-foreground block">Allow access from external networks</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="px-5">
                <div class="flex items-center gap-2.5">
                    <button class="kt-btn kt-btn-outline flex-1" data-kt-modal-dismiss="true">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript Functions --}}
<script>
function copyToClipboard(text, message = 'Copied!') {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message (you might want to integrate with your notification system)
        console.log(message);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
    });
}

function togglePassword(button) {
    const passwordField = button.parentElement.querySelector('.password-field');
    const icon = button.querySelector('i');
    const password = passwordField.getAttribute('data-password');
    
    if (passwordField.textContent === '••••••••••••••••') {
        passwordField.textContent = password;
        icon.className = 'ki-filled ki-eye-slash text-xs';
        button.title = 'Hide Password';
    } else {
        passwordField.textContent = '••••••••••••••••';
        icon.className = 'ki-filled ki-eye text-xs';
        button.title = 'Show Password';
    }
}

function refreshStorageInfo() {
    location.reload();
}

// Initialize modals when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modals
    const modals = document.querySelectorAll('[data-kt-modal="true"]');
    modals.forEach(modal => {
        if (typeof KTModal !== 'undefined') {
            new KTModal(modal);
        }
    });
});
</script>