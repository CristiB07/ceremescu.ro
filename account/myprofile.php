<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Administrare profil";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/account/login.php?message=MLF");
}
$uid=$_SESSION['uid'];
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">

        <?php
echo "<h1>$strPageTitle</h1>";
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();

// Validate required fields
if (!isset($_POST['account_first_name'], $_POST['account_last_name'], $_POST['account_email'], 
    $_POST['account_phone'], $_POST['account_address'], $_POST['account_city'], $_POST['account_county'])) {
    die('<div class="callout alert">All fields are required!</div>');
}

// Validate email
$email = filter_var(trim($_POST['account_email']), FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('<div class="callout alert">Invalid email format!</div>');
}

// Sanitize inputs
$first_name = trim($_POST["account_first_name"]);
$last_name = trim($_POST["account_last_name"]);
$address = trim($_POST["account_address"]);
$city = trim($_POST["account_city"]);
$county = trim($_POST["account_county"]);
$phone = trim($_POST["account_phone"]);

// Only update password if provided
if (!empty($_POST["account_password"])) {
    // Validate password strength
    if (strlen($_POST["account_password"]) < 8) {
        die('<div class="callout alert">Password must be at least 8 characters!</div>');
    }
    $password = password_hash($_POST["account_password"], PASSWORD_DEFAULT);
    
    // Update with password
    $stmt = $conn->prepare("UPDATE site_accounts SET account_first_name=?, account_city=?, account_county=?, account_last_name=?, account_address=?, account_password=?, account_email=?, account_phone=? WHERE account_id=?");
    $stmt->bind_param("ssssssssi", $first_name, $city, $county, $last_name, $address, $password, $email, $phone, $uid);
} else {
    // Update without password
    $stmt = $conn->prepare("UPDATE site_accounts SET account_first_name=?, account_city=?, account_county=?, account_last_name=?, account_address=?, account_email=?, account_phone=? WHERE account_id=?");
    $stmt->bind_param("sssssssi", $first_name, $city, $county, $last_name, $address, $email, $phone, $uid);
}

if (!$stmt->execute())
  {
  $stmt->close();
  die('Error: ' . $conn->error);
  }
else{
$stmt->close();
echo "<div class=\"callout success\">$strRecordModified</div></div></div>"; ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"myprofile.php\"
}
setTimeout('delayer()', 1500);
//-->
</script>";
include '../bottom.php';
exit();
}
}// ends post
else {
?>
        <script>
document.addEventListener('DOMContentLoaded', function () {
  const searchBox = document.getElementById('search-box');
  const suggestionBox = document.getElementById('suggesstion-box');
  const responseEl = document.getElementById('response');

  if (!searchBox) return;

  function showLoader() {
    searchBox.style.background = '#FFF url(../img/LoaderIcon.gif) no-repeat 165px';
  }
  function hideLoader() {
    searchBox.style.background = '#FFF';
  }

  searchBox.addEventListener('keyup', function (e) {
    const keyword = searchBox.value;

    const body = new URLSearchParams({ keyword });

    showLoader();

    fetch('../common/city_select.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body
    })
    .then(async (r) => {
      if (!r.ok) {
        throw new Error(`HTTP ${r.status}`);
      }
      const data = await r.text();

      try {
        suggestionBox.style.display = '';
        suggestionBox.innerHTML = data;
        hideLoader();
      } catch (err) {
        if (responseEl) {
          responseEl.textContent = err.message;
        } else {
          console.error(err);
        }
        hideLoader();
      }
    })
    .catch((err) => {
      console.error(err);
      alert('Some error occurred!');
      hideLoader();
    });
  });
});

function selectCity(val) {
  const parts = String(val).split(' - ');
  const city = parts[0] || '';
  const judet = parts[1] || '';

  const searchBox = document.getElementById('search-box');
  const judetInput = document.getElementById('judet');
  const suggestionBox = document.getElementById('suggesstion-box');

  if (searchBox) searchBox.value = city;
  if (judetInput) judetInput.value = judet;
  if (suggestionBox) suggestionBox.style.display = 'none';
}
</script>
        <?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
