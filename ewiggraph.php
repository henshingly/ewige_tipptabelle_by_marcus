<?
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

require(dirname(__FILE__)."/init.php");


// falls die Auswertung nur eingeloggte User zu Gesicht bekommen sollen:
/*if (($show_regist_only == 1) and ($_SESSION["lmotipperok"] != 5)) {
  die("Sorry, aber der Zugang ist nur für eingeloggte User möglich!");
}*/

//$zeit1 = microtime();  //zeit nehmen start


if (!defined('PATH_TO_TIPPARCHIV'))   define('PATH_TO_TIPPARCHIV',    PATH_TO_ADDONDIR . "/tipp/archiv/");

if (!is_dir(PATH_TO_TIPPARCHIV)) {
  die("Pfad zum Tippspielarchiv " . PATH_TO_TIPPARCHIV . " nicht vorhanden!");
}

$gesamtfiles = array();

$tipparchiv = opendir (PATH_TO_TIPPARCHIV);

while ($file = readdir($tipparchiv)) {
  if ($file != "." && $file != ".." && $file != "index.htm") {
    // Prüfen ob die Datei richtig benannt ist - form gesamtXXYY.aus
    if ((strlen($file) == 14) and (substr($file, 0, 6) == "gesamt") and (substr($file, 10, 4) == ".aus")) {
      Array_push($gesamtfiles, $file);
    }
  }
}
closedir($tipparchiv);

if (sizeof($gesamtfiles) == 0) {
  die("Keine Auswertungsfiles *.aus im Ordner " . PATH_TO_TIPPARCHIV . " vorhanden!");
}

// Saisons nach Datum sortieren
array_multisort($gesamtfiles, SORT_ASC, SORT_STRING, $gesamtfiles);

$saisons = array();
$punkte_max = 0;
$gesamt = $platz1 = $platz2 = array();

// Gesamtarray mit allen Daten bilden
for ($s = 0; $s < count($gesamtfiles); $s ++) {

  // Datei gesamt.aus in Array einlesen... evtl. Pfad anpassen
  $auswertdatei = PATH_TO_TIPPARCHIV . $gesamtfiles[$s];  // "gesamt2425.aus";

  $array = @file($auswertdatei);


  // Anzahl der Tipp-Ligen ermitteln
  $zeile = trim($array[1]);  // unnötige Zeilenumbrüche ... entfernen
  $anzligen = (int)substr($zeile, 9, strlen($zeile));  / /-> eigentlich immer ab der 10. Stelle

  $ligenkurz = array();                    // Beinhaltet Kürzel für Ligen
  $anzgetipptkurz = array();               // Beinhaltet Kürzel für Anzahl getippter Spiele
  for ($i=1; $i<=$anzligen; $i++) {
    Array_push($ligenkurz, 'TP'.$i);       //-- ligenkürzel = 'TP'.$i
    Array_push($anzgetipptkurz, 'SG'.$i);  //-- kürzel = 'SG'.$i
  }

  $anztipper=0;
  $tipper = $spiele = $punkte = array();

  for ($i = $anzligen+3; $i < sizeof($array); $i++) {

    // Usernamen ermitteln, wenn gefunden in Array speichern
    $posname = strpos($array[$i], "[");
    if ($posname !== false) {
      // Gefundenen Namen ins Array speichern
      $tipper[++$anztipper] = trim($array[$i]);
      $spiele[$anztipper] = $punkte[$anztipper] = 0;
    }


    // foreach1 ermittelt die erzielten Punkte
    foreach ($ligenkurz as $value) {
      $value = $value."=";  // = muss stehen da bei TP1 auch TP10 TP11 erfasst
      $pos1 = strpos($array[$i], $value);
      if ($pos1 !== false) {
         // Punkte gleich Array dazu addieren
         $punkte[$anztipper] += (int)ltrim(strrchr($array[$i],'='),'=');
      }
    }  // foreach1 end

    // foreach2 ermittelt die Anzahl an getippten Spielen
    foreach ($anzgetipptkurz as $value) {
      $value = $value."=";  // = muss stehen da bei TP1 auch TP10 TP11 erfasst
      $pos1 = strpos($array[$i], $value);
      if ($pos1 !== false) {
        // Anzahl getippter Spiele gleich Array dazu addieren
        $spiele[$anztipper] += (int)ltrim(strrchr($array[$i],'='),'=');
      }
    }  // foreach2 end

  } //for - Anzahl Tipper ermitteln end


  //array_multisort($punkte, SORT_DESC, SORT_NUMERIC, $spiele, SORT_DESC, SORT_NUMERIC, $tipper, SORT_DESC, SORT_STRING);
  Array_multisort($punkte, SORT_DESC, SORT_NUMERIC, $spiele, SORT_ASC, SORT_NUMERIC, $tipper, SORT_ASC, SORT_STRING);

  $saison = substr($gesamtfiles[$s], 6, 4);
  $platz1[$saison] = 0;
  $platz2[$saison] = 0;
  $saisons[] = substr($gesamtfiles[$s], 6, 2) . "/" . substr($gesamtfiles[$s], 8, 2);  // für Tabellenbeschriftung

  for ($i=0; $i<count($tipper); $i++) {
    $name=$tipper[$i];
    $gesamt[$name]['gesamt_spiele'] += $spiele[$i];  // Spiele werden aufaddiert = gesamtspiele
    $gesamt[$name]['gesamt_punkte'] += $punkte[$i];  // Punkte werden aufaddiert = gesamtpunkte
    $gesamt[$name]['spiele'.$saison] = $spiele[$i];  // für jede Saison wird ein neuer Eintrag angelegt
    $gesamt[$name]['punkte'.$saison] = $punkte[$i];

    if ($punkte[$i] > $punkte_max) { $punkte_max = $punkte[$i]; }  // höchsten Punktewerte ermitteln
  }

}  // for Gesamtarray mit allen Daten end 

