<?php
require_once 'config.php';

if (isLoggedIn()) {
    // 온라인 상태 제거
    try {
        $stmt = $pdo->prepare("DELETE FROM online_status WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        // 무시
    }
}

// 세션 종료
session_destroy();
header('Location: login.php');
exit;
