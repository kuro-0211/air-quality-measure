# 공기질 실시간 모니터링 프로젝트

## 프로젝트 개요
LAMP 스택을 활용하여 가상의 CO₂ 공기질 데이터를 생성·저장하고
PHP로 실시간 모니터링하는 웹 애플리케이션

## 기술 스택
- Linux (Zorin OS / Ubuntu 24.04) on VMware
- Apache2 (웹 서버)
- MySQL (데이터베이스)
- PHP (동적 HTML 생성)
- Python (가상 데이터 주입)
- Chart.js (실시간 그래프)

## 구현 기능

### 1. 가상 데이터 생성 및 MySQL 저장 (injector.py)
- Python으로 CO₂ 농도(400~2000 ppm) 난수 생성
- 5초 간격으로 MySQL `chellydb.airquality` 테이블에 자동 저장
- co2 수치에 따라 상태(좋음/보통/나쁨/위험) 자동 분류

### 2. PHP 실시간 모니터링 (monitor.php)
- 5초마다 AJAX로 최신 20건 조회
- Chart.js 라인 그래프로 CO₂ 추이 시각화
- 현재 수치 게이지 + 상태 뱃지 표시
- Apache2를 통해 `/var/www/html/`에서 서비스

### 3. 데이터 목록 페이지 (list.php)
- 전체 데이터를 10건씩 페이지네이션으로 조회
- CO₂ 수치, 상태, 기록 시각 표시

### 4. GitHub 업로드 및 문서화
- 작업 폴더 전체를 GitHub repo에 push
- `process.md`에 시스템 설명 및 Mermaid 블록도 포함
- 동작 영상과 repo 명을 별도 txt 파일로 제출

## 데이터베이스 구조
- DB명: `chellydb`
- 테이블명: `airquality`
- 컬럼: `id`, `co2`, `status`, `recorded_at`

## 디렉터리 구조
```
mysql_php/
├── python/
│   └── injector.py       # 데이터 생성 및 MySQL 저장
├── php/
│   ├── monitor.php       # 실시간 모니터링 페이지
│   └── list.php          # 데이터 목록 페이지
├── project.md            # 프로젝트 기획 문서
├── process.md            # 구현 과정 및 Mermaid 블록도
└── pyproject.toml
```

## 실행 순서
1. `sudo mysql` → DB/유저 생성 및 권한 부여
2. `sudo cp php/*.php /var/www/html/` → PHP 배포
3. `python3 python/injector.py` → 데이터 주입 시작
4. 브라우저에서 `http://localhost/monitor.php` 접속
