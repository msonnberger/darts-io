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

/*
INSERT INTO game(point_mode, in_mode, out_mode, rounds_player1, rounds_player2, avg_player1, avg_player2)
VALUES
(501, 'Single', 'Double', 5, 8, 83.5, 49.8);
*/

