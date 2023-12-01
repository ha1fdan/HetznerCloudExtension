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
        <u>Temporarily</u> SSH Password: <code>{{$server_root_passwd}}</code>
        <br />
    </div>
    <div class="flex gap-2">
        <p>Server status: {{ $status }}</p>

        <button class="button button-success" onclick="hetzner_control('poweron')">
            Start Server
        </button>

        <button class="button button-primary" onclick="hetzner_control('reboot')">
            Reboot Server
        </button>

        <button class="button button-danger" onclick="hetzner_control('poweroff')">
            Force Stop Server
        </button>
    </div>
</div>
<script>
    function hetzner_control(action) {
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