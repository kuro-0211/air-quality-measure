# To-Do List 앱 구조

## 전체 아키텍처

```mermaid
graph TD
    User["사용자 (Browser)"]

    subgraph LAMP["LAMP 스택"]
        subgraph Frontend["프론트엔드"]
            HTML["HTML/CSS\n(UI 렌더링)"]
        end

        subgraph Server["웹 서버"]
            Apache["Apache2\n(HTTP 서버)"]
        end

        subgraph Backend["백엔드"]
            PHP["PHP\n(비즈니스 로직)"]
        end

        subgraph Database["데이터베이스"]
            MySQL["MySQL\n(todo_db)"]
            subgraph Table["todos 테이블"]
                id["id (PK, AUTO_INCREMENT)"]
                title["title (VARCHAR)"]
                is_done["is_done (BOOLEAN)"]
                created_at["created_at (DATETIME)"]
            end
            MySQL --> Table
        end
    end

    User -->|"HTTP 요청"| Apache
    Apache -->|"PHP 실행"| PHP
    PHP -->|"SQL 쿼리"| MySQL
    MySQL -->|"결과 반환"| PHP
    PHP -->|"HTML 생성"| Apache
    Apache -->|"HTTP 응답"| HTML
    HTML -->|"화면 표시"| User
```

## 주요 기능 흐름

```mermaid
flowchart LR
    User["사용자"]

    Add["할 일 추가\nINSERT INTO todos"]
    List["목록 조회\nSELECT * FROM todos"]
    Toggle["완료 체크/해제\nUPDATE todos\nSET is_done"]
    Delete["할 일 삭제\nDELETE FROM todos"]

    User -->|"제목 입력 후 제출"| Add
    User -->|"페이지 접속"| List
    User -->|"체크박스 클릭"| Toggle
    User -->|"삭제 버튼 클릭"| Delete

    Add -->|"DB 저장 후 새로고침"| List
    Toggle -->|"상태 변경 후 새로고침"| List
    Delete -->|"레코드 삭제 후 새로고침"| List
```

## 기술 스택

| 계층 | 기술 | 역할 |
|------|------|------|
| OS | Linux (Zorin OS) | 운영 환경 |
| 웹 서버 | Apache2 | HTTP 요청 처리 |
| 백엔드 | PHP | 비즈니스 로직 / DB 연동 |
| 데이터베이스 | MySQL | 데이터 저장 및 관리 |
| 프론트엔드 | HTML / CSS | UI 렌더링 |
