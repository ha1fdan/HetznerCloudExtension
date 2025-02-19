<div class="space-y-6">
    <!-- Firewall Management -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt text-gray-400 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Firewall Management</h3>
                        <p class="text-sm text-gray-500">Server ID: <span class="font-medium">{{ $server_id }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Create New Firewall -->
            <div class="space-y-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-plus-circle text-gray-400"></i>
                    <h4 class="text-sm font-medium text-gray-700">Create New Firewall</h4>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <form id="createFirewallForm" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="firewallName" class="block text-sm font-medium text-gray-700">Firewall Name</label>
                                <input type="text" id="firewallName" name="name" required
                                       class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <span>Firewall Rules</span>
                                    <span class="ml-2 text-xs text-gray-500">Configure inbound and outbound traffic rules</span>
                                </label>
                                <div id="rulesContainer" class="space-y-4">
                                    <div class="rule-entry bg-white rounded-lg border border-gray-200 overflow-hidden">
                                        <!-- Rule Header -->
                                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-sm font-medium text-gray-700">Rule Configuration</h4>
                                                <button type="button" onclick="this.closest('.rule-entry').remove()"
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 hover:text-red-900">
                                                    <i class="fas fa-trash-alt mr-1"></i>
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Rule Content -->
                                        <div class="p-4 space-y-4">
                                            <!-- Direction & Protocol -->
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Direction</label>
                                                    <div class="relative">
                                                        <select name="direction" onchange="updateIpLabel(this)" class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                            <option value="in">Inbound (IN)</option>
                                                            <option value="out">Outbound (OUT)</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                            <i class="fas fa-exchange-alt text-gray-400"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Protocol</label>
                                                    <div class="relative">
                                                        <select name="protocol" class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                            <option value="tcp">TCP</option>
                                                            <option value="udp">UDP</option>
                                                            <option value="icmp">ICMP</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                            <i class="fas fa-network-wired text-gray-400"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Port & IPs -->
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="port-input">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                                                    <div class="relative">
                                                        <input type="text" name="port" placeholder="80 or 80-443"
                                                               class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                            <i class="fas fa-plug text-gray-400"></i>
                                                        </div>
                                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                                            <span class="text-xs text-gray-400">Leave empty for all ports</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1 ip-label">Source IPs</label>
                                                    <div class="relative">
                                                        <input type="text" name="source_ips" placeholder="0.0.0.0/0"
                                                               class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                            <i class="fas fa-globe text-gray-400"></i>
                                                        </div>
                                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                                            <span class="text-xs text-gray-400">Comma-separated</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Help Text -->
                                            <div class="mt-2 text-xs text-gray-500 bg-gray-50 rounded p-2">
                                                <ul class="list-disc list-inside space-y-1">
                                                    <li>For TCP/UDP, specify ports like "80" or ranges like "80-443"</li>
                                                    <li>Source IPs: Use CIDR notation (e.g., "10.0.0.0/24, 192.168.1.0/24")</li>
                                                    <li>Leave port empty for ICMP protocol</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Add Rule Button -->
                                <div class="mt-4">
                                    <button type="button" onclick="addRule()"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-plus-circle mr-2"></i>
                                        Add Another Rule
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="pt-3 border-t border-gray-200">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i>
                                Create Firewall
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing Firewalls -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-list text-gray-400"></i>
                        <h4 class="text-sm font-medium text-gray-700">Existing Firewalls</h4>
                    </div>
                    <span class="text-sm text-gray-500">{{ count($firewalls) }} firewalls</span>
                </div>
                <div class="bg-white shadow overflow-hidden border border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rules</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($firewalls as $firewall)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <div class="flex items-center space-x-2">
                                    <span>{{ $firewall['name'] }}</span>
                                    @php
                                        $isApplied = false;
                                        foreach ($firewall['applied_to'] ?? [] as $application) {
                                            if ($application['type'] === 'server' && 
                                                isset($application['server']['id']) && 
                                                (string)$application['server']['id'] === (string)$server_id) {
                                                $isApplied = true;
                                                break;
                                            }
                                        }
                                    @endphp
                                    @if($isApplied)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Applied
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <button onclick="viewRules({{ json_encode($firewall['rules']) }})"
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-eye mr-1.5"></i>
                                    View Rules ({{ count($firewall['rules']) }})
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex space-x-2">
                                    @if(!$isApplied)
                                    <button onclick="applyFirewall('{{ $firewall['id'] }}')"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <i class="fas fa-check mr-1.5"></i>
                                        Apply
                                    </button>
                                    @else
                                    <button onclick="removeFirewall('{{ $firewall['id'] }}')"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                        <i class="fas fa-times mr-1.5"></i>
                                        Remove
                                    </button>
                                    @endif
                                    <button onclick="deleteFirewall('{{ $firewall['id'] }}')"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <i class="fas fa-trash-alt mr-1.5"></i>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No firewalls found. Create one above.
                            </td>
                        </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rules Modal -->
<div id="rulesModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="rulesModalLabel" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

        <!-- Modal panel -->
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-shield-alt text-gray-400 text-lg"></i>
                        <div>
                            <h5 class="text-lg font-semibold text-gray-900" id="rulesModalLabel">Firewall Rules</h5>
                            <p class="text-sm text-gray-500">Configured traffic rules for this firewall</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeRulesModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div id="rulesDisplay"></div>
            </div>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <button type="button" onclick="closeRulesModal()"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function addRule() {
        const ruleTemplate = `
            <div class="rule-entry bg-white rounded-lg border border-gray-200 overflow-hidden mt-4">
                <!-- Rule Header -->
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-medium text-gray-700">Rule Configuration</h4>
                        <button type="button" onclick="this.closest('.rule-entry').remove()"
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 hover:text-red-900">
                            <i class="fas fa-trash-alt mr-1"></i>
                            Remove
                        </button>
                    </div>
                </div>
                
                <!-- Rule Content -->
                <div class="p-4 space-y-4">
                    <!-- Direction & Protocol -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Direction</label>
                            <div class="relative">
                                <select name="direction" onchange="updateIpLabel(this)" class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="in">Inbound (IN)</option>
                                    <option value="out">Outbound (OUT)</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-exchange-alt text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Protocol</label>
                            <div class="relative">
                                <select name="protocol" class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="tcp">TCP</option>
                                    <option value="udp">UDP</option>
                                    <option value="icmp">ICMP</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-network-wired text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Port & IPs -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="port-input">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                            <div class="relative">
                                <input type="text" name="port" placeholder="80 or 80-443"
                                       class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-plug text-gray-400"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <span class="text-xs text-gray-400">Leave empty for all ports</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 ip-label">Source IPs</label>
                            <div class="relative">
                                <input type="text" name="source_ips" placeholder="0.0.0.0/0"
                                       class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-globe text-gray-400"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <span class="text-xs text-gray-400">Comma-separated</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help Text -->
                    <div class="mt-2 text-xs text-gray-500 bg-gray-50 rounded p-2">
                        <ul class="list-disc list-inside space-y-1">
                            <li>For TCP/UDP, specify ports like "80" or ranges like "80-443"</li>
                            <li>Source IPs: Use CIDR notation (e.g., "10.0.0.0/24, 192.168.1.0/24")</li>
                            <li>Leave port empty for ICMP protocol</li>
                        </ul>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('rulesContainer').insertAdjacentHTML('beforeend', ruleTemplate);
    }

    function collectRules() {
        const rules = [];
        document.querySelectorAll('.rule-entry').forEach(entry => {
            const direction = entry.querySelector('[name="direction"]').value;
            const ips = entry.querySelector('[name="source_ips"]').value
                .split(',')
                .map(ip => ip.trim())
                .filter(ip => ip);

            // Ensure we always have at least one IP
            if (ips.length === 0) {
                ips.push('0.0.0.0/0');
            }

            const port = entry.querySelector('[name="port"]').value.trim();
            const protocol = entry.querySelector('[name="protocol"]').value;
            
            const rule = {
                description: null,
                direction: direction,
                protocol: protocol
            };

            // Handle IPs based on direction
            if (direction === 'in') {
                rule.source_ips = ips;
            } else {
                rule.destination_ips = ips;
            }

            // Handle port for TCP/UDP
            if (protocol !== 'icmp') {
                if (port) {
                    if (port.includes('-')) {
                        const [start, end] = port.split('-');
                        rule.port = start.trim() + "-" + end.trim();
                    } else {
                        rule.port = port;
                    }
                } else {
                    // If no port specified for TCP/UDP, use "1-65535"
                    rule.port = "1-65535";
                }
            }

            rules.push(rule);
        });
        return rules;
    }

    document.getElementById('createFirewallForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('firewallName').value;
        const rules = collectRules();

        if (rules.length === 0) {
            alert('Please add at least one rule to the firewall.');
            return;
        }

        try {
            const formData = new URLSearchParams();
            formData.append('action', 'create');
            formData.append('name', name);
            formData.append('rules', JSON.stringify(rules));

            console.log('Sending rules:', JSON.stringify(rules, null, 2)); // Pretty print debug log

            const response = await fetch('{{ route("extensions.hetzner.firewall.action", ["product" => $orderProduct->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            });

            let data;
            const text = await response.text();
            console.log('Raw server response:', text); // Debug log

            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse response:', text);
                alert('Error: Invalid response from server');
                return;
            }

            if (data.success) {
                window.location.reload();
            } else {
                // Try to parse the error message if it's a JSON string
                let errorMessage = data.error;
                try {
                    const errorData = JSON.parse(data.error);
                    if (errorData.error && errorData.error.message) {
                        errorMessage = errorData.error.message;
                    }
                } catch (e) {
                    // If we can't parse the error, use it as is
                }
                alert('Error: ' + errorMessage);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        }
    });

    async function deleteFirewall(id) {
        if (!confirm('Are you sure you want to delete this firewall?')) return;

        try {
            const response = await fetch('{{ route("extensions.hetzner.firewall.action", ["product" => $orderProduct->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: new URLSearchParams({
                    'action': 'delete',
                    'firewall_id': id
                })
            });

            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete firewall'));
                }
            } catch (e) {
                console.error('Response:', text);
                alert('Error: Invalid response from server');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }

    function viewRules(rules) {
        let html = `
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">${rules.length} rule${rules.length !== 1 ? 's' : ''} configured</span>
            </div>
            <div class="bg-white shadow overflow-hidden border border-gray-200 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Direction</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Protocol</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Port Range</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Addresses</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
        `;

        if (rules.length === 0) {
            html += `
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                        No rules configured for this firewall
                    </td>
                </tr>
            `;
        } else {
            rules.forEach(rule => {
                const directionIcon = rule.direction === 'in' ? 'fa-arrow-right' : 'fa-arrow-left';
                const directionColor = rule.direction === 'in' ? 'text-green-600' : 'text-blue-600';
                const directionText = rule.direction === 'in' ? 'Inbound' : 'Outbound';
                
                const protocolIcon = {
                    'tcp': 'fa-exchange-alt',
                    'udp': 'fa-random',
                    'icmp': 'fa-broadcast-tower'
                }[rule.protocol] || 'fa-network-wired';

                html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 mr-2">
                                    <i class="fas ${directionIcon} ${directionColor}"></i>
                                </div>
                                <div class="text-sm font-medium text-gray-900">${directionText}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 mr-2">
                                    <i class="fas ${protocolIcon} text-gray-500"></i>
                                </div>
                                <div class="text-sm text-gray-900">${rule.protocol.toUpperCase()}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm">
                                ${rule.protocol === 'icmp' 
                                    ? '<span class="text-gray-500">N/A</span>' 
                                    : `<span class="font-medium">${rule.port || '1-65535'}</span>`
                                }
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                ${rule.direction === 'in' 
                                    ? formatIPs(rule.source_ips)
                                    : formatIPs(rule.destination_ips)
                                }
                            </div>
                        </td>
                    </tr>
                `;
            });
        }

        html += `
                    </tbody>
                </table>
            </div>
            <div class="mt-4 bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h6 class="text-sm font-medium text-gray-700 mb-2">Rule Information</h6>
                <div class="text-xs text-gray-500 space-y-1">
                    <p><i class="fas fa-arrow-right text-green-600 mr-1"></i> Inbound rules control incoming traffic to your server</p>
                    <p><i class="fas fa-arrow-left text-blue-600 mr-1"></i> Outbound rules control outgoing traffic from your server</p>
                    <p><i class="fas fa-info-circle text-gray-400 mr-1"></i> Port ranges can be single ports (80) or ranges (80-443)</p>
                    <p><i class="fas fa-shield-alt text-gray-400 mr-1"></i> IP addresses use CIDR notation (e.g., 0.0.0.0/0 for any IP)</p>
                </div>
            </div>
        </div>
        `;

        document.getElementById('rulesDisplay').innerHTML = html;
        document.getElementById('rulesModal').classList.remove('hidden');
        // Prevent body scrolling when modal is open
        document.body.style.overflow = 'hidden';
    }

    function closeRulesModal() {
        document.getElementById('rulesModal').classList.add('hidden');
        // Restore body scrolling
        document.body.style.overflow = '';
    }

    // Close modal when clicking outside
    document.getElementById('rulesModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRulesModal();
        }
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !document.getElementById('rulesModal').classList.contains('hidden')) {
            closeRulesModal();
        }
    });

    function formatIPs(ips) {
        if (!ips || ips.length === 0) return '<span class="text-gray-500">Any IP (0.0.0.0/0)</span>';
        
        return ips.map(ip => `
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-1 mb-1">
                <i class="fas fa-globe-americas mr-1 text-gray-400"></i>
                ${ip}
            </span>
        `).join('');
    }

    async function applyFirewall(id) {
        if (!confirm('Are you sure you want to apply this firewall to the current server?')) return;

        try {
            const response = await fetch('{{ route("extensions.hetzner.firewall.action", ["product" => $orderProduct->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: new URLSearchParams({
                    'action': 'apply',
                    'firewall_id': id,
                    'server_id': '{{ $server_id }}'
                })
            });

            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    alert('Firewall successfully applied to server');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to apply firewall'));
                }
            } catch (e) {
                console.error('Response:', text);
                alert('Error: Invalid response from server');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }

    async function removeFirewall(id) {
        if (!confirm('Are you sure you want to remove this firewall from the current server?')) return;

        try {
            const response = await fetch('{{ route("extensions.hetzner.firewall.action", ["product" => $orderProduct->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: new URLSearchParams({
                    'action': 'remove',
                    'firewall_id': id,
                    'server_id': '{{ $server_id }}'
                })
            });

            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    alert('Firewall successfully removed from server');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to remove firewall'));
                }
            } catch (e) {
                console.error('Response:', text);
                alert('Error: Invalid response from server');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }

    function updateIpLabel(select) {
        const ruleEntry = select.closest('.rule-entry');
        const ipLabel = ruleEntry.querySelector('.ip-label');
        ipLabel.textContent = select.value === 'in' ? 'Source IPs' : 'Destination IPs';
    }
</script> 
