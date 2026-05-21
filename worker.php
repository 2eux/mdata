<?php

error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/include/email_helper.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

// koneksi RabbitMQ
$connection = new AMQPStreamConnection(
    'localhost',
    5672,
    'guest',
    'guest'
);

$channel = $connection->channel();

// queue
$channel->queue_declare(
    'email_queue',
    false,
    true,
    false,
    false
);

echo "Menunggu message...\n";

// =====================================================
// CALLBACK
// =====================================================
$callback = function ($msg) use ($koneksi) {

    $data = json_decode($msg->body, true);
    echo "MESSAGE MASUK:\n";
    print_r($data);
    echo "\n";

    try {

        // =========================================
        // NEXT STEP
        // =========================================
        if (
            isset($data['type']) &&
            $data['type'] == 'NEXT_STEP'
        ) {

            $request_id = $data['request_id'];
            $next_step  = $data['next_step'];

            $header = mysqli_fetch_assoc(mysqli_query($koneksi, "
                SELECT rh.*, u.nama
                FROM request_header rh
                JOIN users u ON rh.requestor_id = u.id
                WHERE rh.id = '$request_id'
            "));

echo "NEXT STEP:\n";
print_r($header);
echo "\n";

echo "NEXT ROLE: ";
echo $next_step;
echo "\n";


            sendNotifNextStep(
                $koneksi,
                $request_id,
                $next_step,
                $header
            );

            echo "Email next step berhasil dikirim\n";
        }

        // =========================================
        // REJECTED
        // =========================================
        elseif (
            isset($data['type']) &&
            $data['type'] == 'REJECTED'
        ) {

            $request_id = $data['request_id'];
            $role       = $data['role'];
            $remarks    = $data['remarks'];

            $header = mysqli_fetch_assoc(mysqli_query($koneksi, "
                SELECT rh.*, u.nama
                FROM request_header rh
                JOIN users u ON rh.requestor_id = u.id
                WHERE rh.id = '$request_id'
            "));

            sendNotifRejected(
                $koneksi,
                $request_id,
                $header,
                $role,
                $remarks,
                null
            );

            echo "Email reject berhasil dikirim\n";
        }

        // =========================================
        // FINAL EMAIL
        // =========================================
        elseif (
            isset($data['type']) &&
            $data['type'] == 'FINAL_EMAIL'
        ) {

            $request_id = $data['request_id'];

            $header = mysqli_fetch_assoc(mysqli_query($koneksi, "
                SELECT rh.*, u.nama
                FROM request_header rh
                JOIN users u ON rh.requestor_id = u.id
                WHERE rh.id = '$request_id'
            "));

            sendFinalEmail(
                $koneksi,
                $request_id,
                $header,
                'COMPLETED'
            );

            echo "Final email berhasil dikirim\n";
        }


    } catch (\Throwable $e) {

        echo "Gagal kirim email\n";
        echo $e->getMessage();
        echo "\n";
    }
};


// =====================================================
// LISTEN QUEUE
// =====================================================
$channel->basic_consume(
    'email_queue',
    '',
    false,
    true,
    false,
    false,
    $callback
);

// =====================================================
// LOOP
// =====================================================
while (true) {

    try {

        $channel->wait(null, false, 30);

    } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {

        continue;

    } catch (\Throwable $e) {

        echo "Error: ";
        echo $e->getMessage();
        echo "\n";

        sleep(1);
    }
}