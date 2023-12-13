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
            'version' => '2.0.0',
            'author' => 'Ha1fdan',
            'website' => 'https://ha1fdan.xyz',
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
        $servername = "vps-".date('dmYs');

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
            ],
        ];
    }

    public function status(Request $request, OrderProduct $product)
    {
        $data = ExtensionHelper::getParameters($product);
        $params = $data->config;
        $server_id = $params['config']['server_id'];
        $server_image = $params['config']['server_image'];
        // Change status
        $postData = [
            'id' => $server_id,
        ];

        if($request->status == "rebuild") {
            $postData['image'] = $server_image;
        }

        $status = $this->postRequest('https://api.hetzner.cloud/v1/servers/'.$server_id.'/actions/'.$request->status, $postData);
        //dd($status->json());
        if ($status->json()['action']['error'] != null) throw new Exception('Unable to ' . $request->status . ' server');
        //Check for a new root password with command reset_password
        if (isset($status->json()['root_password'])) {
            ExtensionHelper::setOrderProductConfig('server_root_passwd', $status->json()["root_password"], $product->id);
        }

        // Return json response
        return response()->json([
            'status' => 'success',
            'message' => 'Server status is ' . $request->status,
        ]);
    }
    
}