<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Intrare cont";
include '../header.php';

?>
	    <div class="grid-x grid-margin-x">
		<div class="large-12 medium-12 small-12 cell">
        <form method="POST" action="validate.php">
  <fieldset>
<legend><h3><?php echo $strLoginForm ?></h3></legend>
<?php
If ((isSet($_GET['message'])) AND $_GET['message']=="WP"){
echo "<div class=\"callout alert\">$strWrongCredentials</div>" ;
}?>
<?php
If ((isSet($_GET['message'])) AND $_GET['message']=="NL"){
echo "<div class=\"callout alert\">$strNotLogedIn</div>" ;
}?>		      
	    <div class="grid-x grid-margin-x">
              <div class="large-6 medium-6 small-6 cell" >
                  <label><?php echo $strUserName ?></label>
                <input type="text" id="username" name="username" placeholder="<?php echo $strUserName ?>" />
              </div>
     <div class="large-6 medium-6 small-6 cell" >
      <label><?php echo $strPassword ?></label>
                <input type="password" id="password" name="password" placeholder="<?php echo $strPassword ?>"  />
              </div>   			  		  
              </div>   			  
   
              	    <div class="grid-x grid-margin-x">
		<div class="large-12 medium-12 small-12 cell text-center">			  
<input type="submit" class="button" value="<?php echo $strLogin ?>" /> <a href="inscriere.php" class="button"><?php echo $strAddNewAccount?></a>
</div>
</div>
</fieldset>
</form>
</div>
</div>
<?php
include '../bottom.php';
?>