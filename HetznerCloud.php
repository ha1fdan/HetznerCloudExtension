<?php
namespace App\Extensions\Servers\HetznerCloud;

use App\Classes\Extensions\Server;
use App\Helpers\ExtensionHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\OrderProduct;
use App\Models\Product;

class HetznerCloud extends Server
{
    public function getMetadata(): array
    {
        return [
            'display_name' => 'Hetzner Cloud',
            'version' => '2.0.1',
            'author' => 'Ha1fdan',
            'website' => 'https://ha1fdan.xyz/HetznerCloudExtension/',
        ];
    }

    private function config($key): ?string
    {
        $config = ExtensionHelper::getConfig('HetznerCloud', $key);
        if ($config) {
            return $config;
        }
        return null;
    }

    public function getConfig(): array
    {
        return [
            [
                'name' => 'apiToken',
                'friendlyName' => 'API Token',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'serverHostname',
                'friendlyName' => 'Hostname of server + creation date | Examples: vps-,server-,instance-  | Example with current settings: '.$this->config('serverHostname').date('dmYs'),
                'type' => 'text', //dropdown
                'options' => [
                    [
                        'name' => 'vps-'.date('dmYs'),
                        'value' => 'none-',
                    ],
                    [
                        'name' => 'server-'.date('dmYs'),
                        'value' => 'server-',
                    ],
                    [
                        'name' => 'instance-'.date('dmYs'),
                        'value' => 'instance-',
                    ],
                    [
                        'name' => 'node-'.date('dmYs'),
                        'value' => 'node-',
                    ],
                    [
                        'name' => 'cloud-'.date('dmYs'),
                        'value' => 'cloud-',
                    ],
                ],
                'required' => true,
            ],
        ];
    }


