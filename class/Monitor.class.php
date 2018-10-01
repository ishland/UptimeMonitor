<?php

class Monitor
{

    protected $monitors = Array();

    public function monitoring ($id, $debug = false)
    {
        logging("INFO", "Starting monitor of id " . $id . "...");
        self::getMonitors();
        foreach ($this->monitors as $value) {
            if ($value["id"] == $id) {
                $target = $value;
                break;
            }
        }
        if (! $target)
            return self::output("Invaild monitor ID", 1, $debug);
        switch ($target["type"]) {
            case "Minecraft":
                self::monitoringMinecraft($target);
                break;
            case "TCP":
                self::monitoringTCP($target);
                break;
            case "Content":
                self::monitoringContent($target);
                break;
        }
    }

    protected function getMonitors ()
    {
        logging("INFO", "Refreshing monitor list...");
        $return = Array();
        @mkdir(getcwd() . "/MonitorList");
        $dir = opendir(getcwd() . "/MonitorList");
        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..')) {
                if (! is_dir(getcwd() . "/MonitorList/" . $file)) {
                    $content = json_decode(
                            file_get_contents(
                                    getcwd() . "/MonitorList/" . $file), true);
                    if (! $content)
                        continue;
                    logging("DEBUG-1", "Found " . $file . ".");
                    $return[] = $content;
                }
            }
        }
        closedir($dir);
        $this->monitors = $return;
        logging("INFO", "Done.");
        return $return;
    }

    protected function monitoringMinecraft ($content)
    {
        $class = new MinecraftServerStatus();
        $result = $class->query($content["addr"], $content["port"]);
        if (! $result) {
            $content["data"][] = Array(
                    "status" => 1,
                    "result" => $result
            );
        } else {
            $content["data"][] = Array(
                    "status" => 0,
                    "result" => $result
            );
        }
        self::updateData($content);
    }

    protected function monitoringTCP ($content)
    {
        $sock = fsockopen($content["addr"], $content["port"], $errno, $errstr,
                10);
        if (! $sock) {
            $content["data"][] = Array(
                    "status" => 1,
                    "result" => Array(
                            "errno" => $errno,
                            "errstr" => $errstr
                    )
            );
        } else {
            $content["data"][] = Array(
                    "status" => 0,
                    "result" => Array()
            );
        }
    }

    protected function monitoringContent ($content)
    {
        if (file_get_contents($content["addr"])) {
            $content["data"][] = Array(
                    "status" => 0
            );
        } else {
            $content["data"][] = Array(
                    "status" => 1
            );
        }
    }

    protected function updateData ($content)
    {
        file_put_contents(
                getcwd() . "/MonitorList/" . $content["id"] . ".monitor",
                json_encode($content));
    }

    protected function output ($msg, $status, $debug)
    {
        if ($debug) {
            return array(
                    "status" => $status,
                    "msg" => $msg
            );
        }
        if ($status == 0) {
            logging("INFO", $msg);
            return true;
        }
        logging("ERROR", $msg);
        return false;
    }
}