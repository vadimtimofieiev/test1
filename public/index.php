<?php
const STUDENTS = array(
    ['id'=>1, 'name'=>'Anton', 'school_board_id'=>1],
    ['id'=>2, 'name'=>'Anastasia', 'school_board_id'=>1],
    ['id'=>3, 'name'=>'Vladislav', 'school_board_id'=>1],
    ['id'=>4, 'name'=>'Viktoria', 'school_board_id'=>2],
    ['id'=>5, 'name'=>'Nikolay', 'school_board_id'=>2],
    ['id'=>6, 'name'=>'Katerina', 'school_board_id'=>2]
);

const GRADES = array(
    ['student_id'=>1, 'grade'=>9],
    ['student_id'=>1, 'grade'=>6],
    ['student_id'=>1, 'grade'=>3],
    ['student_id'=>2, 'grade'=>8],
    ['student_id'=>2, 'grade'=>6],
    ['student_id'=>3, 'grade'=>4],
    ['student_id'=>3, 'grade'=>2],
    ['student_id'=>3, 'grade'=>1],
    ['student_id'=>3, 'grade'=>1],
    ['student_id'=>4, 'grade'=>5],
    ['student_id'=>5, 'grade'=>10],
    ['student_id'=>5, 'grade'=>10],
    ['student_id'=>6, 'grade'=>1],
    ['student_id'=>6, 'grade'=>5],
    ['student_id'=>6, 'grade'=>10],
);

const SCHOOL_BOARDS = array(
    ['id'=>1, 'name'=>'CSM', 'result_format_id'=>1],
    ['id'=>2, 'name'=>'CSMB', 'result_format_id'=>2]
);

const RESULT_FORMATS= array(
    1=>'json',
    2=>'xml'
);

/**
 * @param array $grades
 * @return string
 */
function getCSMResult(array $grades): string
{
    return (getAverage($grades)>=7) ? 'pass' : 'fail';
}

/**
 * @param array $grades
 * @return string
 */
function getCSMBResult(array $grades): string
{
    return (max($grades)>8) ? 'pass' : 'fail';
}

/**
 * @param array $grades
 * @return float
 */
function getAverage(array $grades): float
{
    $sum = 0;
    foreach ($grades as $grade){
        $sum += $grade;
    }
    return count($grades)>0 ? round(($sum/count($grades)),2) : 0;
}

/**
 * @param int $student_id
 * @return array
 */
function getGrades(int $student_id): array
{
    $result = array();
    foreach (GRADES as $grades){
        if($grades['student_id']==$student_id){
            $result[] = $grades['grade'];
        }
    }
    return $result;
}

/**
 * @param int $board_id
 * @return array
 */
function getSchoolBoard(int $board_id): array
{
    foreach (SCHOOL_BOARDS as $board){
        if ($board['id']== $board_id) return $board;
    }
    return array();
}

/**
 * @param array $student
 * @param array $grades
 * @param float $average
 * @param array $board
 * @return array
 */
function getResultByFormat(array $student, array $grades, float $average, array $board): array
{
    switch ($board['result_format_id']){
        case 1: //json
            return array(
                'header' => 'Content-Type: application/json',
                'content' => json_encode(array(
                    'student_id'=>$student['id'],
                    'name'=>$student['name'],
                    'grades'=>$grades,
                    'average'=>$average,
                    'final_result'=> getCSMResult($grades)
                    )
                )
            );
            break;
        case 2:
            $output = "
                <root>
                    <student_id>".$student['id']."</student_id>
                    <name>".$student['name']."</name>
                    <grades>";
            foreach ($grades as $grade){
                $output .="<grade>$grade</grade>";
            }
            $output .= "</grades>
                    <average>".$average."</average>
                    <final_result>".getCSMBResult($grades)."</final_result>
                </root>";
            return array(
                'header' => 'Content-Type: application/xml',
                'content' => $output
            );
            break;
    }
    return array(
        'header' => 'Content-Type: text/html',
        'content' => 'no results'
    );
}

/**
 * @param int $id
 * @return string[]
 */
function result(int $id): array
{
    $students = STUDENTS; //TODO get object
    foreach ($students as $student){
        if($student['id'] != $id) continue;
        $grades = getGrades($student['id']);
        $board = getSchoolBoard($student['school_board_id']);
        $average = getAverage($grades);
        return getResultByFormat($student, $grades,$average,$board);
    }
    return array();
}
if(isset($_GET['student']) and !empty($_GET['student'])){
    $result = result($_GET['student']);
    header($result['header']);
    echo $result['content'];
} else {
    echo 'set student id by "?student=ID" in address';
}
