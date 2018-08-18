<?php

class MonitorManager
{

    /**
     * Add a monitor.
     * Returns bool on $debug = false
     * Returns array on $debug = true
     *
     * @return mixed
     * @param string $monitorType
     * @param array $monitorArgs
     * @param bool $debug
     *            = false
     */
    public function addMonitor ($monitorType, $monitorArgs, $debug = false)
    {
        if (! $monitorType xor ! $monitorArgs)
            return self::output("Invaild params", 1, $debug);
        if ($monitorType == "Minecraft") {
            if (! in_array("addr", array_keys($monitorArgs)) xor
                    ! in_array("port", array_keys($monitorArgs)) xor
                    ! in_array("interval", array_keys($monitorArgs)))
                return self::output("Invaild monitor argumemts", 1, $debug);
            self::addMonitorMinecraft($monitorArgs["addr"], $monitorArgs["port"],
                    $monitorArgs["interval"]);
        }
        if ($monitorType == "TCP") {
            if (! in_array("addr", array_keys($monitorArgs)) xor
                    ! in_array("port", array_keys($monitorArgs)) xor
                    ! in_array("interval", array_keys($monitorArgs)))
                return self::output("Invaild monitor arguments", 1, $debug);
            self::addMonitorTCP($monitorArgs["addr"], $monitorArgs["port"],
                    $monitorArgs["interval"]);
        }
        if ($monitorType == "Content") {
            if (! in_array("addr", array_keys($monitorArgs)) xor
                    ! in_array("interval", array_keys($monitorArgs)))
                return self::output("Invaild monitor arguments", 1, $debug);
            self::addMonitorContent($monitorArgs["addr"],
                    $monitorArgs["interval"]);
        }
    }

    /**
     * Remove a monitor.
     * Returns bool on $debug = false
     * Returns array on $debug = true
     *
     * @return mixed
     * @param int $id
     * @param bool $debug
     *            = false
     */
    public function removeMonitor ($id, $debug = false)
    {
        if (! json_decode(
                @file_get_contents(
                        getcwd() . "/MonitorList/" . $id . ".monitor"), true))
            return self::output("Invaild ID", 1, $debug);
        file_put_contents(getcwd() . "/MonitorList/" . $id . ".monitor", "");
        return true;
    }

    /**
     * Get a information about a monitor.
     * Returns bool on $debug = false
     * Returns array on $debug = true
     *
     * @return mixed
     * @param int $id
     * @param bool $debug
     *            = false
     */
    public function infoMonitor ($id, $debug = false)
    {
        if (! json_decode(
                @file_get_contents(
                        getcwd() . "/MonitorList/" . $id . ".monitor"), true))
            return self::output("Invaild ID", 1, $debug);
        return json_decode(
                file_get_contents(getcwd() . "/MonitorList/" . $id . ".monitor"),
                true);
    }

    protected function addMonitorMinecraft ($addr, $port, $interval)
    {
        file_put_contents(
                getcwd() . "/MonitorList/" . self::getNumber() . ".monitor",
                json_encode(
                        array(
                                "type" => "Minecraft",
                                "addr" => $addr,
                                "port" => $port,
                                "interval" => $interval,
                                "id" => self::getNumber(),
                                "data" => Array()
                        )));
    }

    protected function addMonitorTCP ($addr, $port, $interval)
    {
        file_put_contents(
                getcwd() . "/MonitorList/" . self::getNumber() . ".monitor",
                json_encode(
                        array(
                                "type" => "TCP",
                                "addr" => $addr,
                                "port" => $port,
                                "interval" => $interval,
                                "id" => self::getNumber(),
                                "data" => Array()
                        )));
    }

    protected function addMonitorContent ($addr, $interval)
    {
        file_put_contents(
                getcwd() . "/MonitorList/" . self::getNumber() . ".monitor",
                json_encode(
                        array(
                                "type" => "Content",
                                "addr" => $addr,
                                "interval" => $interval,
                                "id" => self::getNumber(),
                                "data" => Array()
                        )));
    }

    protected function getNumber ()
    {
        $i = 0;
        @mkdir(getcwd() . "/MonitorList");
        $dir = opendir(getcwd() . "/MonitorList");
        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..')) {
                $name = explode(".", $file);
                if (! is_dir(getcwd() . "/MonitorList/" . $file)) {
                    $i ++;
                }
            }
        }
        closedir($dir);
        $i ++;
        return $i;
    }

    protected function output ($msg, $status, $debug)
    {
        if ($debug) {
            return array(
                    "status" => $status,
                    "msg" => $msg
            );
        }
        if ($status == 0)
            return true;
        return false;
    }
}