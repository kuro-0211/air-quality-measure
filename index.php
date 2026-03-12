<?php
// DB 연결
$host = 'localhost';
$user = 'chelly';
$pass = 'jjk00jjk';
$db   = 'todo_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('<p style="color:red;">DB 연결 실패: ' . htmlspecialchars($conn->connect_error) . '</p>');
}

$conn->set_charset('utf8mb4');

// 테이블 생성 (없을 경우)
$conn->query("
    CREATE TABLE IF NOT EXISTS todos (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        title      VARCHAR(255) NOT NULL,
        is_done    TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
");

// 액션 처리
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' && !empty(trim($_POST['title'] ?? ''))) {
    $title = trim($_POST['title']);
    $stmt  = $conn->prepare("INSERT INTO todos (title) VALUES (?)");
    $stmt->bind_param('s', $title);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if ($action === 'toggle' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE todos SET is_done = NOT is_done WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM todos WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// 목록 조회
$result = $conn->query("SELECT * FROM todos ORDER BY created_at DESC");
$todos  = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();

$total    = count($todos);
$done     = array_sum(array_column($todos, 'is_done'));
$remaining = $total - $done;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f5;
            color: #333;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 40px 16px;
        }

        .container {
            width: 100%;
            max-width: 560px;
        }

        h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stats {
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 24px;
        }

        /* 입력 폼 */
        .add-form {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
        }

        .add-form input[type="text"] {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
            transition: border-color .2s;
        }

        .add-form input[type="text"]:focus {
            border-color: #555;
        }

        .add-form button {
            padding: 10px 18px;
            background: #333;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background .2s;
        }

        .add-form button:hover { background: #111; }

        /* 할 일 목록 */
        .todo-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .todo-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border: 1px solid #e8e8e8;
            border-radius: 10px;
            padding: 12px 14px;
            transition: opacity .2s;
        }

        .todo-item.done { opacity: 0.5; }

        /* 체크박스 커스텀 */
        .toggle-btn {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid #ccc;
            background: #fff;
            cursor: pointer;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: border-color .2s, background .2s;
            text-decoration: none;
        }

        .todo-item.done .toggle-btn {
            background: #333;
            border-color: #333;
        }

        .toggle-btn::after {
            content: '';
            display: none;
            width: 6px;
            height: 10px;
            border: 2px solid #fff;
            border-top: none;
            border-left: none;
            transform: rotate(45deg) translate(-1px, -1px);
        }

        .todo-item.done .toggle-btn::after { display: block; }

        .todo-title {
            flex: 1;
            font-size: 0.95rem;
            word-break: break-word;
        }

        .todo-item.done .todo-title {
            text-decoration: line-through;
            color: #aaa;
        }

        .todo-date {
            font-size: 0.75rem;
            color: #bbb;
            flex-shrink: 0;
        }

        .delete-btn {
            background: none;
            border: none;
            color: #ccc;
            font-size: 1.1rem;
            cursor: pointer;
            line-height: 1;
            padding: 2px 4px;
            border-radius: 4px;
            transition: color .2s;
            text-decoration: none;
        }

        .delete-btn:hover { color: #e55; }

        .empty {
            text-align: center;
            color: #bbb;
            padding: 48px 0;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>To-Do List</h1>
    <p class="stats">전체 <?= $total ?>개 &nbsp;·&nbsp; 완료 <?= $done ?>개 &nbsp;·&nbsp; 남은 <?= $remaining ?>개</p>

    <form class="add-form" method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
        <input type="hidden" name="action" value="add">
        <input type="text" name="title" placeholder="할 일을 입력하세요" autocomplete="off" autofocus required>
        <button type="submit">추가</button>
    </form>

    <div class="todo-list">
        <?php if (empty($todos)): ?>
            <p class="empty">할 일이 없습니다. 새로운 할 일을 추가해 보세요!</p>
        <?php else: ?>
            <?php foreach ($todos as $todo): ?>
                <?php $done_class = $todo['is_done'] ? ' done' : ''; ?>
                <div class="todo-item<?= $done_class ?>">
                    <a class="toggle-btn"
                       href="?action=toggle&id=<?= $todo['id'] ?>"
                       title="<?= $todo['is_done'] ? '완료 해제' : '완료' ?>"></a>

                    <span class="todo-title"><?= htmlspecialchars($todo['title']) ?></span>

                    <span class="todo-date"><?= date('m/d H:i', strtotime($todo['created_at'])) ?></span>

                    <a class="delete-btn"
                       href="?action=delete&id=<?= $todo['id'] ?>"
                       title="삭제"
                       onclick="return confirm('삭제하시겠습니까?')">✕</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