unset($punkte);
unset($spiele);

// Anzahl Tippsaison
$pgst = sizeof($saisons);

// Y-achse max Wert - aufgerundet
$pgteams = $punkte_max = ceil($punkte_max/100);  // ceil — Rundet Brüche auf


// Gesamt Array ist nun mit alles Saisons gefüllt
// nun gehts ans sortieren....
$z=0;
$tipper=array();
$quote=array();

$shownichttipper = 0;
// diese foreach stellt Werte für die Usersortierung bereit
foreach ($gesamt as $name => $inhalt) {
  if (($shownichttipper != 0) or ($inhalt['gesamt_spiele'] > 0)) {
    $punkte[$name] = $inhalt['gesamt_punkte'];
    $spiele[$name] = $inhalt['gesamt_spiele'];
    $tipper[] = strtolower($name);
    if ($inhalt['gesamt_spiele'] > 0) {
      $quote[] = $inhalt['gesamt_punkte']/$inhalt['gesamt_spiele'];
    }
    else {
      $quote[] = 0;
    }
  }
}

array_multisort($punkte, SORT_DESC, SORT_NUMERIC, $spiele, SORT_ASC, SORT_NUMERIC, $punkte);

//echo "<pre>";
//print_r($gesamt);

// Ausgabe alle Tippspieler mit Gesamtpunkten
/*foreach ($punkte as $name => $anzpunkte)
{
  echo "<br>" . $name . " " . $anzpunkte;
}*/


// Tippspieler Platz 1 und 2 - mit den meisten Punkten - Vorauswahl
$i = 1;
foreach ($punkte as $name => $anzpunkte) {
  switch ($i) {
    case 1: $name1 = $name; break;  // Platz 1
    case 2: $name2 = $name; break;  // Platz 2
  }
  if ($i++ == 2) { break; }
}


