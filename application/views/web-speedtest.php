<?php
    defined('BASEPATH') or exit('No direct script access allowed');
?>

<div id="page-wrapper" class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12 m-b-md">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    User Experience
                </div>
                <div class="ibox-content">                    
                    <a class="btn btn-danger" id="st-stop" onclick="stopTest()">Stop</a>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3 st-block" id="download">
                            <h3>Download</h3>
                            <p class="display-4 st-value"><span id="speed-value"></span></p>
                            <p id="speed-units" class="lead">Mbit/s</p>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3 st-block" id="ping">
                            <h3>Ping</h3>
                            <p class="display-4 st-value"><span id="ping-value">0</span></p>
                            <p class="lead" id="ping-units">ms</p>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3 st-block" id="jitter">
                            <h3>Jitter</h3>
                            <p class="display-4 st-value"><span id="jitter-value">0</span></p>
                            <p class="lead" id="jitter-units">ms</p>
                        </div>
                    </div>
                </div>
                </div>
        </div>
</div>
<?php $this->load->view('additional/footer'); ?>
</div>

    <script type="text/javascript">

           var worker = new Worker('/assets/js/plugins/speedtest/worker.js')

            var interval = setInterval(function () { worker.postMessage('status') }, 100)

            worker.onmessage = function (event) { // when status is received, split the string and put the values in the appropriate fields
                var download = document.getElementById('speed-value')
                var ping = document.getElementById('ping-value')
                var jitter = document.getElementById('jitter-value')

                // string format: status;download;upload;ping (speeds are in mbit/s) (status: 0=not started, 1=downloading, 2=uploading, 3=ping, 4=done, 5=aborted)
                var data = event.data.split(';')

                var status = Number(data[0])

                // The test has been aborted
                if (status >= 4) {
                    clearInterval(interval)
                    //document.getElementById('abortBtn').style.display = 'none'
                    //document.getElementById('startBtn').style.display = ''
                    worker = null
                }

                // Add class to the download speed value in order to signal to
                // node script that the test is complete
                if (status === 4) {
                    $('#speed-value').addClass('succeeded');
                }

                download.textContent = data[1]
                ping.textContent = data[3]
                jitter.textContent = data[5]
            }
            worker.postMessage('start {"test_order" :"P_D"}') 

        function stopTest() {
            if (worker) {
                worker.postMessage('abort')
            }
        }
    </script>

</body>
</html>
