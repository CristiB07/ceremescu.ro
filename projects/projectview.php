<?php
$strPageTitle = "Vizualizare proiect";
include '../settings.php';
include '../classes/common.php';
if(!isset($_SESSION)) { session_start(); }
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("location:$strSiteURL/login/siteprojects.php?message=MLF");
    die;
}
$role = $_SESSION['clearence'];
$uid = $_SESSION['uid'];
$lang = isset($_SESSION['$lang']) ? $_SESSION['$lang'] : 'RO';
if ($lang=="RO") { include '../lang/language_RO.php'; } else { include '../lang/language_EN.php'; }
include '../dashboard/header.php';
// ...vizualizare detalii proiect...
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <h1><?php echo $strPageTitle?></h1>
        <!-- Gantt Chart -->
        <canvas id="ganttChart" height="120"></canvas>
            <!-- Chart.js trebuie încărcat înainte de pluginuri -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php
            // Preluare date proiect pentru axa X
            $projectStart = null;
            $projectEnd = null;
            $ganttData = [];
            if (isset($_GET['id'])) {
                $projectID = (int)$_GET['id'];
                // Date proiect pentru axa X
                $stmt = mysqli_prepare($conn, "SELECT proiect_data_inceput, proiect_data_sfarsit FROM proiecte WHERE proiect_id=?");
                mysqli_stmt_bind_param($stmt, 'i', $projectID);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                mysqli_stmt_close($stmt);
                $projectStart = $row['proiect_data_inceput'] ? date('c', strtotime($row['proiect_data_inceput'])) : null;
                $projectEnd = $row['proiect_data_sfarsit'] ? date('c', strtotime($row['proiect_data_sfarsit'])) : null;
                // Activități
                $q = ezpub_query($conn, "SELECT activitate_titlu, activitate_data_inceput, activitate_data_sfarsit FROM proiecte_activitati WHERE activitate_proiect_id='$projectID' ORDER BY activitate_data_inceput ASC");
                while ($act = ezpub_fetch_array($q)) {
                    $ganttData[] = [
                        'label' => $act['activitate_titlu'],
                        'start' => $act['activitate_data_inceput'] ? date('c', strtotime($act['activitate_data_inceput'])) : null,
                        'end' => $act['activitate_data_sfarsit'] ? date('c', strtotime($act['activitate_data_sfarsit'])) : null
                    ];
                }
            }
            ?>
            const ganttData = <?php echo json_encode($ganttData); ?>;
            const projectStart = <?php echo $projectStart ? json_encode($projectStart) : 'null'; ?>;
            const projectEnd = <?php echo $projectEnd ? json_encode($projectEnd) : 'null'; ?>;
            if (ganttData.length > 0 && projectStart && projectEnd) {
                // Sortăm activitățile după data de început
                ganttData.sort((a, b) => (a.start > b.start ? 1 : -1));
                // Axa Y: titlurile activităților (category)
                const yLabels = ganttData.map(a => a.label);
                // Pentru fiecare activitate, calculăm durata în zile
                const durationArr = ganttData.map(a => {
                    if (a.start && a.end) {
                        const d1 = new Date(a.start);
                        const d2 = new Date(a.end);
                        // Durata în zile, inclusiv dacă start == end (minim 1 zi)
                        return Math.max(1, Math.round((d2-d1)/(1000*60*60*24))+1);
                    }
                    return 1;
                });
                // Dataset 1: "padding" invizibil până la data de început
                const padArr = ganttData.map((a, idx) => {
                    // Zile de la începutul proiectului până la start activitate
                    if (a.start && projectStart) {
                        const d1 = new Date(projectStart);
                        const d2 = new Date(a.start);
                        return (d2-d1)/(1000*60*60*24);
                    }
                    return 0;
                });
                // Dataset 2: durata efectivă
                try {
                    const ctx = document.getElementById('ganttChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: yLabels,
                            datasets: [
                                {
                                    label: 'Start',
                                    data: padArr,
                                    backgroundColor: 'rgba(0,0,0,0)',
                                    borderWidth: 0,
                                    stack: 'gantt',
                                },
                                {
                                    label: 'Activitate',
                                    data: durationArr,
                                    backgroundColor: 'rgba(60,120,200,0.7)',
                                    borderColor: 'rgba(60,120,200,1)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    stack: 'gantt',
                                    // fără datalabels
                                }
                            ]
                        },
                        options: {
                            indexAxis: 'y',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const idx = context.dataIndex;
                                            const a = ganttData[idx];
                                            let label = a.label + ' (';
                                            if (a.start) label += new Date(a.start).toLocaleDateString();
                                            if (a.end) label += ' - ' + new Date(a.end).toLocaleDateString();
                                            label += ')';
                                            return label;
                                        }
                                    }
                                },
                                // fără datalabels
                            },
                            scales: {
                                x: {
                                    type: 'linear',
                                    min: 0,
                                    max: Math.ceil((new Date(projectEnd)-new Date(projectStart))/(1000*60*60*24))+1,
                                    title: { display: true, text: 'Ziua proiectului' },
                                    ticks: {
                                        callback: function(value) {
                                            // Afișează data calendaristică pentru fiecare zi
                                            const d = new Date(projectStart);
                                            d.setDate(d.getDate() + value);
                                            return d.toLocaleDateString();
                                        }
                                    }
                                },
                                y: {
                                    type: 'category',
                                    labels: yLabels,
                                    title: { display: true, text: 'Activitate' }
                                }
                            },
                            responsive: true,
                            maintainAspectRatio: true
                        },
                        // plugins: [] // fără ChartDataLabels
                    });
                } catch (e) {
                    console.error('Chart.js error:', e);
                    const chartDiv = document.getElementById('ganttChart');
                    if (chartDiv) {
                        chartDiv.parentNode.insertAdjacentHTML('beforeend', '<div class="callout alert">Eroare la generarea diagramei: ' + e.message + '</div>');
                    }
                }
            } else {
                // Fallback dacă nu există activități sau date proiect
                const chartDiv = document.getElementById('ganttChart');
                if (chartDiv) {
                    chartDiv.parentNode.insertAdjacentHTML('beforeend', '<div class="callout warning">Nu există activități sau date proiect pentru Gantt.</div>');
                }
            }
        });
        </script>
        <?php
        // VIZUALIZARE DETALII PROIECT
