<?php

function fetch_quizzes() {
    $api_url = 'http://10.0.10.4:6868/quizzes';

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return 'Błąd podczas wykonania zapytania do API: ' . $response->get_error_message();
    }

    $body = wp_remote_retrieve_body($response);

    return json_decode($body);
}

if(isset($_GET['action']) && $_GET['action'] == 'delete_quiz' && isset($_GET['quiz_id'])) {
    $quiz_id = $_GET['quiz_id'];

    $api_url = 'http://10.0.10.4:6868/quizzes/' . $quiz_id;

     $ch = curl_init($api_url);

        // Ustaw opcje cURL
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Wykonaj zapytanie
        $response = curl_exec($ch);

        // Sprawdź czy wystąpiły błędy
        if (curl_errno($ch)) {
            echo 'Błąd cURL: ' . curl_error($ch);
        }

        // Zamknij połączenie cURL
        curl_close($ch);


     $redirect_url = admin_url('/admin.php?page=quzies&successMessage=Quiz+zostal+usuniety');
     wp_redirect($redirect_url);
     exit;
}

$quzzies = fetch_quizzes();
?>

<style>
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .admin-table th, .admin-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    .admin-table th {
        background-color: #f2f2f2;
    }

    .correct-answer {
        background-color: #8aff8a;
    }
</style>

<table class="admin-table">
    <tr>
        <th>ID</th>
        <th>Nazwa</th>
        <th>Akcje</th>
    </tr>

    <?php
    foreach ($quzzies as $row) {
        $quiz_id = $row->id;
        $quiz_name = $row->name;

        echo "<tr>";
        echo "<td>" . $quiz_id . "</td>";
        echo "<td>" . $quiz_name . "</td>";
        echo "<td> <a href='?page=quzies&sub_page=edit_quiz&quiz_id=" . $quiz_id . "'>Edytuj</a> &nbsp;
         <a href='?page=quzies&action=delete_quiz&quiz_id=" . $quiz_id . "'>Usuń</a></td>";
        echo "</tr>";
    }
    ?>
</table>