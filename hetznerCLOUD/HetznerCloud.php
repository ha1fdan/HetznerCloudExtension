<?php

namespace Paymenter\Extensions\Servers\HetznerCloud;

use App\Classes\Extension\Server;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HetznerCloud extends Server
{
    private string $baseUrl = 'https://api.hetzner.cloud/v1';
    private array $rateLimitInfo = [];

    public function boot(): void
    {
        // Register views namespace for Blade templates
        View::addNamespace('hetznercloud', __DIR__ . '/resources/views');
    }

    public function request($endpoint, $method = 'GET', $data = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        $apiToken = $this->config('api_token');
        if (empty($apiToken)) {
            throw new \Exception('Hetzner Cloud API Error: API token not configured');
        }

        $headers = [
            'Authorization' => 'Bearer ' . $apiToken,
            'Content-Type' => 'application/json',
        ];

        // Log the request data for debugging (only for non-GET requests)
        if ($method !== 'GET' && !empty($data)) {
            \Log::info('Hetzner Cloud API Request', [
                'url' => $url,
                'method' => $method,
                'data' => $data
            ]);
        }

        try {
            $response = Http::withHeaders($headers)->$method($url, $data);
        } catch (\Exception $e) {
            \Log::error('Hetzner Cloud HTTP Request Failed', [
                'url' => $url,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Hetzner Cloud API Error: Request failed - ' . $e->getMessage());
        }

        // Ensure we have a valid response object
        if (!$response) {
            throw new \Exception('Hetzner Cloud API Error: No response received');
        }

        if (!$response->successful()) {
            // Try to get error message from response
            $statusCode = $response->status();
            $rawBody = $response->body();
            $errorMessage = 'API request failed';

            \Log::warning('Hetzner Cloud API Error Response', [
                'url' => $url,
                'method' => $method,
                'status' => $statusCode,
                'body' => $rawBody
            ]);

            // Check if response has content and try to parse JSON
            if (!empty($rawBody)) {
                try {
                    $responseData = $response->json();
                    if (isset($responseData['error']['message'])) {
                        $errorMessage = $responseData['error']['message'];
                    } elseif (isset($responseData['message'])) {
                        $errorMessage = $responseData['message'];
                    } else {
                        $errorMessage = 'API request failed with status ' . $statusCode;
                    }
                } catch (\Exception $e) {
                    // If JSON parsing fails, check content type
                    $contentType = $response->header('Content-Type');
                    if (strpos($contentType, 'application/json') !== false) {
                        $errorMessage = 'Invalid JSON response from API (status: ' . $statusCode . ')';
                    } else {
                        // Non-JSON response, use raw body if reasonable length
                        $errorMessage = strlen($rawBody) <= 200 ? $rawBody : 'HTTP ' . $statusCode;
                    }
                }
            } else {
                $errorMessage = 'HTTP ' . $statusCode . ' - No response body';
            }

            throw new \Exception('Hetzner Cloud API Error: ' . $errorMessage);
        }

        // Handle successful responses
        $rawBody = $response->body();

        // If response is empty, return empty array (common for DELETE requests)
        if (empty($rawBody)) {
            return [];
        }

        // Check content type
        $contentType = $response->header('Content-Type') ?? '';
        if (strpos($contentType, 'application/json') === false) {
            // Non-JSON response for successful request
            \Log::info('Hetzner Cloud Non-JSON Response', [
                'url' => $url,
                'method' => $method,
                'content_type' => $contentType,
                'body_length' => strlen($rawBody)
            ]);
            return [];
        }

        try {
            return $response->json() ?? [];
        } catch (\Exception $e) {
            \Log::error('Hetzner Cloud JSON Parse Error', [
                'url' => $url,
                'method' => $method,
                'content_type' => $contentType,
                'body' => substr($rawBody, 0, 500), // First 500 chars for debugging
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Hetzner Cloud API Error: Failed to parse JSON response - ' . $e->getMessage());
        }
    }

    /**
     * Convert a value to a proper boolean for API requests
     */
    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) $value;
    }

    /**
     * Get all the configuration for the extension
     */
    public function getConfig($values = []): array
    {
        return [
            [
                'name' => 'api_token',
                'type' => 'text',
                'label' => 'API Token',
                'description' => 'Your Hetzner Cloud API Token',
                'required' => true,
            ],
            [
                'name' => 'hostname_prefix',
                'type' => 'text',
                'label' => 'Hostname Prefix',
                'description' => 'Prefix for server hostnames (e.g., "vps", "server", "instance")',
                'required' => false,
                'default' => 'vps',
            ],
        ];
    }

    /**
     * Get available locations
     */
    private function getLocations(): array
    {
        try {
            $response = $this->request('locations');
            $locations = [];

            if (!isset($response['locations']) || !is_array($response['locations'])) {
                throw new \Exception('Invalid response format for locations');
            }

            foreach ($response['locations'] as $location) {
                if (isset($location['name'])) {
                    $locations[$location['name']] = $location['name'] . ' (' . ($location['description'] ?? '') . ')';
                }
            }

            return $locations;
        } catch (\Exception $e) {
            \Log::warning('Hetzner Cloud: Failed to fetch locations', ['error' => $e->getMessage()]);
            return [
                'nbg1' => 'Nuremberg 1',
                'fsn1' => 'Falkenstein 1',
                'hel1' => 'Helsinki 1',
                'ash' => 'Ashburn',
                'hil' => 'Hillsboro',
            ];
        }
    }

    /**
     * Get available server types
     */
    private function getServerTypes(): array
    {
        try {
            $response = $this->request('server_types');
            $types = [];

            if (!isset($response['server_types']) || !is_array($response['server_types'])) {
                throw new \Exception('Invalid response format for server types');
            }

            foreach ($response['server_types'] as $type) {
                if (isset($type['id'], $type['name'])) {
                    $price = 'N/A';
                    if (isset($type['prices'][0]['price_monthly']['gross'])) {
                        $price = '€' . $type['prices'][0]['price_monthly']['gross'] . '/mo';
                    }

                    $types[$type['id']] = $type['name'] . ' - ' . $price . ' (' .
                        ($type['cores'] ?? 'N/A') . ' vCPU, ' .
                        ($type['memory'] ?? 'N/A') . 'GB RAM, ' .
                        ($type['disk'] ?? 'N/A') . 'GB SSD)';
                }
            }

            return $types;
        } catch (\Exception $e) {
            \Log::warning('Hetzner Cloud: Failed to fetch server types', ['error' => $e->getMessage()]);
            return [
                1 => 'cx11 - Basic (1 vCPU, 4GB RAM)',
                3 => 'cx21 - Basic (2 vCPU, 8GB RAM)',
                5 => 'cx31 - Basic (2 vCPU, 16GB RAM)',
                7 => 'cx41 - Basic (4 vCPU, 32GB RAM)',
            ];
        }
    }

    /**
     * Get available images
     */
    private function getImages(): array
    {
        try {
            $response = $this->request('images?type=system');
            $images = [];

            if (!isset($response['images']) || !is_array($response['images'])) {
                throw new \Exception('Invalid response format for images');
            }

            foreach ($response['images'] as $image) {
                if (isset($image['name'], $image['status']) && $image['status'] === 'available') {
                    $images[$image['name']] = $image['description'] ?? $image['name'];
                }
            }

            asort($images);
            return $images;
        } catch (\Exception $e) {
            \Log::warning('Hetzner Cloud: Failed to fetch images', ['error' => $e->getMessage()]);
            return [
                'ubuntu-22.04' => 'Ubuntu 22.04 Server',
                'ubuntu-20.04' => 'Ubuntu 20.04 Server',
                'debian-12' => 'Debian 12',
                'debian-11' => 'Debian 11',
                'centos-stream-9' => 'CentOS Stream 9',
                'fedora-39' => 'Fedora 39',
            ];
        }
    }

    /**
     * Get product config
     */
    public function getProductConfig($values = []): array
    {
        return [
            [
                'name' => 'server_type',
                'type' => 'select',
                'label' => 'Server Type',
                'description' => 'The server type/plan',
                'required' => true,
                'options' => $this->getServerTypes(),
            ],
            [
                'name' => 'location',
                'type' => 'select',
                'label' => 'Location',
                'description' => 'The datacenter location',
                'required' => true,
                'options' => $this->getLocations(),
            ],
            [
                'name' => 'image',
                'type' => 'select',
                'label' => 'Default Image',
                'description' => 'Default operating system image',
                'required' => true,
                'options' => $this->getImages(),
            ],
            [
                'name' => 'backups',
                'type' => 'checkbox',
                'label' => 'Enable Backups',
                'description' => 'Enable automated backups',
                'required' => false,
            ],
            [
                'name' => 'ipv6',
                'type' => 'checkbox',
                'label' => 'Enable IPv6',
                'description' => 'Enable IPv6 networking',
                'required' => false,
            ],
        ];
    }

    public function getCheckoutConfig(Product $product): array
    {
        return [
            [
                'name' => 'hostname',
                'type' => 'text',
                'label' => 'Hostname',
                'placeholder' => 'my-server',
                'description' => 'The hostname for your server',
                'required' => true,
            ],
            [
                'name' => 'image',
                'type' => 'select',
                'label' => 'Operating System',
                'description' => 'Choose the operating system for your server',
                'required' => true,
                'options' => $this->getImages(),
            ],
            [
                'name' => 'location',
                'type' => 'select',
                'label' => 'Location',
                'description' => 'Choose the datacenter location',
                'required' => false,
                'options' => $this->getLocations(),
            ],
        ];
    }

    /**
     * Check if current configuration is valid
     */
    public function testConfig(): bool|string
    {
        try {
            // Test with a simple API call
            $response = $this->request('server_types?per_page=1');

            if (empty($response)) {
                return 'API responded but returned empty data. Please check your API token.';
            }

            return true;
        } catch (\Exception $e) {
            return 'API Test Failed: ' . $e->getMessage();
        }
    }

    /**
     * Generate a random password for the server
     */
    private function generatePassword(): string
    {
        return Str::password(16);
    }

    /**
     * Create a server
     */
    public function createServer(Service $service, $settings, $properties)
    {
        // Validate hostname
        $hostname = trim($properties['hostname'] ?? '');
        if (empty($hostname)) {
            throw new \Exception('Hostname is required');
        }
        if (strlen($hostname) > 63) {
            throw new \Exception('Hostname cannot be longer than 63 characters');
        }
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$/', $hostname)) {
            throw new \Exception('Hostname can only contain letters, numbers, and hyphens, and must start and end with a letter or number');
        }

        $image = $properties['image'] ?? $settings['image'];
        $serverType = $settings['server_type'];
        $location = $properties['location'] ?? $settings['location'];

        // Generate password
        $password = $this->generatePassword();

        // Add hostname prefix if configured
        $hostnamePrefix = $this->config('hostname_prefix') ?? 'vps';
        $serverName = $hostnamePrefix . '-' . $hostname . '-' . date('dmYs');

        $data = [
            'name' => $serverName,
            'server_type' => $serverType,
            'image' => $image,
            'location' => $location,
            'start_after_create' => true,
            'automount' => false,
            'public_net' => [
                'enable_ipv4' => true,
                'enable_ipv6' => $this->toBool($settings['ipv6'] ?? false),
            ],
        ];

        if ($this->toBool($settings['backups'] ?? false)) {
            $data['backups'] = true;
        }

        // Validate data types before sending to API
        \Log::debug('Hetzner Cloud: Creating server with data', [
            'server_name' => $serverName,
            'server_type' => $serverType,
            'image' => $image,
            'location' => $location,
            'enable_ipv6' => $data['public_net']['enable_ipv6'],
            'enable_ipv6_type' => gettype($data['public_net']['enable_ipv6']),
            'backups' => $data['backups'] ?? false,
            'backups_type' => gettype($data['backups'] ?? false),
        ]);

        $response = $this->request('servers', 'POST', $data);

        if (!isset($response['server'])) {
            throw new \Exception('Failed to create server');
        }

        $server = $response['server'];

        // Store server information
        $service->properties()->updateOrCreate([
            'key' => 'server_id',
        ], [
            'name' => 'Hetzner Server ID',
            'value' => $server['id'],
        ]);

        $service->properties()->updateOrCreate([
            'key' => 'password',
        ], [
            'name' => 'Root Password',
            'value' => $response['root_password'],
        ]);

        // Wait for the server to be created and get the IP
        sleep(5);
        $serverInfo = $this->getServerInfo($server['id']);

        if (!empty($serverInfo['public_net']['ipv4']['ip'])) {
            $service->properties()->updateOrCreate([
                'key' => 'ip_address',
            ], [
                'name' => 'IP Address',
                'value' => $serverInfo['public_net']['ipv4']['ip'],
            ]);
        }

        if (!empty($serverInfo['public_net']['ipv6']['ip'])) {
            $service->properties()->updateOrCreate([
                'key' => 'ipv6_address',
            ], [
                'name' => 'IPv6 Address',
                'value' => $serverInfo['public_net']['ipv6']['ip'],
            ]);
        }

        return [
            'server' => $server,
            'password' => $response['root_password'],
            'ip_address' => $serverInfo['public_net']['ipv4']['ip'] ?? 'Pending',
        ];
    }

    /**
     * Get server information
     */
    private function getServerInfo($serverId): array
    {
        $response = $this->request("servers/$serverId");
        return $response['server'] ?? [];
    }

    /**
     * Upgrade/resize a server
     */
    public function upgradeServer(Service $service, $settings, $properties)
    {
        $serverId = $properties['server_id'] ?? null;

        if (!$serverId) {
            throw new \Exception('Server ID not found');
        }

        $newServerType = $settings['server_type'];

        // Power off the server first
        $this->request("servers/$serverId/actions/poweroff", 'POST');

        // Wait for power off
        sleep(30);

        // Resize the server
        $response = $this->request("servers/$serverId/actions/change_type", 'POST', [
            'server_type' => $newServerType,
            'upgrade_disk' => true
        ]);

        // Power back on
        sleep(60);
        $this->request("servers/$serverId/actions/poweron", 'POST');

        return [
            'action' => $response['action'] ?? null,
            'message' => 'Server resize initiated'
        ];
    }

    /**
     * Suspend a server (power off)
     */
    public function suspendServer(Service $service, $settings, $properties)
    {
        $serverId = $properties['server_id'] ?? null;

        if (!$serverId) {
            throw new \Exception('Server ID not found');
        }

        $this->request("servers/$serverId/actions/poweroff", 'POST');
    }

    /**
     * Unsuspend a server (power on)
     */
    public function unsuspendServer(Service $service, $settings, $properties)
    {
        $serverId = $properties['server_id'] ?? null;

        if (!$serverId) {
            throw new \Exception('Server ID not found');
        }

        $this->request("servers/$serverId/actions/poweron", 'POST');
    }

    /**
     * Terminate a server
     */
    public function terminateServer(Service $service, $settings, $properties)
    {
        $serverId = $properties['server_id'] ?? null;

        if (!$serverId) {
            throw new \Exception('Server ID not found');
        }

        $this->request("servers/$serverId", 'DELETE');

        // Remove stored properties
        $service->properties()->whereIn('key', ['server_id', 'password', 'ip_address', 'ipv6_address'])->delete();
    }

    /**
     * Get available actions for the service
     */
    public function getActions(Service $service): array
    {
        $serverId = $service->properties()->where('key', 'server_id')->first()?->value;

        if (!$serverId) {
            return [];
        }

        return [
            [
                'label' => 'Server Information',
                'name' => 'server_info',
                'type' => 'view',
                'function' => 'getStatisticsView',
            ],
            [
                'type' => 'button',
                'label' => 'Power On',
                'function' => 'powerOn',
                'color' => 'success',
                'hidden' => true, // Hide from service buttons but keep functionality
            ],
            [
                'type' => 'button',
                'label' => 'Power Off',
                'function' => 'powerOff',
                'color' => 'warning',
                'hidden' => true, // Hide from service buttons but keep functionality
            ],
            [
                'type' => 'button',
                'label' => 'Reboot',
                'function' => 'reboot',
                'color' => 'info',
                'hidden' => true, // Hide from service buttons but keep functionality
            ],
            [
                'type' => 'button',
                'label' => 'Reset Password',
                'function' => 'resetPassword',
                'color' => 'secondary',
                'hidden' => true, // Hide from service buttons but keep functionality
            ],
            [
                'type' => 'button',
                'label' => 'Rebuild OS',
                'function' => 'rebuild',
                'color' => 'danger',
                'hidden' => true, // Hide from service buttons but keep functionality
            ],
        ];
    }

    /**
     * Power on server
     */
    public function powerOn(Service $service): array
    {
        $serverId = $service->properties()->where('key', 'server_id')->first()?->value;

        if (!$serverId) {
            throw new \Exception('Server ID not found');
        }

        $response = $this->request("servers/$serverId/actions/poweron", 'POST');

        return [
            'success' => true,
            'message' => 'Server has been successfully powered on.',
            'action_id' => $response['action']['id'] ?? null,
        ];
    }

    /**
     * Power off server
     */
    public function powerOff(Service $service): array
    {
        $serverId = $service->properties()->where('key', 'server_id')->first()?->value;

        if (!$serverId) {
            throw new \Exception('Server ID not found');
        }

        $response = $this->request("servers/$serverId/actions/poweroff", 'POST');

        return [
            'success' => true,
            'message' => 'Server has been powered off successfully.',
            'action_id' => $response['action']['id'] ?? null,
        ];
    }

    /**
     * Reboot server
     */
    public function reboot(Service $service): array
    {
        $serverId = $service->properties()->where('key', 'server_id')->first()?->value;

        if (!$serverId) {
            throw new \Exception('Server ID not found');
        }

        $response = $this->request("servers/$serverId/actions/reboot", 'POST');

        return [
            'success' => true,
            'message' => 'Server has been rebooted successfully.',
            'action_id' => $response['action']['id'] ?? null,
        ];
    }

    /**
     * Validate server state before rebuild according to Hetzner Cloud requirements
     */
    private function validateServerState(Service $service, string $serverId): void
    {
        try {
            $serverInfo = $this->getServerInfo($serverId);

            if (empty($serverInfo)) {
                throw new \Exception('Server not found or inaccessible. Please verify the server exists and is accessible.');
            }

            // Check if server is locked by another operation
            if (isset($serverInfo['locked']) && $serverInfo['locked'] === true) {
                throw new \Exception('Server is currently locked by another operation. Please wait for the current operation to complete before rebuilding.');
            }

            // Check protection settings
            if (isset($serverInfo['protection']['rebuild']) && $serverInfo['protection']['rebuild'] === true) {
                throw new \Exception('Server rebuild protection is enabled. Please disable rebuild protection first.');
            }

            // Log server status for debugging
            \Log::info('Server state validation for rebuild', [
                'server_id' => $serverId,
                'status' => $serverInfo['status'] ?? 'unknown',
                'locked' => $serverInfo['locked'] ?? false,
                'rebuild_protection' => $serverInfo['protection']['rebuild'] ?? false,
                'service_id' => $service->id
            ]);

        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Server not found') !== false) {
                throw new \Exception('Server not found in Hetzner Cloud. Please contact support if this persists.');
            }

            // Re-throw validation errors
            throw $e;
        }
    }

    /**
     * Get server status and information
     */
    public function getServerStatus(Service $service): array
    {
        $serverId = $service->properties()->where('key', 'server_id')->first()?->value;

        if (!$serverId) {
            throw new \Exception('Server ID not found');
        }

        $server = $this->getServerInfo($serverId);

        return [
            'status' => $server['status'] ?? 'unknown',
            'name' => $server['name'] ?? 'N/A',
            'ip_address' => $server['public_net']['ipv4']['ip'] ?? 'N/A',
            'ipv6_address' => $server['public_net']['ipv6']['ip'] ?? 'N/A',
            'location' => $server['datacenter']['name'] ?? 'N/A',
            'server_type' => $server['server_type']['name'] ?? 'N/A',
            'image' => $server['image']['name'] ?? 'N/A',
            'created_at' => $server['created'] ?? 'N/A',
            'cores' => $server['server_type']['cores'] ?? 'N/A',
            'memory' => $server['server_type']['memory'] ?? 'N/A',
            'disk' => $server['server_type']['disk'] ?? 'N/A',
        ];
    }

    /**
     * Get statistics view for service display
     */
    public function getStatisticsView(Service $service, $settings, $properties, $viewName): string
    {
        try {
            $serverInfo = $this->getServerInfoForView($service);

            // Add server actions to the view data
            $serverActions = $this->getServerActions($service);
            $serverInfo['server_actions'] = $serverActions;

            return view('hetznercloud::info', $serverInfo)->render();
        } catch (\Exception $e) {
            return '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }

    /**
     * Get comprehensive server information for view display
     */
    private function getServerInfoForView(Service $service): array
    {
        $properties = $service->properties->pluck('value', 'key')->toArray();

        $serverId = $properties['server_id'] ?? null;
        if (!$serverId) {
            throw new \Exception('Server ID not found');
        }

        // Get server status information
        $serverStatus = $this->getServerStatus($service);

        // Get stored password
        $password = $properties['password'] ?? 'N/A';

        // Get reverse DNS if available
        $reverseDns = 'N/A';
        try {
            $serverInfo = $this->getServerInfo($serverId);
            $reverseDns = $serverInfo['public_net']['ipv4']['dns_ptr'] ?? 'N/A';
        } catch (\Exception $e) {
            // Ignore errors when fetching reverse DNS
            \Log::debug('Failed to fetch reverse DNS for server ' . $serverId . ': ' . $e->getMessage());
        }

        // Get available images for rebuild functionality
        $availableImages = [];
        try {
            $availableImages = $this->getImages();
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch available images for rebuild: ' . $e->getMessage());
            // Provide fallback images if API fails
            $availableImages = [
                'ubuntu-24.04' => 'Ubuntu 24.04 LTS (Noble Numbat)',
                'ubuntu-22.04' => 'Ubuntu 22.04 LTS (Jammy Jellyfish)',
                'ubuntu-20.04' => 'Ubuntu 20.04 LTS (Focal Fossa)',
                'debian-12' => 'Debian 12 (Bookworm)',
                'debian-11' => 'Debian 11 (Bullseye)',
                'centos-stream-9' => 'CentOS Stream 9',
                'rocky-9' => 'Rocky Linux 9',
                'almalinux-9' => 'AlmaLinux 9',
                'fedora-40' => 'Fedora 40',
                'fedora-39' => 'Fedora 39',
            ];
        }

        // Map the data to the format expected by the view
        return [
            'server_id' => $serverId,
            'server_ipv4' => $serverStatus['ip_address'] ?? 'N/A',
            'server_ipv6' => $serverStatus['ipv6_address'] ?? 'N/A',
            'server_root_passwd' => $password,
            'status' => $serverStatus['status'] ?? 'unknown',
            'description' => $serverStatus['image'] ?? 'N/A',
            'cores' => $serverStatus['cores'] ?? 'N/A',
            'memory' => $serverStatus['memory'] ?? 'N/A',
            'disk' => $serverStatus['disk'] ?? 'N/A',
            'reverse_dns' => $reverseDns,
            'available_images' => $availableImages, // Available images for rebuild
            'server_type' => $serverStatus['server_type'] ?? 'N/A',
            'location' => $serverStatus['location'] ?? 'N/A',
            'created_at' => $serverStatus['created_at'] ?? 'N/A',
            'orderProduct' => $service, // Pass the service as orderProduct for compatibility
            'service' => $service, // Also pass as service for clarity
        ];
    }

    /**
     * Change reverse DNS
     */
    public function changeReverseDns(Service $service): array
    {
        $serverId = $service->properties()->where('key', 'server_id')->first()?->value;
        $serverIpv4 = $service->properties()->where('key', 'ip_address')->first()?->value;

        if (!$serverId || !$serverIpv4) {
            throw new \Exception('Server ID or IP address not found');
        }

        $reverseDns = request('reverse_dns');
        if (!$reverseDns) {
            throw new \Exception('Reverse DNS is required');
        }

        $response = $this->request("servers/$serverId/actions/change_dns_ptr", 'POST', [
            'ip' => $serverIpv4,
            'dns_ptr' => $reverseDns,
        ]);

        if (isset($response['action']['error']) && $response['action']['error'] !== null) {
            throw new \Exception('Unable to change reverse DNS: ' . $response['action']['error']['message']);
        }

        return [
            'success' => true,
            'message' => 'Reverse DNS has been updated successfully.',
            'action_id' => $response['action']['id'] ?? null,
        ];
    }

    /**
     * Reset server root password
     */
    public function resetPassword(Service $service): array
    {
        $serverId = $service->properties()->where('key', 'server_id')->first()?->value;

        if (!$serverId) {
            throw new \Exception('Server ID not found');
        }

        $response = $this->request("servers/$serverId/actions/reset_password", 'POST');

        if (isset($response['action']['error']) && $response['action']['error'] !== null) {
            throw new \Exception('Unable to reset password: ' . $response['action']['error']['message']);
        }

        // Store the new password if provided in response
        if (isset($response['root_password'])) {
            $service->properties()->updateOrCreate(
                ['key' => 'password'],
                [
                    'name' => 'Root Password',
                    'value' => $response['root_password']
                ]
            );
        }

        return [
            'success' => true,
            'message' => 'Root password has been reset successfully.',
            'action_id' => $response['action']['id'] ?? null,
            'root_password' => $response['root_password'] ?? null,
        ];
    }

    /**
     * Rebuild server with a specific image selected from modal
     */
    public function rebuildWithImage(Service $service, string $image = null): array
    {
        $serverId = $service->properties()->where('key', 'server_id')->first()?->value;

        if (!$serverId) {
            throw new \Exception('Server ID not found in service properties');
        }

        // Validate server state before rebuild
        $this->validateServerState($service, $serverId);

        // Use provided image or fall back to current server image
        if (!$image) {
            $image = $this->getBaseImageFromProduct($service);
        }

        if (!$image) {
            throw new \Exception('Unable to determine image for rebuild. Please select an image and try again.');
        }

        // Validate that the image exists and is available
        $this->validateImageAvailability($image);

        try {
            \Log::info('Starting server rebuild process with selected image', [
                'server_id' => $serverId,
                'service_id' => $service->id,
                'selected_image' => $image,
                'user_agent' => request()->userAgent()
            ]);

            // Perform rebuild via Hetzner Cloud API
            $response = $this->request("servers/$serverId/actions/rebuild", 'POST', [
                'image' => $image
            ]);

            // Check for API errors in response
            if (isset($response['action']['error']) && $response['action']['error'] !== null) {
                $errorMessage = $response['action']['error']['message'] ?? 'Unknown API error';
                throw new \Exception('Hetzner Cloud API Error: ' . $errorMessage);
            }

            // Store the new root password if provided
            $rootPassword = $response['root_password'] ?? null;
            if ($rootPassword) {
                $service->properties()->updateOrCreate(
                    ['key' => 'password'],
                    [
                        'name' => 'Root Password',
                        'value' => $rootPassword,
                    ]
                );
            }

            // Get image details for logging and response
            $imageDetails = $this->getImageDetails($image);
            $imageName = $imageDetails['name'] ?? $image;

            \Log::info('Server rebuild initiated successfully', [
                'server_id' => $serverId,
                'service_id' => $service->id,
                'action_id' => $response['action']['id'] ?? null,
                'image_name' => $imageName,
                'new_password_provided' => !empty($rootPassword)
            ]);

            $message = "Server rebuild has been initiated successfully with {$imageName}. ";
            $message .= "Your server will be automatically powered off and rebuilt with the selected image. ";
            if ($rootPassword) {
                $message .= "A new root password has been generated and saved. ";
            }
            $message .= "This process typically takes 1-3 minutes to complete.";

            return [
                'success' => true,
                'message' => $message,
                'action_id' => $response['action']['id'] ?? null,
                'image' => $image,
                'image_name' => $imageName,
                'root_password' => $rootPassword,
            ];

        } catch (\Exception $e) {
            \Log::error('Server rebuild failed', [
                'server_id' => $serverId,
                'service_id' => $service->id,
                'selected_image' => $image,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception('Server rebuild failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle goto actions (called by Livewire goto method or direct POST)
     */
    public function goto($action = null)
    {
        $action = $action ?? request('action') ?? request()->route('action');

        // If this is a POST request, we might be handling a direct form submission
        if (request()->isMethod('post') && request()->has('action')) {
            $action = request('action');
        }

        switch ($action) {
            case 'rebuild':
                // Get the service for rebuild
                $serviceId = request('service_id') ?? request()->route('service');
                if (!$serviceId) {
                    if (request()->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Service ID not found'], 400);
                    }
                    return redirect()->back()->with('error', 'Service ID not found');
                }

                $service = \App\Models\Service::find($serviceId);
                if (!$service) {
                    if (request()->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Service not found'], 404);
                    }
                    return redirect()->back()->with('error', 'Service not found');
                }

                try {
                    $result = $this->rebuild($service);
                    if (request()->ajax()) {
                        return response()->json(['success' => true, 'message' => 'Server rebuild initiated successfully', 'data' => $result]);
                    }
                    return redirect()->back()->with('success', 'Server rebuild initiated successfully');
                } catch (\Exception $e) {
                    if (request()->ajax()) {
                        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
                    }
                    return redirect()->back()->with('error', $e->getMessage());
                }

            default:
                if (request()->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Unknown action: ' . $action], 400);
                }
                return redirect()->back()->with('error', 'Unknown action: ' . $action);
        }
    }

    /**
     * Rebuild server with a selected image
     */
    public function rebuild(Service $service): array
    {
        $serverId = $service->properties()->where('key', 'server_id')->first()?->value;

        if (!$serverId) {
            throw new \Exception('Server ID not found in service properties');
        }

        // Validate server state before rebuild
        $this->validateServerState($service, $serverId);

        // Get the image from the request (from modal selection)
        $image = $this->getImageFromRequest();

        // If no image provided in request, fall back to current server image
        if (!$image) {
            $image = $this->getBaseImageFromProduct($service);
        }

        if (!$image) {
            throw new \Exception('Unable to determine image for rebuild. Please select an image and try again.');
        }

        // Validate that the image exists and is available
        $this->validateImageAvailability($image);

        try {
            \Log::info('Starting server rebuild process with selected image', [
                'server_id' => $serverId,
                'service_id' => $service->id,
                'selected_image' => $image,
                'request_method' => request()->method(),
                'user_agent' => request()->userAgent()
            ]);

            // Perform rebuild via Hetzner Cloud API
            $response = $this->request("servers/$serverId/actions/rebuild", 'POST', [
                'image' => $image
            ]);

            // Check for API errors in response
            if (isset($response['action']['error']) && $response['action']['error'] !== null) {
                $errorMessage = $response['action']['error']['message'] ?? 'Unknown API error';
                throw new \Exception('Hetzner Cloud API Error: ' . $errorMessage);
            }

            // Store the new root password if provided
            $rootPassword = $response['root_password'] ?? null;
            if ($rootPassword) {
                $service->properties()->updateOrCreate(
                    ['key' => 'password'],
                    [
                        'name' => 'Root Password',
                        'value' => $rootPassword
                    ]
                );
            }

            // Get image details for logging and response
            $imageDetails = $this->getImageDetails($image);
            $imageName = $imageDetails['name'] ?? $image;

            \Log::info('Server rebuild initiated successfully', [
                'server_id' => $serverId,
                'service_id' => $service->id,
                'action_id' => $response['action']['id'] ?? null,
                'image_name' => $imageName,
                'image_type' => $imageDetails['type'] ?? 'unknown',
                'new_password_provided' => !empty($rootPassword)
            ]);

            $message = "Server rebuild has been initiated successfully with {$imageName}. ";
            $message .= "Your server will be automatically powered off and rebuilt with the selected image. ";
            if ($rootPassword) {
                $message .= "A new root password has been generated and saved. ";
            }
            $message .= "This process typically takes 1-3 minutes to complete.";

            return [
                'success' => true,
                'message' => $message,
                'action_id' => $response['action']['id'] ?? null,
                'image' => $image,
                'image_name' => $imageName,
                'root_password' => $rootPassword,
                'image_info' => [
                    'name' => $imageName,
                    'type' => $imageDetails['type'] ?? 'unknown'
                ]
            ];

        } catch (\Exception $e) {
            \Log::error('Server rebuild failed', [
                'server_id' => $serverId,
                'service_id' => $service->id,
                'selected_image' => $image,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception('Server rebuild failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the base image from current server configuration
     */
    private function getBaseImageFromProduct(Service $service): ?string
    {
        try {
            $serverId = $service->properties()->where('key', 'server_id')->first()?->value;

            if (!$serverId) {
                \Log::warning('No server ID found for service', ['service_id' => $service->id]);
                return null;
            }

            // Get current server information to retrieve the image
            $serverInfo = $this->getServerInfo($serverId);

            if (empty($serverInfo)) {
                \Log::warning('No server info found', [
                    'service_id' => $service->id,
                    'server_id' => $serverId
                ]);
                return null;
            }

            // Get the current image name from server info
            $currentImage = $serverInfo['image']['name'] ?? null;

            if (!$currentImage) {
                \Log::warning('No current image found in server info', [
                    'service_id' => $service->id,
                    'server_id' => $serverId,
                    'server_info_keys' => array_keys($serverInfo)
                ]);
                return null;
            }

            \Log::info('Using current server image for rebuild', [
                'service_id' => $service->id,
                'server_id' => $serverId,
                'current_image' => $currentImage
            ]);

            return $currentImage;

        } catch (\Exception $e) {
            \Log::error('Failed to get current image from server info', [
                'service_id' => $service->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get image parameter from request with multiple fallback methods
     */
    private function getImageFromRequest(): ?string
    {
        // Try various methods to get the image parameter
        $sources = [
            function () {
                return request('image'); },
            function () {
                return request()->input('image'); },
            function () {
                return request()->post('image'); },
            function () {
                return request()->get('image'); },
            function () {
                return request('rebuild_image'); },
            function () {
                return request()->input('rebuild_image'); },
            function () {
                return $_POST['image'] ?? null; },
            function () {
                return $_GET['image'] ?? null; },
            function () {
                return $_GET['rebuild_image'] ?? null; },
            function () {
                return session('rebuild_image'); },
            function () {
                return request()->cookie('rebuild_image'); }, // Check cookies
            function () {
                return $_COOKIE['rebuild_image'] ?? null; }, // Direct cookie access
        ];

        foreach ($sources as $source) {
            $image = $source();
            if ($image) {
                // Clear session and cookie data if we found it there
                if (session('rebuild_image') === $image) {
                    session()->forget('rebuild_image');
                }
                if (request()->cookie('rebuild_image') === $image) {
                    // Clear the cookie by setting it to expire
                    setcookie('rebuild_image', '', time() - 3600, '/');
                }

                \Log::info('Found rebuild image from request', ['image' => $image, 'source' => 'request_parameter']);
                return $image;
            }
        }

        \Log::debug('No rebuild image found in request sources');
        return null;
    }

    /**
     * Validate that image is available for use
     */
    private function validateImageAvailability(string $image): void
    {
        $availableImages = $this->getImages();

        // Check if image exists in available images list
        if (!array_key_exists($image, $availableImages)) {
            \Log::warning('Selected image not found in available images', [
                'selected_image' => $image,
                'available_images_count' => count($availableImages),
                'available_images' => array_keys($availableImages)
            ]);
            throw new \Exception('Selected image is not available. Please refresh the page and select a valid image.');
        }
    }

    /**
     * Get detailed information about an image
     */
    private function getImageDetails(string $imageId): array
    {
        try {
            $response = $this->request("images/$imageId");
            return $response['image'] ?? [];
        } catch (\Exception $e) {
            \Log::warning('Failed to get image details', [
                'image_id' => $imageId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get server actions/activities from Hetzner Cloud API
     * 
     * @param Service $service
     * @return array
     */
    public function getServerActions(Service $service): array
    {
        try {
            $serverId = $service->properties()->where('key', 'server_id')->first()?->value;
            if (!$serverId) {
                return [];
            }

            // Get server actions from API with pagination and sorting
            $response = $this->request("servers/$serverId/actions?sort=started:desc&per_page=10");

            if (!isset($response['actions']) || !is_array($response['actions'])) {
                return [];
            }

            return array_map(function ($action) {
                return [
                    'id' => $action['id'],
                    'command' => $action['command'],
                    'status' => $action['status'],
                    'progress' => $action['progress'] ?? 0,
                    'started' => $action['started'],
                    'finished' => $action['finished'],
                    'error' => $action['error'] ?? null,
                    'resources' => $action['resources'] ?? []
                ];
            }, $response['actions']);

        } catch (\Exception $e) {
            \Log::warning('Failed to get server actions', [
                'service_id' => $service->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}