<?php
namespace App\Http\Controllers;
use App\Classwork;
use App\Control_test_dictionary;
use App\Controls;
use App\Lectures;
use App\Seminars;
use App\Testing\Test;
use App\Totalresults;
use App\Statements_progress;
use App\TeacherHasGroup;
use App\Pass_plan;
use App\Testing\Result;
use App\Group;
use App\User;
use Auth;
use Illuminate\Http\Request;
use App\Question;
use App\Codificator;
use Session;

class StatementsController extends Controller{

    //Возвращает главную страницу для выбора типа ведомости и группы
    public function statements(){
        $user = Auth::user();
        $groups = TeacherHasGroup::where('user_id', $user['id'])->join('groups', 'groups.group_id', '=', 'teacher_has_group.group')->get();
        $group_set = Group::get();
        return view('personal_account/statements', compact('groups', 'user', 'group_set'));
    }

    //показывает личный кабинет студента, вкладку со статистикой
    public function showPersonalAccount()
    {
        $user = Auth::user();
        if (($user['role'] == 'Админ') or ($user['role'] == 'Преподаватель')) {
            return AdministrationController::getAdminPanel();
        } else {
            if ($user['role'] == 'Студент')
            {
                return $this->showStudentInfo();

            }
            else{
                return PersonalAccount::showTestResults();
            }
        }
    }

    //показывает личный кабинет студента, вкладку со статистикой
    public function showStudentInfo()
    {
        $user = Auth::user();
        $this->calc_first(0, $user['id'], false);
        $this->calc_second(0, $user['id'], false);
        $this->calc_third(0, $user['id'], false);
        $this->calc_fourth(0, $user['id'], false);
        $this->calc_term(0, $user['id'], false);
        $this->calc_final(0, $user['id'], false);
        $lectures = Lectures::where('userID', $user['id'])->first();
        $seminars = Seminars::where('userID', $user['id'])->first();
        $classwork = Classwork::where('userID', $user['id'])->first();
        $controls = Controls::where('userID', $user['id'])->first();
        $results = Totalresults::where('userID', $user['id'])->first();
        $progress = Statements_progress::where('userID', $user['id'])->first();
        $dictionary = Control_test_dictionary::where('id', $user['id'])->first();
        $plan = Pass_plan::where('group', $user['group'])->first();
        $test1 = Result::where('id_test', $dictionary['test1'])->where('id', $user['id'])->orderBy('id_result', 'desc')->first();
        $test2 = Result::where('id_test', $dictionary['test2'])->where('id', $user['id'])->orderBy('id_result', 'desc')->first();
        $test3 = Result::where('id_test', $dictionary['test3'])->where('id', $user['id'])->orderBy('id_result', 'desc')->first();
        return view('personal_account/student_account', compact('lectures', 'seminars', 'classwork', 'controls', 'results', 'progress', 'test1', 'test2', 'test3', 'plan'));
    }

    public function manage_plan(Request $request){
        $user = Auth::user();
        if($user['role'] == 'Админ') {
            $plans = Pass_plan::join('groups', 'groups.group_id', '=', 'pass_plan.group')->get();
        } else {
            $plans = TeacherHasGroup::join('pass_plan', 'teacher_has_group.group', '=', 'pass_plan.group')->join('groups', 'groups.group_id', '=', 'pass_plan.group')->where('teacher_has_group.user_id', $user['id'])->get();
        }
        return view('personal_account/manage_plan', compact('plans'));
    }

    public function plan_is(Request $request){
        $column = $request->input('column');
        $group = $request->input('group');
        Pass_plan::where('group', $group)->update([$column => 1]);
        return 0;
    }
    public function plan_is_not(Request $request){
        $column = $request->input('column');
        $group = $request->input('group');
        Pass_plan::where('group', $group)->update([$column => 0]);
        return 0;
    }



