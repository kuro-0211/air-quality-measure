<?php
$host = 'localhost';
$user = 'chellydb';
$pass = 'jjk00jjk';
$db   = 'chellydb';

$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset('utf8mb4');

$total       = $conn->query("SELECT COUNT(*) FROM airquality")->fetch_row()[0];
$total_pages = max(1, ceil($total / $limit));

$result = $conn->query("SELECT id, co2, status, recorded_at FROM airquality ORDER BY recorded_at DESC LIMIT $limit OFFSET $offset");
$rows   = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>공기질 데이터 목록</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a; color: #e2e8f0;
            min-height: 100vh; display: flex; flex-direction: column;
            align-items: center; padding: 40px 16px;
        }
        h1 { font-size: 1.4rem; color: #86efac; margin-bottom: 6px; }
        .info { font-size: .82rem; color: #64748b; margin-bottom: 24px; }
        a.back { font-size: .82rem; color: #7dd3fc; text-decoration: none; margin-bottom: 20px; display: block; }

        table { width: 100%; max-width: 700px; border-collapse: collapse; font-size: .88rem; }
        th { background: #1e293b; padding: 11px 16px; text-align: left; color: #94a3b8; font-weight: 600; }
        td { padding: 10px 16px; border-bottom: 1px solid #1e293b; }
        tr:nth-child(odd)  td { background: #1a2332; }
        tr:nth-child(even) td { background: #0f172a; }

        .st-좋음 { color: #4ade80; font-weight: 600; }
        .st-보통 { color: #fbbf24; font-weight: 600; }
        .st-나쁨 { color: #fb923c; font-weight: 600; }
        .st-위험 { color: #f87171; font-weight: 600; }

        .pagination { display: flex; gap: 8px; margin-top: 24px; }
        .pagination a, .pagination span {
            padding: 7px 14px; border-radius: 6px; font-size: .85rem; text-decoration: none;
        }
        .pagination a { background: #1e293b; color: #7dd3fc; }
        .pagination a:hover { background: #334155; }
        .pagination .current { background: #16a34a; color: #fff; }
    </style>
</head>
<body>
    <h1>공기질 데이터 목록</h1>
    <p class="info">전체 <?= $total ?>건 &nbsp;|&nbsp; 페이지 <?= $page ?> / <?= $total_pages ?></p>
    <a class="back" href="monitor.php">← 실시간 모니터로 돌아가기</a>

    <table>
        <thead>
            <tr><th>#</th><th>CO₂ (ppm)</th><th>상태</th><th>기록 시각</th></tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $i => $row): ?>
            <tr>
                <td><?= $offset + $i + 1 ?></td>
                <td><?= number_format($row['co2'], 1) ?></td>
                <td class="st-<?= $row['status'] ?>"><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['recorded_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>">← 이전</a>
        <?php endif; ?>
        <?php for ($p = max(1, $page - 2); $p <= min($total_pages, $page + 2); $p++): ?>
            <?php if ($p === $page): ?>
                <span class="current"><?= $p ?></span>
            <?php else: ?>
                <a href="?page=<?= $p ?>"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>">다음 →</a>
        <?php endif; ?>
    </div>
</body>
</html>
