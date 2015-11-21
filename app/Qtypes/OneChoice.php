<?php
/**
 * Created by PhpStorm.
 * User: Станислав
 * Date: 30.05.15
 * Time: 13:49
 */
namespace App\Qtypes;
use App\Http\Controllers\QuestionController;
use App\Question;
use Illuminate\Http\Request;
class OneChoice extends QuestionType {
    const type_code = 1;
    function __construct($id_question){
        parent::__construct($id_question);
    }
    public function  create(){
    }
    public  function add(Request $request, $code){
        $variants = $request->input('variants')[0];
        for ($i=1; $i<count($request->input('variants')); $i++){
            $variants = $variants.';'.$request->input('variants')[$i];
        }
        $answer = $request->input('variants')[0];
        Question::insert(array('code' => $code, 'title' => $request->input('title'), 'variants' => $variants, 'answer' => $answer, 'points' => $request->input('points')));
    }
    public function show($count){
        $parse = $this->variants;
        $variants = explode(";", $parse);
        $new_variants = QuestionController::mixVariants($variants);
        $view = 'tests.show1';
        $array = array('view' => $view, 'arguments' => array('text' => $this->text, "variants" => $new_variants, "type" => self::type_code, "id" => $this->id_question, "count" => $count));
        return $array;
    }
    public function check($array){
        if ($array[0] == $this->answer){
            $score = $this->points;
            $data = array('mark'=>'Верно','score'=> $score, 'id' => $this->id_question, 'points' => $this->points, 'choice' => $array[0]);
        }
        else {
            $score = 0;
            $data = array('mark'=>'Неверно','score'=> $score, 'id' => $this->id_question, 'points' => $this->points, 'choice' => $array[0]);
        }
        //echo $score.'<br>';
        if ($score != $this->points){
            $data = array('mark'=>'Неверно','score'=> $score, 'id' => $this->id_question, 'points' => $this->points, 'choice' => $array[0]);
        }
        //echo $score.'<br>';
        return $data;
    }
} 