    private function postRequest($url, $data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config('apiToken'),
            'Content-Type' => 'application/json',
        ])->post($url, $data);
    }

    private function getRequest($url): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config('apiToken'),
            'Content-Type' => 'application/json',
        ])->get($url);
    }

    public function deleteRequest($url): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config('apiToken'),
            'Content-Type' => 'application/json',
        ])->delete($url);
    }

    public function getProductConfig($options): array
    {
        $locations =  $this->getRequest("https://api.hetzner.cloud/v1/locations");
        $locationsList = [
            [
                'name' => 'None',
                'value' => '',
            ],
        ];
        foreach ($locations->json()["locations"] as $location) {
            $locationsList[] = [
                'name' => $location['name'],
                'value' => $location['name'],
            ];
        }

        $images =  $this->getRequest("https://api.hetzner.cloud/v1/images");
        $imagesList = [
            [
                'name' => 'None',
                'value' => '',
            ],
        ];
        foreach ($images->json()["images"] as $image) {
            $imagesList[] = [
                'name' => $image['name'],
                'value' => $image['name'],
            ];
        }

        $serverTypes =  $this->getRequest("https://api.hetzner.cloud/v1/server_types");
        $serverTypesList = [
            [
                'name' => 'None',
                'value' => '',
            ],
        ];
        foreach ($serverTypes->json()["server_types"] as $serverType) {
            $serverTypesList[] = [
                'name' => $serverType['name'],
                'value' => $serverType['id'],
            ];
        }

        return [
            [
                'name' => 'location',
                'friendlyName' => 'Server Location',
                'type' => 'dropdown',
                'options' => $locationsList,
                'required' => true,
            ],
            [
                'name' => 'image',
                'friendlyName' => 'System Image',
                'type' => 'dropdown',
                'options' => $imagesList,
                'required' => true,
            ],
            [
                'name' => 'server_type',
                'friendlyName' => 'Server Type',
                'type' => 'dropdown',
                'options' => $serverTypesList,
                'required' => true,
            ],
        ];
    }

    public function createServer($user, $params, $order, $orderProduct, $configurableOptions): bool
    {
        $url = "https://api.hetzner.cloud/v1/servers";
        $location = $configurableOptions['location'] ?? $params['location'];
        $image = $configurableOptions['image'] ?? $params['image'];
        $server_type = $params['server_type'];
        //$servername = "vps-".date('dmYs');
        $servername = $this->config('serverHostname').date('dmYs');

        $json = [
            'automount' => false,
            'image' => $image,
            'location' => $location,
            'name' => $servername,
            'public_net' => [
                'enable_ipv4' => true,
                'enable_ipv6' => true,
            ],
            'server_type' => $server_type,
            'start_after_create' => true,
        ];
        $response = $this->postRequest($url, $json);

        if (!$response->successful()) {
            ExtensionHelper::error('HetznerCloud', 'Failed to create server for order ' . $orderProduct->id . ' with error ' . $response->body());
            return false;
        }
        ExtensionHelper::setOrderProductConfig('server_id', $response->json()["server"]["id"], $orderProduct->id);
        ExtensionHelper::setOrderProductConfig('server_ipv4', $response->json()["server"]["public_net"]["ipv4"]["ip"], $orderProduct->id);
        ExtensionHelper::setOrderProductConfig('server_ipv6', $response->json()["server"]["public_net"]["ipv6"]["ip"], $orderProduct->id);
        ExtensionHelper::setOrderProductConfig('server_root_passwd', $response->json()["root_password"], $orderProduct->id);
        ExtensionHelper::setOrderProductConfig('server_image', $image, $orderProduct->id);
        return true;

    }

    public function suspendServer($user, $params, $order, $orderProduct, $configurableOptions): bool
    {
        throw new Exception('Not implemented');
    }

    public function unsuspendServer($user, $params, $order, $orderProduct, $configurableOptions): bool
    {
        throw new Exception('Not implemented');
    }

    public function terminateServer($user, $params, $order, $orderProduct, $configurableOptions): bool
    {
        if (!isset($params['config']['server_id'])) {
            return false;
        }
        $server_id = $params['config']['server_id'];
        $server = $this->serverExists($server_id);
        if ($server) {
            $url = "https://api.hetzner.cloud/v1/servers/" . $server_id;
            $this->deleteRequest($url);
            return true;
        }
        return false;
    }

    private function serverExists($server_id)
    {
        $url = "https://api.hetzner.cloud/v1/servers/" . $server_id;
        $response = $this->getRequest($url);
        $code = $response->status();
        if ($code == 200) {
            return $response->json()["server"]['id'];
        }
        return false;
    }


    public function getCustomPages($user, $params, $order, $product, $configurableOptions)
    {
        if(!isset($params['config']['server_id'])) {
            return false;
        }
        $server_id = $params['config']['server_id'];
        $server_ipv4 = $params['config']['server_ipv4'];
        $server_ipv6 = $params['config']['server_ipv6'];
        $server_root_passwd = $params['config']['server_root_passwd'];

        $status_request = $this->getRequest('https://api.hetzner.cloud/v1/servers/'.$server_id);
        if (!$status_request->json()) throw new Exception('Unable to get server status');
        $status = $status_request->json()['server']['status'];
        $description = $status_request->json()['server']['image']['description'];
        $cores = $status_request->json()['server']['server_type']['cores'];
        $memory = $status_request->json()['server']['server_type']['memory'];
        $disk = $status_request->json()['server']['server_type']['disk'];
        $reverse_dns = $status_request->json()['server']['public_net']['ipv4']['dns_ptr'];

        // Server Metrics
        $ctime = time(); // current time
        $start_time = strtotime('-1 hour', $ctime);
        // Format the times in ISO-8601 format
        $start = urlencode(date('c', $start_time));
        $end = urlencode(date('c', $ctime));

        $metrics_cpu_request = $this->getRequest('https://api.hetzner.cloud/v1/servers/'.$server_id.'/metrics?type=cpu&start='.$start.'&end='.$end);
        if (!$metrics_cpu_request->json()) throw new Exception('Unable to get server metrics for CPU');
        $metrics_cpu = $metrics_cpu_request->json()['metrics']['time_series']['cpu']['values'];
        
        $metrics_disk_request = $this->getRequest('https://api.hetzner.cloud/v1/servers/'.$server_id.'/metrics?type=disk&start='.$start.'&end='.$end);
        if (!$metrics_disk_request->json()) throw new Exception('Unable to get server metrics for DISK');
        $metrics_disk = $metrics_disk_request->json()['metrics']['time_series'];
        
        $metrics_network_request = $this->getRequest('https://api.hetzner.cloud/v1/servers/'.$server_id.'/metrics?type=network&start='.$start.'&end='.$end);
        if (!$metrics_network_request->json()) throw new Exception('Unable to get server metrics for NETWORK');
        $metrics_network = $metrics_network_request->json()['metrics']['time_series'];

        $firewalls = $this->getFirewalls();

        return [
            'name' => 'info',
            'template' => 'hetznercloud::info',
            'data' => [
                'server_id' => $server_id,
                'server_ipv4' => $server_ipv4,
                'server_ipv6' => $server_ipv6,
                'server_root_passwd' => $server_root_passwd,
                'status' => $status,
                'description' => $description,
                'cores' => $cores,
                'memory' => $memory,
                'disk' => $disk,
                'reverse_dns' => $reverse_dns,
                'metrics_cpu' => $metrics_cpu,
                'metrics_disk' => $metrics_disk,
                'metrics_network' => $metrics_network,
                'orderProduct' => $product,
                'firewalls' => $firewalls
            ],
            'pages' => [
                [
                    'template' => 'hetznercloud::metrics',
                    'name' => 'Server Metrics',
                    'url' => 'metrics',
                ],
                [
                    'template' => 'hetznercloud::firewall',
                    'name' => 'Firewall',
                    'url' => 'firewall',
                ]
            ]
        ];
    }

    public function status(Request $request, OrderProduct $product)
    {
        $data = ExtensionHelper::getParameters($product);
        $params = $data->config;
        $server_id = $params['config']['server_id'];
        $server_ipv4 = $params['config']['server_ipv4'];
        $server_image = $params['config']['server_image'];
        $request_action = $request->status;
        // Change status
        $postData = [
            'id' => $server_id,
        ];

        if(str_starts_with($request->status, "change_dns_ptr")) {
            $request_action = "change_dns_ptr";
            $new_dns_ptr = explode("__",$request->status)[1];
            $postData['dns_ptr'] = $new_dns_ptr;
            $postData['ip'] = $server_ipv4; //$server_ipv6 also works :)
        }

        if($request->status == "rebuild") {
            $postData['image'] = $server_image;
        }

        $status = $this->postRequest('https://api.hetzner.cloud/v1/servers/'.$server_id.'/actions/'.$request_action, $postData);
        //dd($status->json());
        if ($status->json()['action']['error'] != null) throw new Exception('Unable to ' . $request_action . ' server');
        //Check for a new root password with command reset_password
        if (isset($status->json()['root_password'])) {
            ExtensionHelper::setOrderProductConfig('server_root_passwd', $status->json()["root_password"], $product->id);
        }

        // Return json response
        return response()->json([
            'status' => 'success',
            'message' => 'Server status is ' . $request_action,
        ]);
    }

    private function getFirewalls()
    {
        $url = "https://api.hetzner.cloud/v1/firewalls";
        $response = $this->getRequest($url);
        $firewalls = $response->json()['firewalls'] ?? [];
        
        // Fetch detailed information for each firewall
        foreach ($firewalls as &$firewall) {
            $detailedFirewall = $this->getFirewall($firewall['id']);
            if ($detailedFirewall) {
                $firewall['applied_to'] = $detailedFirewall['applied_to'] ?? [];
            }
        }
        
        return $firewalls;
    }

    private function getFirewall($id)
    {
        $url = "https://api.hetzner.cloud/v1/firewalls/" . $id;
        $response = $this->getRequest($url);
        return $response->json()['firewall'] ?? null;
    }

    private function createFirewall($name, $rules)
    {
        $url = "https://api.hetzner.cloud/v1/firewalls";
        $data = [
            'name' => $name,
            'rules' => json_decode($rules, true)
        ];
        return $this->postRequest($url, $data);
    }

    private function updateFirewall($id, $rules)
    {
        $url = "https://api.hetzner.cloud/v1/firewalls/" . $id;
        $data = [
            'rules' => json_decode($rules, true)
        ];
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config('apiToken'),
            'Content-Type' => 'application/json',
        ])->put($url, $data);
    }

    private function deleteFirewall($id)
    {
        $url = "https://api.hetzner.cloud/v1/firewalls/" . $id;
        return $this->deleteRequest($url);
    }

    public function firewallPage($user, $params, $order, $product, $configurableOptions)
    {
        $server_id = $params['config']['server_id'];
        $firewalls = $this->getFirewalls();
        
        return view('Extensions.Servers.HetznerCloud.views.firewall', [
            'server_id' => $server_id,
            'firewalls' => $firewalls,
            'product' => $product
        ]);
    }

    private function applyFirewall($firewall_id, $server_id)
    {
        $url = "https://api.hetzner.cloud/v1/firewalls/" . $firewall_id . "/actions/apply_to_resources";
        $data = [
            'apply_to' => [
                [
                    'type' => 'server',
                    'server' => [
                        'id' => $server_id
                    ]
                ]
            ]
        ];
        return $this->postRequest($url, $data);
    }

    private function removeFirewall($firewall_id, $server_id)
    {
        $url = "https://api.hetzner.cloud/v1/firewalls/" . $firewall_id . "/actions/remove_from_resources";
        $data = [
            'remove_from' => [
                [
                    'type' => 'server',
                    'server' => [
                        'id' => $server_id
                    ]
                ]
            ]
        ];
        return $this->postRequest($url, $data);
    }

    public function handleFirewallAction(Request $request, OrderProduct $product)
    {
        $action = $request->input('action');
        $firewall_id = $request->input('firewall_id');
        
        switch ($action) {
            case 'create':
                $name = $request->input('name');
                $rules = $request->input('rules');
                
                // Validate rules format
                $decodedRules = json_decode($rules, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['error' => 'Invalid rules format'], 400);
                }
                
                $response = $this->createFirewall($name, $rules);
                break;
            
            case 'update':
                $rules = $request->input('rules');
                
                // Validate rules format
                $decodedRules = json_decode($rules, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['error' => 'Invalid rules format'], 400);
                }
                
                $response = $this->updateFirewall($firewall_id, $rules);
                break;
            
            case 'delete':
                $response = $this->deleteFirewall($firewall_id);
                break;

            case 'apply':
                $server_id = $request->input('server_id');
                if (!$server_id) {
                    return response()->json(['error' => 'Server ID is required'], 400);
                }
                $response = $this->applyFirewall($firewall_id, $server_id);
                break;

            case 'remove':
                $server_id = $request->input('server_id');
                if (!$server_id) {
                    return response()->json(['error' => 'Server ID is required'], 400);
                }
                $response = $this->removeFirewall($firewall_id, $server_id);
                break;
            
            default:
                return response()->json(['error' => 'Invalid action'], 400);
        }

        if ($response->successful()) {
            return response()->json(['success' => true]);
        }
        
        return response()->json(['error' => $response->body()], $response->status());
    }

    public function metricsPage($user, $params, $order, $product, $configurableOptions)
    {
        $server_id = $params['config']['server_id'];
        
        // Fetch metrics for the last hour (you can adjust the time range as needed)
        $end = time();
        $start = $end - 3600; // Last hour
        
        // Fetch CPU metrics
        $metrics_cpu = $this->getMetrics($server_id, 'cpu', $start, $end);
        
        // Fetch memory metrics
        $metrics_memory = $this->getMetrics($server_id, 'memory', $start, $end);
        
        // Fetch disk metrics
        $metrics_disk = $this->getMetrics($server_id, 'disk', $start, $end);
        
        // Fetch network metrics
        $metrics_network = $this->getMetrics($server_id, 'network', $start, $end);
        
        return view('Extensions.Servers.HetznerCloud.views.metrics', [
            'server_id' => $server_id,
            'metrics_cpu' => $metrics_cpu,
            'metrics_memory' => $metrics_memory,
            'metrics_disk' => $metrics_disk,
            'metrics_network' => $metrics_network,
            'product' => $product
        ]);
    }

    private function getMetrics($server_id, $type, $start, $end)
    {
        $url = "https://api.hetzner.cloud/v1/servers/" . $server_id . "/metrics";
        
        $response = $this->getRequest($url . "?" . http_build_query([
            'type' => $type,
            'start' => date('c', $start),
            'end' => date('c', $end),
            'step' => '60' // 1-minute intervals
        ]));

        if ($response->successful()) {
            return $response->json()['metrics'] ?? null;
        }
        
        return null;
    }

    public function revdns(Request $request, OrderProduct $product)
    {
        $request->validate([
            'reverse_dns' => ['required', 'string', 'max:255'],
        ]);
        $data = ExtensionHelper::getParameters($product);
        $params = $data->config;
        $server_id = $params['config']['server_id'];
        $server_ipv4 = $params['config']['server_ipv4'];
        $form_data_dns = $request->status;

        $postData = [
            'id' => $server_id,
            'ip' => $server_ipv4,
            'dns_ptr' => $request->reverse_dns,
        ];

        $set_dns = $this->postRequest('https://api.hetzner.cloud/v1/servers/'.$server_id.'/actions/change_dns_ptr', $postData);
        if ($set_dns->json()['action']['error'] != null) throw new Exception('Unable to change reverse dns for server');
        return redirect()->back()->with('success', 'Reverse dns entry has been updated successfully');
    }
    

}
