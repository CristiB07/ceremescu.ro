<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Acces teste";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/account/login.php?message=MLF");
}
?>
    <script src='../js/simple-editor/simple-editor.js'></script>
    <link rel="stylesheet" href='../js/simple-editor/simple-editor.css'>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
$d = date("Y-m-d H:i:s");

include '../classes/paginator.class.php';
$uid=(int)$_SESSION['uid'];

// Sanitize and validate input
if (isSet($_GET["tID"])) {
	$test=(int)$_GET["tID"];
	if ($test <= 0) {
		header("location:$strSiteURL/elearning/student_mycourses.php");
		die();
	}
}
else
{
echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = document.referrer
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

	//check to see if user took the test before
// Use prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_tests_takes WHERE take_stud_id=? AND take_test_id=?");
mysqli_stmt_bind_param($stmt, "ii", $uid, $test);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$numar=mysqli_num_rows($result);
if ($numar==0)
{
	//student didn't took the test we register the test take
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
$code=generateRandomString(10);

// Use prepared statement for INSERT
$stmt_insert = mysqli_prepare($conn, "INSERT INTO elearning_tests_takes(take_stud_id, take_test_id, take_code, take_date) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt_insert, "iiss", $uid, $test, $code, $d);
mysqli_stmt_execute($stmt_insert);
$last_id = mysqli_insert_id($conn);

$arr=$_POST;
$count = count($arr);
foreach ($arr as $key => $value) {
	if (--$count <= 0) {
        break;
    }
    // $arr[3] will be updated with each value from $arr...
   // echo "{$key} => {$value} " ."<br>";


//insert answer - use prepared statement
	$stmt_ans = mysqli_prepare($conn, "INSERT INTO elearning_answers(answer_student, answer_time, answer_question, answer_test, answer_test_take, answer_option) VALUES (?, ?, ?, ?, ?, ?)");
	$key_int = (int)$key;
	$value_clean = mysqli_real_escape_string($conn, $value);
	mysqli_stmt_bind_param($stmt_ans, "isiiss", $uid, $d, $key_int, $test, $last_id, $value_clean);
	mysqli_stmt_execute($stmt_ans);
}

//grade test with prepared statements
$stmt_grade = mysqli_prepare($conn, "SELECT answer_option, answer_ID, quizoption_ID, quizoption_score FROM elearning_answers, elearning_quizoptions WHERE answer_test=? AND quizoption_ID=answer_option");
mysqli_stmt_bind_param($stmt_grade, "i", $test);
mysqli_stmt_execute($stmt_grade);
$result_grade = mysqli_stmt_get_result($stmt_grade);
while ($row=ezpub_fetch_array($result_grade)) {
	$stmt_upd = mysqli_prepare($conn, "UPDATE elearning_answers SET answer_grade=? WHERE answer_option=? AND answer_test_take=?");
	mysqli_stmt_bind_param($stmt_upd, "dii", $row['quizoption_score'], $row['quizoption_ID'], $last_id);
	mysqli_stmt_execute($stmt_upd);
}
$stmt_score = mysqli_prepare($conn, "SELECT SUM(answer_grade) as total_score FROM elearning_answers WHERE answer_test_take=? AND answer_student=?");
mysqli_stmt_bind_param($stmt_score, "ii", $last_id, $uid);
mysqli_stmt_execute($stmt_score);
$scoreresult = mysqli_stmt_get_result($stmt_score);
$scorerow=ezpub_fetch_array($scoreresult);
$stmt_upd_score = mysqli_prepare($conn, "UPDATE elearning_tests_takes SET take_score=? WHERE take_ID=?");
mysqli_stmt_bind_param($stmt_upd_score, "di", $scorerow['total_score'], $last_id);
mysqli_stmt_execute($stmt_upd_score);
//show results
echo "<div class=\"grid-x grid-margin-x\">
    <div class=\"large-12 medium-12 small-12 cell\">
	<a href=\"javascript:history.go(-1)\" class=\"button\"><i class=\"fa fa-backward fa-xl\"></i> $strBack</a>";
echo "<h1>$strTestResults</h1>";
$stmt_results = mysqli_prepare($conn, "SELECT Course_id, course_name, take_stud_id, take_test_id, take_score, take_date, take_observation, take_id, test_id, test_name, test_course, test_description FROM elearning_courses, elearning_tests, elearning_tests_takes WHERE test_id=? AND Course_id=test_course AND take_test_id=test_id AND take_stud_id=?");
mysqli_stmt_bind_param($stmt_results, "ii", $test, $uid);
mysqli_stmt_execute($stmt_results);
$result1 = mysqli_stmt_get_result($stmt_results);
$numar1=mysqli_num_rows($result1);
if ($numar1==0)
{
echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;
}
else {
While ($row1=ezpub_fetch_array($result1)){

$testdate=date('d M Y', strtotime($row1['take_date']));
		
	echo "<p>$strTest".":"." $row1[test_description]</p>";
	echo "$strScore".":"." $row1[take_score]<br />";
	echo "$strDate".":"." $testdate<br />";
	echo "<p>$strObservations".":"." $row1[take_observation]</p>";

$stmt_q = mysqli_prepare($conn, "SELECT question_ID, question_question, question_type, answer_question, answer_grade, answer_option, answer_student, answer_test_take FROM elearning_answers, elearning_questions WHERE answer_question=question_ID AND answer_test=? AND answer_test_take=? AND answer_student=? ORDER BY question_type DESC");
mysqli_stmt_bind_param($stmt_q, "iii", $row1['test_id'], $row1['take_id'], $uid);
mysqli_stmt_execute($stmt_q);
$result2 = mysqli_stmt_get_result($stmt_q);
$numar=mysqli_num_rows($result2);
$grade=0;
while ($row2=ezpub_fetch_array($result2)) {
	$grade=$grade+$row2["answer_grade"];
echo "<p class=\"question\">". " " .strip_tags($row2["question_question"]). "</p>";
if ($row2["answer_grade"]==0){ echo "<div class=\"alert callout\">$strYourScoreIs: $row2[answer_grade].</div>";}
else{ echo "<div class=\"success callout\">$strYourScoreIs: $row2[answer_grade].</div>";};
if ($row2["question_type"]==0)
{
echo "<p>$row2[answer_option]</p>";
}	
else {	
	$stmt_opt = mysqli_prepare($conn, "SELECT * FROM elearning_quizoptions WHERE quizoption_question=? ORDER BY RAND()");
	mysqli_stmt_bind_param($stmt_opt, "i", $row2['question_ID']);
	mysqli_stmt_execute($stmt_opt);
	$result3 = mysqli_stmt_get_result($stmt_opt);
while ($row3=ezpub_fetch_array($result3)) 
{
	$option=strip_tags($row3["quizoption_option"]);
	if ($row3["quizoption_value"]<>0){
		if ($row3["quizoption_ID"]==$row2["answer_option"]) {
echo "<p class=\"correct\"><strong>$option</strong></p>";
	} else
	{
		echo "<p class=\"correct\">$option</p>";
	}}
	else {
		if ($row3["quizoption_ID"]==$row2["answer_option"]) {
			
			echo "<p class=\"wrong\">$option</p>";
		}
		else{
echo "<p>$option</p>";
	}} //ends elses
	} //ends while options
}//ends not open question
} //ends while questions
?>

<div class="success callout">
<h4><?php echo $strYourTotalScoreIs?> - <?php echo $grade?>.</h4>
    </div>
    </div>
    </div>
<?php
} // ends while questions
} // ends else (test results found)
} // ends if POST - register and show result

else // no POST, show test form
{
?>
        <script>
        // Set the countdown time (30 minutes in seconds)
        var countdownTime = 1800;

        // Function to update the timer display
        function updateTimer() {
            var minutes = Math.floor(countdownTime / 60);
            var seconds = countdownTime % 60;
            document.getElementById("timer").innerHTML = minutes + "m " + seconds + "s ";
            countdownTime--;

            // If the countdown is over, submit the form
            if (countdownTime < 0) {
                clearInterval(timerInterval);
                document.forms[0].submit();
            }
        }

        // Update the timer every second
        var timerInterval = setInterval(updateTimer, 1000);
        </script>
        <?php
$stmt_test = mysqli_prepare($conn, "SELECT * FROM elearning_tests WHERE test_ID=?");
mysqli_stmt_bind_param($stmt_test, "i", $test);
mysqli_stmt_execute($stmt_test);
$result = mysqli_stmt_get_result($stmt_test);
if ($row=ezpub_fetch_array($result)) {
?>
        <div class="grid-x grid-margin-x">
            <div class="large-10 medium-10 small-10 cell">
            </div>
            <div class="large-2 medium-2 small-2 callout alert">
                <h1><?php echo $strTimeLeft ?>:
                    <div id="timer">
                        <h1>30m 0s</h1>
                    </div>
                </h1>
            </div>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <h1><?php  echo $row['test_name']?></h1>
            <?php  echo $row['test_description']?>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <?php }?>
            <form id="test" method="POST" action="student_sitetests.php?tID=<?php echo (int)$test?>">
                <?php
$stmt_q2 = mysqli_prepare($conn, "SELECT * FROM elearning_questions WHERE question_test=? ORDER BY RAND()");
mysqli_stmt_bind_param($stmt_q2, "i", $test);
mysqli_stmt_execute($stmt_q2);
$result2 = mysqli_stmt_get_result($stmt_q2);
$numar=mysqli_num_rows($result2);
while ($row2=ezpub_fetch_array($result2)) {
	//$n=1;
	//while ($n <= $numar){
echo "<p class=\"question\">". " " .strip_tags($row2["question_question"]). "</p>";	
if ($row2["question_type"]==0)
{
echo "<textarea name=\"$row2[question_ID]\" class=\"simple-html-editor\"></textarea>";
}	
else {	
	$stmt_opt2 = mysqli_prepare($conn, "SELECT * FROM elearning_quizoptions WHERE quizoption_question=? ORDER BY RAND()");
	mysqli_stmt_bind_param($stmt_opt2, "i", $row2['question_ID']);
	mysqli_stmt_execute($stmt_opt2);
	$result3 = mysqli_stmt_get_result($stmt_opt2);
while ($row3=ezpub_fetch_array($result3)) 
{
	$option=strip_tags($row3["quizoption_option"]);
	if ($row2["question_type"]==1){
echo "<input type=\"radio\" name=\"$row2[question_ID]\" value=\"$row3[quizoption_ID]\"> $option<br>
";
}// ends if row=1
elseif ($row2["question_type"]==2){
echo "<input type=\"checkbox\" name=\"$row2[question_ID]\" value=\"$row3[quizoption_ID]\"> $option<br>
";
}// ends checkbox
	} //ends while options
}//ends not open question
//$n++;}// ends while număr
} //ends while elearning_questions
?>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <INPUT type="submit" class="button" value="<?php echo $strSend?>" name="Submit">
        </div>
    </div>
    </form>
</div>
</div>
<?php 
} // ends else (show test form)
} // ends if ($numar==0) - student didn't take test
else {
    // User already took the test - show error message
    echo "<div class=\"callout alert\">$strYouHaveAlreadyTakenThisTest</div>";
//show results
echo "<div class=\"grid-x grid-margin-x\">
    <div class=\"large-12 medium-12 small-12 cell\">
	<a href=\"javascript:history.go(-2)\" class=\"button\"><i class=\"fa fa-backward fa-xl\"></i> $strBack</a>";  
echo "<h1>$strTestResults</h1>";
$stmt_results2 = mysqli_prepare($conn, "SELECT Course_id, course_name, take_stud_id, take_test_id, take_score, take_date, take_observation, take_id, test_id, test_name, test_course, test_description FROM elearning_courses, elearning_tests, elearning_tests_takes WHERE test_id=? AND Course_id=test_course AND take_test_id=test_id AND take_stud_id=?");
mysqli_stmt_bind_param($stmt_results2, "ii", $test, $uid);
mysqli_stmt_execute($stmt_results2);
$result1 = mysqli_stmt_get_result($stmt_results2);
$numar1=mysqli_num_rows($result1);
if ($numar1==0)
{
echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;
}
else {
While ($row1=ezpub_fetch_array($result1)){

$testdate=date('d M Y', strtotime($row1['take_date']));
		
	echo "<p>$strTest".":"." $row1[test_description]</p>";
	echo "$strScore".":"." $row1[take_score]<br />";
	echo "$strDate".":"." $testdate<br />";
	echo "<p>$strObservations".":"." $row1[take_observation]</p>";

	$stmt_q2 = mysqli_prepare($conn, "SELECT question_ID, question_question, question_type, answer_question, answer_grade, answer_option, answer_student, answer_test_take FROM elearning_answers, elearning_questions WHERE answer_question=question_ID AND answer_test=? AND answer_test_take=? AND answer_student=? ORDER BY question_type DESC");
	mysqli_stmt_bind_param($stmt_q2, "iii", $row1['test_id'], $row1['take_id'], $uid);
	mysqli_stmt_execute($stmt_q2);
	$result2 = mysqli_stmt_get_result($stmt_q2);
	$numar=mysqli_num_rows($result2);
$grade=0;
while ($row2=ezpub_fetch_array($result2)) {
	$grade=$grade+$row2["answer_grade"];
echo "<p class=\"question\">". " " .strip_tags($row2["question_question"]). "</p>";
if ($row2["answer_grade"]==0){ echo "<div class=\"alert callout\">$strYourScoreIs: $row2[answer_grade].</div>";}
else{ echo "<div class=\"success callout\">$strYourScoreIs: $row2[answer_grade].</div>";};
if ($row2["question_type"]==0)
{
echo "<p>$row2[answer_option]</p>";
}	
else {	
	$stmt_opt3 = mysqli_prepare($conn, "SELECT * FROM elearning_quizoptions WHERE quizoption_question=? ORDER BY RAND()");
	mysqli_stmt_bind_param($stmt_opt3, "i", $row2['question_ID']);
	mysqli_stmt_execute($stmt_opt3);
	$result3 = mysqli_stmt_get_result($stmt_opt3);
while ($row3=ezpub_fetch_array($result3)) 
{
	$option=strip_tags($row3["quizoption_option"]);
	if ($row3["quizoption_value"]<>0){
		if ($row3["quizoption_ID"]==$row2["answer_option"]) {
echo "<p class=\"correct\"><strong>$option</strong></p>";
	} else
	{
		echo "<p class=\"correct\">$option</p>";
	}}
	else {
		if ($row3["quizoption_ID"]==$row2["answer_option"]) {
			
			echo "<p class=\"wrong\">$option</p>";
		}
		else{
echo "<p>$option</p>";
	}} //ends elses
	} //ends while options
}//ends not open question
} //ends while questions
?>

<div class="success callout">
<h4><?php echo $strYourTotalScoreIs?> - <?php echo $grade?>.</h4>
    </div>
    </div>
    </div>
<?php
} // ends while questions
} // ends not zero test results
} // ends else user took test
?>
<?php
include '../bottom.php';
?>