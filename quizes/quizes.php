<?php
/*
 * Plugin Name: quizes
 */

function quizes_admin_page() {
    add_menu_page('Quizy', 'Quizy', 'manage_options', 'quzies', 'quizes_admin_form');
}

function quizes_admin_form() {
    if(isset($_GET['successMessage'])) {
         echo "<div class=\"notice notice-success is-dismissible\">
                        <p>". $_GET['successMessage']."</p>
                    </div>";
    }
    include_once(plugin_dir_path(__FILE__) . 'question-form.php');
    include_once(plugin_dir_path(__FILE__) . 'quizes-form.php');
}

function get_quiz_for_js($quiz_id) {
    $api_url = 'http://10.0.10.4:6868/quizzes/' . $quiz_id;

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return 'Błąd podczas wykonania zapytania do API: ' . $response->get_error_message();
    }

    $body = wp_remote_retrieve_body($response);

    return json_decode($body);
}

function interactive_quiz($quiz_id) {
    $quiz_output = base_quiz_output();
    if ( is_user_logged_in() ) {
            $quiz_data = get_quiz_for_js($quiz_id);
            $quiz_output .= render_quiz($quiz_data);
        } else {
            $quiz_output .= not_logged_user_banner();
        }

    return $quiz_output;
}

function not_logged_user_banner() {
    return "<div class=\"quiz-container\" id=\"quiz\">
                <div class=\"quiz-header \" id=\"quiz-content\">
                    <h3 id=\"question\">We have quiz for this article, <br/>but you need to be logged in to take the quiz</h2>
                    <a href=\"/login\"><button>Log in</button></a>
                </div>
            </div>";
}

function base_quiz_output() {
    return "<style>
            *, *::before, *::after{
              box-sizing: border-box;
              margin:0;
            }


            .quiz-container{
              background-color: #400E32;
              color:#fff;
              width: 600px;
              border-radius: 5px;
              box-shadow: 0 0 10px 2px rgba(100, 100, 100, 0.1);
              overflow: hidden;
              margin: 10px 0 10px 0;
            }


            .quiz-header {
              padding: 4rem;
            }

            h2, h3 {
              padding: 1rem;
              text-align: center;
              margin: 0;
            }

            h3 {
                margin-bottom: 7px;
            }

            ul {
              list-style-type: none;
              padding: 0;
            }

            ul li {
              font-size: 1.2rem;
              margin: 1rem 0;
            }

            ul li label {
              cursor: pointer;
            }

            button {
              background-color: #A61F69;
              color: #fff;
              border: none;
              display: block;
              width: 100%;
              cursor: pointer;
              font-size: 1.2rem;
              letter-spacing: 2px;
              font-family: inherit;
              padding: 1.3rem;
            }

            button:hover {
              background-color: #851954;
            }

            button:focus {
              outline: none;
              background-color: #851954;
            }

            .hidden {
              display: none;
            }

            </style>";
}

