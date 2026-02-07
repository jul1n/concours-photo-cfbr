import sqlite3
import os

db_path = os.path.join("data", "concours.db")

try:
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    
    # Create jury_votes_analytics table
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS jury_votes_analytics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        photo_id INTEGER NOT NULL,
        jury_identifier TEXT NOT NULL,
        score_aesthetic DECIMAL(4,2),
        score_theme DECIMAL(4,2),
        comment TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (photo_id) REFERENCES photos(id),
        UNIQUE(photo_id, jury_identifier)
    )
    """)
    
    conn.commit()
    conn.close()
    print("Database updated successfully.")
    
except Exception as e:
    print(f"Error: {e}")
