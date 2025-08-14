<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<p class="text-2xl font-bold">Server Metrics for the last hour</p>
<canvas id="cpu" width="400" height="400" style="max-height: 400px;"></canvas>
<canvas id="disk" width="400" height="400" style="max-height: 400px;"></canvas>
<canvas id="network" width="400" height="400" style="max-height: 400px;"></canvas>
<script>
    var cpuCtx = document.getElementById('cpu').getContext('2d');
    var cpuChart = new Chart(cpuCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach ($metrics_cpu as $stat)
                    '{{ date('Y-m-d H:i:s', $stat[0]) }}',
                @endforeach
            ],
            datasets: [{
                label: 'CPU Usage %',
                data: [
                    @foreach ($metrics_cpu as $stat)
                        '{{ $stat[1] }}',
                    @endforeach
                ],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                x: {
                    display: false // Hide x-axis
                },
                y: {
                    title: {
                        display: true,
                        text: 'CPU Usage %'
                    }
                }
            }
        }
    });

    var diskCtx = document.getElementById('disk').getContext('2d');
    var diskChart = new Chart(diskCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach ($metrics_disk['disk.0.bandwidth.write']['values'] as $stat)
                    '{{ date('Y-m-d H:i:s', $stat[0]) }}',
                @endforeach
            ],
            datasets: [
                {
                    label: 'Disk iop/s Read',
                    data: [
                        @foreach ($metrics_disk['disk.0.iops.read']['values'] as $stat)
                            '{{ $stat[1] }}',
                        @endforeach
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Disk iop/s Write',
                    data: [
                        @foreach ($metrics_disk['disk.0.iops.write']['values'] as $stat)
                            '{{ $stat[1] }}',
                        @endforeach
                    ],
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Disk bytes/s Write',
                    data: [
                        @foreach ($metrics_disk['disk.0.bandwidth.write']['values'] as $stat)
                            '{{ $stat[1] }}',
                        @endforeach
                    ],
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Disk bytes/s Read',
                    data: [
                        @foreach ($metrics_disk['disk.0.bandwidth.read']['values'] as $stat)
                            '{{ $stat[1] }}',
                        @endforeach
                    ],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
            ]
        },
        options: {
            scales: {
                x: {
                    display: false // Hide x-axis
                },
                y: {
                    title: {
                        display: true,
                        text: 'Disk Metrics'
                    }
                }
            }
        }
    });

    var networkCtx = document.getElementById('network').getContext('2d');
    var networkChart = new Chart(networkCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach ($metrics_network['network.0.pps.in']['values'] as $stat)
                    '{{ date('Y-m-d H:i:s', $stat[0]) }}',
                @endforeach
            ],
            datasets: [
                {
                    label: 'Network packets/s In',
                    data: [
                        @foreach ($metrics_network['network.0.pps.in']['values'] as $stat)
                            '{{ $stat[1] }}',
                        @endforeach
                    ],
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Network packets/s Out',
                    data: [
                        @foreach ($metrics_network['network.0.pps.out']['values'] as $stat)
                            '{{ $stat[1] }}',
                        @endforeach
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Network bytes/s In',
                    data: [
                        @foreach ($metrics_network['network.0.bandwidth.in']['values'] as $stat)
                            '{{ $stat[1] }}',
                        @endforeach
                    ],
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Network bytes/s Out',
                    data: [
                        @foreach ($metrics_network['network.0.bandwidth.out']['values'] as $stat)
                            '{{ $stat[1] }}',
                        @endforeach
                    ],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
            ]
        },
        options: {
            scales: {
                x: {
                    display: false // Hide x-axis
                },
                y: {
                    title: {
                        display: true,
                        text: 'Network Metrics'
                    }
                }
            }
        }
    });
</script>
