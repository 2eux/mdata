<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/vendor/autoload.php';

function publishQueue(string $queue, array $data): void
{
    $connection = new AMQPStreamConnection(
        'localhost',
        5672,
        'guest',
        'guest'
    );

    $channel = $connection->channel();

    $channel->queue_declare(
        $queue,
        false,
        true,
        false,
        false
    );

    $msg = new AMQPMessage(
        json_encode($data),
        ['delivery_mode' => 2]
    );

    $channel->basic_publish(
        $msg,
        '',
        $queue
    );

    $channel->close();
    $connection->close();
}