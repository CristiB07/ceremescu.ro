<?php
$strPageTitle = "Error Logs - Jurnale Erori";
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
include '../dashboard/header.php';

// Check admin access
if (!isset($_SESSION['clearence']) || $_SESSION['clearence'] !== 'ADMIN') {
    header("Location: $strSiteURL/index.php");
    exit();
}

// Get filter parameters
$error_type = isset($_GET['type']) ? $_GET['type'] : '';
$date_from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d', strtotime('-7 days'));
$date_to = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');
$user_id = isset($_GET['uid']) && is_numeric($_GET['uid']) ? (int)$_GET['uid'] : null;

?>
<div class="grid-x grid-padding-x">
    <div class="large-12 cell">
        <h1><?php echo htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8')?></h1>

        <!-- Filters -->
        <form method="get" class="callout">
            <div class="grid-x grid-padding-x">
                <div class="large-3 medium-6 small-12 cell">
                    <label>Error Type
                        <select name="type">
                            <option value="">All Types</option>
                            <option value="PHP Error" <?php echo $error_type === 'PHP Error' ? 'selected' : '' ?>>PHP
                                Error</option>
                            <option value="PHP Warning" <?php echo $error_type === 'PHP Warning' ? 'selected' : '' ?>>
                                PHP Warning</option>
                            <option value="PHP Exception"
                                <?php echo $error_type === 'PHP Exception' ? 'selected' : '' ?>>PHP Exception</option>
                            <option value="MySQL Error" <?php echo $error_type === 'MySQL Error' ? 'selected' : '' ?>>
                                MySQL Error</option>
                        </select>
                    </label>
                </div>
                <div class="large-2 medium-6 small-12 cell">
                    <label>From Date
                        <input type="date" name="from" value="<?php echo htmlspecialchars($date_from, ENT_QUOTES, 'UTF-8')?>">
                    </label>
                </div>
                <div class="large-2 medium-6 small-12 cell">
                    <label>To Date
                        <input type="date" name="to" value="<?php echo htmlspecialchars($date_to, ENT_QUOTES, 'UTF-8')?>">
                    </label>
                </div>
                <div class="large-2 medium-6 small-12 cell">
                    <label>User ID
                        <input type="number" name="uid" value="<?php echo $user_id ? htmlspecialchars($user_id, ENT_QUOTES, 'UTF-8') : ''?>"
                            placeholder="Optional">
                    </label>
                </div>
                <div class="large-3 medium-12 small-12 cell">
                    <label>&nbsp;</label>
                    <button type="submit" class="button">Filter</button>
                    <a href="siteerrors.php" class="button secondary">Reset</a>
                </div>
            </div>
        </form>

        <?php
        // Build query with filters
        $where_conditions = ["error_time >= ?", "error_time <= ?"];
        $params_types = "ss";
        $params_values = [$date_from . ' 00:00:00', $date_to . ' 23:59:59'];

        if ($error_type) {
            $where_conditions[] = "error_type = ?";
            $params_types .= "s";
            $params_values[] = $error_type;
        }

        if ($user_id) {
            $where_conditions[] = "error_utilizator_id = ?";
            $params_types .= "i";
            $params_values[] = $user_id;
        }

        $where_sql = "WHERE " . implode(" AND ", $where_conditions);

        // Count total errors
        $count_query = "SELECT COUNT(*) as total FROM application_errors $where_sql";
        $stmt_count = mysqli_prepare($conn, $count_query);
        mysqli_stmt_bind_param($stmt_count, $params_types, ...$params_values);
        mysqli_stmt_execute($stmt_count);
        $result_count = mysqli_stmt_get_result($stmt_count);
        $row_count = mysqli_fetch_assoc($result_count);
        $total = $row_count['total'];
        mysqli_stmt_close($stmt_count);

        // Pagination
        $pages = new Pagination();
        $pages->items_total = $total;
        $pages->mid_range = 5;
        $pages->paginate();

        // Get errors with pagination
        // Join both user tables - date_utilizatori for staff, site_accounts for clients
        $query = "SELECT e.*, 
                  COALESCE(
                      CASE 
                          WHEN e.error_user_role = 'CLIENT' THEN CONCAT(sa.account_first_name, ' ', sa.account_last_name)
                          ELSE CONCAT(u.utilizator_Nume, ' ', u.utilizator_Prenume)
                      END,
                      e.error_visitor_id
                  ) as user_name 
                  FROM application_errors e 
                  LEFT JOIN date_utilizatori u ON e.error_utilizator_id = u.utilizator_ID AND e.error_user_role != 'CLIENT'
                  LEFT JOIN site_accounts sa ON e.error_utilizator_id = sa.account_id AND e.error_user_role = 'CLIENT'
                  $where_sql 
                  ORDER BY error_time DESC " . $pages->limit;

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, $params_types, ...$params_values);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($total == 0) {
            echo "<div class=\"callout success\">No errors found for the selected period!</div>";
        } else {
            echo "<div class=\"paginate\">";
            echo "Total: $total errors<br><br>";
            echo $pages->display_pages();
            echo "</div>";
        ?>

        <table>
            <thead>
                <tr>
                    <th width="150">Time</th>
                    <th width="120">Type</th>
                    <th width="150">User</th>
                    <th>Message</th>
                    <th width="200">File</th>
                    <th width="50">Line</th>
                    <th width="100">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    $error_class = '';
                    if (strpos($row['error_type'], 'Error') !== false || strpos($row['error_type'], 'Exception') !== false) {
                        $error_class = 'style="background-color: #ffebee;"';
                    } elseif (strpos($row['error_type'], 'Warning') !== false) {
                        $error_class = 'style="background-color: #fff3e0;"';
                    }

                    echo "<tr $error_class>";
                    echo "<td><small>" . htmlspecialchars($row['error_time'], ENT_QUOTES, 'UTF-8') . "</small></td>";
                    echo "<td><strong>" . htmlspecialchars($row['error_type'], ENT_QUOTES, 'UTF-8') . "</strong></td>";
                    echo "<td>" . htmlspecialchars($row['user_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td><small>" . htmlspecialchars(substr($row['error_message'], 0, 200), ENT_QUOTES, 'UTF-8') . 
                         (strlen($row['error_message']) > 200 ? '...' : '') . "</small></td>";
                    echo "<td><small>" . htmlspecialchars(basename($row['error_file'] ?? ''), ENT_QUOTES, 'UTF-8') . "</small></td>";
                    echo "<td>" . htmlspecialchars($row['error_line'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td><small>" . htmlspecialchars($row['error_IP_address'], ENT_QUOTES, 'UTF-8') . "</small></td>";
                    echo "</tr>";
                    
                    // Full error details in collapsible row
                    if (strlen($row['error_message']) > 200 || $row['error_file']) {
                        echo "<tr $error_class><td colspan=\"7\"><details><summary>Details</summary>";
                        echo "<strong>Page:</strong> " . htmlspecialchars($row['error_page'], ENT_QUOTES, 'UTF-8') . "<br>";
                        if ($row['error_file']) {
                            echo "<strong>File:</strong> " . htmlspecialchars($row['error_file'], ENT_QUOTES, 'UTF-8') . "<br>";
                        }
                        echo "<strong>Message:</strong> " . htmlspecialchars($row['error_message'], ENT_QUOTES, 'UTF-8');
                        echo "</details></td></tr>";
                    }
                }
                mysqli_stmt_close($stmt);
                ?>
            </tbody>
        </table>

        <div class="paginate">
            <?php echo $pages->display_pages(); ?>
        </div>

        <?php
        }
        ?>

    </div>
</div>

<?php include '../bottom.php'; ?>
