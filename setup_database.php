<?php
/**
 * Railway 데이터베이스 초기화 스크립트
 *
 * 사용 방법:
 * 1. 이 파일을 Railway에 배포
 * 2. 브라우저에서 https://your-app.railway.app/setup_database.php 접속
 * 3. 테이블이 자동으로 생성됨
 * 4. 완료 후 이 파일을 삭제하거나 이름 변경
 */

// Railway 환경변수 사용 (여러 형식 지원)
$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: '127.0.0.1';
$user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
$dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'chat_app';
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306';

echo "<h1>데이터베이스 설정 중...</h1>";
echo "<pre>";

try {
    // 데이터베이스 연결
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "✓ 데이터베이스 연결 성공\n\n";

    // 사용자 테이블 생성
    echo "users 테이블 생성 중...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ users 테이블 생성 완료\n\n";

    // 메시지 테이블 생성
    echo "messages 테이블 생성 중...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            username VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ messages 테이블 생성 완료\n\n";

    // 온라인 상태 테이블 생성
    echo "online_status 테이블 생성 중...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS online_status (
            user_id INT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ online_status 테이블 생성 완료\n\n";

    // 테이블 확인
    echo "생성된 테이블 목록:\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "  - $table\n";
    }

    echo "\n✓✓✓ 데이터베이스 설정 완료! ✓✓✓\n";
    echo "\n⚠️  보안을 위해 이 파일(setup_database.php)을 삭제해주세요.\n";

} catch (PDOException $e) {
    echo "✗ 에러 발생: " . $e->getMessage() . "\n";
    echo "\n연결 정보:\n";
    echo "Host: $host\n";
    echo "Port: $port\n";
    echo "User: $user\n";
    echo "Database: $dbname\n";
}

echo "</pre>";
?>
