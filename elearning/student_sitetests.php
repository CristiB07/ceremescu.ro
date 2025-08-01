<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Acces Teste";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
?>
    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
$d = date("Y-m-d H:i:s");

include '../classes/paginator.class.php';
$uid=$_SESSION['uid'];
if (isSet($_GET["tID"])) {
	$test=$_GET["tID"];
}
Else
{
echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = document.referrer
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();

$arr=$_POST;
$count = count($arr);
foreach ($arr as $key => $value) {
	if (--$count <= 0) {
        break;
    }
    // $arr[3] will be updated with each value from $arr...
   // echo "{$key} => {$value} " ."<br>";


//insert answer

	$mSQL = "INSERT INTO elearning_answers(";
	$mSQL = $mSQL . "answer_student,";
	$mSQL = $mSQL . "answer_time,";
	$mSQL = $mSQL . "answer_question,";
	$mSQL = $mSQL . "answer_option)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$uid . "', ";
	$mSQL = $mSQL . "'" .$d. "', ";
	$mSQL = $mSQL . "'" .$key . "', ";
	$mSQL = $mSQL . "'" .$value ."')";

ezpub_query($conn,$mSQL); }

echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteelearning_tests.php?tID=$_GET[tID]&q=[qID]\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";

include '../bottom.php';
die;

}
Else //show test form
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
$query="SELECT * FROM elearning_tests WHERE test_ID=$_GET[tID]";
$result=ezpub_query($conn, $query);
if ($row=ezpub_fetch_array($result)) {
?>
    <div class="grid-x grid-margin-x">
			  <div class="large-10 medium-10 small-10 cell">
</div>
<div class="large-2 medium-2 small-2 callout alert"><h1><?php echo $strTimeLeft ?>: 
<div id="timer"><h1>30m 0s</h1></div>
</h1></div>
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
<form id="test" method="POST" action="student_sitetests.php?tID=<?php echo $test?>">
<?php
$query2="SELECT * FROM elearning_questions WHERE question_test=$test ORDER BY RAND()";
$result2=ezpub_query($conn, $query2);
$numar=ezpub_num_rows($result2,$query2);
while ($row2=ezpub_fetch_array($result2)) {
	//$n=1;
	//while ($n <= $numar){
echo "<p class=\"question\">". " " .strip_tags($row2["question_question"]). "</p>";	
if ($row2["question_type"]==0)
{
echo "<textarea name=\"$row2[question_ID]\" style=\"width:100%;   height: 200;\" id=\"plustextarea\"></textarea>";
}	
Else {	
	$query3="SELECT * FROM elearning_quizoptions WHERE quizoption_question=$row2[question_ID] ORDER BY RAND()";
$result3=ezpub_query($conn, $query3);
while ($row3=ezpub_fetch_array($result3)) 
{
	$option=strip_tags($row3["quizoption_option"]);
	if ($row2["question_type"]==1){
echo "<input type=\"radio\" name=\"$row2[question_ID]\" value=\"$row3[quizoption_ID]\"> $option<br>
";
}// ends if row=1
Elseif ($row2["question_type"]==2){
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
<INPUT Type="submit" class="button" Value="<?php echo $strSend?>" name="Submit"> 	
</div>
</div>
</form>
</div>
</div>
<?php } // end if post?>
<?php
include '../bottom.php';
?>