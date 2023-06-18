<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
include_once("config.php");

$wa_token = 'X1CWu_x9GrebaQUxyVGdJF3_4SCsVW9z1QjX-XJ9B6k';
$template_id = 'b47daffc-7caf-4bea-9f36-edf4067b2c08';
$integration_id = '31c076d5-ac80-4204-adc9-964c9b0c590b';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        $result = mysqli_query($conn, "SELECT * FROM tb_order WHERE id_order = '$id'");

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $transArray[] = $row;
        }

        mysqli_close($conn);

        echo json_encode(array("status" => "ok", "result" => $transArray));
    } else {
        $result = mysqli_query($conn, "SELECT * FROM tb_order WHERE tb_order.status = 'waiting'");

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $transArray[] = $row;
        }

        mysqli_close($conn);

        echo json_encode(array("status" => "ok", "results" => $transArray));
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $nominal = $_POST['nominal'];
    $nomor_hp = $_POST['nomorhp'];

    $pengurangan = 0;

    $queryCheck = mysqli_query($conn, "SELECT * FROM tb_order WHERE tb_order.status = 'waiting'");

    while ($row = $queryCheck->fetch_array(MYSQLI_ASSOC)) {
        $transArray[] = $row;
    }


    foreach ($transArray as $transArray) {
        //     echo '<br>Nominal Skrg: ' . $nominal - $pengurangan;
        //     echo '<br>Nominal DB: ' . $transArray['nominal'];
        if ($transArray['nominal'] == ($nominal - $pengurangan)) {
            $pengurangan = $pengurangan + 5;
        }
    }
    // echo '<br>Pengurangan : ' . $pengurangan;

    $nominal = $nominal - $pengurangan;

    // echo '<br>Nominal Baru: ' . $nominal;

    $result = mysqli_query($conn, "INSERT INTO tb_order(nama, nominal, nomorhp) VALUES('$nama', $nominal, '$nomor_hp')");

    if ($result) {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "to_number": "' . $nomor_hp . '",
                "to_name": "' . $nama . '",
                "message_template_id": "' . $template_id . '",
                "channel_integration_id": "' . $integration_id . '",
                "language": {
                    "code": "id"
                },
                "parameters": {
                    "body": [
                    {
                        "key": "1",
                        "value": "nama",
                        "value_text": "' . $nama . '"
                    },
                    {
                        "key": "2",
                        "value": "nominal",
                        "value_text": "' . $nominal . '"
                    },
                    {
                        "key": "3",
                        "value": "norek",
                        "value_text": "1234567890"
                    }
                    ]
                }
                }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $wa_token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $response = ["response" => 200, "status" => "ok", "message" => "Berhasil menambah data transaksi!"];
        echo json_encode($response);
    } else {
        $response = ["response" => 200, "status" => "failed", "message" => "Gagal menambah data transaksi!"];
        echo json_encode($response);
    }

    mysqli_close($conn);
}
