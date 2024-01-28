<?php
    
    enum PACKET_TYPE { 
        case SERVER_INFO;
        case SERVER_RULES;
        case SERVER_PING;
        case CLIENT_LIST;
        case DETAILED_PLAYER_INFO;
        case RCON_COMMAND;
    }


    class SampQueryApi {
        
        private $mIp;
        private $mArrIp;
        private $mPort;
        private $mSocket;
        private $mPType;
        private $mPacket;
        private $mDataReciveAll;
        private $mDataRecive;
        private $mDebInfo = false;
        private $mDataArray;

        const TAG = "SAMP";
        
        function __construct($ip, $port)
        {
            $this->mIp = $ip;
            $this->mPort = $port;
            $this->mArrIp = explode('.', $this->mIp);
            
        }

        function getServerIp() 
        {
            return $this->mIp;
        }

        function getServerPort()
        {
            return $this->mPort;
        }

        function connect() {
            $this->mSocket = fsockopen('udp://'.$this->mIp, $this->mPort, $errco, $errmsg, 3);
            if(!$this->mSocket) {
                return 0;
            } else {
                $this->createPacket(PACKET_TYPE::SERVER_INFO);
                $this->send();
                $this->readPacket();
                return 1;
            }
        }


        function createPacket(PACKET_TYPE $type) {
            $this->mPacket = null;
            $this->mPType = $type;
            if(is_null($this->mPacket)) {
                $this->mPacket .= self::TAG;
                $this->mPacket .= chr($this->mArrIp[0]);
                $this->mPacket .= chr($this->mArrIp[1]);
                $this->mPacket .= chr($this->mArrIp[2]);
                $this->mPacket .= chr($this->mArrIp[3]);
                $this->mPacket .= chr($this->mPort && 0xFF);
                $this->mPacket .= chr($this->mPort >> 8 & 0xFF);

            } else {
                return 0;
            }

            switch($type)
            {
                case PACKET_TYPE::SERVER_INFO: {
                    $this->mPacket .= 'i';
                    $this->mDebInfo = "SERVER INFO PACKET CREATING";
                    break;
                }

                case PACKET_TYPE::SERVER_RULES: {
                    $this->mPacket .= 'r';
                    break;
                }

                case PACKET_TYPE::SERVER_PING: {
                    $this->mPacket .= 'p';
                    break;
                }

                case PACKET_TYPE::CLIENT_LIST: {
                    $this->mPacket .= 'c';
                    break;
                }

                case PACKET_TYPE::DETAILED_PLAYER_INFO: {
                    $this->mPacket .= 'd';
                    break;
                }

                case PACKET_TYPE::RCON_COMMAND: {
                    $this->mPacket .= 'x';
                    break;
                }
               
            }
        }

        function send() {
            $this->mDataReciveAll = null;
            $this->mDataRecive = null;
            fwrite($this->mSocket, $this->mPacket);
        }

        function read() {
            $this->mDataReciveAll = fread($this->mSocket, 2048);
            
            return $this->mDataReciveAll." | total bytes: ".strlen($this->mDataReciveAll);
        }

        function readPacket() {
            $this->mDataReciveAll = fread($this->mSocket, 2048);

            if(is_null($this->mDataReciveAll))
            {
                return 0;
            } else {
                $this->mDataRecive = substr($this->mDataReciveAll, 11);
            }
            
            switch($this->mPType) {
                case PACKET_TYPE::SERVER_INFO: {
                    $this->mDataArray['password'] = implode("",unpack("cchars", $this->mDataRecive[0]));
                    $this->mDataArray['onlineplayers'] = implode("", unpack("cchars", $this->mDataRecive[1]) + unpack("cchars", $this->mDataRecive[2]));
                    $this->mDataArray['maxplayers'] = implode("",unpack("cchars", $this->mDataRecive[3]) + unpack("cchars", $this->mDataRecive[4]));
                    $this->mDataArray['hostname_length'] = unpack("cchars", $this->mDataRecive[5]) + unpack("cchars", $this->mDataRecive[6]) + unpack("cchars", $this->mDataRecive[7]) + unpack("cchars", $this->mDataRecive[8]);
                    
                    $this->mDataArray['hostname'] = "";
                    
                    for($i = 1; $i <= implode("",$this->mDataArray['hostname_length']); $i++) {
                        
                        if($i == 1)
                            $this->mDataArray['hostname'] .= implode("",unpack("a", $this->mDataRecive[$i + 8]));
                        else
                            $this->mDataArray['hostname'] .= "".implode("",unpack("a", $this->mDataRecive[$i + 8]));
                        
                       
                    }

                    $this->mDataArray['gamemode_length'] = unpack("cchars", $this->mDataRecive[intval(implode("",$this->mDataArray['hostname_length'])) + 9]) + unpack("cchars", $this->mDataRecive[intval(implode("",$this->mDataArray['hostname_length'])) + 10]) + unpack("cchars", $this->mDataRecive[intval(implode("",$this->mDataArray['hostname_length'])) + 11]) + unpack("cchars", $this->mDataRecive[intval(implode("",$this->mDataArray['hostname_length'])) + 12]);
                   
                    $this->mDataArray['gamemode'] = "";
                    
                    for($i = 1; $i <= implode("", $this->mDataArray['gamemode_length']); $i++) {
                        if($i == 1)
                            $this->mDataArray['gamemode'] .= implode("", unpack("a", $this->mDataRecive[intval(implode("",$this->mDataArray['hostname_length'])) + 12 + $i]));
                        else
                            $this->mDataArray['gamemode'] .= "".implode("", unpack("a", $this->mDataRecive[intval(implode("",$this->mDataArray['hostname_length']))+ 12 + $i]));
                    }
                    $this->mDataArray['lang_length'] = unpack("cchars", $this->mDataRecive[intval(implode("", $this->mDataArray['gamemode_length'])) + intval(implode("", $this->mDataArray['hostname_length'])) + 13]) + unpack("cchars", $this->mDataRecive[intval(implode("", $this->mDataArray['gamemode_length'])) + intval(implode("", $this->mDataArray['hostname_length'])) + 14]) + unpack("cchars", $this->mDataRecive[intval(implode("", $this->mDataArray['gamemode_length'])) + intval(implode("", $this->mDataArray['hostname_length'])) + 15]) + unpack("cchars", $this->mDataRecive[intval(implode("", $this->mDataArray['gamemode_length'])) + intval(implode("", $this->mDataArray['hostname_length'])) + 16]);
                   
                    $this->mDataArray['lang'] = "";
                    for($i = 1; $i <= implode("", $this->mDataArray['lang_length']); $i++) {
                        if($i == 1) 
                            $this->mDataArray['lang'] .= implode("", unpack("a", $this->mDataRecive[intval(implode("", $this->mDataArray['gamemode_length'])) + intval(implode("",$this->mDataArray['hostname_length'])) + intval(implode("",$this->mDataArray['lang_length'])) + $i + 9]));
                        else 
                            $this->mDataArray['lang'] .= "".implode("", unpack("a", $this->mDataRecive[intval(implode("", $this->mDataArray['gamemode_length'])) + intval(implode("",$this->mDataArray['hostname_length'])) + intval(implode("",$this->mDataArray['lang_length'])) + $i + 9]));
                    }

                    

                    break;
                }

                case PACKET_TYPE::SERVER_RULES: {
                    
                    break;
                }

                case PACKET_TYPE::SERVER_PING: {
                    
                    break;
                }

                case PACKET_TYPE::CLIENT_LIST: {
                    
                    break;
                }

                case PACKET_TYPE::DETAILED_PLAYER_INFO: {
                    
                    break;
                }

                case PACKET_TYPE::RCON_COMMAND: {
                    
                    break;
                }
               
            }
            
        }
        

        function close() {
            fclose($this->mSocket);
        }

        function showDebugInfo() {
            return $this->mDebInfo;
        }

        function getPasswordInfo() {
            return $this->mDataArray["password"];

        }

        function getHostname() {
            return $this->mDataArray["hostname"];
        }

        function getPlayersOnline() {
            return $this->mDataArray["onlineplayers"];
        }

        function getMaxPlayers() {
            return $this->mDataArray["maxplayers"];
        }

        function getGamemode() {
            return $this->mDataArray["gamemode"];
        }

        function getLanguage() {
            return $this->mDataArray["lang"];
        }
    }
?>