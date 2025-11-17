<?php
$sql = "SELECT id FROM lots WHERE winner_id IS NULL AND expiration_date < CURDATE()";
$result = mysqli_query($connection, $sql);
$lots = mysqli_fetch_all($result, MYSQLI_ASSOC);

foreach ($lots as $lot) {
    $lot_id = $lot['id'];

    $stmt = mysqli_prepare($connection, "SELECT user_id FROM bets WHERE lot_id = ? ORDER BY date_placed DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $lot_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $winner_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($winner_id) {
        $update = mysqli_prepare($connection, "UPDATE lots SET winner_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($update, "ii", $winner_id, $lot_id);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
    }
}
