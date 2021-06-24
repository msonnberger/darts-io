![logo](https://users.multimediatechnology.at/~fhs45907/mmp1/img/logo.svg)

# Verbesserungen nach User Testing und Code Review

## Bug Fixes

- Die Links auf `/statistics/index.php` waren kaputt, hab ich nun auf die neue Ordnerstruktur angepasst.
- Im Spielmodus 'Master' wurden eigentlich korrekte Scores als 'no score' gewertet. Das lag an falsch gedachter bool'scher Logik in zwei if-Abfragen in `Game.php`. Hab die Expression umgeschrieben und gleichzeitig verständlicher gemacht.

## Usability Improvements

- Nach dem Erstellen eines neuen Raums wird jetzt ein Modal angezeigt, welches auf die Raum-ID hinweist und dazu auffordert, diese mit dem Gegner zu teilen. Da ich schon an anderen Stellen Modals benutze (zum Beispiel bei Fehlern), musste ich die bereits bestehende Funktion nur leicht verändern.
- Während man auf den Wurf des Gegners wartet, wird nun ein Spinner angzeigt, der verdeutlichen soll, dass man gerade nicht dran ist und auf den Gegenspieler warten muss. Analog dazu wird 'Du bist dran!' angezeigt, wenn man schließlich an der Reihe ist.
- Verbesserte Responsiveness auf sehr kleinen Geräten wie zum Beispiel dem iPhone 5. Hierzu habe ich mit einer media-query die Schriftgröße etwas verkleinert. Da ich sonst mit den Schriften zufrieden war, hab ich einen "harten" Breakpoint gesetzt, anstatt skalierende Größen mit der `clamp()` Funktion zu setzen.
