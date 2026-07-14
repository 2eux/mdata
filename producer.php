<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// koneksi RabbitMQ
$connection = new AMQPStreamConnection(
    'localhost',
    5672,
    'guest',
    'guest'
);

$channel = $connection->channel();

// buat queue
$channel->queue_declare(
    'email_queue',
    false,
    true,
    false,
    false
);

// data email
$data = [
    "email" => "test@gmail.com",
    "subject" => "Request Approved",
    "message" => "Request kamu disetujui"
];

// ubah ke JSON
$msg = new AMQPMessage(json_encode($data));

// kirim ke queue
$channel->basic_publish(
    $msg,
    '',
    'email_queue'
);

echo "Message berhasil masuk queue";

// tutup koneksi
$channel->close();
$connection->close();