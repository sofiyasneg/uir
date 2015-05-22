<?php
/**
 * Created by PhpStorm.
 * User: Станислав
 * Date: 05.04.15
 * Time: 16:15
 */

namespace App\Http\Controllers;


use App\Test;
use App\Theme;
use Illuminate\Http\Request;
use App\Question;
use App\Codificator;
use View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use PDOStatement;
use Session;
use App\Bruser;

class QuestionController extends Controller{
    private $question;

    function __construct(Question $question){
        $this->question=$question;
    }

    private function setCode(Request $request){  //Установить код вопроса
        $codificator = new Codificator();
        //получаем необходимые данные из формы
        $section = $request->input('section');
        $theme = $request->input('theme');
        $type = $request->input('type');

        //с помощью кодификаторов составляем трехразрядный код вопроса
        $query1 = $codificator->whereCodificator_type('Раздел')->whereValue($section)->select('code')->first();
        $section_code = $query1->code;
        $query2 = $codificator->whereCodificator_type('Тема')->whereValue($theme)->select('code')->first();
        $theme_code = $query2->code;
        $query3 = $codificator->whereCodificator_type('Тип')->whereValue($type)->select('code')->first();
        $type_code = $query3->code;
        $code = $section_code.'.'.$theme_code.'.'.$type_code;

        return $code;
    }

    private function getCode($id){        //декодирование вопроса в асс. массив
        $codificator = new Codificator();
        $question = $this->question;
        $query = $question->whereId_question($id)->select('code')->first();
        $code = $query->code;          //получили код вопроса
        $array = explode('.',$code);
        // print_r($array);
        $query1 = $codificator->whereCodificator_type('Раздел')->whereCode($array[0])->select('value')->first();
        $section = $query1->value;
        $query2 = $codificator->whereCodificator_type('Тема')->whereCode($array[1])->join('themes', 'themes.theme', '=', 'codificators.value')->where('themes.section', '=', $section)->select('value')->first();
        $theme = $query2->value;
        $query3 = $codificator->whereCodificator_type('Тип')->whereCode($array[2])->select('value')->first();
        $type = $query3->value;
        $decode = array('section' => $section, 'theme' => $theme, 'type' => $type,
            'section_code' => $array[0], 'theme_code' => $array[1], 'type_code' => $array[2]);

        return $decode;
    }

    private function randomArray($array){
        $index = rand(0,count($array)-1);     //выбираем случайный вопрос
        $chosen = $array[$index];
        $array[$index]=$array[count($array)-1];
        $array[count($array)-1] = $chosen;
        return $array;                        //получаем тот же массив, где выбранный элемент стоит на последнем месте для удаления
    }

    private function mixVariants($variants){
        $num_var = count($variants);
        $new_variants = [];
        for ($i=0; $i<$num_var; $i++){                        //варианты в случайном порядке
            $variants = $this->randomArray($variants);
            $chosen = array_pop($variants);
            $new_variants[$i] = $chosen;
        }
        return $new_variants;
    }

    private function destruct($id_test){
        $test = new Test();
        $query = $test->whereId_test($id_test)->select('structure')->first();
        $structure = $query->structure;
        $destructured = explode(';', $structure);
        $array = [];
        for ($i=0; $i<count($destructured); $i++){
            $temp_array = explode('-', $destructured[$i]);
            for ($j=0; $j<=1; $j++){
                $array[$i][$j] = $temp_array[$j];
            }
        }
        return $array;
    }

    private function prepareTest($id_test){            //выборка вопросов
        $question = $this->question;
        $array = [];
        $k = 0;
        $temp_array = [];
        $destructured = $this->destruct($id_test);

        for ($i=0; $i<count($destructured); $i++){
            //echo $destructured[$i][1].'<br>';
            $query=$question->where('code', '=', $destructured[$i][1])->get();          //ищем всевозможные коды вопросов
            //$query=$question->where('code', '=', '1.1.1')->get();
            foreach ($query as $id){
                array_push($temp_array,$id->id_question);                               //для каждого кода создаем массив всех вопрососв с этим кодом
            }
            for ($j=0; $j<$destructured[$i][0]; $j++){                                  //и выбираем заданное количество случайных
                $index = rand(0,count($temp_array)-1);
                $choisen = $temp_array[$index];
                $temp_array[$index]=$temp_array[count($temp_array)-1];
                $temp_array[count($temp_array)-1] = $choisen;
                array_pop($temp_array);
                $array[$k] = $choisen;
                $k++;
            }
            $temp_array = [];
        }
        return $array;          //формируем массив из id вошедших в тест вопросов
    }

