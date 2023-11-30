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
</div>