# Ewige Tipptabelle (by Marcus)

Ewige Tipptabelle für das LMO-Tippspiel: Die Ergebnisse **aller archivierten Tippspiel-Saisons** werden zu einer
Gesamttabelle zusammengefasst (Spiele, Punkte, Punkte pro Spiel, Joker). Ein Klick auf einen Tipper zeigt seine
Punkte je Saison, und im **Tippspielervergleich** lassen sich zwei Tipper als Kurve über alle Saisons vergleichen.

Autor: Marcus ([BC Erlbach](http://www.bcerlbach.de/)), hier für **PHP 8** und die aktuelle LMO-Version überarbeitet.

## Voraussetzungen

- LMO (PHP-8-Version) mit Tippspiel-Addon (`addon/tipp`)
- Archivierte Auswertungen im Ordner `addon/tipp/archiv/`, benannt nach dem Muster `gesamtXXYY.aus`
  (z. B. `gesamt2324.aus` für die Saison 2023/24)
- Optional: die aktuelle Saison (`addon/tipp/tipps/auswert/gesamt.aus`) kann mit eingebunden werden

## Installation

1. `ewigetippspieltabelle.php` und `ewigpopup.php` in das **LMO-Hauptverzeichnis** kopieren (dorthin, wo `init.php` liegt).
2. Die Bilder aus dem Ordner `img` in den **LMO-Ordner `img`** kopieren
   (`pfeil_hoch.png`, `pfeil_hoch2.png`, `pfeil_runter.png`, `pfeil_runter2.png`, `mehr1.png`, `weniger1.png`).
3. Optional die Einstellungen am Anfang von `ewigetippspieltabelle.php` anpassen (siehe unten).
4. Aufrufen: `https://deine-domain/lmo/ewigetippspieltabelle.php`

Der Aufruf kann auch per `include` in eine eigene Seite eingebunden werden.
Die Datei `ewiggraph.php` aus älteren Versionen wird nicht mehr benötigt und kann gelöscht werden.

## Funktionen

- Sortierbare Tabelle (Name, Spiele, Punkte pro Spiel, Punkte) über die Pfeile in der Kopfzeile
- Plätze 1 bis 3 farbig hervorgehoben
- „Alle Saisons anzeigen“ je Tipper (Auf-/Zuklappen mit jQuery aus dem LMO)
- Eingeloggter Tipper wird hervorgehoben
- **Tippspielervergleich** im Popup: Kurvendiagramm der Punkte je Saison, gezeichnet im Browser mit
  Chart.js (`js/chart.js` aus dem LMO). Die GD-Bibliothek von PHP ist dafür nicht mehr nötig.
  Saisons, in denen ein Tipper nicht dabei war, bleiben als Lücke (nicht als 0).
  Ohne Auswahl werden die beiden Tipper mit den meisten Gesamtpunkten verglichen.

## Einstellungen

Im Block `ab hier können Einstellungen vorgenommen werden` in `ewigetippspieltabelle.php`:

| Einstellung                              | Bedeutung                                                   |
|------------------------------------------|-------------------------------------------------------------|
| `$fontfamily`, `$fontsize`, `$fontcolor` | Schrift, Größe, Farbe                                       |
| `$tableheadercolor`, `$tablebackcolor`, `$tablefontcolor`, `$tablefontsize` | Tabellenfarben und -schrift |
| `$colorplatz1` … `$colorplatz3` (+ `_hover`) | Farben für Platz 1–3 (leer lassen = keine Farbe)        |
| `$showtipper`                            | Anzahl angezeigter Tipper (`-1` = alle)                     |
| `$shownichttipper`                       | Tipper ohne Tipp anzeigen (`1`) oder nicht (`0`)            |
| `$show_regist_only`                      | Nur für eingeloggte Tipper sichtbar (`1`)                   |
| `$max_laenge_name`                       | Namen kürzen auf n Zeichen (`0` = nicht kürzen)             |
| `$sortieren_erlauben`                    | Sortierung per Klick erlauben (`1`) oder nicht (`0`)        |
| `$aktuelle_saison_einbinden`             | Aktuelle `gesamt.aus` mit einbinden (`1`)                   |
| `$show_sp_ges`, `$show_sp_proz`, `$show_joker`, `$show_punkte`, `$show_team` | Spalten ein-/ausblenden (1/0) |
| `$var_…`                                 | Spaltenüberschriften und deren Tooltips                     |
| `$title`                                 | Seitentitel                                                 |

Unterhalb von `ab hier nichts mehr ändern` sollte nichts angepasst werden.

## Änderungen für PHP 8

Die ausführliche Liste steht in [changelog.md](changelog.md). Kurz:

- Nicht initialisierte Variablen/Felder, `$_GET['sort']` und fehlende Archivdateien werden abgefangen
- Fehler in der Saisonzeile (Zuweisung innerhalb einer Zeichenkette -> „non-numeric value“) behoben
- `ewigpopup.php` beginnt mit `<?php` (mit `<?` wurde bei ausgeschaltetem `short_open_tag` der Quelltext ausgegeben)
- Namen und URL-Parameter werden HTML-sicher ausgegeben (vorher war über `?best1=` Script-Einschleusung möglich),
  Popup-Parameter sind URL-kodiert
- jQuery kommt aus dem LMO statt per http von Google, die Grafik aus Chart.js statt GD
- PSR-12-formatiert (php-cs-fixer), UTF-8, einheitlich LF-Zeilenenden

## Dateien

| Datei                              | Zweck                                           |
|------------------------------------|-------------------------------------------------|
| `ewigetippspieltabelle.php`        | Ewige Tipptabelle                               |
| `ewigpopup.php`                    | Popup mit dem Tippspielervergleich              |
| `img/`                             | Bilder (in den LMO-Ordner `img` kopieren)       |
| `changelog.md`                     | Änderungen gegenüber dem Original               |
| `readme_ewige_tipptabelle.htm`     | ursprüngliche Beschreibung                      |
| `LICENSE`                          | GNU General Public License                      |

## Lizenz

GNU General Public License, Version 2 oder höher – siehe [LICENSE](LICENSE).
Die Urheberhinweise im Quelltext dürfen nicht entfernt oder geändert werden.
