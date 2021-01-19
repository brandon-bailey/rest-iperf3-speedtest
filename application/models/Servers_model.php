<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Servers_model extends CI_Model
{
    private $ports;
    private $usedPorts;

    public function __construct()
    {
        parent::__construct();
        $this->ports = range(8444, 9444);
    }

    public function checkHostAndPorts()
    {
    }

    private function getUsedPorts()
    {
        $query = $this->db->select('port')
        ->where('in_use', 1)
        ->get('used');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $result) {
                $this->usedPorts[] = $result->port;
            }
        }
    }
    
    private function getRandomPort()
    {
        return array_rand($this->ports, 1);
    }

    public function getPortNumber()
    {
        do {
            $port = $this->getRandomPort();
            $portToUse = $this->ports[$port];
            if (!$this->isPortUsed($portToUse)) {
                break;
            }
        } while (0);
        return $portToUse;
    }

    public function getDefaultHost()
    {
        if ($this->db->table_exists('servers')) {
            $query = $this->db->select('host')
            ->where('id', 1)
            ->get('servers');
            if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function doesHostExist($host)
    {
        try {
            $query = $this->db->select('host')
            ->where('host', $host)
            ->get('servers');
            if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
        }
    }

    private function createNewHost($host)
    {
        if ($this->db->insert('servers', $host)) {
            return $this->db->insert_id();
        } else {
            return false;
        }
    }

    public function addServer($input)
    {
        if (!isset($input['host']) || $input['host'] === "") {
            $message = array('message' =>
                    array(
                    $this->config->item('rest_message_field_name')=>'host field is required for this endpoint.',
                    $this->config->item('rest_status_field_name') => false
                    ),
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
                );
            return $message;
        } else {
            if (!$this->doesHostExist($input['host'])) {
                if ($id = $this->createNewHost($input) !== false) {
                    $message = array(
                        'message' => array(
                            'id' => (int)$id,
                            $this->config->item('rest_message_field_name')=>'Added new speedtest host',
                            $this->config->item('rest_status_field_name') => true
                        ),
                        'response_code' => REST_Controller::HTTP_CREATED
                    );
                    return $message;
                }
            } else {
                $message = array(
                        'message' => array(
                            $this->config->item('rest_message_field_name') => 'A host with that name already exists',
                            $this->config->item('rest_status_field_name') => false
                        ),
                        'response_code' => REST_Controller::HTTP_CONFLICT
                    );
                return $message;
            }
        }
    }

    public function isPortUsed($port)
    {
        $return = shell_exec("netstat -tulpn | grep {$port}");
        if (empty($return)) {
            return false;
        }
        return true;
    }

    public function cleanUpServers()
    {
        try {
            $query = $this->db->select('*')
            ->where('in_use', 1)
            ->get('used');
            foreach ($query->result() as $result) {
                echo $result->port . PHP_EOL;
            }
        } catch (Exception $e) {
        }
    }

    private function setServerStopped($server)
    {
        $pid = $this->getServerPid($server['port']);
        exec("kill -9 {$pid}");
        $this->db->set('in_use', 0);
        $this->db->where('port', $server['port']);
        $this->db->where('id', $server['instance']);
        $this->db->where('pid', $pid);
        $this->db->update('used');

        if ($this->db->affected_rows() !== 1) {
            $array = array(
                'message' => 'Error trying to update database',
                'status' => false,
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
                );
            return $array;
        } else {
            $pidfile = $this->getPidFile($server['port']);
            unlink($pidfile);
            $array = array(
                'message' => 'Successfully updated server',
                'status' => true,
                'response_code' => REST_Controller::HTTP_OK
                );
            return $array;
        }
    }

    public function stopServer($params)
    {
        if (!empty($params['instance']) && !empty($params['port'])) {
            return $this->setServerStopped($params);
        } else {
            $array = array(
                'message' => 'Missing required parameters.',
                'status' => false,
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
                );
            return $array;
        }
    }

    public function getClosestServer($client)
    {
        return $this->mapClientToClosestServer($client);
    }
    
    private function mapClientToClosestServer($clientIp)
    {
        $servers = $this->getAvailableServers();
        $clientLocation = $this->getClientLocation($clientIp);

        $closest = new StdClass;
        $closest->distance = 0;
        // For future use maybe to pass physical distance to client from server
        /*foreach ($servers as $server) {
            $server->distance = $this->getDistanceBetweenLocations($clientLocation, $server);
        }*/
        return $servers;
    }


    private function getDistanceBetweenLocations($locationA, $locationB)
    {
        $this->load->model('Lookup_model', 'lookup');
        return Lookup_model::vincentyGreatCircleDistance($locationA->latitude, $locationA->longitude, $locationB->latitude, $locationB->longitude);
    }

    public function getLocationByName($name)
    {
        if (self::isValidIp($name)) {
            $host = $name;
        } elseif (self::isValidHostname($name)) {
            $host = gethostbyname($name);
        } else {
            return false;
        }
        return $this->getClientLocation($host);
    }

    private function getClientLocation($clientIp)
    {
        $this->load->model('Lookup_model', 'lookup');
        return $this->lookup->getLocationByAddress($clientIp);
    }

    /**
     * This is currently a very basic implementation of the speedtest
     * host selection process.
     * @todo  Implement much more selective logic that selects
     * the best host.
     * @return [type] [description]
     */
    private function getSpeedtestHost()
    {
        $server = $this->db->select('*')
        ->where('id', 1)
        ->where('status', 1)
        ->get('servers')->row();

        $server->port = $this->getPortNumber();
        return $server;
    }

    private function getPidFile($port)
    {
        return $this->config->item('speedtest_pid_directory') . "/pspeed3-{$port}.pid";
    }

    /**
     * [startNewServerInstance description]
     * @param  [type] $port   Port number to start the instance listening on
     * @param  [type] $method ('tls' || 'udp')
     * @return [type]         [description]
     */
    public function startNewServerInstance($client, $method, $test = false)
    {
        $type = '';
        $status = 0;
        $location = $this->getClientLocation($client);
        $server = $this->getSpeedtestHost();

        if ($method === 'udp') {
            $type = '--udp';
        }
        $pidfile = $this->getPidFile($server->port);

        if ((bool) $test !== true) {
            log_message('debug', 'TEST variable : ' . $test);
            exec("{$this->config->item('speedtest_server')} -s --one-off -p {$server->port} {$type} -D --pidfile {$pidfile} &", $output, $status);
        

            if (0 !== $status) {
                log_message('error', 'Error starting new speedtest instance');
                return false;
            }

            $pid = $this->getServerPid($server->port);

            if ($pid) {
                $array = array('server_id' => $server->id, 'port' => $server->port, 'in_use' => 1, 'pid' => $pid, 'date' => date("Y-m-d H:i:s"));
                try {
                    $this->db->insert('used', $array);
                } catch (Exception $e) {
                    log_message('error', $e->getMessage());
                }
            } else {
                log_message('error', 'PID is not Set');
                //return false;
            }
        }
        return $server;
    }

    private function getServerPid($port)
    {
        $pidfile = $this->getPidFile($port);
        log_message('debug', 'PID File: ' . $pidfile);
        if (is_file($pidfile)) {
            return trim(file_get_contents($pidfile));
        } else {
            log_message('error', 'PID file does not exist');
            return false;
        }
    }

    /**
     * [startNewServerInstance description]
     * @param  [type] $port   Port number to start the instance listening on
     * @param  [type] $method ('tls' || 'udp')
     * @return [type]         [description]
     */
    public function deleteServerInstance($input)
    {
        if (0 !== $status) {
            return false;
        }

        $array = array('server_id' => 1,'port' => $port, 'in_use' => 1);
        try {
            $this->db->insert('used', $array);
        } catch (Exception $e) {
        }
        return true;
    }

    public function getAvailableServers()
    {
        try {
            $query = $this->db->select('*')
            ->get('servers')->result();
            // Also return the ip address of the server
            foreach ($query as $server) {
                $server->ip_address = gethostbyname($server->host);
            }
            return $query;
        } catch (Exception $e) {
            return false;
        }
    }

    private function updateServer($input)
    {
        $this->db->set('host', $input['new_hostname']);
        $this->db->where('host', $input['host']);
        $this->db->update('servers');
    }

    public function updateServers($input)
    {
        if ((!isset($input['host']) || $input['host'] === "") || (!isset($input['new_hostname']) || $input['new_hostname'] === "")) {
            $message = array('message' =>
                    array(
                    $this->config->item('rest_message_field_name')=>'host and new_hostname fields are required for this endpoint.',
                    $this->config->item('rest_status_field_name') => false
                    ),
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
                );
            return $message;
        } else {
            if ($this->doesHostExist($input['host'])) {
                if ($this->updateServer($input) !== false) {
                    $message = array(
                        'message' => array(
                            $this->config->item('rest_message_field_name')=>'Successfully modified speedtest host',
                            $this->config->item('rest_status_field_name') => true
                        ),
                        'response_code' => REST_Controller::HTTP_ACCEPTED
                    );
                    return $message;
                }
            } else {
                $message = array(
                        'message' => array(
                            $this->config->item('rest_message_field_name') =>'A host with that name does not exists',
                            $this->config->item('rest_status_field_name') => false
                        ),
                        'response_code' => REST_Controller::HTTP_CONFLICT
                    );
                return $message;
            }
        }
    }

    public function getRunningServers()
    {
        $query = $this->db->select('*')
        ->from('used')
        ->where('in_use', 1)
        ->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        }
    }

    public static function isJson($string, $return_data = false)
    {
        try {
            $data = json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : true) : false;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function isValidIp($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        } else {
            return false;
        }
    }
    public static function isValidHostname($host)
    {
        if (filter_var($host, FILTER_VALIDATE_URL)) {
            return true;
        } else {
            return false;
        }
    }
}
