<?php

class Monitor
{

    protected function getMonitors ()
    {
        $return = Array(
                "Minecraft" => Array(),
                "TCP" => Array(),
                "Content" => Array()
        );
        @mkdir(getcwd() . "/MonitorList");
        $dir = opendir(getcwd() . "/MonitorList");
        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..')) {
                $name = explode(".", $file);
                if (! is_dir(getcwd() . "/MonitorList/" . $file)) {
                    $content = json_decode(
                            file_get_contents(
                                    getcwd() . "/MonitorList/" . $file), true);
                }
            }
        }
        closedir($dir);
    }
}