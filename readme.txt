1. CREATE DATABASE  <database_name>;
2. SHOW DATABASES;
3. CONNECT <database_name>;
4. CREATE TABLE <table_name_in_plural>();
5. INSERT INTO <table_name_in_plural>
        (columns) VALUES
        (values);



# Utility Commands
\! cls 
mysqldump -u root -p --databases library_db > D:\dev\it30b\backups\date_db_name.sql