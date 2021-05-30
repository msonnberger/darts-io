![logo](https://users.multimediatechnology.at/~fhs45907/mmp1/img/logo.svg)

# Technische Beschreibung

## Backend

![file-structure](https://users.multimediatechnology.at/~fhs45907/mmp1/doc/file-structure-backend.png)

Das Backend basiert auf einem WebSocket Server, der mithilfe von [Ratchet](http://socketo.me/) implementiert wurde. In `/server/bin/start-server.php` wird der Server und die zugehörige `App.php` Klasse instanziert.

In `server/bin/src/` sind 3 Klassen zu finden:

- `App.php`: Behandelt alle WebSocket messages, verwaltet verbundene Clients und Räume.
- `GameRoom.php`: Beschreibt einen einzelnen Raum, Spieleinstellungen, zugehörige Clients.
- `Game.php`: Implementiert die Logik zum Punkte berechnen einer einzelnen Darts-Runde.

### App.php

Die App Klasse erbt von `MessageComponentInterface` (aus der Ratchet Library) und behandelt das Senden und Empfangen von WebSocket requests (messages). In einem `SplObjectStorage` werden alle verbundenen Clients mit ihren zugehörigen Raum-IDs gespeichert. Das Format der messages ist immer ein JSON-Objekt mit einer `cmd` Property und (je nach message) sonstigem Content. In einem `switch` Statement werden alle möglichen `cmd` Werte abgefragt und die entsprechenden Methoden aufgerufen. Im `default` case wird an den Client eine Error Message zurückgesendent, dass dieser `cmd` nicht existiert.

`MessageComponentInterface` implementiert die 4 Methoden `onOpen`, `onMessage`, `onClose` und `onError`, welche auch im Frontend beim WebSocket `conn` Objekt zu finden sind.

In dieser Klasse werden auch neue `GameRoom` Objekte instanziert und Clients durch Aufruf der entsprechenden Methoden von `GameRoom` den Räumen hinzugefügt bzw. entfernt.

### GameRoom.php

In einem `GameRoom` Objekt werden die Spieleinstellungen des Raums und die beiden verbundenen Spieler verwaltet, wobei sich in einem Raum maximal 2 Spieler befinden können. Hier werden auch Objekte der `Game` Klasse angelegt wo dann die eigentliche Punkteberechnung stattfindet. Ist ein Spiel beendet wird ein neuen `Game` Objekt instanziert, dass wiederrum die Einstellungen des Raumes übernimmt. Werden die Einstellungen also während eines Spiels geändert, werden diese beim nächsten Spiel automatisch angewendet.

### Game.php

Hier wird der aktuelle Spielstand bestehend aus Punkten, Average, und der Anzahl an hohen Scores (> 100) gespeichert. Diese Klasse kennt die eigentlichen Clients nicht mehr, sondern hat ein Scoreboards Array, welches am Index 0 bzw. 1 die beiden Scoreboards beinhaltet.

Bei der Scoreberechung werden immer die Punkte von einer Aufnahme (3 Würfe) berechnet und dann ggf. vom aktuellen Score abgezogen. Erreicht ein Spieler genau 0 Punkte ist das Spiel beendet und die Statistiken (Average und hohe Aufnahmen) werden in die Datenbank gespeichert. Dies geschieht wenn der Destructor aufgerufen wird, also wenn entweder ein neues Spiel gestartet, oder der Raum geschlossen wird.

## Frontend

![file-structure](https://users.multimediatechnology.at/~fhs45907/mmp1/doc/file-structure-frontend.png)

Im Frontend wird zuerst die Startseite geladen, welche ein Input field für eine Raum-ID und Buttons zum Beitreten und Erstellen von Räumen beinhaltet. Außerdem ist hier ein Link zu den Statistiken sowie zum Impressum zu finden.

### app.js

`app.js` ist das einzige JS File, hier wird die WebSocket Verbindung erstellt, das Senden und Empfangen der messages verwaltet und auch das Laden, Einfügen und Verändern der verschiedenen DOM-Elemente passiert hier. Tritt ein Spieler einem Raum bei, wird mit `fetch` der Game Screen geladen, welcher das virtuelle Dartboard (bzw. Zahlen-Grid auf kleineren Screens) und die Scoreboards beinhaltet. Schickt einen Spieler seine Würfe mit `OK` ab, werden diese ans Backend geschickt; zurück kommt das neue Scoreboard mit den aktuellen Werten. Wie oben beschrieben wird für jede message ein Objekt mit einer `cmd` Property und etwaigem Content angelegt.
