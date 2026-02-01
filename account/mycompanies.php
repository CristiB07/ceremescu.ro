<?php
include '../settings.php';
include '../classes/common.php';
$strDescription="Modificare date de facturare";
$strPageTitle="Creare cont MedReport";

if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
include '../dashboard/header.php';
$strPageTitle=$strInvoiceData;
$uid=$_SESSION['uid'];
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$d = date("Y-m-d H:i:s");

If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){
    // Validate cID parameter
    if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
        die('<div class="callout alert">Invalid company ID</div>');
    }
    
    $cID = intval($_GET['cID']);
    
    // Check if company belongs to current user
    $stmt_check = $conn->prepare("SELECT company_id FROM site_companies WHERE company_id=? AND company_siteaccount=?");
    $stmt_check->bind_param("ii", $cID, $uid);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows == 0) {
        $stmt_check->close();
        die('<div class="callout alert">Unauthorized access</div>');
    }
    $stmt_check->close();
    
    // Delete company using prepared statement
    $stmt = $conn->prepare("DELETE FROM site_companies WHERE company_id=?");
    $stmt->bind_param("i", $cID);
    $stmt->execute();
    $stmt->close();
    
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"myprofile.php\"
}
setTimeout('delayer()', 1500);
//-->
</script>";
include '../bottom.php';
exit();}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">

        <h3><?php echo $strInvoiceData?></h3>

        <?php
if ($_SERVER['REQUEST_METHOD'] == 'POST'){

// Validate required fields
if (!isset($_POST['company_name'], $_POST['company_VAT'], $_POST['company_ro'], 
    $_POST['company_reg'], $_POST['company_address'], $_POST['company_city'], $_POST['company_county'])) {
    die('<div class="callout alert">All required fields must be filled!</div>');
}

If ($_GET['mode']=="new"){
//insert new company
    $company_name = trim($_POST["company_name"]);
    $company_VAT = trim($_POST["company_VAT"]);
    $company_ro = trim($_POST["company_ro"]);
    $company_reg = trim($_POST["company_reg"]);
    $company_address = trim($_POST["company_address"]);
    $company_city = trim($_POST["company_city"]);
    $company_county = trim($_POST["company_county"]);
    $company_bank = isset($_POST["company_bank"]) ? trim($_POST["company_bank"]) : '';
    $company_IBAN = isset($_POST["company_IBAN"]) ? trim($_POST["company_IBAN"]) : '';
    
    // Use prepared statement
    $stmt = $conn->prepare("INSERT INTO site_companies(company_name, company_VAT, company_ro, company_reg, company_address, company_city, company_county, company_bank, company_siteaccount, company_IBAN) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssis", $company_name, $company_VAT, $company_ro, $company_reg, $company_address, $company_city, $company_county, $company_bank, $uid, $company_IBAN);
				
//It executes the SQL
if (!$stmt->execute())
  {
  $stmt->close();
  die('Error: ' . $conn->error);
  }
  else {
$stmt->close();
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
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
}// ends else
}//ends new
else
{// edit
    // Validate cID parameter
    if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
        die('<div class="callout alert">Invalid company ID</div>');
    }
    
    $cID = intval($_GET['cID']);
    
    // Check if company belongs to current user
    $stmt_check = $conn->prepare("SELECT company_id FROM site_companies WHERE company_id=? AND company_siteaccount=?");
    $stmt_check->bind_param("ii", $cID, $uid);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows == 0) {
        $stmt_check->close();
        die('<div class="callout alert">Unauthorized access</div>');
    }
    $stmt_check->close();
    
    // Sanitize input
    $company_name = trim($_POST["company_name"]);
    $company_VAT = trim($_POST["company_VAT"]);
    $company_ro = trim($_POST["company_ro"]);
    $company_reg = trim($_POST["company_reg"]);
    $company_address = trim($_POST["company_address"]);
    $company_city = trim($_POST["company_city"]);
    $company_county = trim($_POST["company_county"]);
    $company_bank = isset($_POST["company_bank"]) ? trim($_POST["company_bank"]) : '';
    $company_IBAN = isset($_POST["company_IBAN"]) ? trim($_POST["company_IBAN"]) : '';
    
    // Use prepared statement for UPDATE
    $stmt = $conn->prepare("UPDATE site_companies SET company_name=?, company_VAT=?, company_ro=?, company_reg=?, company_address=?, company_city=?, company_county=?, company_bank=?, company_IBAN=? WHERE company_id=?");
    $stmt->bind_param("sssssssssi", $company_name, $company_VAT, $company_ro, $company_reg, $company_address, $company_city, $company_county, $company_bank, $company_IBAN, $cID);
    
if (!$stmt->execute())
  {
  $stmt->close();
  die('Error: ' . $conn->error);
  }