// Tippspieler 1 - entweder per get übergeben oder der mit den meisten Gesamtppunkten
if ((isset($_GET['tipper1'])) and ((strlen($_GET['tipper1']) > 0))) {
  $pgteam1 = strip_tags($_GET['tipper1']);
  $punkte_tmp = $gesamt[$pgteam1];
  $pgteam1_tmp = (strlen($pgteam1)-2 > 15) ? substr($pgteam1, 0, 15) . '*]' : $pgteam1;
}
else {
  $pgteam1_tmp = $pgteam1 = $name1;
  $punkte_tmp = $gesamt[$pgteam1];
}
array_shift($punkte_tmp);  // gesamt_spiele löschen
array_shift($punkte_tmp);  // gesamt_punkte löschen

//echo "<pre>";
//print_r($punkte_tmp);

$i = 1;

// Array erst mit Punkte der jeweiligen Saison füllen - Gesamtplatz 1
foreach ($punkte_tmp as $name => $wert) {
  if (strpos($name, "punkte") === 0) { $platz1[substr($name, 6, 4)] = $wert; }
}
foreach ($platz1 as $saison => $punkte) {
  if ($punkte == 0) {
    $pgplatz1[] = 0.01;
  }
  else {
    $pgplatz1[] = round($punkte/100, 2);
  }
}
//echo "<pre>";
//print_r($pgplatz1);


// Tippspieler 2
if ((isset($_GET['tipper2'])) and ((strlen($_GET['tipper2']) > 0))) {
  $pgteam2 = strip_tags($_GET['tipper2']);
  $punkte_tmp = $gesamt[$pgteam2];
  $pgteam2_tmp = (strlen($pgteam2)-2 > 15) ? substr($pgteam2, 0, 15) . '*]' : $pgteam2;
}
else {
  $pgteam2_tmp = $pgteam2 = $name2;
  $punkte_tmp = $gesamt[$pgteam2];
}
array_shift($punkte_tmp);  // gesamt_spiele löschen
array_shift($punkte_tmp);  // gesamt_punkte löschen

$i = 1;

// Array erst mit Punkte der jeweiligen Saison füllen - Gesamtplatz 2
foreach ($punkte_tmp as $name => $wert) {
  if (strpos($name, "punkte") === 0) { $platz2[substr($name, 6, 4)] = $wert; }
}
foreach ($platz2 as $saison => $punkte) {
  if ($punkte == 0) {
    $pgplatz2[] = 0.01;
  }
  else {
    $pgplatz2[] = round($punkte/100, 2);
  }
}

//echo "<pre>";
//print_r($pgplatz2);
/*echo "<pre>";
//print_r($punkte_tmp);
print_r($pgplatz1);

die;*/


$pgtext1="SAISON";  // $text[135];
$pgtext2="PUNKTE";  // $text[136];

//$pgst=isset($_GET['pgst'])?$_GET['pgst']:1;
//$pgteams=isset($_GET['pgteams'])?$_GET['pgteams']:1;
//$pgteam1=isset($_GET['pgteam1'])?$_GET['pgteam1']:'';
//$pgteam2=isset($_GET['pgteam2'])?$_GET['pgteam2']:'';





//$pgteam1 = isset($_GET['tipper1']) ? strip_tags($_GET['tipper1']) : $name1;  // $_GET['tipper1'];  // $name1;  // "[Marcus]";
//$pgteam2 = isset($_GET['tipper2']) ? strip_tags($_GET['tipper2']) : $name2;  // $name2;  // "[mountainking]";

//$pgplatz1=isset($_GET['pgplatz1'])?$_GET['pgplatz1']:1;
//$pgplatz1="5.3,0.01,7,8,9,2";
//$pgplatz2=isset($_GET['pgplatz2'])?$_GET['pgplatz2']:'';
//$pgplatz2="1,3,9,8,3,6";
//$pganz=isset($_GET['pganz'])?$_GET['pganz']:1;
$pganz=2;


