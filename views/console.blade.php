<!-- Open up the console using websocket with proxy from Hetzner -->

<div id="vnc-container"></div>

<script>
    // Initialize noVNC
    const vncContainer = document.getElementById('vnc-container');
    const rfb = new noVnc.RFB(vncContainer);
    rfb.connect('{!! $wss_url !!}');

    // Handle the 'credentialsrequired' event
    rfb.addEventListener('credentialsrequired', (event) => {
        // Provide the VNC password
        rfb.sendCredentials({ password: '{!! $wss_password !!}' });
    });
    
    // Handle the 'disconnect' event
    rfb.addEventListener('disconnect', (event) => {
        console.log('Disconnected from VNC server:', event.detail.reason);
    });
</script>

<script src="https://unpkg.com/websockify/websockify.js"></script>
<script src="https://unpkg.com/@novnc/novnc@1.4.0/lib/rfb.js"></script>