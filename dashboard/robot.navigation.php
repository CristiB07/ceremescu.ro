<?php
$role=$_SESSION['clearence'];
$function=$_SESSION['function'];
$uid=$_SESSION['uid'];
if ($role=='ADMIN')
{
?>
<li>
    <a href="#"><?php echo $strAdministration?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/admin/siteusers.php"><i class="fas fa-user"></i><?php echo $strUsers?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/admin/siteerrors.php"><i class="fas fa-exclamation-triangle"></i><?php echo $strErrors?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/admin/backupdb.php"><i class="fas fa-save"></i><?php echo $strDatabaseBackup?></a></li>
    </ul>
</li>
<li>
    <a href="#">Importuri ANAF</a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/importurianaf/import_all_bilanturi.php"><i class="fas fa-invoice"></i>Importă bilanțuri</a></li>
        <li><a href="<?php echo $strSiteURL ?>/importurianaf/import_all_date_fiscale.php"><i class="fas fa-exclamation-triangle"></i>Importă date fiscale</a></li>
        <li><a href="<?php echo $strSiteURL ?>/importurianaf/mark_bilant_status.php"><i class="fas fa-save"></i>Status bilanțuri</a></li>
        <li><a href="<?php echo $strSiteURL ?>/importurianaf/mark_fiscale_status.php"><i class="fas fa-save"></i>Status date fiscale</a></li>
    </ul>
</li>
</ul>
<?php
    }
?>
</div>
<div class="top-bar-right text-right">
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/login/logout.php"><i class="fas fa-sign-out-alt fa-xl"></i></a></li>
    </ul>
</div>
</div>
