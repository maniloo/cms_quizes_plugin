<?php
if (isset($_GET['sub_page'])){
    return;
}

function create_quiz($name) {
    $api_url = 'http://10.0.10.4:6868/quizzes';

    $data = array(
        'name' => $name,
    );

    $request_args = array(
            'headers'     => array(
                'Content-Type' => 'application/json',
            ),
            'body'        => json_encode($data),
            'method'      => 'POST',
        );

    $response = wp_remote_post($api_url, $request_args);


    if (is_wp_error($response)) {
        $result = array(
            'status' => "failed",
            'data'   => $response->get_error_message(),
        );
    } else {
       $body = wp_remote_retrieve_body($response);

        $result = array(
            'status' => "success",
            'data'   => json_decode($body),
        );
    }

    return $result;
}

if (isset($_POST['submit']) && isset($_POST['name'])) {

    $result = create_quiz($_POST['name']);

    if($result['status'] == 'success') {
            echo "<div class=\"notice notice-success is-dismissible\">
                <p>Quzi został dodany!</p>
            </div>";
    } else {
        echo "<div class=\"notice notice-error is-dismissible\">
                <p>{$result['data']}</p>
            </div>";
    }
} ?>

<div class="wrap">
      <h1>QUIZY</h1>
        <form method="post" action="">
           <div>
            <label for="name">Nazwa:</label>
            <input type="text" name="name" />
</div>
<div>
            <input type="submit" name="submit" value="Dodaj Quiz" />
            </div>
        </form>
</div>

<?php
include_once(plugin_dir_path(__FILE__) . 'quizes-table.php');
?>