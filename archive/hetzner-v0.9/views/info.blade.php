<div class="flex justify-between">
    <h1 class="flex-1 text-3xl">
        Server: #{{ $server_id }}
    </h1>
    @switch($status)
        @case("running")
            <span class="flex-1 text-xl my-auto text-right text-green-500">
                Running
            </span>
            @break
        @case("initializing")
            <span class="flex-1 text-xl my-auto text-right text-indigo-500">
                Initializing
            </span>
            @break
        @case("starting")
            <span class="flex-1 text-xl my-auto text-right text-orange-500">
                Starting
            </span>
            @break
        @case("stopping")
            <span class="flex-1 text-xl my-auto text-right text-orange-500">
                Stopping
            </span>
            @break
        @case("off")
            <span class="flex-1 text-xl my-auto text-right text-red-500">
                Stopped
            </span>
            @break
        @case("rebuilding")
            <span class="flex-1 text-xl my-auto text-right text-orange-500">
                Rebuilding
            </span>
            @break
        @default
            <span class="flex-1 text-xl my-auto text-right text-gray-500">
                {{ ucfirst($status) }}
            </span>
    @endswitch
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-4">
    <div>
        <h2 class="text-xl font-bold mt-4 mb-2 dark:text-darkmodetext">Information</h2>
        <div class="flex flex-col gap-2">
            <div class="flex justify-between">
                <label>ID:</label>
                <label><strong>{{ $server_id }}</strong></label>
            </div>
            <div class="flex justify-between">
                <label>IPv4 Address:</label>
                <label><strong>{{ $server_ipv4 }}</strong></label>
            </div>
            <div class="flex justify-between">
                <label>IPv6 Address:</label>
                <label><strong>{{ $server_ipv6 }}</strong></label>
            </div>
            <div class="flex justify-between password-paragraph" onmouseenter="showPassword(this)" onmouseleave="dontShowPassword(this)">
                <label><u>Temporarily</u> root Password:</label>
                <label class="hidden-text"><i>Hover to show password</i></label>
            </div>
            <script>
                function showPassword(element) {
                var hiddenText = element.querySelector('.hidden-text');
                hiddenText.innerHTML = '<strong><code>{{$server_root_passwd}}</code></strong>';
                }
                function dontShowPassword(element) {
                var hiddenText = element.querySelector('.hidden-text');
                hiddenText.innerHTML = '<i>Hover to show password</i>';
                }
            </script>
        </div>
    </div>

    <div>
        <h2 class="text-xl font-bold mt-4 mb-2 dark:text-darkmodetext">Power Control</h2>
        <p>Your server is currently 
        @switch($status)
        @case("running")
            <span class="text-green-500">
                Running
            </span>
            @break
        @case("initializing")
            <span class="text-indigo-500">
                Initializing
            </span>
            @break
        @case("starting")
            <span class="text-orange-500">
                Starting
            </span>
            @break
        @case("stopping")
            <span class="text-orange-500">
                Stopping
            </span>
            @break
        @case("off")
            <span class="text-red-500">
                Stopped
            </span>
            @break
        @case("rebuilding")
            <span class="text-orange-500">
                Rebuilding
            </span>
            @break
        @default
            <span class="text-gray-500">
                {{ ucfirst($status) }}
            </span>
    @endswitch
        </p>
        
        <div class="flex gap-2 mt-2">
            <button class="button button-success" @if ($status == "running") disabled @endif onclick="hetzner_control('poweron')">
                @if ($status == "running")
                    Server is Running
                @else
                    Start Server
                @endif
            </button>

            <button class="button bg-yellow-500 hover:bg-yellow-700 text-white" onclick="hetzner_control('reboot')">
                Reboot Server
            </button>

            <button class="button button-danger text-white" @if ($status == "off") disabled @endif onclick="hetzner_control('poweroff')">
                @if ($status == "off")
                    Server is Offline
                @else
                    Force Stop Server
                @endif
            </button>

            <button class="button button-secondary text-white" onclick="hetzner_control('reset_password')">
                Reset root Password
            </button>
        </div>
        <div class="flex gap-2 mt-2">
            <button class="button button-danger" onclick="hetzner_control('rebuild')">
                Rebuild OS
            </button>
            
            <form action="{{ route('extensions.hetzner.revdns', $orderProduct->id) }}" method="POST">
                <input type="text" name="reverse_dns" class="bg-secondary-200 text-secondary-800 font-medium rounded-md placeholder-secondary-500 outline-none" style="width: 20rem;" value="{{ $reverse_dns }}" required />
                <button class="button button-success" type="submit">
                    Save Reverse DNS
                </button>
            </form>
        </div>
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