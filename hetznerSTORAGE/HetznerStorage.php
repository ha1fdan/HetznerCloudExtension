<?php

namespace Paymenter\Extensions\Servers\HetznerStorage;

use App\Classes\Extension\Server;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HetznerStorage extends Server
{
    private string $baseUrl = 'https://api.hetzner.com/v1';
    private array $rateLimitInfo = [];

    public function boot(): void
    {
        // Register views namespace for Blade templates
        View::addNamespace('hetznerstorage', __DIR__ . '/resources/views');
    }

    public function request($endpoint, $method = 'GET', $data = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        $apiToken = $this->config('api_token');
        if (empty($apiToken)) {
            throw new \Exception('Hetzner Storage API Error: API token not configured');
        }

        $headers = [
            'Authorization' => 'Bearer ' . $apiToken,
            'Content-Type' => 'application/json',
        ];

        // Log the request data for debugging (only for non-GET requests)
        if ($method !== 'GET' && !empty($data)) {
            \Log::info('Hetzner Storage API Request', [
                'url' => $url,
                'method' => $method,
                'data' => $data
            ]);
        }

        try {
            $response = Http::withHeaders($headers)->$method($url, $data);
        } catch (\Exception $e) {
            \Log::error('Hetzner Storage HTTP Request Failed', [
                'url' => $url,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Hetzner Storage API Error: Request failed - ' . $e->getMessage());
        }

        // Ensure we have a valid response object
        if (!$response) {
            throw new \Exception('Hetzner Storage API Error: No response received');
        }

        if (!$response->successful()) {
            // Try to get error message from response
            $statusCode = $response->status();
            $rawBody = $response->body();
            $errorMessage = 'API request failed';

            \Log::warning('Hetzner Storage API Error Response', [
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

            throw new \Exception('Hetzner Storage API Error: ' . $errorMessage);
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
            \Log::error('Hetzner Storage JSON Parse Error', [
                'url' => $url,
                'method' => $method,
                'content_type' => $contentType,
                'body' => substr($rawBody, 0, 500), // First 500 chars for debugging
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Hetzner Storage API Error: Failed to parse JSON response - ' . $e->getMessage());
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
     * Generate a secure password that meets Hetzner Storage Box requirements
     */
    private function generatePassword(): string
    {
        // Password must be between 8 and 128 characters long
        // Must contain at least one upper case letter, one lower case letter and one number or special character
        // Can only contain: a-z A-Z 0-9 ! ? . = + % # _ * ~ & $ ( ) / [ ] { } -
        
        $length = 16;
        $upperCase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowerCase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $specialChars = '!?.=+%#_*~&$()[]{}';
        
        // Ensure we have at least one character from each required group
        $password = '';
        $password .= $upperCase[random_int(0, strlen($upperCase) - 1)];
        $password .= $lowerCase[random_int(0, strlen($lowerCase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $specialChars[random_int(0, strlen($specialChars) - 1)];
        
        // Fill the rest with random characters from all allowed characters
        $allChars = $upperCase . $lowerCase . $numbers . $specialChars;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        // Shuffle the password to randomize the order
        return str_shuffle($password);
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
                'description' => 'Your Hetzner Storage API Token',
                'required' => true,
            ],
            [
                'name' => 'name_prefix',
                'type' => 'text',
                'label' => 'Storage Box Name Prefix',
                'description' => 'Prefix for storage box names (e.g., "storage", "backup", "box")',
                'required' => false,
                'default' => 'storage',
            ],
        ];
    }

    /**
     * Get available locations
     */
    private function getLocations(): array
    {
        // Storage Boxes are only available in these locations
        $availableStorageBoxLocations = ['fsn1', 'hel1'];
        
        try {
            $response = $this->request('locations');
            $locations = [];

            if (!isset($response['locations']) || !is_array($response['locations'])) {
                throw new \Exception('Invalid response format for locations');
            }

            foreach ($response['locations'] as $location) {
                if (isset($location['name']) && in_array($location['name'], $availableStorageBoxLocations)) {
                    $locations[$location['name']] = $location['name'] . ' (' . ($location['description'] ?? '') . ')';
                }
            }

            // If no valid locations found from API, return fallback
            if (empty($locations)) {
                throw new \Exception('No valid Storage Box locations found in API response');
            }

            return $locations;
        } catch (\Exception $e) {
            \Log::warning('Hetzner Storage: Failed to fetch locations', ['error' => $e->getMessage()]);
            // Only fsn1 (Falkenstein) and hel1 (Helsinki) are available for Storage Boxes
            return [
                'fsn1' => 'Falkenstein 1',
                'hel1' => 'Helsinki 1',
            ];
        }
    }

    /**
     * Get available storage box types
     */
    private function getStorageBoxTypes(): array
    {
        try {
            $response = $this->request('storage_box_types');
            $types = [];

            if (!isset($response['storage_box_types']) || !is_array($response['storage_box_types'])) {
                throw new \Exception('Invalid response format for storage box types');
            }

            foreach ($response['storage_box_types'] as $type) {
                if (isset($type['id'], $type['name'])) {
                    $price = 'N/A';
                    if (isset($type['prices'][0]['price_monthly']['gross'])) {
                        $price = '€' . $type['prices'][0]['price_monthly']['gross'] . '/mo';
                    }

                    $sizeGB = isset($type['size']) ? round($type['size'] / 1024 / 1024 / 1024, 0) : 'N/A';

                    $types[$type['name']] = $type['name'] . ' - ' . $price . ' (' .
                        $sizeGB . 'GB, ' .
                        ($type['snapshot_limit'] ?? 'N/A') . ' snapshots, ' .
                        ($type['subaccounts_limit'] ?? 'N/A') . ' subaccounts)';
                }
            }

            return $types;
        } catch (\Exception $e) {
            \Log::warning('Hetzner Storage: Failed to fetch storage box types', ['error' => $e->getMessage()]);
            return [
                'bx11' => 'BX11 - Basic (100GB)',
                'bx21' => 'BX21 - Standard (1TB)',
                'bx31' => 'BX31 - Professional (5TB)',
                'bx41' => 'BX41 - Enterprise (10TB)',
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
                'name' => 'storage_box_type',
                'type' => 'select',
                'label' => 'Storage Box Type',
                'description' => 'The storage box type/plan',
                'required' => true,
                'options' => $this->getStorageBoxTypes(),
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
                'name' => 'ssh_enabled',
                'type' => 'checkbox',
                'label' => 'Enable SSH',
                'description' => 'Enable SSH access to the storage box',
                'required' => false,
            ],
            [
                'name' => 'samba_enabled',
                'type' => 'checkbox',
                'label' => 'Enable Samba',
                'description' => 'Enable Samba/CIFS access',
                'required' => false,
            ],
            [
                'name' => 'webdav_enabled',
                'type' => 'checkbox',
                'label' => 'Enable WebDAV',
                'description' => 'Enable WebDAV access',
                'required' => false,
            ],
            [
                'name' => 'zfs_enabled',
                'type' => 'checkbox',
                'label' => 'Enable ZFS Snapshots',
                'description' => 'Enable ZFS snapshot folder visibility',
                'required' => false,
            ],
            [
                'name' => 'reachable_externally',
                'type' => 'checkbox',
                'label' => 'External Access',
                'description' => 'Make storage box accessible from outside Hetzner network',
                'required' => false,
            ],
        ];
    }

    public function getCheckoutConfig(Product $product): array
    {
        return [
            [
                'name' => 'storage_name',
                'type' => 'text',
                'label' => 'Storage Box Name',
                'placeholder' => 'my-storage',
                'description' => 'The name for your storage box',
                'required' => true,
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
            $response = $this->request('storage_box_types?per_page=1');

            if (empty($response)) {
                return 'API responded but returned empty data. Please check your API token.';
            }

            return true;
        } catch (\Exception $e) {
            return 'API Test Failed: ' . $e->getMessage();
        }
    }

    /**
     * Create a storage box
     */
    public function createServer(Service $service, $settings, $properties)
    {
        // Validate storage box name
        $storageName = trim($properties['storage_name'] ?? '');
        if (empty($storageName)) {
            throw new \Exception('Storage box name is required');
        }
        if (strlen($storageName) > 63) {
            throw new \Exception('Storage box name cannot be longer than 63 characters');
        }
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$/', $storageName)) {
            throw new \Exception('Storage box name can only contain letters, numbers, and hyphens, and must start and end with a letter or number');
        }

        $storageBoxType = $settings['storage_box_type'];
        $location = $properties['location'] ?? $settings['location'];

        // Generate password
        $password = $this->generatePassword();

        // Add name prefix if configured
        $namePrefix = $this->config('name_prefix') ?? 'storage';
        $boxName = $namePrefix . '-' . $storageName . '-' . date('dmYs');

        $data = [
            'storage_box_type' => $storageBoxType,
            'location' => $location,
            'name' => $boxName,
            'password' => $password,
        ];

        // Set access settings
        $accessSettings = [
            'ssh_enabled' => $this->toBool($settings['ssh_enabled'] ?? false),
            'samba_enabled' => $this->toBool($settings['samba_enabled'] ?? false),
            'webdav_enabled' => $this->toBool($settings['webdav_enabled'] ?? false),
            'zfs_enabled' => $this->toBool($settings['zfs_enabled'] ?? false),
            'reachable_externally' => $this->toBool($settings['reachable_externally'] ?? false),
        ];

        $data['access_settings'] = $accessSettings;

        // Validate data types before sending to API
        \Log::debug('Hetzner Storage: Creating storage box with data', [
            'box_name' => $boxName,
            'storage_box_type' => $storageBoxType,
            'location' => $location,
            'access_settings' => $accessSettings,
        ]);

        $response = $this->request('storage_boxes', 'POST', $data);

        if (!isset($response['action'])) {
            throw new \Exception('Failed to create storage box');
        }

        $action = $response['action'];
        $storageBoxId = null;

        // Extract storage box ID from action resources
        if (isset($action['resources'])) {
            foreach ($action['resources'] as $resource) {
                if ($resource['type'] === 'storage_box') {
                    $storageBoxId = $resource['id'];
                    break;
                }
            }
        }

        if (!$storageBoxId) {
            throw new \Exception('Storage box ID not found in response');
        }

        // Store storage box information
        $service->properties()->updateOrCreate([
            'key' => 'storage_box_id',
        ], [
            'name' => 'Hetzner Storage Box ID',
            'value' => $storageBoxId,
        ]);

        $service->properties()->updateOrCreate([
            'key' => 'password',
        ], [
            'name' => 'Storage Box Password',
            'value' => $password,
        ]);

        $service->properties()->updateOrCreate([
            'key' => 'username',
        ], [
            'name' => 'Storage Box Username',
            'value' => $boxName, // Username will be assigned by Hetzner
        ]);

        // Wait for the storage box to be created and get the details
        sleep(10);
        $storageBoxInfo = $this->getStorageBoxInfo($storageBoxId);

        if (!empty($storageBoxInfo['username'])) {
            $service->properties()->updateOrCreate([
                'key' => 'username',
            ], [
                'name' => 'Storage Box Username',
                'value' => $storageBoxInfo['username'],
            ]);
        }

        if (!empty($storageBoxInfo['server'])) {
            $service->properties()->updateOrCreate([
                'key' => 'server',
            ], [
                'name' => 'Storage Box Server',
                'value' => $storageBoxInfo['server'],
            ]);
        }

        return [
            'storage_box' => $storageBoxInfo,
            'password' => $password,
            'username' => $storageBoxInfo['username'] ?? $boxName,
            'server' => $storageBoxInfo['server'] ?? 'Pending',
            'action_id' => $action['id'] ?? null,
        ];
    }

    /**
     * Get storage box information
     */
    private function getStorageBoxInfo($storageBoxId): array
    {
        $response = $this->request("storage_boxes/$storageBoxId");
        return $response['storage_box'] ?? [];
    }

    /**
     * Upgrade/resize a storage box
     */
    public function upgradeServer(Service $service, $settings, $properties)
    {
        $storageBoxId = $properties['storage_box_id'] ?? null;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        $newStorageBoxType = $settings['storage_box_type'];

        // Change storage box type
        $response = $this->request("storage_boxes/$storageBoxId/actions/change_type", 'POST', [
            'storage_box_type' => $newStorageBoxType
        ]);

        return [
            'action' => $response['action'] ?? null,
            'message' => 'Storage box resize initiated'
        ];
    }

    /**
     * Suspend a storage box (disable access)
     */
    public function suspendServer(Service $service, $settings, $properties)
    {
        $storageBoxId = $properties['storage_box_id'] ?? null;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        // Disable all access methods
        $this->request("storage_boxes/$storageBoxId/actions/update_access_settings", 'POST', [
            'ssh_enabled' => false,
            'samba_enabled' => false,
            'webdav_enabled' => false,
            'reachable_externally' => false,
        ]);
    }

    /**
     * Unsuspend a storage box (restore access)
     */
    public function unsuspendServer(Service $service, $settings, $properties)
    {
        $storageBoxId = $properties['storage_box_id'] ?? null;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        // Restore access settings based on product configuration
        $this->request("storage_boxes/$storageBoxId/actions/update_access_settings", 'POST', [
            'ssh_enabled' => $this->toBool($settings['ssh_enabled'] ?? false),
            'samba_enabled' => $this->toBool($settings['samba_enabled'] ?? false),
            'webdav_enabled' => $this->toBool($settings['webdav_enabled'] ?? false),
            'reachable_externally' => $this->toBool($settings['reachable_externally'] ?? false),
        ]);
    }

    /**
     * Terminate a storage box
     */
    public function terminateServer(Service $service, $settings, $properties)
    {
        $storageBoxId = $properties['storage_box_id'] ?? null;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        $this->request("storage_boxes/$storageBoxId", 'DELETE');

        // Remove stored properties
        $service->properties()->whereIn('key', ['storage_box_id', 'password', 'username', 'server'])->delete();
    }

    /**
     * Get available actions for the service
     */
    public function getActions(Service $service): array
    {
        $storageBoxId = $service->properties()->where('key', 'storage_box_id')->first()?->value;

        if (!$storageBoxId) {
            return [];
        }

        return [
            [
                'label' => 'Storage Box Information',
                'name' => 'storage_info',
                'type' => 'view',
                'function' => 'getStatisticsView',
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
                'label' => 'Create Snapshot',
                'function' => 'createSnapshot',
                'color' => 'info',
                'hidden' => true, // Hide from service buttons but keep functionality
            ],
            [
                'type' => 'button',
                'label' => 'Update Access Settings',
                'function' => 'updateAccessSettings',
                'color' => 'secondary',
                'hidden' => true, // Hide from service buttons but keep functionality
            ],
        ];
    }

    /**
     * Reset storage box password
     */
    public function resetPassword(Service $service): array
    {
        $storageBoxId = $service->properties()->where('key', 'storage_box_id')->first()?->value;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        try {
            \Log::info('Starting storage box password reset', [
                'storage_box_id' => $storageBoxId,
                'service_id' => $service->id,
            ]);

            // Generate a secure random password for the storage box
            $newPassword = $this->generatePassword();

            // For Storage Boxes, we need to provide the new password in the request
            $response = $this->request("storage_boxes/$storageBoxId/actions/reset_password", 'POST', [
                'password' => $newPassword
            ]);

            // Check for API errors in response
            if (isset($response['action']['error']) && $response['action']['error'] !== null) {
                $errorMessage = $response['action']['error']['message'] ?? 'Unknown API error';
                throw new \Exception('Hetzner Storage API Error: ' . $errorMessage);
            }

            // Store the new password
            $service->properties()->updateOrCreate(
                ['key' => 'password'],
                [
                    'name' => 'Storage Box Password',
                    'value' => $newPassword
                ]
            );

            \Log::info('Storage box password reset completed successfully', [
                'storage_box_id' => $storageBoxId,
                'service_id' => $service->id,
                'action_id' => $response['action']['id'] ?? null,
                'password_set' => true
            ]);

            return [
                'success' => true,
                'message' => 'Storage box password has been reset successfully. The new password has been saved and is now visible in the details above.',
                'action_id' => $response['action']['id'] ?? null,
                'root_password' => $newPassword,
            ];

        } catch (\Exception $e) {
            \Log::error('Storage box password reset failed', [
                'storage_box_id' => $storageBoxId,
                'service_id' => $service->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Password reset failed: ' . $e->getMessage());
        }
    }

    /**
     * Create a snapshot
     */
    public function createSnapshot(Service $service): array
    {
        $storageBoxId = $service->properties()->where('key', 'storage_box_id')->first()?->value;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        try {
            // Use a default description since we can't get it from the modal
            $description = 'Manual snapshot created at ' . date('Y-m-d H:i:s');

            \Log::info('Starting storage box snapshot creation', [
                'storage_box_id' => $storageBoxId,
                'service_id' => $service->id,
                'description' => $description,
            ]);

            // First, let's try different possible endpoints for snapshots
            $endpoints = [
                "storage_boxes/$storageBoxId/snapshots",
                "storage_boxes/$storageBoxId/actions/create_snapshot", 
                "storage_boxes/$storageBoxId/backup",
                "storage_boxes/$storageBoxId/actions/backup"
            ];
            
            $response = null;
            $usedEndpoint = null;
            
            foreach ($endpoints as $endpoint) {
                try {
                    \Log::info('Trying snapshot endpoint', ['endpoint' => $endpoint]);
                    $response = $this->request($endpoint, 'POST', [
                        'description' => $description
                    ]);
                    $usedEndpoint = $endpoint;
                    break; // Success, exit loop
                } catch (\Exception $e) {
                    \Log::info('Endpoint failed', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);
                    continue; // Try next endpoint
                }
            }
            
            if (!$response) {
                throw new \Exception('No working snapshot endpoint found. Storage Box snapshots may not be available or may require a different plan.');
            }
            
            \Log::info('Snapshot creation successful with endpoint', ['endpoint' => $usedEndpoint]);

            // Handle response - could be either snapshot or action format
            $snapshotId = null;
            $actionId = null;
            
            if (isset($response['snapshot']['id'])) {
                $snapshotId = $response['snapshot']['id'];
            } elseif (isset($response['action']['id'])) {
                $actionId = $response['action']['id'];
            }

            \Log::info('Storage box snapshot creation initiated successfully', [
                'storage_box_id' => $storageBoxId,
                'service_id' => $service->id,
                'snapshot_id' => $snapshotId,
                'action_id' => $actionId,
                'description' => $description,
            ]);

            return [
                'success' => true,
                'message' => 'Snapshot creation has been initiated successfully. The snapshot "' . $description . '" will be created in the background.',
                'snapshot_id' => $snapshotId,
                'action_id' => $actionId,
                'description' => $description,
            ];

        } catch (\Exception $e) {
            \Log::error('Storage box snapshot creation failed', [
                'storage_box_id' => $storageBoxId,
                'service_id' => $service->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Snapshot creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Update access settings - Enable all common access methods
     */
    public function updateAccessSettings(Service $service): array
    {
        $storageBoxId = $service->properties()->where('key', 'storage_box_id')->first()?->value;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        try {
            \Log::info('Starting access settings update', [
                'storage_box_id' => $storageBoxId,
                'service_id' => $service->id,
            ]);

            // Get current access settings first
            $storageBoxInfo = $this->getStorageBoxInfo($storageBoxId);
            $currentSettings = $storageBoxInfo['access_settings'] ?? [];

            // Enable commonly used access methods (you can customize this logic)
            $settings = [
                'ssh_enabled' => true,  // Enable SSH by default
                'samba_enabled' => true,  // Enable Samba by default
                'webdav_enabled' => !($currentSettings['webdav_enabled'] ?? false), // Toggle WebDAV
                'zfs_enabled' => true,  // Enable ZFS features
                'reachable_externally' => !($currentSettings['reachable_externally'] ?? false), // Toggle external access
            ];

            \Log::info('Access settings to be applied', [
                'storage_box_id' => $storageBoxId,
                'current_settings' => $currentSettings,
                'new_settings' => $settings,
            ]);

            $response = $this->request("storage_boxes/$storageBoxId/actions/update_access_settings", 'POST', $settings);

            // Check for API errors in response
            if (isset($response['action']['error']) && $response['action']['error'] !== null) {
                $errorMessage = $response['action']['error']['message'] ?? 'Unknown API error';
                throw new \Exception('Hetzner Storage API Error: ' . $errorMessage);
            }

            \Log::info('Access settings update completed successfully', [
                'storage_box_id' => $storageBoxId,
                'service_id' => $service->id,
                'action_id' => $response['action']['id'] ?? null,
            ]);

            return [
                'success' => true,
                'message' => 'Access settings have been updated successfully. SSH, Samba and ZFS are now enabled. WebDAV and External Access have been toggled.',
                'action_id' => $response['action']['id'] ?? null,
                'applied_settings' => $settings,
            ];

        } catch (\Exception $e) {
            \Log::error('Access settings update failed', [
                'storage_box_id' => $storageBoxId,
                'service_id' => $service->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to update access settings: ' . $e->getMessage());
        }
    }

    /**
     * Get storage box status and information
     */
    public function getStorageBoxStatus(Service $service): array
    {
        $storageBoxId = $service->properties()->where('key', 'storage_box_id')->first()?->value;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        $storageBox = $this->getStorageBoxInfo($storageBoxId);

        return [
            'status' => $storageBox['status'] ?? 'unknown',
            'name' => $storageBox['name'] ?? 'N/A',
            'username' => $storageBox['username'] ?? 'N/A',
            'server' => $storageBox['server'] ?? 'N/A',
            'location' => $storageBox['location']['name'] ?? 'N/A',
            'storage_box_type' => $storageBox['storage_box_type']['name'] ?? 'N/A',
            'size' => $storageBox['storage_box_type']['size'] ?? 'N/A',
            'created_at' => $storageBox['created'] ?? 'N/A',
            'snapshot_limit' => $storageBox['storage_box_type']['snapshot_limit'] ?? 'N/A',
            'subaccounts_limit' => $storageBox['storage_box_type']['subaccounts_limit'] ?? 'N/A',
            'stats' => $storageBox['stats'] ?? [],
            'access_settings' => $storageBox['access_settings'] ?? [],
        ];
    }

    /**
     * Get statistics view for service display
     */
    public function getStatisticsView(Service $service, $settings, $properties, $viewName): string
    {
        try {
            $storageBoxInfo = $this->getStorageBoxInfoForView($service);

            // Add storage box actions to the view data
            $storageBoxActions = $this->getStorageBoxActions($service);
            $storageBoxInfo['storage_actions'] = $storageBoxActions;

            return view('hetznerstorage::info', $storageBoxInfo)->render();
        } catch (\Exception $e) {
            return '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }

    /**
     * Get comprehensive storage box information for view display
     */
    private function getStorageBoxInfoForView(Service $service): array
    {
        $properties = $service->properties->pluck('value', 'key')->toArray();

        $storageBoxId = $properties['storage_box_id'] ?? null;
        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        // Get storage box status information
        $storageBoxStatus = $this->getStorageBoxStatus($service);

        // Get stored password
        $password = $properties['password'] ?? 'N/A';

        // Get available storage box types for upgrade functionality
        $availableTypes = [];
        try {
            $availableTypes = $this->getStorageBoxTypes();
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch available storage box types: ' . $e->getMessage());
            // Provide fallback types if API fails
            $availableTypes = [
                'bx11' => 'BX11 - Basic (100GB)',
                'bx21' => 'BX21 - Standard (1TB)',
                'bx31' => 'BX31 - Professional (5TB)',
                'bx41' => 'BX41 - Enterprise (10TB)',
            ];
        }

        // Map the data to the format expected by the view
        return [
            'storage_box_id' => $storageBoxId,
            'username' => $storageBoxStatus['username'] ?? 'N/A',
            'server' => $storageBoxStatus['server'] ?? 'N/A',
            'password' => $password,
            'status' => $storageBoxStatus['status'] ?? 'unknown',
            'size' => $storageBoxStatus['size'] ?? 'N/A',
            'snapshot_limit' => $storageBoxStatus['snapshot_limit'] ?? 'N/A',
            'subaccounts_limit' => $storageBoxStatus['subaccounts_limit'] ?? 'N/A',
            'available_types' => $availableTypes, // Available types for upgrade
            'storage_box_type' => $storageBoxStatus['storage_box_type'] ?? 'N/A',
            'location' => $storageBoxStatus['location'] ?? 'N/A',
            'created_at' => $storageBoxStatus['created_at'] ?? 'N/A',
            'stats' => $storageBoxStatus['stats'] ?? [],
            'access_settings' => $storageBoxStatus['access_settings'] ?? [],
            'orderProduct' => $service, // Pass the service as orderProduct for compatibility
            'service' => $service, // Also pass as service for clarity
        ];
    }

    /**
     * Get storage box actions/activities from Hetzner Storage API
     * 
     * @param Service $service
     * @return array
     */
    public function getStorageBoxActions(Service $service): array
    {
        try {
            $storageBoxId = $service->properties()->where('key', 'storage_box_id')->first()?->value;
            if (!$storageBoxId) {
                return [];
            }

            // Get storage box actions from API with pagination and sorting
            $response = $this->request("storage_boxes/$storageBoxId/actions?sort=started:desc&per_page=10");

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
            \Log::warning('Failed to get storage box actions', [
                'service_id' => $service->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * List snapshots for a storage box
     */
    public function listSnapshots(Service $service): array
    {
        $storageBoxId = $service->properties()->where('key', 'storage_box_id')->first()?->value;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        $response = $this->request("storage_boxes/$storageBoxId/snapshots");

        return $response['snapshots'] ?? [];
    }

    /**
     * Delete a snapshot
     */
    public function deleteSnapshot(Service $service, int $snapshotId): array
    {
        $storageBoxId = $service->properties()->where('key', 'storage_box_id')->first()?->value;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        $response = $this->request("storage_boxes/$storageBoxId/snapshots/$snapshotId", 'DELETE');

        return [
            'success' => true,
            'message' => 'Snapshot has been deleted successfully.',
            'action_id' => $response['action']['id'] ?? null,
        ];
    }

    /**
     * List subaccounts for a storage box
     */
    public function listSubaccounts(Service $service): array
    {
        $storageBoxId = $service->properties()->where('key', 'storage_box_id')->first()?->value;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        $response = $this->request("storage_boxes/$storageBoxId/subaccounts");

        return $response['subaccounts'] ?? [];
    }

    /**
     * Create a subaccount
     */
    public function createSubaccount(Service $service): array
    {
        $storageBoxId = $service->properties()->where('key', 'storage_box_id')->first()?->value;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        $data = [
            'password' => $this->generatePassword(),
            'home_directory' => request('home_directory', 'subaccount'),
            'description' => request('description', 'Subaccount created via API'),
            'access_settings' => [
                'ssh_enabled' => $this->toBool(request('ssh_enabled', false)),
                'samba_enabled' => $this->toBool(request('samba_enabled', false)),
                'webdav_enabled' => $this->toBool(request('webdav_enabled', false)),
                'readonly' => $this->toBool(request('readonly', false)),
                'reachable_externally' => $this->toBool(request('reachable_externally', false)),
            ],
        ];

        $response = $this->request("storage_boxes/$storageBoxId/subaccounts", 'POST', $data);

        return [
            'success' => true,
            'message' => 'Subaccount has been created successfully.',
            'action_id' => $response['action']['id'] ?? null,
            'password' => $data['password'],
        ];
    }

    /**
     * List folder contents
     */
    public function listFolders(Service $service, string $path = ''): array
    {
        $storageBoxId = $service->properties()->where('key', 'storage_box_id')->first()?->value;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        $queryParams = $path ? '?path=' . urlencode($path) : '';
        $response = $this->request("storage_boxes/$storageBoxId/folders$queryParams");

        return $response['folders'] ?? [];
    }

    /**
     * Change storage box protection
     */
    public function changeProtection(Service $service): array
    {
        $storageBoxId = $service->properties()->where('key', 'storage_box_id')->first()?->value;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        $deleteProtection = $this->toBool(request('delete_protection', false));

        $response = $this->request("storage_boxes/$storageBoxId/actions/change_protection", 'POST', [
            'delete' => $deleteProtection
        ]);

        return [
            'success' => true,
            'message' => 'Protection settings have been updated successfully.',
            'action_id' => $response['action']['id'] ?? null,
        ];
    }

    /**
     * Enable/configure snapshot plan
     */
    public function configureSnapshotPlan(Service $service): array
    {
        $storageBoxId = $service->properties()->where('key', 'storage_box_id')->first()?->value;

        if (!$storageBoxId) {
            throw new \Exception('Storage Box ID not found');
        }

        $enable = $this->toBool(request('enable', true));
        
        if (!$enable) {
            // Disable snapshot plan
            $response = $this->request("storage_boxes/$storageBoxId/actions/disable_snapshot_plan", 'POST');
        } else {
            // Enable snapshot plan with configuration
            $data = [
                'max_snapshots' => (int) request('max_snapshots', 7),
                'minute' => request('minute') !== null ? (int) request('minute') : null,
                'hour' => request('hour') !== null ? (int) request('hour') : null,
                'day_of_week' => request('day_of_week') !== null ? (int) request('day_of_week') : null,
                'day_of_month' => request('day_of_month') !== null ? (int) request('day_of_month') : null,
            ];

            $response = $this->request("storage_boxes/$storageBoxId/actions/enable_snapshot_plan", 'POST', $data);
        }

        return [
            'success' => true,
            'message' => 'Snapshot plan has been ' . ($enable ? 'enabled' : 'disabled') . ' successfully.',
            'action_id' => $response['action']['id'] ?? null,
        ];
    }

    /**
     * Generate a secure random password
     */
    private function generateSecurePassword(int $length = 16): string
    {
        return $this->generatePassword();
    }

}