<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['speedtest_server'] = FCPATH .'/assets/pocketspeed/centos/iperf3';

$config['speedtest_pid_directory'] = FCPATH .'/assets/pocketspeed/run';

$config['plugin_directory'] = '/assets/plugins/';

$config['geoip_database'] = FCPATH . '/assets/geoip/GeoLite2-City.mmdb';
