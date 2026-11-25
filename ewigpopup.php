<?php
/** Liga Manager Online 4
  *
  * http://lmo.sourceforge.net/
  *
  * This program is free software; you can redistribute it and/or
  * modify it under the terms of the GNU General Public License as
  * published by the Free Software Foundation; either version 2 of
  * the License, or (at your option) any later version.
  *
  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
  * General Public License for more details.
  *
  * REMOVING OR CHANGING THE COPYRIGHT NOTICES IS NOT ALLOWED!
  *
  */

//ERROR_REPORTING(E_ALL);

require(dirname(__FILE__) . '/init.php');

if (!defined('PATH_TO_TIPPARCHIV')) {
    define('PATH_TO_TIPPARCHIV', PATH_TO_ADDONDIR . '/tipp/archiv/');
}

if (!is_dir(PATH_TO_TIPPARCHIV)) {
    die('Pfad zum Tippspielarchiv ' . PATH_TO_TIPPARCHIV . ' nicht vorhanden!');
}

$gesamtfiles = [];

$tipparchiv = opendir(PATH_TO_TIPPARCHIV);

while (($file = readdir($tipparchiv)) !== false) {
    if ($file != '.' && $file != '..' && $file != 'index.htm') {
        // Prüfen ob die Datei richtig benannt ist - form gesamtXXYY.aus
        if ((strlen($file) == 14) and (substr($file, 0, 6) == 'gesamt') and (substr($file, 10, 4) == '.aus')) {
            array_push($gesamtfiles, $file);
        }
    }
}
closedir($tipparchiv);

if (sizeof($gesamtfiles) == 0) {
    die('Keine Auswertungsfiles *.aus im Ordner ' . PATH_TO_TIPPARCHIV . ' vorhanden!');
}

// Saisons nach Datum sortieren
array_multisort($gesamtfiles, SORT_ASC, SORT_STRING, $gesamtfiles);

$saisons = [];
$punkte_max = 0;
$gesamt = $platz1 = $platz2 = [];
$tipper = $tipperklein = [];
$anztipper = 0;
$saisonLabels = [];  // Beschriftung der Saisons (z. B. 23/24)
$punkteSaison = [];  // je Saison: Name => Punkte

// Gesamtarray mit allen Daten bilden
for ($s = 0; $s < count($gesamtfiles); $s++) {

    // Datei gesamt.aus in Array einlesen... evtl. Pfad anpassen
    $auswertdatei = PATH_TO_TIPPARCHIV . $gesamtfiles[$s];  // "gesamt2425.aus";

    $array = @file($auswertdatei);
    if (!is_array($array)) {
        continue;
    }  // Datei nicht lesbar -> Saison überspringen


    // Anzahl der Tipp-Ligen ermitteln
    $zeile = trim($array[1] ?? '');  // unnötige Zeilenumbrüche ... entfernen
    $anzligen = (int)substr($zeile, 9, strlen($zeile));  // -> eigentlich immer ab der 10. Stelle

    $saisonLabels[] = substr($gesamtfiles[$s], 6, 2) . '/' . substr($gesamtfiles[$s], 8, 2);
    $punkteSaison[] = [];
    $sIdx = count($punkteSaison) - 1;
    $aktName = '';

    $ligenkurz = [];                    // Beinhaltet Kürzel für Ligen
    $anzgetipptkurz = [];               // Beinhaltet Kürzel für Anzahl getippter Spiele
    for ($i = 1; $i <= $anzligen; $i++) {
        array_push($ligenkurz, 'TP'.$i);       // -- Ligenkürzel = 'TP'.$i
        array_push($anzgetipptkurz, 'SG'.$i);  // -- Kürzel = 'SG'.$i
    }




    for ($i = $anzligen + 3; $i < sizeof($array); $i++) {

        // Usernamen ermitteln, wenn gefunden in Array speichern
        $posname = strpos($array[$i], '[');
        if ($posname !== false) {
            $aktName = trim($array[$i]);
            $punkteSaison[$sIdx][$aktName] = 0;  // Tipper war in dieser Saison dabei
            // gefundenen Namen ins Array speichern - aber nur wenn der Name noch nicht enthalten ist
            if (!in_array(trim($array[$i]), $tipper)) {
                $tipper[++$anztipper] = trim($array[$i]);
                $tipperklein[$anztipper] = strtolower(trim($array[$i]));
            }
        }
        // erzielte Punkte (TP1=, TP2=, ...) dem aktuellen Tipper zuzählen
        if ($aktName !== '') {
            foreach ($ligenkurz as $value) {
                if (strpos($array[$i], $value . '=') === 0) {
                    $punkteSaison[$sIdx][$aktName] += (int)ltrim(strrchr($array[$i], '='), '=');
                }
            }
        }
    }  //for - Anzahl Tipper ermitteln end

    //array_multisort($punkte, SORT_DESC, SORT_NUMERIC, $spiele, SORT_DESC, SORT_NUMERIC, $tipper, SORT_DESC, SORT_STRING);
    array_multisort($tipperklein, SORT_ASC, SORT_STRING, $tipper, SORT_ASC, SORT_STRING);


}  // for Gesamtarray mit allen Daten bilden end

