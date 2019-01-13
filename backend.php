<?php
require_once __DIR__ . '/Workerman/Autoloader.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/class/PortChecker.class.php';
require_once __DIR__ . '/class/MinecraftServerStatus.class.php';
require_once __DIR__ . '/class/MonitorManager.class.php';
require_once __DIR__ . '/class/Monitor.class.php';
use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;

define("PROTOCOL_VERSION", 0);

$main = new Worker("tcp://0.0.0.0:3406");
$main->onMessage = function ($connection, $data)
{
    $data = json_decode($data, true);
    if (! $data) {
        $connection->close();
        return;
    }
    if ($data["version"] !== PROTOCOL_VERSION) {
        $connection->send(
                json_encode(
                        Array(
                                "version" => PROTOCOL_VERSION,
                                "status" => 1,
                                "msg" => "Protocol version wrong! Please use " .
                                PROTOCOL_VERSION
                        )));
        $connection->close();
        return;
    }

    if ($data["action"] !== "add" xor $data["action"] !== "remove" xor
            $data["action"] !== "get") {
        $connection->send(
                json_encode(
                        Array(
                                "version" => PROTOCOL_VERSION,
                                "status" => 2,
                                "msg" => "Invaild action"
                        )));
        $connection->close();
        return;
    }

    if ($data["action"] == "add") {
        if (! in_array("type", $data["args"]) xor
                ! in_array("args", $data["args"])) {
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 2,
                                    "msg" => "Invaild arguments"
                            )));
            $connection->close();
            return;
        }
        if ($data["args"]["type"] !== "Minecraft" xor
                $data["args"]["type"] !== "TCP" xor
                $data["args"]["type"] !== "Content") {
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 2,
                                    "msg" => "Invaild monitor type"
                            )));
            $connection->close();
            return;
        }
        if (! $data["args"]["args"]) {
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 2,
                                    "msg" => "Invaild arguments"
                            )));
            $connection->close();
            return;
        }
        if (! $data["args"]["args"]["addr"] xor
                ! $data["args"]["args"]["interval"]) {
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 2,
                                    "msg" => "Invaild arguments"
                            )));
            $connection->close();
            return;
        }
        $class = new MonitorManager();
        switch ($data["args"]["type"]) {
            case "Minecraft":
                if (! $data["args"]["args"]["port"])
                    break;
                $class->addMonitor("Minecraft", $data["args"]["args"]);
                $connection->send(
                        json_encode(
                                Array(
                                        "version" => PROTOCOL_VERSION,
                                        "status" => 0,
                                        "msg" => "Success"
                                )));
                $connection->close();
                return;
            case "TCP":
                if (! $data["args"]["args"]["port"])
                    break;
                $class->addMonitor("TCP", $data["args"]["args"]);
                $connection->send(
                        json_encode(
                                Array(
                                        "version" => PROTOCOL_VERSION,
                                        "status" => 0,
                                        "msg" => "Success"
                                )));
                $connection->close();
                return;
            case "Content":
                $class->addMonitor("Content", $data["args"]["args"]);
                $connection->send(
                        json_encode(
                                Array(
                                        "version" => PROTOCOL_VERSION,
                                        "status" => 0,
                                        "msg" => "Success"
                                )));
                $connection->close();
                return;
        }
        $connection->send(
                json_encode(
                        Array(
                                "version" => PROTOCOL_VERSION,
                                "status" => 2,
                                "msg" => "Not handled"
                        )));
        $connection->close();
        return;
    }
    if ($data["action"] == "remove") {
        if (! in_array("id", array_keys($data["args"]))) {
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 2,
                                    "msg" => "Invaild arguments"
                            )));
            $connection->close();
            return;
        }
        if (! json_decode(
                @file_get_contents(
                        getcwd() . "/MonitorList/" . $data["args"]["id"] .
                        ".monitor"), true)) {
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 2,
                                    "msg" => "Invaild id"
                            )));
            $connection->close();
            return;
        }
        $class = new MonitorManager();
        $result = $class->removeMonitor($data["args"]["id"], false);
        if ($result) {
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 0,
                                    "msg" => "Success"
                            )));
            $connection->close();
            return;
        }
        $connection->send(
                json_encode(
                        Array(
                                "version" => PROTOCOL_VERSION,
                                "status" => 2,
                                "msg" => "Not handled"
                        )));
        $connection->close();
        return;
    }
    if ($data["action"] == "get") {
        if (! in_array("id", array_keys($data["args"]))) {
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 2,
                                    "msg" => "Invaild arguments"
                            )));
            $connection->close();
            return;
        }
        if (! json_decode(
                @file_get_contents(
                        getcwd() . "/MonitorList/" . $data["args"]["id"] .
                        ".monitor"), true)) {
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 2,
                                    "msg" => "Invaild id"
                            )));
            $connection->close();
            return;
        }
        $class = new MonitorManager();
        $origin = $class->infoMonitor($data["args"]["id"], true);
        $G2 = Array();
        if ($origin && $origin["type"] == "Minecraft") {
            foreach ($origin["data"] as $key => $value) {
                $G2[$key]["time"] == $key;
                $G2[$key]["success"] == $value["success"];
                $G2[$key]["players"] == $value["players"];
                $G2[$key]["ping"] == $value["ping"];
            }
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 0,
                                    "msg" => "Success",
                                    "data" => Array(
                                            "version" => 0,
                                            "origin" => $origin,
                                            "G2" => $G2
                                    )
                            )));
            $connection->close();
            return;
        }
        $connection->send(
                json_encode(
                        Array(
                                "version" => PROTOCOL_VERSION,
                                "status" => 2,
                                "msg" => "Not handled"
                        )));
        $connection->close();
        return;
    }
    if ($data["action"] == "list") {
        $class = new MonitorManager();
        $connection->send(
                json_encode(
                        Array(
                                "version" => PROTOCOL_VERSION,
                                "status" => 0,
                                "msg" => "Success",
                                "data" => $class->listMonitor()
                        )));
        $connection->close();
        return;

        $connection->send(
                json_encode(
                        Array(
                                "version" => PROTOCOL_VERSION,
                                "status" => 2,
                                "msg" => "Not handled"
                        )));
        $connection->close();
        return;
    }
};

$monitor = new Worker("unix://" . getcwd() . "/monitor_socket");
$monitor->onWorkerStart = function ($worker)
{
    $worker->mainClass = new Monitor();
    $worker->managerClass = new MonitorManager();

    // Refreshing data
    $worker->managerClass->updateList();
};
$monitor->onMessage = function ($connection, $data)
{
    if (! json_decode($data))
        return;
    $data = json_decode($data, true);
    if ($data["action"] == "refresh")
        $connection->worker->managerClass->updateList();
};

$timer = new Worker("unix://" . getcwd() . "/timer_socket");
$timer->onWorkerStart = function ($worker)
{
    $worker->mainClass = new Monitor();
    $worker->managerClass = new MonitorManager();
    $worker->connectionMonitor = new AsyncTcpConnection(
            "unix://" . getcwd() . "/monitor_socket");
};

Worker::runAll();