    private function chooseQuestion($id_test){
        @session_start();
        if (empty($_SESSION['test'.$_SESSION['username']])){                //генерируем тест, если еше не создан
            $array = $this->prepareTest($id_test);
            $ser_array = serialize($array);
            $_SESSION['test'.$_SESSION['username']] = $ser_array;         //в сессии храним массив вопросов
        }
        $ser_array = $_SESSION['test'.$_SESSION['username']];
        $array = unserialize($ser_array);
        if (empty($array)){               //если вопросы кончились, завершаем тест
            unset($_SESSION['test'.$_SESSION['username']]);
            return -1;
        }
        else{
            $index = rand(0,count($array)-1);     //выбираем случайный вопрос
            $choisen = $array[$index];
            $array[$index]=$array[count($array)-1];
            $array[count($array)-1] = $choisen;
            array_pop($array);                   //удаляем его из списка
            $ser_array = serialize($array);
            $_SESSION['test'.$_SESSION['username']] = $ser_array;
            return $choisen;
        }
    }

    private function check($array){       //проверяет правильность вопроса и на выходе дает баллы за вопрос
        $question = $this->question;
        $id = $array[0];
        $query = $question->whereId_question($id)->select('answer','points')->first();
        $answer = $query->answer;
        $points = $query->points;
        $type = $this->getCode($id)['type'];
        if (count($array)==1){           //если не был отмечен ни один вариант
            $score = 0;
            $data = array('mark'=>'Неверно','score'=> $score, 'id' => $id, 'points' => $points);
            return $data;
        }
        for ($i=0; $i < count($array)-1; $i++){                                    //передвигаем массив, чтобы первый элемент оказался последни
            $array[$i] = $array[$i+1];
        }
        array_pop($array);                                             //убираем из входного массива id вопроса, чтобы остались лишь выбранные варианты ответа
        switch($type){
            case 'Выбор одного из списка':                      //Стас
                if ($array[0] == $answer){
                    $score = $points;
                    $data = array('mark'=>'Верно','score'=> $score, 'id' => $id, 'points' => $points);
                }
                else {
                    $score = 0;
                    $data = array('mark'=>'Неверно','score'=> $score, 'id' => $id, 'points' => $points);
                }
                //echo $score.'<br>';
                if ($score != $points){
                    $data = array('mark'=>'Неверно','score'=> $score, 'id' => $id, 'points' => $points);
                }
                //echo $score.'<br>';
                return $data;
                break;

            case 'Выбор нескольких из списка':
                $choices = $array;
                $answers = explode(';', $answer);
                $score = 0;
                $step = $points/count($answers);
                for ($i=0; $i<count($answers); $i++ ){        //сравниваем каждый правильный ответ
                    for ($j=0; $j<count($choices); $j++){      // с каждым выбранным
                        if ($answers[$i] == $choices[$j]){
                            $buf = $choices[$j];
                            $choices[$j] = $choices[count($choices)-1];     //меняем местами правильный ответ с последним для удаления
                            $choices[count($choices)-1] =  $buf;
                            array_pop($choices);                         //удаляем правильный проверенный вариант из массива выбранных ответов
                            $score += $step;
                            break;
                        }
                    }
                }
                if (!(empty($choices))){                    //если выбраны лишние варианты
                    for ($i=0; $i<count($choices); $i++){
                        $score -= $step;
                    }
                }
                if ($score > $points){                    //если при округлении получилось больше максимального числа баллов
                    $score = $points;
                }
                if ($score < 0){                          //если ушел в минус
                    $score = 0;
                }

                if ($score == $points){
                    $data = array('mark'=>'Верно','score'=> $score, 'id' => $id, 'points' => $points);
                }
                else $data = array('mark'=>'Неверно','score'=> $score, 'id' => $id, 'points' => $points);
                //echo $score.'<br>';
                return $data;
                break;

            case 'Текстовый вопрос':                            //Стас
                $question = $this->question;
                $query = $question->whereId_question($id)->select('variants', 'answer', 'points')->first();
                $parse = $query->variants;
                $variants = explode("<>", $parse);
                $parse_answer = $query->answer;
                $answer = explode(";", $parse_answer);
                $points = $query->points;
                $score = 0;
                $step = $points/count($variants);
                for ($i=0; $i < count($variants); $i++){
                    if ($array[$i] == $answer[$i]){
                        $score +=$step;
                    }
                }
                return $score;
                break;

            case 'Таблица соответствий':                        //Миша
                echo 'Вопрос на таблицу соответствий';
                break;

            case 'Да/Нет':                                      //Миша
                $query = $question->whereId_question($id)->select('variants', 'answer', 'points', 'title')->first();
                $count = 0;
                $text_parse = $query->title;
                $parse_answer = $query->answer;
                $answer_parse = explode(";" ,$parse_answer);
                $text = explode(";" , $text_parse);
                for ($i = 0; $i < count($text); $i++){
                    if($answer_parse[$i] == $array[$i]) $count++;
                }
                return $count;
                break;

            case 'Вопрос на вычисление':
                echo 'Вопрос на вычисление';
                break;

            case 'Вопрос на соответствие':
                echo 'Вопрос на соответствие';
                break;

            case 'Вид функции':
                echo 'Вопрос на определение аналитического вида функции';
                break;
        }
    }