    //Возвращают представление соответствующей ведомости
    public function get_lectures(Request $request){
        $group = json_decode($request->input('group'),true);
        $statement = Lectures::where('statements_lectures.group', $group)->join('users', 'statements_lectures.userID', '=', 'users.id')->join('groups', 'groups.group_id', '=', 'statements_lectures.group')->orderBy('users.last_name', 'asc')->distinct()->get();
        $last_names = [];
        $first_names = [];
        foreach ($statement as $state){
            $user = User::whereId($state->userID)->get();
            array_push($last_names, $user[0]->last_name);
            array_push($first_names, $user[0]->first_name);
        }
        return view('personal_account/statements/lectures', compact('statement', 'first_names', 'last_names', 'group'));
    }
    public function get_seminars(Request $request){
        $group = json_decode($request->input('group'),true);
        $statement = Seminars::where('seminars.group', $group)->join('users', 'seminars.userID', '=', 'users.id')->join('groups', 'groups.group_id', '=', 'seminars.group')->orderBy('users.last_name', 'asc')->distinct()->get();
        $last_names = [];
        $first_names = [];
        foreach ($statement as $state){
            $user = User::whereId($state->userID)->get();
            array_push($last_names, $user[0]->last_name);
            array_push($first_names, $user[0]->first_name);
        }
        return view('personal_account/statements/seminars', compact('statement', 'first_names', 'last_names', 'group'));
    }
    public function get_classwork(Request $request){
        $group = json_decode($request->input('group'),true);
        $statement = Classwork::where('classwork.group', $group)->join('users', 'classwork.userID', '=', 'users.id')->join('groups', 'groups.group_id', '=', 'classwork.group')->orderBy('users.last_name', 'asc')->distinct()->get();
        $last_names = [];
        $first_names = [];
        foreach ($statement as $state){
            $user = User::whereId($state->userID)->get();
            array_push($last_names, $user[0]->last_name);
            array_push($first_names, $user[0]->first_name);
        }
        return view('personal_account/statements/classwork', compact('statement', 'first_names', 'last_names'));
    }
    public function get_controls(Request $request){
        $group = json_decode($request->input('group'),true);
        $statement = Controls::where('controls.group', $group)->join('users', 'controls.userID', '=', 'users.id')->join('groups', 'groups.group_id', '=', 'controls.group')->orderBy('users.last_name', 'asc')->distinct()->get();
        $last_names = [];
        $first_names = [];
        foreach ($statement as $state){
            $user = User::whereId($state->userID)->get();
            array_push($last_names, $user[0]->last_name);
            array_push($first_names, $user[0]->first_name);
        }
        return view('personal_account/statements/control', compact('statement', 'first_names', 'last_names'));
    }
    public function get_resulting(Request $request){
        $group = json_decode($request->input('group'),true);
        $this->calc_first($group, 0, true);
        $this->calc_second($group, 0, true);
        $this->calc_third($group, 0, true);
        $this->calc_fourth($group, 0, true);
        $this->calc_term($group, 0, true);
        $this->calc_final($group, 0, true);
        $statement =Totalresults::where('totalresults.group', $group)->join('users', 'totalresults.userID', '=', 'users.id')->join('groups', 'groups.group_id', '=', 'totalresults.group')->orderBy('users.last_name', 'asc')->distinct()->get();
//        $progress =Statements_progress::whereGroup($group)->get();
        $last_names = [];
        $first_names = [];
        $progress1 = [];
        $progress2 = [];
        $progress3 = [];
        $progress4 = [];
        foreach ($statement as $state){
            $user = User::whereId($state->userID)->get();
            $p = Statements_progress::where('userID', $state->userID)->get();
            array_push($last_names, $user[0]->last_name);
            array_push($first_names, $user[0]->first_name);
            array_push($progress1, $p[0]->section1);
            array_push($progress2, $p[0]->section2);
            array_push($progress3, $p[0]->section3);
            array_push($progress4, $p[0]->section4);
        }
        return view('personal_account/statements/results', compact('statement', 'first_names', 'last_names', 'progress1', 'progress2', 'progress3', 'progress4'));
    }


    //Отмечает или раз-отмечает студента на лекции
    public function lecture_was(Request $request){
        $id = json_decode($request->input('id'),true);
        $column = $request->input('column');
        Lectures::where('userID', $id)->update([$column => 1]);
        return 0;
    }
    public function lecture_wasnot(Request $request){
        $id = json_decode($request->input('id'),true);
        $column = $request->input('column');
        Lectures::where('userID', $id)->update([$column => 0]);
        return 0;
    }

    //NEW 05.09.2016
    public function lecture_was_all(Request $request){
        $column = $request->input('column');
        $group = json_decode($request->input('group'),true);
        Lectures::where('group', $group)->update([$column => 1]);
        return 0;
    }
    public function seminar_was_all(Request $request){
        $column = $request->input('column');
        $group = json_decode($request->input('group'),true);
        Seminars::where('group', $group)->update([$column => 1]);
        return 0;
    }