$pgch=isset($_GET['pgch'])?$_GET['pgch']:0;
$pgcl=isset($_GET['pgcl'])?$_GET['pgcl']:0;
$pgck=isset($_GET['pgck'])?$_GET['pgck']:0;
$pguc=isset($_GET['pguc'])?$_GET['pguc']:0;
$pgar=isset($_GET['pgar'])?$_GET['pgar']:0;
$pgab=isset($_GET['pgab'])?$_GET['pgab']:0;


$lmo_faktorhorizontal=30;  // round(21-$pgst/8);
$lmo_faktorvertikal=30;    // round(17-$pgteams/8);

$hoch = (($pgteams+1) * $lmo_faktorvertikal)+47;
$breit = (($pgst+1) * $lmo_faktorhorizontal)+35;
//if ($breit < 300) { $breit = 300; }



$vergleich = imagefontwidth(3) * strlen(stripslashes($pgteam1_tmp))+3;
if ($pganz == 2) {
  $vergleich += imagefontwidth(3) * strlen(stripslashes($pgteam2_tmp))+4;
}
if ($breit < $vergleich) {
  $breit = $vergleich;
}
$image = imagecreate($breit, $hoch);
imageinterlace($image, 0);

$color = isset($lmo_inner_background1)?get_color($lmo_inner_background1):array(255, 255, 255);
$farbe_body = imagecolorallocate($image, $color[0], $color[1], $color[2]);  // Hintergrund

$luminanz=0.3*$color[0] + 0.59*$color[1] + 0.11*$color[2];
$color = $luminanz > 127?array(($color[0]+190-$luminanz),($color[1]+190-$luminanz),($color[2]+190-$luminanz)):array(($color[0]+127-$luminanz),($color[1]+127-$luminanz),($color[2]+127-$luminanz));

$farbe_b = imagecolorallocate($image, $color[0], $color[1], $color[2]);  // Gitter

$color = isset($lmo_inner_color1)?get_color($lmo_inner_color1):array(0, 0, 0);
$farbe_a = imagecolorallocate($image, $color[0], $color[1], $color[2]);  // Schrift

$color = isset($lmo_fieber_color1)?get_color($lmo_fieber_color1):array(0, 0, 255);
$farbe_c = imagecolorallocate($image, $color[0], $color[1], $color[2]);  // Linie1 & Mannschaft1

$color = isset($lmo_fieber_color2)?get_color($lmo_fieber_color2):array(255, 0, 0);
$farbe_d = imagecolorallocate($image, $color[0], $color[1], $color[2]);  // Linie2 & Mannschaft2

$color = isset($lmo_tabelle_background1)?get_color($lmo_tabelle_background1):array(237, 244, 156);
$farbe_e = imagecolorallocate($image, $color[0], $color[1], $color[2]);  // Meister

$color = isset($lmo_tabelle_background2)?get_color($lmo_tabelle_background2):array(204, 205, 254);
$farbe_f = imagecolorallocate($image, $color[0], $color[1], $color[2]);  // Champleague

$color = isset($lmo_tabelle_background3)?get_color($lmo_tabelle_background3):array(166, 238, 237);
$farbe_g = imagecolorallocate($image, $color[0], $color[1], $color[2]);  // Champquali

$color = isset($lmo_tabelle_background4)?get_color($lmo_tabelle_background4):array(192, 255, 192);
$farbe_h = imagecolorallocate($image, $color[0], $color[1], $color[2]);  // UEFA

$color = isset($lmo_tabelle_background6)?get_color($lmo_tabelle_background6):array(255, 187, 208);
$farbe_i = imagecolorallocate($image, $color[0], $color[1], $color[2]);  // Abstieg

$color = isset($lmo_tabelle_background5)?get_color($lmo_tabelle_background5):array(255, 208, 239);
$farbe_j = imagecolorallocate($image, $color[0], $color[1], $color[2]);  // Abstiegsrelegation

$farbe_k = imagecolorallocate($image, 24, 148, 45);  // Top Tipper


imagestring($image, 2, 28, 28+(($pgteams+1) * $lmo_faktorvertikal), $pgtext1, $farbe_a);  // untere Beschriftung (SPIELTAGE)
imagestringup($image, 2, 4, $hoch-28, $pgtext2, $farbe_a);                                // seitliche Beschriftung (PLATZIERUNG)

