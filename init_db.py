import sqlite3
import os

db_path = os.path.join("data", "concours.db")

# Ensure data directory exists
if not os.path.exists("data"):
    os.makedirs("data")

try:
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()

    # Table Participants
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS participants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        email TEXT,
        ip TEXT,
        signature_log TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
    """)

    # Table Photos
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS photos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        participant_id INTEGER,
        filename_original TEXT,
        filename_4k TEXT,
        filename_thumb TEXT,
        width INTEGER,
        height INTEGER,
        is_upscale_suspect INTEGER DEFAULT 0,
        status TEXT DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (participant_id) REFERENCES participants(id)
    )
    """)

    # Table Votes Tour 1
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS votes_tour1 (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        photo_id INTEGER,
        jury_ip TEXT,
        vote_value TEXT CHECK(vote_value IN ('oui', 'non')),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (photo_id) REFERENCES photos(id)
    )
    """)

    # Table Votes Tour 2
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS votes_tour2 (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        photo_id INTEGER,
        jury_ip TEXT,
        rank INTEGER,
        points INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (photo_id) REFERENCES photos(id)
    )
    """)

    conn.commit()
    conn.close()
    print(f"Database initialized successfully at {db_path}")

except Exception as e:
    print(f"Error initializing database: {e}")
