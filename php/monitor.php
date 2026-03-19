<?php
$host = 'localhost';
$user = 'chellydb';
$pass = 'jjk00jjk';
$db   = 'chellydb';

if (isset($_GET['api'])) {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset('utf8mb4');
    $result = $conn->query("SELECT co2, status, recorded_at FROM airquality ORDER BY recorded_at DESC LIMIT 20");
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode(array_reverse($rows));
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>실시간 공기질 모니터</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 16px;
        }
        h1 { font-size: 1.5rem; margin-bottom: 6px; color: #86efac; }
        .subtitle { font-size: 0.85rem; color: #64748b; margin-bottom: 32px; }
        .dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%;
               background: #22c55e; margin-right: 6px; animation: blink 1s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }

        .gauge-wrap { display: flex; flex-direction: column; align-items: center; margin-bottom: 16px; }
        .gauge-label { font-size: .8rem; color: #94a3b8; margin-bottom: 8px; }
        .gauge-value { font-size: 4rem; font-weight: 700; color: #4ade80; line-height: 1; }
        .gauge-unit  { font-size: 1.4rem; color: #86efac; margin-left: 6px; }
        .updated-at  { font-size: .78rem; color: #475569; margin-top: 8px; }

        .badge {
            display: inline-block;
            margin-top: 12px;
            padding: 6px 20px;
            border-radius: 999px;
            font-size: 1rem;
            font-weight: 700;
        }
        .badge.좋음 { background: #166534; color: #86efac; }
        .badge.보통 { background: #713f12; color: #fde68a; }
        .badge.나쁨 { background: #7c2d12; color: #fdba74; }
        .badge.위험 { background: #4c0519; color: #fca5a5; }

        .chart-wrap {
            width: 100%; max-width: 700px;
            background: #1e293b; border-radius: 12px;
            padding: 20px; margin-bottom: 24px;
        }
        canvas { width: 100% !important; }

        table { width: 100%; max-width: 700px; border-collapse: collapse; font-size: .85rem; }
        th { background: #1e293b; padding: 10px 14px; text-align: left; color: #94a3b8; font-weight: 600; }
        td { padding: 9px 14px; border-bottom: 1px solid #1e293b; }
        tr:nth-child(odd)  td { background: #1a2332; }
        tr:nth-child(even) td { background: #0f172a; }

        .st-좋음 { color: #4ade80; font-weight: 600; }
        .st-보통 { color: #fbbf24; font-weight: 600; }
        .st-나쁨 { color: #fb923c; font-weight: 600; }
        .st-위험 { color: #f87171; font-weight: 600; }
    </style>
</head>
<body>
    <h1>실시간 공기질 모니터</h1>
    <p class="subtitle"><span class="dot"></span>5초마다 자동 갱신</p>

    <div class="gauge-wrap">
        <div class="gauge-label">현재 CO₂ 농도</div>
        <div>
            <span class="gauge-value" id="current-val">--</span>
            <span class="gauge-unit">ppm</span>
        </div>
        <div class="badge" id="status-badge">-</div>
        <div class="updated-at" id="updated-at">-</div>
    </div>

    <div class="chart-wrap">
        <canvas id="chart" height="120"></canvas>
    </div>

    <table>
        <thead><tr><th>#</th><th>CO₂ (ppm)</th><th>상태</th><th>기록 시각</th></tr></thead>
        <tbody id="table-body"></tbody>
    </table>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('chart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'CO₂ (ppm)',
                    data: [],
                    borderColor: '#4ade80',
                    backgroundColor: 'rgba(74,222,128,0.1)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#4ade80',
                    tension: 0.3,
                    fill: true,
                }]
            },
            options: {
                plugins: { legend: { labels: { color: '#94a3b8' } } },
                scales: {
                    x: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' } },
                    y: {
                        min: 300, max: 2100,
                        ticks: { color: '#64748b' },
                        grid: { color: '#1e293b' }
                    }
                }
            }
        });

        async function refresh() {
            const res  = await fetch('monitor.php?api=1');
            const rows = await res.json();
            if (!rows.length) return;

            const latest = rows[rows.length - 1];
            document.getElementById('current-val').textContent = parseFloat(latest.co2).toFixed(1);
            document.getElementById('updated-at').textContent  = '마지막 업데이트: ' + latest.recorded_at;

            const badge = document.getElementById('status-badge');
            badge.textContent = latest.status;
            badge.className = 'badge ' + latest.status;

            chart.data.labels = rows.map(r => r.recorded_at.slice(11, 19));
            chart.data.datasets[0].data = rows.map(r => parseFloat(r.co2));
            chart.update();

            const tbody = document.getElementById('table-body');
            tbody.innerHTML = '';
            [...rows].reverse().forEach((r, i) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${i+1}</td><td>${parseFloat(r.co2).toFixed(1)}</td>
                                <td class="st-${r.status}">${r.status}</td><td>${r.recorded_at}</td>`;
                tbody.appendChild(tr);
            });
        }

        refresh();
        setInterval(refresh, 5000);
    </script>
</body>
</html>
