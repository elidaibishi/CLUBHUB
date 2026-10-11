<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

if ($db_error) {
    http_response_code(503);
    exit('Databaza Club Hub nuk është e disponueshme.');
}

$search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$group_filter = isset($_GET['group_filter']) && is_string($_GET['group_filter']) ? $_GET['group_filter'] : '';
$sort = isset($_GET['sort']) && is_string($_GET['sort']) ? $_GET['sort'] : 'date';
if (!in_array($sort, array('date', 'name', 'group'), true)) {
    $sort = 'date';
}
$order_by = $sort === 'name'
    ? 'p.full_name ASC, p.id DESC'
    : ($sort === 'group' ? 'MIN(g.name) ASC, p.full_name ASC' : 'p.created_at DESC, p.id DESC');

$query = 'SELECT p.full_name, p.email, p.created_at,
                 GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ", ") AS group_names
          FROM participants p
          LEFT JOIN participant_groups pg ON pg.participant_id = p.id
          LEFT JOIN `groups` g ON g.id = pg.group_id';
$where = '';
if ($search !== '') {
    $where .= ' WHERE (p.full_name LIKE ? OR p.email LIKE ?)';
}
if ($group_filter === 'ungrouped') {
    $where .= ($where === '' ? ' WHERE ' : ' AND ')
        . 'NOT EXISTS (SELECT 1 FROM participant_groups filter_pg WHERE filter_pg.participant_id = p.id)';
} elseif (ctype_digit((string) $group_filter) && (int) $group_filter > 0) {
    $where .= ($where === '' ? ' WHERE ' : ' AND ')
        . 'EXISTS (SELECT 1 FROM participant_groups filter_pg WHERE filter_pg.participant_id = p.id AND filter_pg.group_id = ?)';
} else {
    $group_filter = '';
}
$query .= $where . ' GROUP BY p.id, p.full_name, p.email, p.created_at ORDER BY ' . $order_by;

$statement = mysqli_prepare($conn, $query);
if (!$statement) {
    error_log('Club Hub participant CSV prepare failed: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Lista nuk mund të eksportohet tani.');
}

$like_search = '%' . $search . '%';
$has_group_id = ctype_digit((string) $group_filter) && (int) $group_filter > 0;
if ($search !== '' && $has_group_id) {
    $group_id = (int) $group_filter;
    mysqli_stmt_bind_param($statement, 'ssi', $like_search, $like_search, $group_id);
} elseif ($search !== '') {
    mysqli_stmt_bind_param($statement, 'ss', $like_search, $like_search);
} elseif ($has_group_id) {
    $group_id = (int) $group_filter;
    mysqli_stmt_bind_param($statement, 'i', $group_id);
}

if (!mysqli_stmt_execute($statement)) {
    error_log('Club Hub participant CSV query failed: ' . mysqli_stmt_error($statement));
    mysqli_stmt_close($statement);
    http_response_code(500);
    exit('Lista nuk mund të eksportohet tani.');
}

mysqli_stmt_bind_result($statement, $full_name, $email, $created_at, $group_names);
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="clubhub-pjesemarresit-' . date('Y-m-d') . '.csv"');
header('X-Content-Type-Options: nosniff');
echo "\xEF\xBB\xBF";
$output = fopen('php://output', 'w');
if ($output === false) {
    error_log('Club Hub participant CSV output stream could not be opened.');
    mysqli_stmt_close($statement);
    http_response_code(500);
    exit('Skedari nuk mund të krijohet.');
}
fputcsv($output, array('Emri i plotë', 'Email', 'Grupet', 'Regjistruar'));

while (mysqli_stmt_fetch($statement)) {
    $values = array($full_name, $email ?? '', $group_names ?? '', $created_at);
    foreach ($values as &$value) {
        $value = (string) $value;
        if (preg_match('/^[\t\r =+\-@]/', $value)) {
            $value = "'" . $value;
        }
    }
    unset($value);
    fputcsv($output, $values);
}

fclose($output);
mysqli_stmt_close($statement);
