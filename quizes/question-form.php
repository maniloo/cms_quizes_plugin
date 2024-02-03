<?php

function get_quiz($quiz_id) {
    $api_url = 'http://10.0.10.4:6868/quizzes/' . $quiz_id;

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return 'Błąd podczas wykonania zapytania do API: ' . $response->get_error_message();
    }

    $body = wp_remote_retrieve_body($response);

    return json_decode($body);
}

function get_questions($quiz) {
    return $quiz->questions;
}

if (isset($_GET['sub_page']) && $_GET['sub_page'] == 'edit_quiz') {
    $quiz_id = $_GET['quiz_id'];
    $quiz = get_quiz($quiz_id);
} else {
    return;
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_answer') {
    $answer_id = $_GET['answer_id'];
    $api_url = 'http://10.0.10.4:6868/questions/'. $answer_id;

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



 $redirect_url = admin_url('/admin.php?page=quzies&sub_page=edit_quiz&quiz_id='. $quiz_id .'&successMessage=Pytanie+zostalo+usuniete');
 wp_redirect($redirect_url);
 exit;
}

function get_quiz_name($quiz) {
    return $quiz->name;
}

function add_question($question, $answer_1, $answer_2, $answer_3, $answer_4, $correct_answer, $quiz) {
    $answers = array(
        $answer_1,
        $answer_2,
        $answer_3,
        $answer_4
    );
    $correct_answer = $answers[$correct_answer - 1];
    $quiz_id = $quiz->id;


    $api_url = 'http://10.0.10.4:6868/quizzes/' . $quiz_id;

   $question = array(
        'content' => $question,
        'options' => $answers,
        'correctAnswer' => $correct_answer
   );

   $quiz_body = array(
         'name' => get_quiz_name($quiz),
         'questions' => array($question)
    );

   $request_args = array(
   'method' => 'PUT',
   'body' => json_encode($quiz_body),
    'headers' => array(
         'Content-Type' => 'application/json'
    ),
    );

    $response = wp_remote_request($api_url, $request_args);

    if (is_wp_error($response)) {
        return array(
                       'success' => false,
                       'message' => $response->get_error_message()
                   );
    } else {
        return array(
                       'success' => true,
                       'message' => 'Rekord został dodany!'
                   );
    }
}

if (isset($_POST['submit'])) {
    $result = add_question(
      $_POST['question'],
      $_POST['answer_1'],
      $_POST['answer_2'],
      $_POST['answer_3'],
      $_POST['answer_4'],
      $_POST['correct_answer'],
      $quiz
    );

    if ($result["success"]) {
      $redirect_url = admin_url('/admin.php?page=quzies&sub_page=edit_quiz&quiz_id='. $quiz_id .'&successMessage=Rekord+zosta%C5%82+dodany%21');
      wp_redirect($redirect_url);
      exit;
    } else {
    echo "<div class=\"notice notice-error is-dismissible\">
                <p>". $result["message"] ."</p>
            </div>";
    }
}

$questions = get_questions($quiz);
?>

<div class="wrap">
      <h1>Quiz: <?php echo(get_quiz_name($quiz)) ?></h1>

      <h2>Dodaj pytanie</h2>
      <section id="add-question">
        <form method="post" action="">
           <div>
            <label for="question">Pytanie:</label>
            <input type="text" name="question" />
</div>
<br />
<div>
            <label for="answer_1">Odpowiedź 1:</label>
            <input type="text" name="answer_1" />
            </div>
            <br />
<div>
            <label for="answer_2">Odpowiedź 2:</label>
            <input type="text" name="answer_2" />
            </div>
            <br />
<div>
            <label for="answer_3">Odpowiedź 3:</label>
            <input type="text" name="answer_3" />
            </div>
            <br />
<div>
            <label for="answer_4">Odpowiedź 4:</label>
            <input type="text" name="answer_4" />
            </div>
            <br />
<div>
            <label for="correct_answer">Poprawna odpowiedź:</label>
            <select name="correct_answer" id="correct_answer">
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
            </select>
            </div>
<div>
            <input type="submit" name="submit" value="Dodaj pytanie" />
            </div>
        </form>
        </section>
</div>

<?php
function renderAnswer($answer, $isCorrectAnswer) {
    if ($isCorrectAnswer) {
        return "<td class='correct-answer'>" . $answer . "</td>";
    } else {
        return "<td>" . $answer . "</td>";
    }
}
?>

<style>
    .admin-table {
        width: 95%;
        border-collapse: collapse;
        margin-top: 20px;
        border-color: white;
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
        <th>Pytanie</th>
        <th>Odp 1</th>
        <th>Odp 2</th>
        <th>Odp 3</th>
        <th>Odp 4</th>
        <th>Akcje</th>
    </tr>

    <?php
    foreach ($questions as $row) {
        $id = $row->id;
        $question = $row->content;
        $answers = $row->options;
        $correctAnswer = $row->correctAnswer;

        echo "<tr>";
        echo "<td>" . $id . "</td>";
        echo "<td>" . $question . "</td>";
        echo renderAnswer($answers[0], $correctAnswer == $answers[0]);
        echo renderAnswer($answers[1], $correctAnswer == $answers[1]);
        echo renderAnswer($answers[2], $correctAnswer == $answers[2]);
        echo renderAnswer($answers[3], $correctAnswer == $answers[3]);
        echo "<td><a href='?page=quzies&sub_page=edit_quiz&action=delete_answer&quiz_id=". $quiz_id ."&answer_id=". $id ."'>Usuń</a></td>";
        echo "</tr>";
    }
    ?>
</table>