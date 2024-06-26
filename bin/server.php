<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

    require dirname(__DIR__) . '/vendor/autoload.php';

    try {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new Chat()
                )
            ),
            8080
        );
    
        echo "WebSocket server started on port 8080\n";
    
        $server->run();
    } catch (Exception $e) {
        echo "Exception caught: " . $e->getMessage() . "\n";
        error_log("Exception in WebSocket server: " . $e->getMessage());
        // Handle or log the exception as appropriate
    }

?>