    //Отмечает или раз-отмечает студента на семинаре
    public function seminar_was(Request $request){
        $id = json_decode($request->input('id'),true);
        $column = $request->input('column');
        Seminars::where('userID', $id)->update([$column => 1]);
        return 0;
    }
    public function seminar_wasnot(Request $request){
        $id = json_decode($request->input('id'),true);
        $column = $request->input('column');
        Seminars::where('userID', $id)->update([$column => 0]);
        return 0;
    }
    //Изменяет балл студента за работу на семинаре
    public function classwork_change(Request $request){
        $id = json_decode($request->input('id'),true);
        $column = $request->input('column');
        $value = $request->input('value');
        Classwork::where('userID', $id)->update([$column => $value]);
        return 0;
    }

    //Изменяет балл студента за контрольную работу
    public function controls_change(Request $request){
        $id = json_decode($request->input('id'),true);
        $column = $request->input('column');
        $value = $request->input('value');
        Controls::where('userID', $id)->update([$column => $value]);
        return 0;
    }

    //Изменяет балл студента в итоговой ведомости
    public function resulting_change(Request $request){
        $id = json_decode($request->input('id'),true);
        $column = $request->input('column');
        $value = $request->input('value');
        Totalresults::where('userID', $id)->update([$column => $value]);
        return 0;
    }