function render_quiz($quiz_data) {
    $question = $quiz_data->questions[0];
    $quiz_title = '<h2 id="question">' . $question->content . '</h2>';

    $quiz_js_data = "const quizData = [";
    foreach ($quiz_data->questions as $question) {
        $answer_a = $question->options[0];
        $answer_b = $question->options[1];
        $answer_c = $question->options[2];
        $answer_d = $question->options[3];

        switch($question->correctAnswer) {
            case $answer_a:
                $correct_answer = 'a';
                break;
            case $answer_b:
                $correct_answer = 'b';
                break;
            case $answer_c:
                $correct_answer = 'c';
                break;
            case $answer_d:
                $correct_answer = 'd';
                break;
        }

        $quiz_js_data .= "{
            question: '$question->content',
            a: '$answer_a',
            b: '$answer_b',
            c: '$answer_c',
            d: '$answer_d',
            correct: '$correct_answer',
        },";
    }

    $quiz_js_data .= "];";

 return "<style>
 *, *::before, *::after{
   box-sizing: border-box;
   margin:0;
 }


 .quiz-container{
   background-color: #400E32;
   color:#fff;
   width: 600px;
   border-radius: 5px;
   box-shadow: 0 0 10px 2px rgba(100, 100, 100, 0.1);
   overflow: hidden;
   margin: 10px 0 10px 0;
 }


 .quiz-header {
   padding: 4rem;
 }

 h2 {
   padding: 1rem;
   text-align: center;
   margin: 0;
 }

 ul {
   list-style-type: none;
   padding: 0;
 }

 ul li {
   font-size: 1.2rem;
   margin: 1rem 0;
 }

 ul li label {
   cursor: pointer;
 }

 button {
   background-color: #A61F69;
   color: #fff;
   border: none;
   display: block;
   width: 100%;
   cursor: pointer;
   font-size: 1.2rem;
   letter-spacing: 2px;
   font-family: inherit;
   padding: 1.3rem;
 }

 button:hover {
   background-color: #851954;
 }

 button:focus {
   outline: none;
   background-color: #851954;
 }

 .hidden {
   display: none;
 }

 </style>
 <div class=\"quiz-container\" id=\"quiz\">
   <div class=\"quiz-header \" id=\"quiz-content\">
     $quiz_title
     <ul>
       <li>
         <input type=\"radio\" name=\"answer\" id=\"a\" class=\"answer\" >
         <label for=\"a\" id=\"a_text\">Answer</label>
       </li>
         <li>
         <input type=\"radio\" name=\"answer\" id=\"b\" class=\"answer\" >
         <label for=\"b\" id=\"b_text\">Answer</label>
       </li>
         <li>
         <input type=\"radio\" name=\"answer\" id=\"c\" class=\"answer\" >
         <label for=\"c\" id=\"c_text\">Answer</label>
       </li>
         <li>
         <input type=\"radio\" name=\"answer\" id=\"d\" class=\"answer\" >
         <label for=\"d\" id=\"d_text\">Answer</label>
       </li>
     </ul>
   </div>
   <div class=\"quiz-header hidden\" id=\"summary_view\"></div>
   <button id=\"submit_button\" class=\"submit_button\">Submit</button>
   <button id=\"reset_button\" class=\"hidden\"> Reload </button>
 </div>
 <script>
   $quiz_js_data

 const quiz = document.getElementById('quiz-content');
 const summaryView = document.getElementById('summary_view');
 const answerEls = document.querySelectorAll('.answer');
 const questionEl = document.getElementById('question');
 const a_text = document.getElementById('a_text');
 const b_text = document.getElementById('b_text');
 const c_text = document.getElementById('c_text');
 const d_text = document.getElementById('d_text');
 const submitBtn = document.getElementById('submit_button');
 const resetButton = document.getElementById('reset_button');

 let currentQuiz = 0;
 let score = 0;

 loadQuiz();

 function loadQuiz(){
   deselectAnswers();

   const currentQuizData = quizData[currentQuiz];
   questionEl.innerText = currentQuizData.question;
   a_text.innerText = currentQuizData.a;
   b_text.innerText = currentQuizData.b;
   c_text.innerText = currentQuizData.c;
   d_text.innerText = currentQuizData.d;

   submitBtn.classList.remove('hidden');
   resetButton.classList.add('hidden');
   summaryView.classList.add('hidden');
   quiz.classList.remove('hidden');

 }

 function   deselectAnswers(){
   answerEls.forEach(answerEl => answerEl.checked = false)
 }

 function getSelected(){
   let answer;
   answerEls.forEach(answerEl => {
     if(answerEl.checked){
       answer = answerEl.id;
     }
   })
    return answer;
 }

 function resetQuiz(){
   currentQuiz = 0;
   score = 0;
   loadQuiz();
 }


 submitBtn.addEventListener('click', ()=> {
   const answer = getSelected();
   const thumbsUp = String.fromCodePoint(0x1F44D);
   const firstPlace = String.fromCodePoint(0x1F947);
   const sadFace = String.fromCodePoint(0x1F61E);
   const maxScore = quizData.length;

   if(answer){
     if(answer === quizData[currentQuiz].correct){
       score++;
     }
     currentQuiz++;
     if(currentQuiz < quizData.length){
       loadQuiz();
     } else {
         let emoji;
         if(score < maxScore/2) {
            emoji = sadFace;
         } else if(score === maxScore){
            emoji = firstPlace;
         } else {
            emoji = thumbsUp;
         }
             summaryView.innerHTML = '<h2>' + emoji + '<br/>You answered ' + score + '/' + maxScore + ' questions correctly<br/>' + emoji + '</h2>';
             submitBtn.classList.add('hidden');
             resetButton.classList.remove('hidden');
             summaryView.classList.remove('hidden');
             quiz.classList.add('hidden');
         }
   }
 })

 resetButton.addEventListener('click', resetQuiz);
 </script>";
}

function awesome_quizz_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'id' => 'brak',
        ),
        $atts,
        'awesome_quizz'
    );

    $id_value = esc_attr($atts['id']);
    $output = interactive_quiz($id_value);

    return $output;
}

add_action('admin_menu', 'quizes_admin_page');
add_shortcode('awesome_quizz', 'awesome_quizz_shortcode');

?>
