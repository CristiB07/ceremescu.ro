<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
$uploaddir = '../data/';
$fileExtensionsAllowed = ['zip', 'rar']; // These will be the only file extensions allowed
$mimeTypesAllowed = ['application/x-zip', 'application/x-rar-compressed']; // These will be the only mime types allowed
$maxFileSizeInBytes = 1024 * 1024 * 1024; // 1024 megabytes


    // Count total files
    $countfiles = count($_FILES['file']['name']);
    $totalFileSize = array_sum($_FILES['file']['size']);
	echo $totalFileSize;
	echo $countfiles;
$errors = [];
// validate the maximum file size
if ($totalFileSize > $maxFileSizeInBytes) {
  $errors[] = 'File must not be greater than ' . number_format($maxFileSizeInBytes) . ' bytes.';
die;
}


// Looping all files
    for($i=0;$i<$countfiles;$i++){
        $filename = $_FILES['file']['name'][$i];
		$fileext = strtolower(pathinfo($_FILES['file']['name'][$i], PATHINFO_EXTENSION));
		// validate the file extension is in our allow list
		if (!in_array($fileext, $fileExtensionsAllowed, strict: true)) {
	$errors[] = 'File must have one of the following extensions: ' . implode(', ', $fileExtensionsAllowed) . '.';
	}
	// validate the file is an allowed mime type based on actual contents
$detectedType = mime_content_type($filename) ?: 'unknown type';
if (!in_array($detectedType, $mimeTypesAllowed, strict: true)) {
  $errors[] = 'File must have one of the following mime types: ' . implode(', ', $mimeTypesAllowed) . '.';
}
	// verify for errors and move the file upon successful validation
if (count($errors) > 0) {
  echo '<h3>Errors</h3>';
  echo '<ul>';
  foreach ($errors as $error) {
    echo '<li>' . $error . '</li>';
  }
  echo '</ul>';

  exit;
}
		        // Upload file
        move_uploaded_file($_FILES['file']['tmp_name'][$i],$uploaddir."/".$filename);
		 
    }
echo "Ați încărcat ".$countfiles." fișiere, având ".totalFileSize." biți.";
}
Else {
?>
<form method="POST" action="file_upload.php" enctype="multipart/form-data">

 <label>Atașează fișierul
    <input type="file" name="file[]" id="file" multiple>
    <input type='submit' name='submit' value='Upload'>
        </label>
</form>
<?php }?>
