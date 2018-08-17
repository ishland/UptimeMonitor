<?php

/**
 * Minecraft Server Status Query
 *
 * @link        https://github.com/FunnyItsElmo/PHP-Minecraft-Server-Status-Query/
 * @author      Julian Spravil <julian.spr@t-online.de>
 * @copyright   Copyright (c) 2016 Julian Spravil
 * @license     https://github.com/FunnyItsElmo/PHP-Minecraft-Server-Status-Query/blob/master/LICENSE
 */
class MinecraftServerStatus
{

    /**
     * Queries the server and returns the servers information
     *
     * @param string $host
     * @param number $port
     */
    public static function query ($host, $port = 25565)
    {
        logging("INFO", "Starting Minecraft Server query of {$host}:{$port}...");
        // check if the host is in ipv4 format
        $host = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname(
                $host);
        
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (! socket_connect($socket, $host, $port)) {
            return false;
        }
        
        // create the handshake and ping packet
        $handshakePacket = new MinecraftServerStatusHandshakePacket($host, $port,
                107, 1);
        $pingPacket = new MinecraftServerStatusPingPacket();
        
        $handshakePacket->send($socket);
        
        logging("DEBUG-1", "Starting data transfer...");
        // high five
        $start = microtime(true);
        $pingPacket->send($socket);
        $length = self::readVarInt($socket);
        $ping = round((microtime(true) - $start) * 1000);
        
        // read the requested data
        $data = socket_read($socket, $length, PHP_NORMAL_READ);
        $data = strstr($data, '{');
        $data = json_decode($data);
        
        $descriptionRaw = isset($data->description) ? $data->description : false;
        $description = $descriptionRaw;
        
        logging("DEBUG-1", "Data transfer finished.");
        // colorize the description if it is supported
        if (gettype($descriptionRaw) == 'object') {
            $description = '';
            
            if (isset($descriptionRaw->text)) {
                $color = isset($descriptionRaw->color) ? $descriptionRaw->color : '';
                $description = '<font color="' . $color . '">' .
                        $descriptionRaw->text . '</font>';
            }
            
            if (isset($descriptionRaw->extra)) {
                foreach ($descriptionRaw->extra as $item) {
                    $description .= isset($item->bold) && $item->bold ? '<b>' : '';
                    $description .= isset($item->color) ? '<font color="' .
                            $item->color . '">' . $item->text . '</font>' : '';
                    $description .= isset($item->bold) && $item->bold ? '</b>' : '';
                }
            }
        }
        
        logging("INFO", "Minecraft Server query of {$host}:{$port} finished.");
        $return = array(
                'hostname' => $host,
                'port' => $port,
                'ping' => $ping,
                'version' => isset($data->version->name) ? $data->version->name : false,
                'protocol' => isset($data->version->protocol) ? $data->version->protocol : false,
                'players' => isset($data->players->online) ? $data->players->online : false,
                'max_players' => isset($data->players->max) ? $data->players->max : false,
                'description' => htmlspecialchars($description),
                'description_raw' => htmlspecialchars($descriptionRaw),
                'favicon' => isset($data->favicon) ? $data->favicon : false,
                'modinfo' => isset($data->modinfo) ? $data->modinfo : false
        );
        logging("DEBUG-3", var_dump($return));
        return $return;
    }

    private static function readVarInt ($socket)
    {
        $a = 0;
        $b = 0;
        while (true) {
            $c = socket_read($socket, 1);
            if (! $c) {
                return 0;
            }
            $c = Ord($c);
            $a |= ($c & 0x7F) << $b ++ * 7;
            if ($b > 5) {
                return false;
            }
            if (($c & 0x80) != 128) {
                break;
            }
        }
        return $a;
    }
}

class MinecraftServerStatusPacket
{

    protected $packetID;

    protected $data;

    public function __construct ($packetID)
    {
        $this->packetID = $packetID;
        $this->data = pack('C', $packetID);
    }

    public function addSignedChar ($data)
    {
        $this->data .= pack('c', $data);
    }

    public function addUnsignedChar ($data)
    {
        $this->data .= pack('C', $data);
    }

    public function addSignedShort ($data)
    {
        $this->data .= pack('s', $data);
    }

    public function addUnsignedShort ($data)
    {
        $this->data .= pack('S', $data);
    }

    public function addString ($data)
    {
        $this->data .= pack('C', strlen($data));
        $this->data .= $data;
    }

    public function send ($socket)
    {
        $this->data = pack('C', strlen($this->data)) . $this->data;
        socket_send($socket, $this->data, strlen($this->data), 0);
    }
}

class MinecraftServerStatusHandshakePacket extends MinecraftServerStatusPacket
{

    public function __construct ($host, $port, $protocol, $nextState)
    {
        parent::__construct(0);
        $this->addUnsignedChar($protocol);
        $this->addString($host);
        $this->addUnsignedShort($port);
        $this->addUnsignedChar($nextState);
    }
}

class MinecraftServerStatusPingPacket extends MinecraftServerStatusPacket
{

    public function __construct ()
    {
        parent::__construct(0);
    }
}