    /*public function result(){
        $score = Session::get('score');
        $total = Session::get('num');
        return view('welcome', compact('score', 'total'));
    }*/

    public function index(){
        //Дефолтная страница при разграничении прав
        /*if (Session::has('username')){
             return view('questions.student.index');
        }
        if (Session::has('teachername')){
            return view('questions.teacher.index');
        }*/
        $questions = $this->question->get();
        //dd($questions);
        $username =  null;
        session_start();
        if (!empty($_SESSION['username'])){
            unset($_SESSION['test'.$_SESSION['username']]);
            $username =  $_SESSION['username'];
        }
        return view('questions.teacher.index', compact('questions', 'username'));
    }

    public function form(Request $request){
        $user = new Bruser();
        session_start();
        $username = $request->input('username');
        $query = $user->whereName($username)->select('password')->get();
        foreach ($query as $password){
            $pass = $password->password;
            //echo $pass.'=='.$request->input('password');
            if ($pass == $request->input('password')){
                $_SESSION['username'] = $username;
            }
        }
       if (empty($_SESSION['username'])){
            echo 'Неверный пароль';
        }
        else echo  $_SESSION['username'];
       return redirect()->route('question_index');
    }

    public function enter(){
        return view('questions.student.ty');
    }

    public function create(){             //переход на страницу формы добавления
        return view('questions.teacher.create');
    }

    public function add(Request $request){  //обработка формы добавления
        $code = $this->setCode($request);

        //добавляем вопрос в таблицу
        $question = $this->question;
        $question->insert(array('code' => $code, 'title' => $request->input('title'), 'variants' => $request->input('variants'), 'answer' => $request->input('answer'), 'points' => $request->input('points')));
        return redirect()->route('question_index');
    }

    private function showTest($id_question, $count){  //показать вопрос в тесте
        $question = $this->question;
        //echo $id.'<br>';
        $decode = $this->getCode($id_question);
        $type = $decode['type'];
        $type_code = $decode['type_code'];

        switch($type){
            case 'Выбор одного из списка':                      //Стас
                $query = $question->whereId_question($id_question)->select('title','variants')->first();
                $text = $query->title;
                $parse = $query->variants;
                $variants = explode(";", $parse);
                $new_variants = $this->mixVariants($variants);
                $view = 'tests.show1';
                $array = array('view' => $view, 'arguments' => array('text' => $text, "variants" => $new_variants, "type" => $type_code, "id" => $id_question, "count" => $count));
                return $array;
                break;

            case 'Выбор нескольких из списка':
                $query = $question->whereId_question($id_question)->select('title','variants')->first();
                $text = $query->title;
                $parse = $query->variants;
                $variants = explode(";", $parse);
                $new_variants = $this->mixVariants($variants);
                $view = 'tests.show2';
                $array = array('view' => $view, 'arguments' => array('text' => $text, "variants" => $new_variants, "type" => $type_code, "id" => $id_question, "count" => $count));
                return $array;
                break;

            case 'Текстовый вопрос':                            //Стас
                $query = $question->whereId_question($id_question)->select('title','variants','answer')->first();
                $text = $query->title;
                $text_parts = explode("<>", $text);                         //части текста между селектами
                $parse = $query->variants;
                $variants = explode("<>", $parse);
                $num_slot = count($variants);
                $parse_group_variants = [];
                $group_variants = [];
                $num_var = [];
                for ($i=0; $i < count($variants); $i++){
                    $parse_group_variants[$i] = explode(";",$variants[$i]);                //варинаты каждого селекта
                    $group_variants[$i] = $this->mixVariants($parse_group_variants[$i]);   //перемешиваем варианты
                    $num_var[$i] = count($group_variants[$i]);
                }
                $view = 'tests.show3';
                $array = array('view' => $view, 'arguments' => array('text' => $text, "variants" => $group_variants, "type" => $type_code, "id" => $id_question, "text_parts" => $text_parts, "num_var" => $num_var, "num_slot" => $num_slot, "count" => $count));
                return $array;
                break;

            case 'Таблица соответствий':                        //Миша
                echo 'Вопрос на таблицу соответствий';
                break;

            case 'Да/Нет':                                      //Миша
                $query = $question->whereId_question($id_question)->select('title','answer')->first();
                $text_parse = $query->title;
                $text = explode(";" , $text_parse);
                $view = 'tests.show5';
                $array = array('view' => $view, 'arguments' => array('text' => $text, "type" => $type_code, "id" => $id_question, "count" => $count));
                return $array;
                break;

            case 'Вопрос на вычисление':
                echo 'Вопрос на вычисление';
                break;

            case 'Вопрос на соответствие':
                echo 'Вопрос на соответствие';
                break;

            case 'Вид функции':
                echo 'Вопрос на определение аналитического вида функции';
                break;
        }
    }