unset($punkte);
unset($spiele);



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Tippspielervergleich</title>
<style type="text/css">
body {
  background-color: #B9C4CC;
    /* Farbe der Scrollbalken */
    scrollbar-face-color: #B9C4CC;
    scrollbar-track-color: #B9C4CC;
    scrollbar-highlight-color: #B9C4CC;
    scrollbar-3dlight-color: #B9C4CC;
    scrollbar-darkshadow-color: #B9C4CC;
    scrollbar-base-color:#B9C4CC;
    scrollbar-arrow-color:  #889CB0;
    scrollbar-shadow-color: #889CB0;
  padding:0;
  margin:5px;
  }
div#wrapper {
  /*min-width:650px;*/
  height:100%;
/*  background-color:#9F99F9;*/
  margin:0 auto;
  padding:0;
}
.divlinks {
  float:left;
  text-align:center;
  /*width:197px;*/
  border: 1px dashed silver;
  padding: 10px 5px 3px 5px;
  -moz-border-radius: 0.4em; /* Ecken gerundet, FF only */
}
.divmitte {
  clear:both;
  text-align:center;
  /*width:197px;*/
  padding: 10px 5px 5px 5px;
  border: 1px dashed silver;
  /*float:right;*/
  -moz-border-radius: 0.4em; /* Ecken gerundet, FF only */
}

.divrechts {
  float:right;
  text-align:center;
  /*width:197px;*/
  padding: 10px 5px 3px 5px;
  border: 1px dashed silver;
  /*float:right;*/
  -moz-border-radius: 0.4em; /* Ecken gerundet, FF only */
}
.absenden {
  clear:both;
  text-align:center;

  height: 40px;
  line-height:40px;
}
</style>
</head>
<body>

<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">
  <div id="wrapper">

<?php

if (isset($_POST['tipper1'])) {
    $tipper1 = strip_tags($_POST['tipper1']);
    //$options ='';
} else {
    $tipper1 = isset($_GET['best1']) ? strip_tags((string)$_GET['best1']) : '';
}

if (isset($_POST['tipper2'])) {
    $tipper2 = strip_tags($_POST['tipper2']);
} else {
    $tipper2 = isset($_GET['best2']) ? strip_tags((string)$_GET['best2']) : '';
}

// Tippspieler 1 und 2 bestimmen; ohne Angabe (oder bei unbekanntem Namen) die beiden mit den meisten Gesamtpunkten
$gesamtpunkte = [];
foreach ($punkteSaison as $saisonWerte) {
    foreach ($saisonWerte as $n => $pkt) {
        $gesamtpunkte[$n] = ($gesamtpunkte[$n] ?? 0) + $pkt;
    }
}
arsort($gesamtpunkte);
$besten = array_keys($gesamtpunkte);
$findeName = function ($eingabe) use ($tipper, $tipperklein) {
    $idx = array_search(strtolower($eingabe), $tipperklein, true);
    return $idx === false ? '' : $tipper[$idx];
};
$name1 = $findeName($tipper1);
$name2 = $findeName($tipper2);
if ($name1 === '') {
    $name1 = $besten[0] ?? '';
}
if ($name2 === '') {
    $name2 = $besten[1] ?? '';
}
$tipper1 = $name1;
$tipper2 = $name2;

