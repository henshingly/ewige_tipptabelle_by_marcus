Änderungen gegenüber dem Original (Anpassung an PHP 8)

Grafik (Tippspielervergleich)
- Die Grafik wird jetzt im Browser mit Chart.js (js/chart.js aus dem LMO) gezeichnet statt als PNG mit der GD-Bibliothek.
  Die Datei ewiggraph.php wird nicht mehr benötigt und kann gelöscht werden; die GD-Bibliothek ist nicht mehr nötig.
- Die Kurven zeigen die Punkte je Saison. Saisons, in denen ein Tipper nicht dabei war, bleiben als Lücke (vorher 0).
- Die Auswahllisten schicken die Auswahl sofort ab (der Button "absenden" bleibt zusätzlich vorhanden).
- Ohne Auswahl werden die beiden Tipper mit den meisten Gesamtpunkten gezeigt.

ewigpopup.php
- Beginn mit "<?" statt "<?php": Bei ausgeschaltetem short_open_tag (heute Standard) wurde der Quelltext ausgegeben und der Vergleich blieb leer.
- Namen/URL-Parameter werden HTML-sicher ausgegeben (vorher konnte Code über ?best1= eingeschleust werden).

ewigetippspieltabelle.php
- PHP 8: nicht initialisierte Variablen/Felder, $_GET['sort'], fehlende Archivdateien.
- Fehler in der Saisonzeile ($spiele_tmp = ... . '</td>'): Zuweisung innerhalb der Zeichenkette -> "non-numeric value".
- Namen HTML-sicher; Popup-Parameter URL-kodiert; Popup-Fenstergröße fest (800 x 600).
- Pfeilbilder in der Zeile des eingeloggten Tippers kamen aus img/ (relativ) statt aus dem LMO-Bilderordner.
- jQuery wird aus dem LMO (js/jquery.min.js) geladen statt per http von Google (Version 1.4.1).

Alle Dateien: Quelltext einheitlich formatiert (PSR-12, kurze Array-Syntax, einfache Anführungszeichen, UTF-8, LF).

Hinweis: Die Bilder aus dem Ordner "img" müssen weiterhin im LMO-Ordner "img" liegen.
