<?php
//create 8.01.2025
include '../settings.php';
include '../classes/common.php';
$url="shop/selectcase.php";
$strKeywords="Comandă pachete de servicii IT, soluții IT pentru afaceri, consultanță IT, mentenanță IT, dezvoltare software, securitate cibernetică, CRM, facturare, management proiecte, automatizare forță de vânzări";
$strDescription="Pagina de selectare mod comandă " . $strSiteName;
$strPageTitle="Alege modul de comandă";
include '../header.php';

// Validare și sanitizare input
if (!isset($_SESSION['buyer']) || empty($_SESSION['buyer'])) {
    header("Location: $strSiteURL/page.php");
    exit;
}

$buyer = $_SESSION['buyer'];

// Verificăm dacă există o comandă activă
$stmt_order = mysqli_prepare($conn, "SELECT comanda_ID FROM magazin_comenzi WHERE comanda_utilizator=? AND comanda_status=0");
mysqli_stmt_bind_param($stmt_order, 's', $buyer);
mysqli_stmt_execute($stmt_order);
$result_order = mysqli_stmt_get_result($stmt_order);
$order = mysqli_fetch_array($result_order, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_order);

if (!$order) {
    echo '<div class="row"><div class="large-12 columns">';
    echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . '</div>';
    echo '</div></div>';
    include '../bottom.php';
    exit;
}

$oID = (int)$order['comanda_ID'];
?>

<div class="row">
    <div class="large-12 columns">
        <h1><?php echo htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p>Alege cum dorești să finalizezi comanda:</p>
    </div>
</div>

<div class="row">
    <div class="large-6 medium-6 small-12 columns">
        <div class="callout" style="text-align: center; padding: 40px 20px;">
            <h3><i class="fas fa-user"></i> Cu cont</h3>
            <p>Autentifică-te sau creează un cont pentru a finaliza comanda mai rapid și a avea acces la istoricul comenzilor.</p>
            
            <?php if (isset($_SESSION['userlogedin']) && $_SESSION['userlogedin'] == "Yes"): ?>
                <!-- Utilizator autentificat -->
                <a href="sendorder.php?oID=<?php echo $oID; ?>" class="button large success expanded">
                    <i class="fas fa-shopping-cart"></i> Finalizează comanda
                </a>
            <?php else: ?>
                <!-- Utilizator neautentificat -->
                <a href="../account/index.php?redirect=<?php echo urlencode('shop/sendorder.php?oID=' . $oID); ?>" class="button large primary expanded">
                    <i class="fas fa-sign-in-alt"></i> Autentificare
                </a>
                <a href="../account/createaccount.php?redirect=<?php echo urlencode('shop/sendorder.php?oID=' . $oID); ?>" class="button large secondary expanded">
                    <i class="fas fa-user-plus"></i> Creează cont
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="large-6 medium-6 small-12 columns">
        <div class="callout" style="text-align: center; padding: 40px 20px;">
            <h3><i class="fas fa-shopping-bag"></i> Fără cont</h3>
            <p>Continuă comanda direct fără a crea un cont. Vei completa datele necesare în pagina următoare.</p>
            
            <a href="directorder.php?oID=<?php echo $oID; ?>" class="button large expanded">
                <i class="fas fa-arrow-right"></i> Continuă fără cont
            </a>
        </div>
    </div>
</div>

<?php include '../bottom.php'; ?>
