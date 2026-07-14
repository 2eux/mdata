<?php

function executeOrFail(mysqli $koneksi, string $query)
{
    $result = mysqli_query($koneksi, $query);

    if (!$result) {
        throw new Exception(mysqli_error($koneksi));
    }

    return $result;
}