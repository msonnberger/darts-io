-- IMPRESSUM:
-- (c) Martin Sonnberger 2021
-- Dieses Projekt ist im Rahmen des 
-- MultiMediaTechnology Bachelorstudiums
-- an der FH Salzburg entstanden.
-- Kontakt: msonnberger.mmt-b2020@fh-salzburg.ac.at

CREATE DATABASE mmp1;

CREATE TABLE game (
    game_id SERIAL PRIMARY KEY,
    point_mode INTEGER,
    in_mode VARCHAR(6),
    out_mode VARCHAR(6),
    rounds_player1 INTEGER,
    rounds_player2 INTEGER,
    avg_player1 NUMERIC(4, 1),
    avg_player2 NUMERIC(4, 1)
);

CREATE INDEX avg_player1 ON game(avg_player1);