    public function showViews($id_test){
        //Session::flush();
        $test = new Test();
        $query = $test->whereId_test($id_test)->select('amount')->first();   //кол-во вопрососв в тесте
        $amount = $query->amount;
        $widgets = [];
        for ($i=0; $i<$amount; $i++){
            $id = $this->chooseQuestion($id_test);
            $data = $this->showTest($id, $i+1);                  //должны получать название view и необходимые параметры
            //print_r (unserialize(Session::get('test')));
            $widgets[] = View::make($data['view'], $data['arguments']);
        }
        $widgetListView = View::make('questions.student.widget_list',compact('amount', 'id_test'))->with('widgets', $widgets);
        return $widgetListView;
    }

    public function checkTest(Request $request){   //обработать ответ на вопрос
        @session_start();
        $amount = $request->input('amount');
        $id_test = $request->input('id_test');
        $test = new Test();
        $query = $test->whereId_test($id_test)->select('total')->first();
        $total = $query->total;
        $score_sum = 0;
        $points_sum = 0;
        $view = [];
        $j = 0;
        for ($i=0; $i<$amount; $i++){        //обрабатываем каждый вопрос
            $data = $request->input($i);
            $array = json_decode($data);
            $data = $this->check($array);
            if ($data['mark'] == 'Неверно'){
                $view[$j]= $data['id'];      //массив неверных вопросов
                $j++;
            }
            $score_sum += $data['score'];
            $points_sum += $data['points'];
        }
        if ($points_sum != 0){
            $score = 100*$score_sum/$points_sum;
        }
        else $score = 100;
        $number_of_wrong = count($view);
        unset($_SESSION['test'.$_SESSION['username']]);
        return view('tests.rybaresults', compact('view','score', 'number_of_wrong'));
        /*print_r($data);
        echo '<br>';
        print_r($array);
        echo '<br><br>';*/
    }

    public function show($id){  //показать вопрос
        $question = $this->question;
        $decode = $this->getCode($id);
        $type = $decode['type'];

        switch($type){
            case 'Выбор одного из списка':                      //Стас
                $query = $question->whereId_question($id)->select('title','variants','answer')->first();
                $text = $query->title;
                $answer = $query->answer;
                $parse = $query->variants;
                $variants = explode(";", $parse);
                //$field = $question->whereId_question($id)->select('title')->first();
                return view('questions.student.show1', compact('text','variants','answer','type','num'));
                break;

            case 'Выбор нескольких из списка':
                $query = $question->whereId_question($id)->select('title','variants','answer', 'points')->first();
                $text = $query->title;
                $answer = $query->answer;
                $points = $query->points;
                $parse = $query->variants;
                $variants = explode(";", $parse);
                $new_variants = $this->mixVariants($variants);            //перемешиваем варианты
                //$field = $question->whereId_question($id)->select('title')->first();
                return view('questions.student.show2', compact('text','new_variants','answer','type', 'points'));
                break;

            case 'Текстовый вопрос':                            //Стас
                $query = $question->whereId_question($id)->select('title','variants','answer')->first();
                $text = $query->title;
                $text_parts = explode("<>", $text);                         //части текста между селектами
                $answer = $query->answer;
                $parse = $query->variants;
                $variants = explode("<>", $parse);
                $num_slot = count($variants);
                $parse_group_variants = [];
                $group_variants = [];
                $num_var = [];
                for ($i=0; $i < count($variants); $i++){
                    $parse_group_variants[$i] = explode(";",$variants[$i]);                //варинаты каждого селекта
                    $group_variants[$i] = $this->mixVariants($parse_group_variants[$i]);   //перемешиваем варианты
                    $num_var[$i] = count($group_variants[$i]);
                }
                return view('questions.student.show3', compact('text_parts','group_variants','answer','type','num_var','num_slot','id'));
                break;

            case 'Таблица соответствий':                        //Миша
                echo 'Вопрос на таблицу соответствий';
                break;

            case 'Да/Нет':                                      //Миша
                $query = $question->whereId_question($id)->select('title','answer')->first();
                $text_parse = $query->title;
                $text = explode(";" , $text_parse);
                $view = 'tests.show5';
                $array = array('view' => $view, 'arguments' => array('text' => $text, "id" => $id, ));
                return view('tests.show5' , compact('text','answer'));
                break;

            case 'Вопрос на вычисление':
                echo 'Вопрос на вычисление';
                break;

            case 'Вопрос на соответствие':
                echo 'Вопрос на соответствие';
                break;

            case 'Вид фунции':
                echo 'Вопрос на определение аналитического вида функции';
                break;
        }
    }

