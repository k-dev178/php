<?php
// 환경변수만 확인 (데이터베이스 연결 안함)
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>환경변수 확인</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #00ff00; }
        table { border-collapse: collapse; margin: 20px 0; }
        td, th { border: 1px solid #00ff00; padding: 10px; text-align: left; }
        .error { color: #ff0000; }
        .success { color: #00ff00; }
        .warning { color: #ffaa00; }
    </style>
</head>
<body>
    <h1>Railway 환경변수 확인</h1>

    <h2>MySQL 환경변수</h2>
    <table>
        <tr>
            <th>변수명</th>
            <th>값</th>
            <th>상태</th>
        </tr>
        <?php
        $vars = [
            'MYSQLHOST',
            'MYSQLUSER',
            'MYSQLPASSWORD',
            'MYSQLDATABASE',
            'MYSQLPORT',
            'MYSQL_HOST',
            'MYSQL_USER',
            'MYSQL_PASSWORD',
            'MYSQL_DATABASE',
            'MYSQL_PORT'
        ];

        foreach ($vars as $var) {
            $value = getenv($var);
            if ($value !== false && $value !== '') {
                $display = (strpos($var, 'PASS') !== false)
                    ? substr($value, 0, 3) . '***'
                    : htmlspecialchars($value);
                $status = '<span class="success">✓ 설정됨</span>';
            } else {
                $display = '(없음)';
                $status = '<span class="error">✗ 없음</span>';
            }
            echo "<tr><td>$var</td><td>$display</td><td>$status</td></tr>";
        }
        ?>
    </table>

    <h2>연결 정보 (config.php에서 읽히는 값)</h2>
    <table>
        <tr>
            <th>항목</th>
            <th>값</th>
        </tr>
        <?php
        $host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: '127.0.0.1';
        $user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: getenv('DB_USER') ?: 'root';
        $pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: getenv('DB_PASS') ?: '';
        $dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'chat_app';
        $port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: '3306';

        echo "<tr><td>Host</td><td>" . htmlspecialchars($host) . "</td></tr>";
        echo "<tr><td>Port</td><td>" . htmlspecialchars($port) . "</td></tr>";
        echo "<tr><td>User</td><td>" . htmlspecialchars($user) . "</td></tr>";
        echo "<tr><td>Password</td><td>" . (empty($pass) ? '(비어있음)' : substr($pass, 0, 3) . '***') . "</td></tr>";
        echo "<tr><td>Database</td><td>" . htmlspecialchars($dbname) . "</td></tr>";
        ?>
    </table>

    <h2>PHP 정보</h2>
    <table>
        <tr>
            <td>PHP Version</td>
            <td><?php echo phpversion(); ?></td>
        </tr>
        <tr>
            <td>PDO Drivers</td>
            <td><?php echo implode(', ', PDO::getAvailableDrivers()); ?></td>
        </tr>
    </table>

    <h2>연결 테스트</h2>
    <?php
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        echo "<p class='warning'>연결 시도 중: $dsn</p>";

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);

        echo "<p class='success'>✓✓✓ 데이터베이스 연결 성공! ✓✓✓</p>";

        // 테이블 목록 확인
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        if (count($tables) > 0) {
            echo "<p>기존 테이블: " . implode(', ', $tables) . "</p>";
        } else {
            echo "<p class='warning'>테이블이 없습니다. setup_database.php를 실행하세요.</p>";
        }

    } catch (PDOException $e) {
        echo "<p class='error'>✗ 연결 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p class='warning'>MySQL 서비스가 실행 중인지 Railway에서 확인하세요.</p>";
    }
    ?>

    <hr>
    <p><strong>⚠️ 확인 후 이 파일을 삭제하세요!</strong></p>
</body>
</html>
