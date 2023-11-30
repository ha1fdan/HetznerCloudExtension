<?php
namespace App\Extensions\Servers\HetznerCloud;

use App\Classes\Extensions\Server;
use App\Helpers\ExtensionHelper;
use Illuminate\Support\Facades\Http;

class HetznerCloud extends Server
{
    public function getMetadata(): array
    {
        return [
            'display_name' => 'Hetzner Cloud',
            'version' => '1.0.0',
            'author' => 'Ha1fdan',
            'website' => 'https://halfdan.top',
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
        //dd($params);
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
        ExtensionHelper::error('HetznerCloud', 'Failed to create server for order ' . $orderProduct->id . ' with error ' . $response->body());
        if (!$response->successful()) {
            return false;
        }
        ExtensionHelper::setOrderProductConfig('server_id', $response->json()["server"]["id"], $orderProduct->id);
        ExtensionHelper::setOrderProductConfig('server_ipv4', $response->json()["server"]["public_net"]["ipv4"]["ip"], $orderProduct->id);
        ExtensionHelper::setOrderProductConfig('server_ipv6', $response->json()["server"]["public_net"]["ipv6"]["ip"], $orderProduct->id);
        ExtensionHelper::setOrderProductConfig('server_root_passwd', $response->json()["root_password"], $orderProduct->id);
        return true;
        
    }

    public function suspendServer($user, $params, $order, $orderProduct, $configurableOptions): bool
    {
        return false;
    }

    public function unsuspendServer($user, $params, $order, $orderProduct, $configurableOptions): bool
    {
        return false;
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

        return [
            'name' => 'info',
            'template' => 'hetznercloud::info',
            'data' => [
                'server_id' => $server_id,
                'server_ipv4' => $server_ipv4,
                'server_ipv6' => $server_ipv6,
                'server_root_passwd' => $server_root_passwd,
            ],
            
        ];
    }
    
}