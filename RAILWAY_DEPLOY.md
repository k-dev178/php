# Railway 배포 가이드

## 문제 해결: "could not find driver" 에러

이 프로젝트를 Railway에 배포할 때 발생하던 "데이터베이스 연결 실패: could not find driver" 에러는 다음 파일들을 추가하여 해결되었습니다:

1. **composer.json** - PHP PDO MySQL 확장 모듈 요구사항 명시
2. **nixpacks.toml** - Railway 빌드 설정
3. **config.php** - 환경변수 지원 추가

## Railway 배포 단계

### 1. Railway 프로젝트 생성

1. [Railway](https://railway.app) 접속 및 로그인
2. "New Project" 클릭
3. "Deploy from GitHub repo" 선택
4. 이 저장소 선택

### 2. MySQL 데이터베이스 추가

1. Railway 프로젝트 대시보드에서 "+ New" 클릭
2. "Database" 선택
3. "Add MySQL" 선택
4. 데이터베이스가 자동으로 생성되고 환경변수가 설정됩니다

### 3. 데이터베이스 테이블 생성

**⭐ 가장 쉬운 방법 - PHP 스크립트 사용 (추천)**

1. 코드를 Railway에 배포 (git push)
2. 배포 완료 후 브라우저에서 접속:
   ```
   https://your-app.railway.app/setup_database.php
   ```
3. 테이블이 자동으로 생성되면 "✓✓✓ 데이터베이스 설정 완료! ✓✓✓" 메시지 확인
4. **보안을 위해 setup_database.php 파일 삭제**:
   ```bash
   git rm setup_database.php
   git commit -m "Remove setup script"
   git push
   ```

**방법 2 - Railway CLI 사용**
```bash
railway login
railway link
railway run mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < database_railway.sql
```

**방법 3 - MySQL Workbench/phpMyAdmin 등 GUI 도구 사용**
1. Railway 대시보드에서 MySQL → "Connect" → 연결 정보 복사
2. MySQL 클라이언트로 접속
3. `database_railway.sql` 파일 내용 실행

### 4. 환경변수 확인

Railway는 MySQL을 추가하면 자동으로 다음 환경변수를 설정합니다:
- `MYSQL_HOST`
- `MYSQL_USER`
- `MYSQL_PASSWORD`
- `MYSQL_DATABASE`
- `MYSQL_PORT`

추가 환경변수가 필요한 경우:
1. Railway 프로젝트 대시보드에서 "Variables" 탭 클릭
2. 필요한 환경변수 추가

### 5. 배포 완료

Railway가 자동으로 빌드 및 배포를 진행합니다. 다음 과정이 자동으로 실행됩니다:

1. nixpacks.toml 설정에 따라 PHP 8.2 및 필요한 확장 모듈 설치
2. composer.json에 명시된 의존성 설치 (있는 경우)
3. PHP 내장 서버로 애플리케이션 시작

배포가 완료되면 Railway가 제공하는 URL로 접속할 수 있습니다.

## 로컬 개발 환경

로컬에서 개발할 때는 여전히 기존 설정을 사용합니다:
- Host: 127.0.0.1
- User: root
- Password: (비어있음)
- Database: chat_app

config.php가 자동으로 로컬 환경과 Railway 환경을 구분합니다.

## 트러블슈팅

### 데이터베이스 연결 실패
- Railway MySQL 서비스가 실행 중인지 확인
- 환경변수가 올바르게 설정되었는지 확인
- 로그에서 정확한 에러 메시지 확인

### PHP 확장 모듈 에러
- nixpacks.toml에 필요한 확장 모듈이 모두 포함되어 있는지 확인
- Railway 재배포 시도

### 세션 에러
- Railway는 기본적으로 임시 파일시스템을 제공
- 세션이 유지되지 않는 경우 데이터베이스 기반 세션 고려

## 유용한 명령어

Railway CLI 설치:
```bash
npm i -g @railway/cli
```

로그 확인:
```bash
railway logs
```

로컬에서 Railway 환경변수 사용:
```bash
railway run php -S localhost:8000
```

## 참고 자료

- [Railway 문서](https://docs.railway.app)
- [Nixpacks PHP 가이드](https://nixpacks.com/docs/providers/php)
