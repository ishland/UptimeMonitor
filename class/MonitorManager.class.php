<?php

class MonitorManager
{

    /**
     * Add a monitor.
     * Returns bool on $debug = false
     * Returns array on $debug = true
     *
     * @return mixed
     * @param string $monitorName
     * @param string $monitorType
     * @param array $monitorArgs
     * @param bool $debug
     *            = false
     */
    public function addMonitor ($monitorName, $monitorType, $monitorArgs,
            $debug = false)
    {
        if (! $monitorName && ! $monitorType && ! $monitorArgs)
            return self::output("Invaild params", 1, $debug);
        if ($monitorType == "Minecraft") {
            if (! in_array("addr", array_keys($monitorArgs)) &&
                    ! in_array("port", array_keys($monitorArgs)))
                return self::output("Invaild monitor argumemts", 1, $debug);
            self::addMonitorMinecraft($monitorArgs["addr"], $monitorArgs["port"]);
        }
    }

    protected function addMonitorMinecraft ($addr, $port)
    {
        file_put_contents(
                getcwd() . "/MonitorList/" . self::getNumber() . ".monitor",
                json_encode(
                        array(
                                "type" => "Minecraft",
                                "addr" => $addr,
                                "port" => $port
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