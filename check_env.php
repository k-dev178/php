<?php
/**
 * Railway 환경변수 확인 스크립트
 * 사용 후 반드시 삭제하세요!
 */

echo "<h1>Railway 환경변수 확인</h1>";
echo "<pre>";

echo "=== MySQL 관련 환경변수 ===\n\n";

$mysql_vars = [
    'MYSQL_HOST',
    'MYSQL_USER',
    'MYSQL_PASSWORD',
    'MYSQL_DATABASE',
    'MYSQL_PORT',
    'MYSQL_URL',
    'MYSQLHOST',
    'MYSQLUSER',
    'MYSQLPASSWORD',
    'MYSQLDATABASE',
    'MYSQLPORT',
    'DATABASE_URL',
    'DB_HOST',
    'DB_USER',
    'DB_PASS',
    'DB_NAME',
    'DB_PORT'
];

$found = false;
foreach ($mysql_vars as $var) {
    $value = getenv($var);
    if ($value !== false) {
        // 비밀번호는 일부만 표시
        if (strpos($var, 'PASS') !== false || strpos($var, 'PASSWORD') !== false) {
            $display = substr($value, 0, 3) . '***';
        } else {
            $display = $value;
        }
        echo "$var = $display\n";
        $found = true;
    }
}

if (!$found) {
    echo "⚠️  MySQL 환경변수를 찾을 수 없습니다!\n";
    echo "\nRailway 설정:\n";
    echo "1. Railway 대시보드 → 프로젝트 선택\n";
    echo "2. MySQL 데이터베이스 서비스 추가 확인\n";
    echo "3. PHP 서비스 → Variables 탭\n";
    echo "4. MySQL 서비스와 연결 (Reference 추가)\n";
}

echo "\n=== 기타 환경변수 ===\n\n";
$other_vars = ['PORT', 'RAILWAY_ENVIRONMENT', 'RAILWAY_SERVICE_NAME'];
foreach ($other_vars as $var) {
    $value = getenv($var);
    if ($value !== false) {
        echo "$var = $value\n";
    }
}

echo "\n=== PHP 정보 ===\n\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";

echo "</pre>";

echo "<h2>⚠️ 보안 주의</h2>";
echo "<p>이 파일은 민감한 정보를 표시할 수 있습니다. 확인 후 <strong>반드시 삭제</strong>하세요!</p>";
?>