    public function checks(Request $request){   //обработать ответ на вопрос
        switch($request->input('type')){
            case 'Выбор одного из списка':                      //Стас
                if ($request->input('choice') == $request->input('answer')){
                    echo 'Верно <br><br>';
                    echo link_to_route('question_index', 'Вернуться к списку вопросов');
                    //header('Refresh: 3; URL=http://localhost/uir/public/questions');     //поменять время ожидания
                }
                else echo 'Неверно<br><br>';
                echo link_to_route('question_index', 'Вернуться к списку вопросов');
                break;

            case 'Выбор нескольких из списка':
                $answer = $request->input('answer');
                $choices = ($request->input('choice'));
                $points = ($request->input('points'));
                // echo $points.'<br>';
                $answers = explode(';', $answer);
                $score = 0;
                $step = $points/count($answers);
                //echo $step.'<br>';
                for ($i=0; $i<count($answers); $i++ ){        //сравниваем каждый правильный ответ
                    for ($j=0; $j<count($choices); $j++){      // с каждым выбранным
                        // echo $answers[$i].'=='.$choices[$j].'<br>';
                        if ($answers[$i] == $choices[$j]){
                            $buf = $choices[$j];
                            $choices[$j] = $choices[count($choices)-1];     //меняем местами правильный ответ с последним для удаления
                            $choices[count($choices)-1] =  $buf;
                            array_pop($choices);                         //удаляем правильный проверенный вариант из массива выбранных ответов
                            $score += $step;
                            break;
                        }
                    }
                }
                //echo $counter.'=='.count($answers).'<br>';
                //echo $broken.'<br>';
                if (!(empty($choices))){                    //если выбраны лишние варианты
                    for ($i=0; $i<count($choices); $i++){
                        return 'Неверно';
                    }
                }
                if ($score > $points){                    //если при округлении получилось больше максимального числа баллов
                    $score = $points;
                    return 'Верно';
                }
                if ($score < 0){                          //если ушел в минус
                    $score = 0;
                    return 'Неверно';
                }
                if ($score == $points){
                    return 'Верно';
                }
                else return 'Неверно';
                break;

            case 'Текстовый вопрос':                            //Стас
                $question = $this->question;
                $id = $request->input('id');
                $query = $question->whereId_question($id)->select('variants', 'answer', 'points')->first();
                $parse = $query->variants;
                $variants = explode("<>", $parse);
                $parse_answer = $query->answer;
                $answer = explode(";", $parse_answer);
                $points = $query->points;
                $score = 0;
                $step = $points/count($variants);
                for ($i=0; $i < count($variants); $i++){
                    //echo $request->input($i).' = '.$answer[$i].'<br>';
                    if ($request->input($i) == $answer[$i]){
                        $score +=$step;
                    }
                }
                echo 'Вы верно выбрали '.$score.' из '.$points.' вариантов!';
                break;

            case 'Таблица соответствий':                        //Миша
                break;

            case 'Да/Нет':                                      //Миша
                echo 'Вопрос выбора да или нет';
                break;

            case 'Вопрос на вычисление':
                echo 'Вопрос на вычисление';
                break;

            case 'Вопрос на соответствие':
                echo 'Вопрос на соответствие';
                break;

            case 'Вид фунции':
                echo 'Вопрос на определение аналитического вида функции';
                break;
        }
    }

    public function killSession(){
        Session::flush();
        return redirect()->route('question_index');
    }
} 