echo '    <div class="divlinks"><select name="tipper1" onchange="this.form.submit()">';

for ($i = 0; $i < sizeof($tipperklein); $i++) {
    $name_tmp = (strlen($tipper[$i]) - 2 > 20) ? substr($tipper[$i], 0, 20) . '&hellip;]' : $tipper[$i];
    echo '<option ' . ($tipper[$i] === $name1 ? 'selected="selected"' : '') . ' value="'.htmlspecialchars($tipper[$i], ENT_QUOTES, 'UTF-8').'">' . htmlspecialchars($name_tmp, ENT_QUOTES, 'UTF-8') . '</option>';
}
echo '</select>

    </div>

      <div class="divrechts"><select name="tipper2" onchange="this.form.submit()">';

for ($i = 0; $i < sizeof($tipperklein); $i++) {
    $name_tmp = (strlen($tipper[$i]) - 2 > 20) ? substr($tipper[$i], 0, 20) . '&hellip;]' : $tipper[$i];
    echo '<option ' . ($tipper[$i] === $name2 ? 'selected="selected"' : '') . ' value="'.htmlspecialchars($tipper[$i], ENT_QUOTES, 'UTF-8').'">' . htmlspecialchars($name_tmp, ENT_QUOTES, 'UTF-8') . '</option>';
}
echo '</select>

    </div>';

// Kurven: Punkte je Saison (null = in dieser Saison nicht dabei -> Lücke)
$kurven = [];
foreach ([[$name1, '0,0,255'], [$name2, '255,0,0']] as $eintrag) {
    if ($eintrag[0] === '') {
        continue;
    }
    $werte = [];
    foreach ($punkteSaison as $saisonWerte) {
        $werte[] = array_key_exists($eintrag[0], $saisonWerte) ? $saisonWerte[$eintrag[0]] : null;
    }
    $kurven[] = ['label' => $eintrag[0], 'color' => $eintrag[1], 'data' => $werte];
}
$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

echo '
    <div class="divmitte">
      <div style="position:relative;width:100%;max-width:720px;height:380px;"><canvas id="ewigChart"></canvas></div>
    </div>
    <script src="' . URL_TO_JSDIR . '/chart.js"></script>
    <script>
      (function () {
        var kurven = ' . json_encode($kurven, $jsonFlags) . ';
        var axisColor = "rgba(90, 90, 90, 1)";
        new Chart(document.getElementById("ewigChart"), {
          type: "line",
          data: {
            labels: ' . json_encode($saisonLabels, $jsonFlags) . ',
            datasets: kurven.map(function (k) {
              return {
                label: k.label,
                data: k.data,
                fill: false,
                spanGaps: false,
                tension: 0.4,
                cubicInterpolationMode: "monotone",
                borderWidth: 3,
                pointRadius: 4,
                backgroundColor: "rgba(" + k.color + ",1)",
                borderColor: "rgba(" + k.color + ",1)"
              };
            })
          },
          options: {
            maintainAspectRatio: false,
            scales: {
              x: { grid: { color: axisColor }, title: { display: true, text: "Saison", color: axisColor }, ticks: { color: axisColor } },
              y: { min: 0, grid: { color: axisColor }, title: { display: true, text: "Punkte", color: axisColor }, ticks: { color: axisColor, precision: 0 } }
            },
            plugins: { tooltip: { mode: "index", intersect: false } }
          }
        });
      })();
    </script>';

?>
    <div class="absenden"><input type="submit" value="absenden" /></div>
  </div>
</form>

</body>
</html>