if (isset(($_GET['id']))) {
    $projectID = (int)$_GET['id'];
    // Detalii proiect
    $stmt = mysqli_prepare($conn, "SELECT p.*, c.Client_Denumire FROM proiecte p LEFT JOIN clienti_date c ON p.proiect_client=c.ID_Client WHERE p.proiect_id=?");
    mysqli_stmt_bind_param($stmt, 'i', $projectID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $proiect = mysqli_fetch_array($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    echo '<div class="grid-x grid-margin-x"><div class="large-12 cell">';
    echo '<h2>Detalii proiect</h2>';
    echo '<table class="unstriped"><tbody>';
    echo '<tr><th>Titlu</th><td>'.htmlspecialchars($proiect['proiect_titlu']).'</td></tr>';
    echo '<tr><th>Cod</th><td>'.htmlspecialchars($proiect['proiect_cod']).'</td></tr>';
    echo '<tr><th>Client</th><td>'.htmlspecialchars($proiect['Client_Denumire']).'</td></tr>';
    echo '<tr><th>Data început</th><td>'.($proiect['proiect_data_inceput'] ? date('d.m.Y', strtotime($proiect['proiect_data_inceput'])) : '').'</td></tr>';
    echo '<tr><th>Data sfârșit</th><td>'.($proiect['proiect_data_sfarsit'] ? date('d.m.Y', strtotime($proiect['proiect_data_sfarsit'])) : '').'</td></tr>';
    echo '<tr><th>Status</th><td>'.($proiect['proiect_status']==0 ? 'Deschis' : ($proiect['proiect_status']==1 ? 'Închis' : 'On hold')).'</td></tr>';
    echo '<tr><th>Importanță</th><td>'.htmlspecialchars($proiect['proiect_importanta']).'</td></tr>';
    echo '<tr><th>Descriere</th><td>'.$proiect['proiect_descriere'].'</td></tr>';
    echo '</tbody></table>';
    // Secțiunea 2: Activități și rapoarte
    echo '<h3>Activități proiect</h3>';
    $q = ezpub_query($conn, "SELECT * FROM proiecte_activitati WHERE activitate_proiect_id='$projectID' ORDER BY activitate_data_inceput ASC");
    if (ezpub_num_rows($q)==0) {
        echo '<div class="callout warning">Nu există activități definite pentru acest proiect.</div>';
    } else {
        while ($act = ezpub_fetch_array($q)) {
            echo '<div class="callout primary"><strong>'.htmlspecialchars($act['activitate_titlu']).'</strong> (';
            echo ($act['activitate_data_inceput'] ? date('d.m.Y', strtotime($act['activitate_data_inceput'])) : '').' - ';
            echo ($act['activitate_data_sfarsit'] ? date('d.m.Y', strtotime($act['activitate_data_sfarsit'])) : '').')<br>';
            echo $act['activitate_descriere'];
            // Rapoarte de stare pentru activitate - tip "forum"
            $qr = ezpub_query($conn, "SELECT s.*, u.utilizator_Prenume, u.utilizator_Nume FROM proiecte_status s LEFT JOIN date_utilizatori u ON s.status_user_id=u.utilizator_ID WHERE s.status_activitate_id='".$act['activitate_id']."' ORDER BY s.status_data_raport DESC");
            if (ezpub_num_rows($qr)==0) {
                echo '<div class="callout secondary">Fără rapoarte de stare.</div>';
            } else {
                while ($r = ezpub_fetch_array($qr)) {
                    $isAuthor = ($r['status_user_id'] == $_SESSION['uid']);
                    echo '<div class="status-post" style="border:1px solid #e0e0e0; border-radius:6px; margin:10px 0; padding:10px; background:#fafbfc; position:relative;">';
                    echo '<div style="font-size:14px; color:#333; margin-bottom:4px;"><strong>'.htmlspecialchars($r['utilizator_Prenume'].' '.$r['utilizator_Nume']).'</strong>';
                    echo ' <span style="color:#888; font-size:12px;">['.($r['status_data_raport'] ? date('d.m.Y H:i', strtotime($r['status_data_raport'])) : '').']</span>';
                    echo '</div>';
                    echo '<div style="font-size:15px; color:#222; margin-bottom:4px;">'.nl2br(htmlspecialchars($r['status_raport'])).'</div>';
                    if ($isAuthor) {
                        echo '<button class="button tiny secondary" style="position:absolute; top:10px; right:10px;" onclick="editStatus'.$r['status_id'].'()">Editează</button>';
                        echo '<script>function editStatus'.$r['status_id'].'() {';
                        echo '  var iframe = document.getElementById(\'status-iframe-'.$act['activitate_id'].'\');';
                        echo '  if (iframe) iframe.src = \"projectstatus.php?action=edit&id='.$r['status_id'].'\";';
                        echo '}</script>';
                    }
                    echo '</div>';
                }
            }
            echo '<iframe id="status-iframe-'.$act['activitate_id'].'" src="projectstatus.php?activitate_id='.$act['activitate_id'].'" style="width:100%; min-height:180px; border:1px solid #e0e0e0; border-radius:6px; margin-top:10px;" frameborder="0" scrolling="no" onload="resizeIframe(this)"></iframe>';
            echo '<script>
            function resizeIframe(iframe) {
                try {
                    if (iframe && iframe.contentWindow && iframe.contentWindow.document.body) {
                        iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 20 + "px";
                    }
                } catch(e) {}
            }
            window.addEventListener("message", function(e) {
                if (e.data && e.data.type === "resizeStatusIframe" && e.data.iframeId) {
                    var iframe = document.getElementById(e.data.iframeId);
                    if (iframe && e.data.height) {
                        iframe.style.height = e.data.height + "px";
                    }
                }
            });
            </script>';
            echo '</div>';
        }
    }
    echo '<a href="siteprojects.php" class="button secondary">Înapoi la listă</a>';
    echo '</div></div>';
    include '../bottom.php';
    die;
}
        ?>