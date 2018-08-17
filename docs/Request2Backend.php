<?php
// This document is for protocol version 0
// Sorry for my poor English, I am Chinese
$data = Array(
        "version" => 0, // Protocol version
        "action" => "add", // add, remove, get
        "args" => Array() // Argument for the action
);

$addArgs = Array(
        "type" => "Minecraft", // Minecraft, TCP, Content
        "args" => Array() // Argument for the type
);

$addArgsArgs1 = Array( // For type: Minecraft, TCP
        "addr" => "play.your.domain",
        "port" => 25565,
        "interval" => 60 // in seconds
);

$addArgsArgs2 = Array( // For type: Content
        "addr" => "https://jenkins-ssl.ishland.site", // <protocol>://<protocol_args>[/more_args/...]，For
                                                      // example,
                                                      // https://jenkins-ssl.ishland.site/job/UptimeMonitor
        "interval" => 60 // in seconds
);

$removeArgs = Array(
        "id" => 0 // The id you want to remove
);

$getArgs = Array(
        "id" => 0 // The id you want to get information
);