else{
$stmt->close();
echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
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
} //ends edit
}// ends edit post
}// ends post
else {
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>
        <script>
document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('btn1');
  const cuiInput = document.getElementById('Cui');
  const responseEl = document.getElementById('response');
  const loaderIcon = document.getElementById('loaderIcon');

  // Prevent form submission if button is in a form
  btn.addEventListener('click', function (e) {
    e.preventDefault();

    const cui = (cuiInput?.value || '').trim();
    if (!cui) {
      alert('Introduceți CUI!');
      return;
    }

    if (loaderIcon) loaderIcon.style.display = '';

    const body = new URLSearchParams({ Cui: cui });

    fetch('../common/cui.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'Accept': 'application/json'
      },
      body
    })
    .then(async (r) => {
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      const text = await r.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        console.error('Response is not valid JSON:', text);
        throw new Error('Invalid JSON response');
      }

      try {
        document.getElementById('company_name').value = (data["denumire"] || "").toUpperCase();
        document.getElementById('company_VAT').value = data["cif"] || '';
        document.getElementById('company_ro').value = data["tva"] || '';
        document.getElementById('company_address').value = data["adresa"] || '';
        document.getElementById('company_county').value = (data["judet"] || "").toUpperCase();
        document.getElementById('company_city').value = (data["oras"] || "").toUpperCase();
        document.getElementById('company_reg').value = data["numar_reg_com"] || '';
        if (loaderIcon) loaderIcon.style.display = 'none';
      } catch(err) {
        if (responseEl) responseEl.textContent = err.message;
      }
    })
    .catch((err) => {
      console.error(err);
      alert('Some error occurred!');
    })
    .finally(() => {
      if (loaderIcon) loaderIcon.style.display = 'none';
    });
  });
});
</script>
        <p><a href="myprofile.php" class="button"><?php echo $strBack?></a></p>
        <form method="Post" action="mycompanies.php?mode=new">
            <div class="grid-x grid-padding-x">
                <div class="large-6 medium-6 small-6 cell">
                    <div id="response"></div>
                    <div class="input-group">
                        <span class="input-group-label"><?php echo $strCompanyVAT?></span>
                        <input class="input-group-field" type="text" name="Cui" id="Cui" placeholder="<?php echo $strEnterVATNumber?>">
                        <div class="input-group-button">
                            <button id="btn1" class="button success"><i class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-x grid-padding-x ">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCompany?>
                        <input type="text" name="company_name" id="company_name" value="" required />
                    </label>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strCompanyFA?>
                        <input type="text" name="company_ro" id="company_ro" value="" required />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCompanyVAT?>
                        <input type="text" name="company_VAT" id="company_VAT" value="" required />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCompanyRC?>
                        <input type="text" name="company_reg" id="company_reg" value="" required />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x ">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strAddress?>
                        <input type="text" name="company_address" id="company_address" value="" required />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCity?>
                        <input type="text" name="company_city" id="company_city" value="" required />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCounty?>
                        <input type="text" name="company_county" id="company_county" value="" required />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x ">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strBank?>
                        <input type="text" name="company_bank" id="company_bank" value="" />
                    </label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strCompanyIBAN?>
                        <input type="text" name="company_IBAN" id="company_IBAN" value="" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" class="button" value="<?php echo $strSubmit?>" />
                </div>
            </div>
        </form>
        <?php
}
else {
	?>
        <p><a href="myprofile.php" class="button"><?php echo $strBack?></a></p>
        <form method="post"  action="mycompanies.php?mode=edit&cID=<?php echo htmlspecialchars($_GET['cID'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php
// Validate cID parameter
if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
    die('<div class="callout alert">Invalid company ID</div>');
}

$cID = intval($_GET['cID']);

// Use prepared statement and check ownership
$stmt = $conn->prepare("SELECT * FROM site_companies WHERE company_id=? AND company_siteaccount=?");
$stmt->bind_param("ii", $cID, $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    die('<div class="callout alert">' . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . '</div>');
}

$row = $result->fetch_assoc();
$stmt->close();
?>
            <div class="grid-x grid-padding-x ">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCompany?>
                        <input type="text" name="company_name" id="company_name" value="<?php echo htmlspecialchars($row['company_name'], ENT_QUOTES, 'UTF-8'); ?>" required />
                    </label>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strCompanyFA?>
                        <input type="text" name="company_ro" id="company_ro" value="<?php echo htmlspecialchars($row['company_ro'], ENT_QUOTES, 'UTF-8'); ?>" required />
                    </label>
                </div>
                <div class="large-3 medium-3 small-1 cell">
                    <label><?php echo $strCompanyVAT?>
                        <input type="text" name="company_VAT" id="company_VAT" value="<?php echo htmlspecialchars($row['company_VAT'], ENT_QUOTES, 'UTF-8'); ?>" required />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCompanyRC?>
                        <input type="text" name="company_reg" id="company_reg" value="<?php echo htmlspecialchars($row['company_reg'], ENT_QUOTES, 'UTF-8'); ?>" required />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x ">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strAddress?>
                        <input type="text" name="company_address" id="company_address" value="<?php echo htmlspecialchars($row['company_address'], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCity?>
                        <input type="text" name="company_city" id="company_city" value="<?php echo htmlspecialchars($row['company_city'], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCounty?>
                        <input type="text" name="company_county" id="company_county" value="<?php echo htmlspecialchars($row['company_county'], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x ">
                <div class="large-6 medium-6 small-4 cell">
                    <label><?php echo $strBank?>
                        <input type="text" name="company_bank" id="company_bank" value="<?php echo htmlspecialchars($row['company_bank'], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strCompanyIBAN?>
                        <input type="text" name="company_IBAN" id="company_IBAN" value="<?php echo htmlspecialchars($row['company_IBAN'], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" class="button" value="<?php echo $strSubmit?>" />
                </div>
            </div>
        </form>
        <?php }?>
    </div>
</div>

<?php
 }
include '../bottom.php';
?>