// Spieltagsbeschriftung vertikal
for($i = 0; $i < $pgteams; $i++) {
  $j = ($pgteams*100)-strval($i)*100;
  /*if ($i < 10) {
    $j = "0".$j;
  }*/
  imagestring($image, 1, 8, 26+$i*$lmo_faktorvertikal, $j, $farbe_a);  // links
  imagestring($image, 1, 32-$lmo_faktorhorizontal+($pgst+1)*$lmo_faktorhorizontal, 26+$i*$lmo_faktorvertikal, $j, $farbe_a);  // Rechts
}

// Spieltagsbeschriftung horizontal
for($i = 1; $i <= $pgst; $i++) {
  //$j = strval($i);
  /*if ($i < 10) {
    $j = "0".$j;
  }*/
  $j = strval($saisons[$i-1]);
  imagestring($image, 1, 31-$lmo_faktorhorizontal+$i*$lmo_faktorhorizontal, 18, $j, $farbe_a);  // horizontale Spieltagsbeschriftung oben (im, offsetLeft+i*faktor, offsetTop, STNr, farbe)
  imagestring($image, 1, 31-$lmo_faktorhorizontal+$i*$lmo_faktorhorizontal, 30-$lmo_faktorvertikal+(($pgteams+1) * $lmo_faktorvertikal), $j, $farbe_a);  // horizontale Spieltagsbeschriftung unten
}

// Kästchen
for($i = 0; $i < $pgteams; $i++) {
  imagerectangle($image, 29, 28+$i*$lmo_faktorvertikal, (29-$lmo_faktorhorizontal)+(($pgst+1) * $lmo_faktorhorizontal), 28+$lmo_faktorvertikal+($i*$lmo_faktorvertikal), $farbe_b);  // horizontal
}
for($i = 0; $i < $pgst; $i++) {
  imagerectangle($image, 29+$i*$lmo_faktorhorizontal, 28, (29+$lmo_faktorhorizontal)+$i*$lmo_faktorhorizontal, 28-$lmo_faktorvertikal+(($pgteams+1)*$lmo_faktorvertikal), $farbe_b);  // vertikal

}

// Y unten (null) ermitteln
$y_min = 28-$lmo_faktorvertikal+(($pgteams+1)*$lmo_faktorvertikal);

$j = 1;
for($i = 1; $i <= $pgteams; $i++) {
  if (($i == 1) && ($pgch != 0)) {
    $j = 2;
    for($k = 1; $k <= $pgst; $k++) {
      imagefill($image, 28+($k * $lmo_faktorhorizontal), 20+($i * $lmo_faktorvertikal), $farbe_e);
    }
  }
  if (($i >= $j) && ($i < $j+$pgcl) && ($pgcl > 0)) {
    for($k = 1; $k <= $pgst; $k++) {
      imagefill($image, 28+($k * $lmo_faktorhorizontal), 20+($i * $lmo_faktorvertikal), $farbe_f);
    }
  }
  if (($i >= $j+$pgcl) && ($i < $j+$pgcl+$pgck) && ($pgck > 0)) {
    for($k = 1; $k <= $pgst; $k++) {
      imagefill($image, 28+($k * $lmo_faktorhorizontal), 20+($i * $lmo_faktorvertikal), $farbe_g);
    }
  }
  if (($i >= $j+$pgcl+$pgck) && ($i < $j+$pgcl+$pgck+$pguc) && ($pguc > 0)) {
    for($k = 1; $k <= $pgst; $k++) {
      imagefill($image, 28+($k * $lmo_faktorhorizontal), 20+($i * $lmo_faktorvertikal), $farbe_h);
    }
  }
  if (($i <= $pgteams) && ($i > $pgteams-$pgab) && ($pgab > 0)) {
    for($k = 1; $k <= $pgst; $k++) {
      imagefill($image, 28+($k * $lmo_faktorhorizontal), 20+($i * $lmo_faktorvertikal), $farbe_i);
    }
  }
  if (($i <= $pgteams-$pgab) && ($i > $pgteams-$pgab-$pgar) && ($pgar > 0)) {
    for($k = 1; $k <= $pgst; $k++) {
      imagefill($image, 28+($k * $lmo_faktorhorizontal), 20+($i * $lmo_faktorvertikal), $farbe_j);
    }
  }
}

