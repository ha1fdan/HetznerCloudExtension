<div class="flex">
    <div class="flex-1">
        Server support ID: <strong><u>{{ $server_id }}</u></strong>
        <br />
        IPv4: <strong>{{ $server_ipv4 }}</strong>
        <br />
        IPv6: <strong>{{ $server_ipv6 }}</strong>
        <br /><br />
        SSH command: <strong>ssh root&#64;{{ $server_ipv4 }}</strong>
        <br />
        <u>Temporarily</u> root Password: <code>{{$server_root_passwd}}</code>
        <br /><br />
        <p class="text-2xl font-bold">Server Configuration: </p>
        <ul>
            <li>OS: <strong>{{ $description }}</strong></li>
            <li>vCPU: <strong>{{ $cores }}</strong></li>
            <li>RAM: <strong>{{ $memory }}GB</strong></li>
            <li>SSD: <strong>{{ $disk }}GB</strong></li>
        </ul>
        <br />
        <button class="button button-success mr-1" @if ($status == "running") disabled @endif onclick="hetzner_control('poweron')">
            @if ($status == "running")
                Server is Running
            @else
                Start Server
            @endif
        </button>

        <button class="button button-secondary mr-1" onclick="hetzner_control('reboot')">
            Reboot Server
        </button>

        <button class="button button-danger mr-1" @if ($status == "off") disabled @endif onclick="hetzner_control('poweroff')">
            @if ($status == "off")
                Server is Off
            @else
                Force Stop Server
            @endif
        </button>

        <button class="button button-danger mr-1" onclick="hetzner_control('reset')">
            Reset Server
        </button>

        <button class="button button-secondary mr-1" onclick="hetzner_control('reset_password')">
            Reset root Password
        </button>

        <button class="button button-danger mr-1" onclick="hetzner_control('rebuild')">
            Rebuild OS
        </button>

        <br/>
        <br/>
        <input type="text" id="reverse_dns" class="py-2 bg-secondary-200 text-secondary-800 font-medium rounded-md placeholder-secondary-500 outline-none" style="width: 20rem;" value="{{ $reverse_dns }}" />

        <button class="button button-success mr-1" onclick="hetzner_control('change_dns_ptr')">
            Save Reverse DNS
        </button>
    </div>
</div>
<script>
    function hetzner_control(action) {
        if(action == "change_dns_ptr") {
            var new_reverse_dns = document.getElementById('reverse_dns').value;
            action=action+"__"+new_reverse_dns;
        }

        var xhr = new XMLHttpRequest(); 
        xhr.open('POST', '{{ route('extensions.hetzner.status', $orderProduct->id) }}');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.status == 'success') {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } else {
                alert('An error occurred while trying to perform this action.');
            }
        };
        xhr.onerror = function() {
            alert('An error occurred while trying to perform this action.');
        };
        xhr.send('_token={{ csrf_token() }}&status=' + action);
    }
</script>