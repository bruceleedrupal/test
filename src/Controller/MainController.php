<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('hello', false, false, false, false);
        
        $msg = new AMQPMessage('Hello World!');
        $channel->basic_publish($msg, '', 'hello');
        
        echo " [x] Sent 'Hello World!'\n";
        $channel->close();
        $connection->close();
        
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
    
    
    /**
     * @Route("/recieve", name="recieve")
     */
    public function recieve()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        
        $channel->queue_declare('hello', false, false, false, false);
        
        echo " [*] Waiting for messages. To exit press CTRL+C\n";
        
        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
        };
        
        $channel->basic_consume('hello', '', false, false, false, false, $callback);
        
        while ($channel->is_consuming()) {
            $channel->wait();
        }
        
        
        $channel->close();
        $connection->close();
        
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