imagestring($image, 3, 3, 1, rawurldecode(stripslashes($pgteam1_tmp)), $farbe_c);  //Mannschaftsname1
if ($pganz == 2) {
  imagestring($image, 3, $breit-imagefontwidth(3) * strlen(stripslashes($pgteam2_tmp))-2, 1, rawurldecode(stripslashes($pgteam2_tmp)), $farbe_d);  //Mannschaftsname2
}


//$pgplatz1="3.25,0.01,7,11,9,11.2";
//   imagestring($image, 1, 38, 40, $pgplatz1, $farbe_a);  //links
//$pgplatz2="0.01,10.3,9,8,3,6";
$pgplatz3="2,7.6,4,3,7,1";

//$linie = explode(',', $pgplatz1);
$linie = $pgplatz1;
if ($pganz == 2) {
  //$lini2 = explode(',', $pgplatz2);
  $lini2 = $pgplatz2;
}
$lini3 = explode(',', $pgplatz3);

for($i = 1; $i < $pgst; $i++) {
  if ($linie[$i] > 0 && $linie[$i-1] > 0) {
/*    imageline($image, 30-$lmo_faktorhorizontal/2+($i * $lmo_faktorhorizontal), 29-$lmo_faktorvertikal/2+($linie[$i-1] * $lmo_faktorvertikal), 30-$lmo_faktorhorizontal/2+(($i+1) * $lmo_faktorhorizontal), 29-$lmo_faktorvertikal/2+($linie[$i] * $lmo_faktorvertikal), $farbe_c);
    imageline($image, 29-$lmo_faktorhorizontal/2+($i * $lmo_faktorhorizontal), 29-$lmo_faktorvertikal/2+($linie[$i-1] * $lmo_faktorvertikal), 29-$lmo_faktorhorizontal/2+(($i+1) * $lmo_faktorhorizontal), 29-$lmo_faktorvertikal/2+($linie[$i] * $lmo_faktorvertikal), $farbe_c);
    imageline($image, 30-$lmo_faktorhorizontal/2+($i * $lmo_faktorhorizontal), 30-$lmo_faktorvertikal/2+($linie[$i-1] * $lmo_faktorvertikal), 30-$lmo_faktorhorizontal/2+(($i+1) * $lmo_faktorhorizontal), 30-$lmo_faktorvertikal/2+($linie[$i] * $lmo_faktorvertikal), $farbe_c);
    imageline($image, 29-$lmo_faktorhorizontal/2+($i * $lmo_faktorhorizontal), 30-$lmo_faktorvertikal/2+($linie[$i-1] * $lmo_faktorvertikal), 29-$lmo_faktorhorizontal/2+(($i+1) * $lmo_faktorhorizontal), 30-$lmo_faktorvertikal/2+($linie[$i] * $lmo_faktorvertikal), $farbe_c);
*/
    //bester
/*      imageline($image,
        29-$lmo_faktorhorizontal/2+($i * $lmo_faktorhorizontal),
        $y_min-$lmo_faktorvertikal*$lini3[$i-1],
        29-$lmo_faktorhorizontal/2+(($i+1) * $lmo_faktorhorizontal),
        $y_min-$lmo_faktorvertikal*$lini3[$i],
        $farbe_k);*/


      imageline($image,
        30-$lmo_faktorhorizontal/2+($i * $lmo_faktorhorizontal),
        $y_min-$lmo_faktorvertikal*$linie[$i-1],
        30-$lmo_faktorhorizontal/2+(($i+1) * $lmo_faktorhorizontal),
        $y_min-$lmo_faktorvertikal*$linie[$i],
        $farbe_c);
      imageline($image,
        29-$lmo_faktorhorizontal/2+($i * $lmo_faktorhorizontal),
        $y_min-$lmo_faktorvertikal*$linie[$i-1],
        29-$lmo_faktorhorizontal/2+(($i+1) * $lmo_faktorhorizontal),
        $y_min-$lmo_faktorvertikal*$linie[$i],
        $farbe_c);


  }
  if ($pganz == 2) {
    if ($lini2[$i] > 0 && $lini2[$i-1] > 0) {
      //imageline($image, 30-$lmo_faktorhorizontal/2+($i * $lmo_faktorhorizontal), 29-$lmo_faktorvertikal/2+($lini2[$i-1] * $lmo_faktorvertikal), 30-$lmo_faktorhorizontal/2+(($i+1) * $lmo_faktorhorizontal), 29-$lmo_faktorvertikal/2+($lini2[$i] * $lmo_faktorvertikal), $farbe_d);

//imagestring($image, 1, 38, 40, $y_min, $farbe_a);  //links

      imageline($image,
        30-$lmo_faktorhorizontal/2+($i * $lmo_faktorhorizontal),
        $y_min-$lmo_faktorvertikal*$lini2[$i-1],
        30-$lmo_faktorhorizontal/2+(($i+1) * $lmo_faktorhorizontal),
        $y_min-$lmo_faktorvertikal*$lini2[$i],
        $farbe_d);
      imageline($image,
        29-$lmo_faktorhorizontal/2+($i * $lmo_faktorhorizontal),
        $y_min-$lmo_faktorvertikal*$lini2[$i-1],
        29-$lmo_faktorhorizontal/2+(($i+1) * $lmo_faktorhorizontal),
        $y_min-$lmo_faktorvertikal*$lini2[$i],
        $farbe_d);

/*      imageline($image, 29-$lmo_faktorhorizontal/2+($i * $lmo_faktorhorizontal), 29-$lmo_faktorvertikal/2+($lini2[$i-1] * $lmo_faktorvertikal), 29-$lmo_faktorhorizontal/2+(($i+1) * $lmo_faktorhorizontal), 29-$lmo_faktorvertikal/2+($lini2[$i] * $lmo_faktorvertikal), $farbe_d);
      imageline($image, 30-$lmo_faktorhorizontal/2+($i * $lmo_faktorhorizontal), 30-$lmo_faktorvertikal/2+($lini2[$i-1] * $lmo_faktorvertikal), 30-$lmo_faktorhorizontal/2+(($i+1) * $lmo_faktorhorizontal), 30-$lmo_faktorvertikal/2+($lini2[$i] * $lmo_faktorvertikal), $farbe_d);
      imageline($image, 29-$lmo_faktorhorizontal/2+($i * $lmo_faktorhorizontal), 30-$lmo_faktorvertikal/2+($lini2[$i-1] * $lmo_faktorvertikal), 29-$lmo_faktorhorizontal/2+(($i+1) * $lmo_faktorhorizontal), 30-$lmo_faktorvertikal/2+($lini2[$i] * $lmo_faktorvertikal), $farbe_d);
*/
    }
  }
}

header("Content-Type: image/png");
imagepng($image);
imagedestroy($image);


function get_color(&$styleclass) {
  if (strlen($styleclass) == 4) {
    return(array(hexdec(substr($styleclass, 1, 1).substr($styleclass, 1, 1)), hexdec(substr($styleclass, 2, 1).substr($styleclass, 2, 1)), hexdec(substr($styleclass, 3, 1).substr($styleclass, 3, 1))));
  } elseif (strlen($styleclass) == 7) {
    return(array(hexdec(substr($styleclass, 1, 2)), hexdec(substr($styleclass, 3, 2)), hexdec(substr($styleclass, 5, 2))));
  }
  return false;
}

clearstatcache();

?>