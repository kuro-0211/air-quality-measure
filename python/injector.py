import time
import random
import mysql.connector

DB_CONFIG = {
    "host": "localhost",
    "user": "chellydb",
    "password": "jjk00jjk",
    "database": "chellydb",
}


def get_status(co2):
    if co2 < 600:
        return "좋음"
    elif co2 < 1000:
        return "보통"
    elif co2 < 1500:
        return "나쁨"
    else:
        return "위험"


def init_db(conn):
    cursor = conn.cursor()
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS airquality (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            co2         FLOAT NOT NULL,
            status      VARCHAR(10) NOT NULL,
            recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    """)
    conn.commit()
    cursor.close()


def insert_data(conn, co2, status):
    cursor = conn.cursor()
    cursor.execute(
        "INSERT INTO airquality (co2, status) VALUES (%s, %s)",
        (co2, status)
    )
    conn.commit()
    cursor.close()


def main():
    conn = mysql.connector.connect(**DB_CONFIG)
    init_db(conn)

    print("공기질 데이터 수집 시작 (5초 간격, Ctrl+C로 종료)")
    try:
        while True:
            co2    = round(random.uniform(400, 2000), 1)
            status = get_status(co2)
            insert_data(conn, co2, status)
            print(f"[저장] CO2 = {co2} ppm | 상태 = {status}")
            time.sleep(5)
    except KeyboardInterrupt:
        print("\n종료")
    finally:
        conn.close()


if __name__ == "__main__":
    main()
