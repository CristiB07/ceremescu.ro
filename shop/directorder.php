<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
$url="shop/selectcase.php";
$strKeywords="Comandă pachete de servicii IT, soluții IT pentru afaceri, consultanță IT, mentenanță IT, dezvoltare software, securitate cibernetică, CRM, facturare, management proiecte, automatizare forță de vânzări";
$strDescription="Pagina finalizare a comenzii". $strSiteName;
$strPageTitle="Trimite comanda";
include '../header.php';

// Validare și sanitizare input
if (!isset($_SESSION['buyer']) || empty($_SESSION['buyer'])) {
    header("Location: $strSiteURL/page.php");
    exit;
}

$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata = $yn . "-" . $m . "-" . $i . " 00:00:00";
$buyer = $_SESSION['buyer'];

echo '<div class="row"><div class="large-12 columns">';
echo '<h1>' . htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8') . '</h1>';

if (isset($_GET['oID']) && is_numeric($_GET['oID'])) {
    $oID = (int)$_GET['oID'];
    
    echo '<table width="100%">';
    echo '<thead>';
    echo '<th width="50%">' . htmlspecialchars($strProduct, ENT_QUOTES, 'UTF-8') . '</th>';
    echo '<th width="10%">' . htmlspecialchars($strProductPrice, ENT_QUOTES, 'UTF-8') . '</th>';
    echo '<th width="20%">' . htmlspecialchars($strQuantity, ENT_QUOTES, 'UTF-8') . '</th>';
    echo '<th width="10%">' . htmlspecialchars($strTotalPrice, ENT_QUOTES, 'UTF-8') . '</th>';
    echo '<th width="10%">' . htmlspecialchars($strVAT, ENT_QUOTES, 'UTF-8') . '</th>';
    echo '</thead>';
    
    // Query comandă cu prepared statement
    $stmt_order = mysqli_prepare($conn, "SELECT * FROM magazin_comenzi WHERE comanda_utilizator=? AND comanda_status=0");
    mysqli_stmt_bind_param($stmt_order, 's', $buyer);
    mysqli_stmt_execute($stmt_order);
    $result_order = mysqli_stmt_get_result($stmt_order);
    $orderr = mysqli_fetch_array($result_order, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt_order);
    
    if (!$orderr) {
        echo '<tr><td colspan="5">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . '</td></tr></table></div></div>';
        include '../bottom.php';
        exit;
    }
    
    $oID = (int)$orderr['comanda_ID'];
    
    // Query articole cu prepared statement
    $stmt_items = mysqli_prepare($conn, "SELECT * FROM magazin_articole WHERE articol_idcomanda=?");
    mysqli_stmt_bind_param($stmt_items, 'i', $oID);
    mysqli_stmt_execute($stmt_items);
    $result_items = mysqli_stmt_get_result($stmt_items);
    $items_array = [];
    while ($item_temp = mysqli_fetch_array($result_items, MYSQLI_ASSOC)) {
        $items_array[] = $item_temp;
    }
    mysqli_stmt_close($stmt_items);
    
    $ordertotal = 0;
    
    foreach ($items_array as $rowi) {
        $articol_produs = (int)$rowi['articol_produs'];
        
        // Query produs cu prepared statement
        $stmt_prod = mysqli_prepare($conn, "SELECT * FROM magazin_produse WHERE produs_id=?");
        mysqli_stmt_bind_param($stmt_prod, 'i', $articol_produs);
        mysqli_stmt_execute($stmt_prod);
        $result_prod = mysqli_stmt_get_result($stmt_prod);
        $row = mysqli_fetch_array($result_prod, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt_prod);
        if ($row["produs_dpret"] !== '0.0000') {
            $unitprice = $row['produs_dpret'];
        } else {
            $unitprice = $row['produs_pret'];
        }
        
        $vatrat = $row["produs_tva"] / 100;
        $vatprc = $vatrat + 1;
        $quantity = (int)$rowi['articol_cantitate'];
        $totalprice = $unitprice * $quantity;
        $ordertotal = $ordertotal + $totalprice;
        $VAT = $totalprice * $vatrat;
        
        $articol_id = (int)$rowi['articol_id'];
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['produs_nume'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td align="right">' . htmlspecialchars(romanize($unitprice), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td align="right">' . htmlspecialchars($quantity, ENT_QUOTES, 'UTF-8') . ' &nbsp;';
        echo '<a href="item.php?id=' . $articol_id . '&action=add"><i class="fas fa-plus"></i></a> ';
        echo '<a href="item.php?id=' . $articol_id . '&action=decrease"><i class="fas fa-minus"></i></a> ';
        echo '<a href="item.php?id=' . $articol_id . '&action=delete"><i class="far fa-trash-alt"></i></a>';
        echo '</td>';
        echo '<td align="right">' . htmlspecialchars(romanize($totalprice), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td align="right">' . htmlspecialchars(romanize($VAT), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '</tr>';
    }
    $totalinterim = $ordertotal * $vatprc;
    $totalVAT = $ordertotal * $vatrat;
    $totalorder = $ordertotal;
    
    if ($paidtransport == "1") {
        if ($totalinterim <= $transportlimit) {
            $transportVAT = $transportprice * $transportvatrat;
            echo '<tr><td colspan="3">' . htmlspecialchars($strTransport, ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td align="right">' . htmlspecialchars(romanize($transportprice), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td align="right">' . htmlspecialchars(romanize($transportVAT), ENT_QUOTES, 'UTF-8') . '</td></tr>';
            $totalorder = $ordertotal + $transportprice;
            $orderVAT = $ordertotal * $vatrat;
            $totalVAT = $orderVAT + $transportVAT;
        }
    }
    $finalprice = $totalorder + $totalVAT;
    
    echo '<tr><td colspan="3">' . htmlspecialchars($strTotals, ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td align="right">' . htmlspecialchars(romanize($totalorder), ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td align="right">' . htmlspecialchars(romanize($totalVAT), ENT_QUOTES, 'UTF-8') . '</td></tr>';
    echo '<tr><td colspan="4">' . htmlspecialchars($strTotal, ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td align="right">' . htmlspecialchars(romanize($finalprice), ENT_QUOTES, 'UTF-8') . '</td></tr></table>';
		?>
</div>
</div>

<div class="large-12 columns" role="content">
    <h3><?php echo $strInvoiceData ?></h3>
    <div class="grid-x grid-padding-x">
        <div class="large-12 cell">
            <div class="callout alert"><?php echo $strDisclaimerCompanies?></div>
        </div>
    </div>
    <script type="text/javascript">
        // Script 1: CUI Lookup (Vanilla JavaScript)
        document.addEventListener('DOMContentLoaded', function() {
            const btn1 = document.getElementById('btn1');
            
            if (btn1) {
                btn1.addEventListener('click', function() {
                    const cuiInput = document.getElementById('Cui');
                    const cuiValue = cuiInput ? cuiInput.value : '';
                    
                    fetch('../common/cui.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'Cui=' + encodeURIComponent(cuiValue)
                    })
                    .then(response => response.json())
                    .then(data => {
                        try {
                            const denumireInput = document.getElementById('factura_client_denumire');
                            const cifInput = document.getElementById('factura_client_CIF');
                            const roInput = document.getElementById('factura_client_RO');
                            const adresaInput = document.getElementById('factura_client_adresa');
                            const judetInput = document.getElementById('factura_client_judet');
                            const localitateInput = document.getElementById('factura_client_localitate');
                            const rcInput = document.getElementById('factura_client_RC');
                            const loaderIcon = document.getElementById('loaderIcon');
                            
                            if (denumireInput) denumireInput.value = (data.denumire || '').toUpperCase();
                            if (cifInput) cifInput.value = data.cif || '';
                            if (roInput) roInput.value = data.tva || '';
                            if (adresaInput) adresaInput.value = data.adresa || '';
                            if (judetInput) judetInput.value = (data.judet || '').toUpperCase();
                            if (localitateInput) localitateInput.value = (data.oras || '').toUpperCase();
                            if (rcInput) rcInput.value = data.numar_reg_com || '';
                            if (loaderIcon) loaderIcon.style.display = 'none';
                        } catch (err) {
                            const responseEl = document.getElementById('response');
                            if (responseEl) responseEl.innerHTML = err.message;
                        }
                    })
                    .catch(error => {
                        alert('Some error occurred!');
                        console.error('Error:', error);
                    });
                });
            }
        });
    </script>
    <script type="text/javascript">
        // Script 2: City Autocomplete (Vanilla JavaScript)
        document.addEventListener('DOMContentLoaded', function() {
            const searchBox = document.getElementById('search-box');
            const suggestionBox = document.getElementById('suggesstion-box');
            
            if (searchBox) {
                searchBox.addEventListener('keyup', function() {
                    const keyword = searchBox.value;
                    
                    // Set loading background
                    searchBox.style.background = '#FFF url(../img/LoaderIcon.gif) no-repeat 165px';
                    
                    fetch('../common/city_select.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'keyword=' + encodeURIComponent(keyword)
                    })
                    .then(response => response.text())
                    .then(data => {
                        try {
                            if (suggestionBox) {
                                suggestionBox.style.display = 'block';
                                suggestionBox.innerHTML = data;
                            }
                            searchBox.style.background = '#FFF';
                        } catch (err) {
                            const responseEl = document.getElementById('response');
                            if (responseEl) responseEl.innerHTML = err.message;
                        }
                    })
                    .catch(error => {
                        alert('Some error occurred!');
                        console.error('Error:', error);
                    });
                });
            }
        });

        function selectCity(val) {
            const split_str = val.split(' - ');
            const searchBox = document.getElementById('search-box');
            const judetInput = document.getElementById('judet');
            const suggestionBox = document.getElementById('suggesstion-box');
            
            if (searchBox) searchBox.value = split_str[0];
            if (judetInput) judetInput.value = split_str[1];
            if (suggestionBox) suggestionBox.style.display = 'none';
        }
    </script>
    <form method="post" action="senddirectorder.php?oID=<?php echo $oID ?>&action=new">
        <div class="grid-x grid-padding-x">
            <div class="large-6 cell">
                <div id="response"></div>
                <div class="input-group">
                    <span class="input-group-label"><?php echo $strCompanyVAT?></span>
                    <input class="input-group-field" type="text" name="Cui" id="Cui"
                        placeholder="<?php echo $strEnterVATNumber?>">
                    <div class="input-group-button">
                        <button id="btn1" class="button success"><i
                                class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid-x grid-padding-x ">
            <div class="large-4 medium-4 cell">
                <label><?php echo $strClient?></label>
                <input type="text" name="factura_client_denumire" id="factura_client_denumire" value="" />
            </div>
            <div class="large-1 medium-1 cell">
                <label><?php echo $strCompanyFA?></label>
                <input type="text" name="factura_client_RO" id="factura_client_RO" value="" />
            </div>
            <div class="large-3 medium-3 cell">
                <label><?php echo $strCompanyVAT?></label>
                <input type="text" name="factura_client_CIF" id="factura_client_CIF" value="" />
            </div>
            <div class="large-4 medium-4 cell">
                <label><?php echo $strCompanyRC?></label>
                <input type="text" name="factura_client_RC" id="factura_client_RC" value="" />
            </div>
        </div>
        <div class="grid-x grid-padding-x ">
            <div class="large-4 medium-4 cell">
                <label><?php echo $strAddress?></label>
                <input type="text" name="factura_client_adresa" id="factura_client_adresa" value="" />
            </div>
            <div class="large-4 medium-4 cell">
                <label><?php echo $strCity?></label>
                <input type="text" name="factura_client_localitate" id="factura_client_localitate" value="" />
            </div>
            <div class="large-4 medium-4 cell">
                <label><?php echo $strCounty?></label>
                <input type="text" name="factura_client_judet" id="factura_client_judet" value="" />
            </div>
        </div>
        <div class="grid-x grid-padding-x ">
            <div class="large-6 medium-6 cell">
                <label><?php echo $strBank?></label>
                <input type="text" name="factura_client_banca" id="factura_client_banca" value="" />
            </div>
            <div class="large-6 medium-6 cell">
                <label><?php echo $strCompanyIBAN?></label>
                <input type="text" name="factura_client_IBAN" id="factura_client_IBAN" value="" />
            </div>
        </div>
        <div class="grid-x grid-padding-x">
            <div class="large-12 cell">
                <h3><?php echo $strCompanyRepresentative?></h3>
            </div>
        </div>
        <div class="grid-x grid-padding-x">
            <div class="large-2 cell">
                <label><?php echo $strFirstName?></label>
                <input name="cumparator_prenume" type="text" size="30" required />
            </div>
            <div class="large-2 cell">
                <label><?php echo $strLastName?></label>
                <input name="cumparator_nume" type="text" size="30" required />
            </div>
            <div class="large-2 cell">
                <label><?php echo $strEmail?></label>
                <input name="cumparator_email" type="email" size="30" required />
            </div>
            <div class="large-2 cell">
                <label><?php echo $strPhone?></label>
                <input name="cumparator_telefon" type="text" size="30" required />
            </div>
            <div class="large-2 cell">
                <label><?php echo $strCity?></label>
                <input type="text" name="cumparator_oras" id="search-box" placeholder="<?php echo $strCity?>" />
                <div id="suggesstion-box" class="suggesstion-box"></div>
            </div>
            <div class="large-2 cell">
                <label><?php echo $strCounty?></label>
                <input type="text" name="cumparator_judet" id="judet" placeholder="<?php echo $strCounty?>" />

            </div>
        </div>
        <div class="grid-x grid-padding-x">
            <div class="large-12 cell">
                <label><?php echo $strAddress?></label>
                <input name="cumparator_adresa" type="text" size="30" />
            </div>
        </div>

        <div class="grid-x grid-padding-x">
            <div class="large-12 cell"> Am citit şi sunt de acord cu <a href="termeni.php"
                    title="Termeni şi condiţii de utilizare">Termenii şi condiţiile de utilizare</a>

                <input type="checkbox" id="strAcord"
                    onclick="javascript:document.getElementById('btn_submit').style.display='block'; javascript:document.getElementById('strAcord').style.display='none';" />
            </div>
        </div>
        <div class="grid-x grid-padding-x">
            <div class="large-12 medium-12 small-12 cell text-center">
                <input type="submit" id="btn_submit" class="button" style="display:none;"
                    value="<?php echo $strSubmit?>" />
            </div>
        </div>
    </form>

</div>
<?php
} else {
    echo '<div data-alert class="alert-box warning round">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . '<a href="#" class="close">&times;</a></div>';
    include('../bottom.php');
    exit;
}

echo '</div></div><hr />';
include '../bottom.php';
?>