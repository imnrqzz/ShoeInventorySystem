<?php
// backend/utils/helpers.php - Shared helper functions

/**
 * Get the server's LAN IP address for QR code URLs
 */
function getServerIp() {
    $ip = '127.0.0.1';
    if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1' && $_SERVER['SERVER_ADDR'] !== '::1') {
        $ip = $_SERVER['SERVER_ADDR'];
    } else {
        $hostIp = gethostbyname(gethostname());
        if ($hostIp !== '127.0.0.1' && $hostIp !== gethostname()) {
            $ip = $hostIp;
        } else {
            $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($sock) {
                @socket_connect($sock, '8.8.8.8', 80);
                $ip = @socket_getsockname($sock)[0] ?: '127.0.0.1';
                @socket_close($sock);
            }
        }
    }
    return $ip;
}