    //метод, подсчитывающий итоги за первый раздел
    //1 раздел = КР1 + КР2 + Тест1Авт + Тест1Письм + 7 недель(каждый тип кроме работы в классе делится на 7)
    public function calc_first($group, $user_id, $flag){
        //инициализация констант: коэффициентов и макс. балла
        $k_lec = 7;
        $k_sem = 7;
        $max_score = 22;
        //проверка флага (если true, то подсчет будет проводится для группы, иначе для одного пользователя)
        if ($flag == true) {
            $results = Totalresults::whereGroup($group)->get();
        }
        else {
            $results = Totalresults::where('userID', $user_id)->get();
        }
        $id = 0;
        foreach ($results as $result){
            $score = 0;
            $id = $result['userID'];
            //поск необходимых записей в таблицах
            $control = Controls::where('userID', $id)->first();
            $lecture = Lectures::where('userID', $id)->first();
            $seminar = Seminars::where('userID', $id)->first();
            $classwork = Classwork::where('userID', $id)->first();
            //подсчет балла
            $score += $control['control1'] + $control['control2'] + $control['test1'] + $control['test1quiz'];
            $score += ($lecture['col1'] + $lecture['col2'] + $lecture['col3'] + $lecture['col4'] + $lecture['col5'] + $lecture['col6'] + $lecture['col7']) / $k_lec;
            $score += $classwork['col1'] + $classwork['col2'] + $classwork['col3'] + $classwork['col4'] + $classwork['col5'] + $classwork['col6'] + $classwork['col7'];
            $score += ($seminar['col1'] + $seminar['col2'] + $seminar['col3'] + $seminar['col4'] + $seminar['col5'] + $seminar['col6'] + $seminar['col7']) / $k_sem;
            //проверка на превышение максимального балла
            if ($score > $max_score) $score = $max_score;
            //запись результата
            Totalresults::where('userID', $id)->update(['section1' => round($score)]);
            //отмечаем в програссе, преодолели ли проходной балл
            if ($control['control1'] >= 4.2){
                Statements_progress::where('userID', $id)->update(['control1' => 1]);
            }
            else {
                Statements_progress::where('userID', $id)->update(['control1' => 0]);
            }
            if ($control['control2'] >= 4.2){
                Statements_progress::where('userID', $id)->update(['control2' => 1]);
            }
            else {
                Statements_progress::where('userID', $id)->update(['control2' => 0]);
            }
            if ($control['test1'] >= 0.6){
                Statements_progress::where('userID', $id)->update(['test1' => 1]);
            }
            else {
                Statements_progress::where('userID', $id)->update(['test1' => 0]);
            }
            if ($control['test1quiz'] >= 2.4){
                Statements_progress::where('userID', $id)->update(['test1quiz' => 1]);
            }
            else {
                Statements_progress::where('userID', $id)->update(['test1quiz' => 0]);
            }
            if (($control['test1'] + $control['test1quiz'] >= 3) and ($control['control2'] >= 4.2) and ($control['control1'] >= 4.2) and (round($score) >= 13)){
                Statements_progress::where('userID', $id)->update(['section1' => 1]);
            }
            else {
                Statements_progress::where('userID', $id)->update(['section1' => 0]);
            }
        }
        return 0;
    }
    //метод, подсчитывающий итоги за второй раздел
    //2 раздел = Тест2Авт + Тест2Письм + 4 недели(каждый тип кроме работы в классе делится на 4)
    public function calc_second($group, $user_id, $flag){
        //инициализация констант: коэффициентов и макс. балла
        $k_lec = 4;
        $k_sem = 4;
        $max_score = 12;
        //проверка флага (если true, то подсчет будет проводится для группы, иначе для одного пользователя)
        if ($flag == true) {
            $results = Totalresults::whereGroup($group)->get();
        }
        else {
            $results = Totalresults::where('userID', $user_id)->get();
        }
        $id = 0;
        foreach ($results as $result){
            $score = 0;
            $id = $result['userID'];
            //поск необходимых записей в таблицах
            $control = Controls::where('userID', $id)->first();
            $lecture = Lectures::where('userID', $id)->first();
            $seminar = Seminars::where('userID', $id)->first();
            $classwork = Classwork::where('userID', $id)->first();
            //подсчет балла
            $score += $control['test2'] + $control['test2quiz'];
            $score += ($lecture['col8'] + $lecture['col9'] + $lecture['col10'] + $lecture['col11']) / $k_lec;
            $score += $classwork['col8'] + $classwork['col9'] + $classwork['col10'] + $classwork['col11'];
            $score += ($seminar['col8'] + $seminar['col9'] + $seminar['col10'] + $seminar['col11']) / $k_sem;
            //проверка на превышение максимального балла
            if ($score > $max_score) $score = $max_score;
            //запись результата
            Totalresults::where('userID', $id)->update(['section2' => round($score)]);
            //отмечаем в програссе, преодолели ли проходной балл
            if ($control['test2'] >= 3.6){
                Statements_progress::where('userID', $id)->update(['test2' => 1]);
            }
            else {
                Statements_progress::where('userID', $id)->update(['test2' => 0]);
            }
            if ($control['test2quiz'] >= 1.8){
                Statements_progress::where('userID', $id)->update(['test2quiz' => 1]);
            }
            else {
                Statements_progress::where('userID', $id)->update(['test2quiz' => 0]);
            }
            if (($control['test2'] + $control['test2quiz'] >= 5.4) and (round($score) >= 7)){
                Statements_progress::where('userID', $id)->update(['section2' => 1]);
            }
            else {
                Statements_progress::where('userID', $id)->update(['section2' => 0]);
            }
        }
        return 0;
    }
    //метод, подсчитывающий итоги за третий раздел
    //3 раздел = КР3Эмуляторы + КР3Письм + Тест3Авт + Тест3Письм + 4 недели(каждый тип кроме работы в классе делится на 4)
    public function calc_third($group, $user_id, $flag){
        //инициализация констант: коэффициентов и макс. балла
        $k_lec = 4;
        $k_sem = 4;
        $max_score = 16;
        //проверка флага (если true, то подсчет будет проводится для группы, иначе для одного пользователя)
        if ($flag == true) {
            $results = Totalresults::whereGroup($group)->get();
        }
        else {
            $results = Totalresults::where('userID', $user_id)->get();
        }        $id = 0;
        foreach ($results as $result){
            $score = 0;
            $id = $result['userID'];
            //поск необходимых записей в таблицах
            $control = Controls::where('userID', $id)->first();
            $lecture = Lectures::where('userID', $id)->first();
            $seminar = Seminars::where('userID', $id)->first();
            $classwork = Classwork::where('userID', $id)->first();
            //подсчет балла
            $score += $control['control3'] + $control['control3quiz'] + $control['test3'] + $control['test3quiz'];
            $score += ($lecture['col12'] + $lecture['col13'] + $lecture['col14'] + $lecture['col15']) / $k_lec;
            $score += $classwork['col12'] + $classwork['col13'] + $classwork['col14'] + $classwork['col15'];
            $score += ($seminar['col12'] + $seminar['col13'] + $seminar['col14'] + $seminar['col15']) / $k_sem;
            //проверка на превышение максимального балла
            if ($score > $max_score) $score = $max_score;
            //запись результата
            Totalresults::where('userID', $id)->update(['section3' => round($score)]);
            //отмечаем в програссе, преодолели ли проходной балл
            if ($control['control3'] >= 2.4){
                Statements_progress::where('userID', $id)->update(['control3' => 1]);
            }
            else {
                Statements_progress::where('userID', $id)->update(['control3' => 0]);
            }
            if ($control['control3quiz'] >= 1.8){
                Statements_progress::where('userID', $id)->update(['control3quiz' => 1]);
            }
            else {
                Statements_progress::where('userID', $id)->update(['control3quiz' => 0]);
            }
            if ($control['test3'] >= 1.8){
                Statements_progress::where('userID', $id)->update(['test3' => 1]);
            }
            else {
                Statements_progress::where('userID', $id)->update(['test3' => 0]);
            }
            if ($control['test3quiz'] >= 1.8){
                Statements_progress::where('userID', $id)->update(['test3quiz' => 1]);
            }
            else {
                Statements_progress::where('userID', $id)->update(['test3quiz' => 0]);
            }
            if (($control['control3'] >= 2.4) and ($control['control3quiz'] >= 1.8) and ($control['test3'] + $control['test3quiz'] >= 3.6) and (round($score) >= 10)){
                Statements_progress::where('userID', $id)->update(['section3' => 1]);
            }
            else {
                Statements_progress::where('userID', $id)->update(['section3' => 0]);
            }
        }
        return 0;
    }
    //метод, подсчитывающий итоги за четвертый раздел
    //4 раздел = Опрос + 1 неделя
    public function calc_fourth($group, $user_id, $flag){
        //инициализация констант: макс. балла
        $max_score = 10;
        //проверка флага (если true, то подсчет будет проводится для группы, иначе для одного пользователя)
        if ($flag == true) {
            $results = Totalresults::whereGroup($group)->get();
        }
        else {
            $results = Totalresults::where('userID', $user_id)->get();
        }
        $id = 0;
        foreach ($results as $result){
            $score = 0;
            $id = $result['userID'];
            //поск необходимых записей в таблицах
            $control = Controls::where('userID', $id)->first();
//            $lecture = Lectures::where('userID', $id)->first();
//            $seminar = Seminars::where('userID', $id)->first();
//            $classwork = Classwork::where('userID', $id)->first();
            //подсчет балла
            $score += $control['lastquiz'];
//            $score += $lecture['col16'];
//            $score += $classwork['col16'];
//            $score += $seminar['col16'];
            //проверка на превышение максимального балла
            if ($score > $max_score) $score = $max_score;
            //запись результата
            Totalresults::where('userID', $id)->update(['section4' => round($score)]);
            if ($control['lastquiz'] >= 6){
                $progress = Statements_progress::where('userID', $id)->update(['lastquiz' => 1]);
            }
            else {
                $progress = Statements_progress::where('userID', $id)->update(['lastquiz' => 0]);
            }
            if ($control['lastquiz'] >= 6){
                $progress = Statements_progress::where('userID', $id)->update(['section4' => 1]);
            }
            else {
                $progress = Statements_progress::where('userID', $id)->update(['section4' => 0]);
            }
        }
        return 0;
    }
    //метод, подсчитывающий итоги работу в семестре
    //Итог за семестр = разде1 + раздел2 + раздел3 + раздел4
    public function calc_term($group, $user_id, $flag){
        //инициализация констант: макс. балла
        $max_score = 60;
        //проверка флага (если true, то подсчет будет проводится для группы, иначе для одного пользователя)
        if ($flag == true) {
            $results = Totalresults::whereGroup($group)->get();
        }
        else {
            $results = Totalresults::where('userID', $user_id)->get();
        }
        foreach ($results as $result){
            $score = 0;
            //поск необходимых записей в таблицах
            $id = $result['userID'];
            //подсчет балла
            $score += $result['section1'] + $result['section2'] + $result['section3'] + $result['section4'];
            //проверка на превышение максимального балла
            if ($score > $max_score) $score = $max_score;
            //запись результата
            Totalresults::where('userID', $id)->update(['termResult' => round($score)]);
        }
        return 0;
    }
    //метод, подсчитывающий итоги за весь раздел
    //Итог  = ИтогЗаСеместр + Экзамен
    public function calc_final($group, $user_id, $flag){
        //проверка флага (если true, то подсчет будет проводится для группы, иначе для одного пользователя)
        if ($flag == true) {
            $results = Totalresults::whereGroup($group)->get();
        }
        else {
            $results = Totalresults::where('userID', $user_id)->get();
        }
        $max_score = 100;
        foreach ($results as $result){
            $score = 0;
            //поск необходимых записей в таблицах
            $id = $result['userID'];
            //подсчет балла и оценок
            $score += $result['termResult'] + $result['exam'];
            if ($score > $max_score) $score = $max_score;
            $markRU = Test::calcMarkRus(100, $score);
            $markEU = Test::calcMarkBologna(100, $score);
            //запись результата
            Totalresults::where('userID', $id)->update(['finalResult' => round($score), 'markRU' => $markRU, 'markEU' => $markEU]);
        }
        return 0;
    }
}