// Use prepared statement
$stmt = $conn->prepare("SELECT * FROM site_accounts WHERE account_id=?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
?>
        <form method="post" Action="myprofile.php?mode=edit&sID=<?php echo htmlspecialchars($uid, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strFirstName?>
                        <input name="account_first_name" type="text" required
                            value="<?php echo htmlspecialchars($row['account_first_name'], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strLastName?>
                        <input name="account_last_name" type="text" required
                            value="<?php echo htmlspecialchars($row['account_last_name'], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strEmail?>
                        <input name="account_email" type="email" required value="<?php echo htmlspecialchars($row['account_email'], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strPhone?>
                        <input name="account_phone" type="text" required value="<?php echo htmlspecialchars($row['account_phone'], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strPassword?>
                        <input name="account_password" id="account_password" type="password" minlength="8"
                            placeholder="Leave blank to keep current password" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strAddress?>
                        <input name="account_address" type="text" required
                            value="<?php echo htmlspecialchars($row['account_address'], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
                <div class="large-2 cell">
                    <label><?php echo $strCity?>
                        <input type="text" name="account_city" id="search-box"
                            value="<?php echo htmlspecialchars($row['account_city'], ENT_QUOTES, 'UTF-8'); ?>" />
                        <div id="suggesstion-box" class="suggesstion-box"></div>
                    </label>
                </div>
                <div class="large-2 cell">
                    <label><?php echo $strCounty?>
                        <input type="text" name="account_county" id="judet"
                            value="<?php echo htmlspecialchars($row['account_county'], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" class="button" Value="<?php echo $strModify?>" name="Submit">
                </div>
            </div>
        </form>
        <?php
}
else
{
	?>
        <ul class="tabs" data-deep-link="true" data-update-history="true" data-deep-link-smudge="true"
            data-deep-link-smudge-delay="500" data-tabs id="deeplinked-tabs">
            <li class="tabs-title is-active"><a href="myprofile.php#panel1"
                    aria-selected="true"><?php echo $strMyProfile?></a></li>
            <li class="tabs-title"><a href="myprofile.php#panel2"><?php echo $strMyCourses?></a></li>
            <li class="tabs-title"><a href="myprofile.php#panel3"><?php echo $strInvoicing?></a></li>
        </ul>
        <div class="tabs-content" data-tabs-content="deeplinked-tabs">
            <div class="tabs-panel is-active" id="panel1">

                <a href="myprofile.php?mode=edit&sID=<?php echo htmlspecialchars($uid, ENT_QUOTES, 'UTF-8'); ?>" class="button"><?php echo $strEdit?>&nbsp;<i
                        class="fas fa-edit"></i></a><br />
                <?php
$stmt = $conn->prepare("SELECT * FROM site_accounts WHERE account_id=?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
$numar = $result->num_rows;
if ($numar==0)
{
echo $strNoRecordsFound;
}
else {
$row = $result->fetch_assoc();
$stmt->close();
?>
                <table width="100%">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>
                                <h4><?php echo $strMyProfile?></h4>
                            </th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
echo "<tr><td>$strName</td><td colspan='2'>" . htmlspecialchars($row['account_first_name'] . ' ' . $row['account_last_name'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
echo "<tr><td>$strEmail</td><td colspan='2'>" . htmlspecialchars($row['account_email'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
echo "<tr><td>$strPhone</td><td colspan='2'>" . htmlspecialchars($row['account_phone'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
echo "<tr><td>$strAddress</td><td colspan='2'>" . htmlspecialchars($row['account_address'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
echo "<tr><td>$strCity</td><td colspan='2'>" . htmlspecialchars($row['account_city'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
echo "</tbody><tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
?>
            </div>
            <div class="tabs-panel" id="panel2">
                <?php
 echo "<a href=\"../elearning/enrollment.php\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a><br/>";
$query="SELECT elearning_enrollments_id, elearning_enrollments_stud_id, elearning_enrollments_course_id, elearning_enrollments_courseschedule_id, elearning_enrollments_date,elearning_enrollments_active, 
account_first_name, account_last_name, account_email, account_phone,
course_ID, course_name, course_price, course_discount, course_url,
schedule_start_date, schedule_end_date, schedule_ID 
FROM elearning_enrollments, site_accounts, elearning_courses, elearning_courseschedules 
WHERE elearning_enrollments.elearning_enrollments_stud_id=site_accounts.account_id AND
elearning_enrollments.elearning_enrollments_stud_id=$uid AND
elearning_courses.course_ID=elearning_enrollments.elearning_enrollments_course_id AND 
elearning_courseschedules.schedule_ID=elearning_enrollments.elearning_enrollments_courseschedule_id";
$result=ezpub_query($conn,$query);
$nume=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $nume;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query2= $query . " ORDER BY elearning_enrollments_date DESC $pages->limit" ;
$result2=ezpub_query($conn,$query2);
if ($nume==0)
{
echo $strNoRecordsFound;
}
else {
?>
                <div class="paginate">
                    <?php
echo $strTotal . " " .$nume." ".$strCourses ;
echo " <br /><br />";
echo $pages->display_pages();
?>
                </div>
                <table width="100%">
                    <thead>
                        <tr>
                            <th><?php echo $strID?></th>
                            <th><?php echo $strCourse?></th>
                            <th><?php echo $strPrice?></th>
                            <th><?php echo $strEnrollmentDate?></th>
                            <th><?php echo $strDetails?></th>
                            <th><?php echo $strActive?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
While ($row=ezpub_fetch_array($result2)){
	$formateddate=date('d-m-Y H:i', strtotime($row["elearning_enrollments_date"]));
    		echo"<tr>
			<td>$row[elearning_enrollments_stud_id]</td>
			<td>$row[course_name]</td>";
			If ($row["course_discount"]=="0") {
			$price=$row["course_price"];}
			else
			{$price=$row["course_discount"];}
		echo "
			<td>$price</td>
			<td>$formateddate</td>
			<td><a href=\"$strSiteURL"."/cursuri/$row[course_url]\"><i class=\"fa fa-book\"  title=\"$strDetails\"></i></td>";
			
if ($row["elearning_enrollments_active"]=='1'){			
echo		"<td><a href=\"mycourse.php?cID=$row[course_ID]&schID=$row[schedule_ID]\"><i class=\"fa fa-unlock\"  title=\"$strActive\"></i></td>";}
else{
echo		"<td><i class=\"fa fa-lock\"  title=\"$strInactive\"></i></td>";}	
echo "</tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
} ?>
            </div>
            <div class="tabs-panel" id="panel3">
                <?php
   echo "<a href=\"mycompanies.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a><br/>";
$stmt = $conn->prepare("SELECT * FROM site_companies WHERE company_siteaccount=?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
$numar = $result->num_rows;
if ($numar==0)
{
echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
$stmt->close();
}
else {
?>
                <table width="100%">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th><?php echo $strCompany?></th>
                            <th><?php echo $strVAT?></th>
                            <th><?php echo $strEdit?></th>
                            <th><?php echo $strDelete?></th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
While ($row=$result->fetch_assoc()){
    		echo"<tr>
			<td>" . htmlspecialchars($row['company_id'], ENT_QUOTES, 'UTF-8') . "</td>
			<td>" . htmlspecialchars($row['company_name'], ENT_QUOTES, 'UTF-8') . "</td>
			<td>" . htmlspecialchars($row['company_VAT'], ENT_QUOTES, 'UTF-8') . "</td>
			<td><a href=\"mycompanies.php?mode=edit&cID=" . htmlspecialchars($row['company_id'], ENT_QUOTES, 'UTF-8') . "\"><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"mycompanies.php?mode=delete&cID=" . htmlspecialchars($row['company_id'], ENT_QUOTES, 'UTF-8') . "\" OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
			</tr>";
}
$stmt->close();
echo "</tbody><tfoot><tr><td></td><td colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
  ?>
            </div>
        </div>

        <?php
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>