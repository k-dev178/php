<?php
require_once 'config.php';

header('Content-Type: application/json');

// 로그인 체크
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => '로그인이 필요합니다.']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'send':
            // 메시지 전송
            $message = trim($_POST['message'] ?? '');

            if (empty($message)) {
                echo json_encode(['success' => false, 'error' => '메시지를 입력해주세요.']);
                exit;
            }

            if (strlen($message) > 500) {
                echo json_encode(['success' => false, 'error' => '메시지는 500자를 초과할 수 없습니다.']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO messages (user_id, username, message) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['username'], $message]);

            echo json_encode(['success' => true]);
            break;

        case 'get':
            // 메시지 조회 (마지막 메시지 ID 이후)
            $last_id = intval($_GET['last_id'] ?? 0);

            $stmt = $pdo->prepare("SELECT id, username, message, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as created_at
                                  FROM messages WHERE id > ? ORDER BY id ASC LIMIT 50");
            $stmt->execute([$last_id]);
            $messages = $stmt->fetchAll();

            echo json_encode(['success' => true, 'messages' => $messages]);
            break;

        case 'load':
            // 초기 메시지 로드 (최근 50개)
            $stmt = $pdo->query("SELECT id, username, message, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as created_at
                                FROM messages ORDER BY id DESC LIMIT 50");
            $messages = array_reverse($stmt->fetchAll());

            echo json_encode(['success' => true, 'messages' => $messages]);
            break;

        case 'update_online':
            // 온라인 상태 업데이트
            $stmt = $pdo->prepare("INSERT INTO online_status (user_id, username) VALUES (?, ?)
                                  ON DUPLICATE KEY UPDATE last_activity = CURRENT_TIMESTAMP");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['username']]);

            echo json_encode(['success' => true]);
            break;

        case 'online_users':
            // 온라인 사용자 목록 (최근 30초 이내 활동)
            $stmt = $pdo->query("SELECT username FROM online_status
                                WHERE last_activity > DATE_SUB(NOW(), INTERVAL 30 SECOND)
                                ORDER BY username");
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

            echo json_encode(['success' => true, 'users' => $users]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => '잘못된 요청입니다.']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => '서버 오류가 발생했습니다.']);
}
