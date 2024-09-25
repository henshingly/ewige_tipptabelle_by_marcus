<?php

/*------------------------------------------------------------------------------
// Ewige Tippspieltabelle für LMO 4
// Autor: Marcus - www.bcerlbach.de
//------------------------------------------------------------------------------
// Download unter: http://forum.bcerlbach.de/downloads.php?cat=7

  * Wer immer über Updates informiert werden will, der sollte sich
  * im BCE-Forum registrieren. Denn dann hat man die Möglichkeit einen
  * Download zu abonieren. D.h. wenn etwas daran geändert wurde,
  * so wird umgehend eine E-Mail verschickt.

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
------------------------------------------------------------------------------*/

/***** ab hier können Einstellungen vorgenommen werden ************************/

// Schriftart - bei Standardschrift Feld leer lassen -> $fontfamily = "";
$fontfamily = "Verdana, Arial, Courier, MS Serif";

// Größe der Tabellenschrift in Punkt
$fontsize = "10";

// Schriftfarbe
$fontcolor = "#000000";

// Schriftgröße der Überschrift in Pixel
$headlinefontsize = "20";

// Farbe des Tabellenkopfes
$tableheadercolor = "#c7cfd6";

// Farbe des Tabellenschriftfarbe
$tablefontcolor = "#123456";

// Farbe des Tabellenhintergrundes
$tablebackcolor = "#C7CFD6";

// Tabellenschriftgröße
$tablefontsize = "10";

// Zellenhintergrundfarbe für Platz 1 bis 3
$colorplatz1       = "#efed25";  // wenn nicht dann frei lassen -> $colorplatz1="";
$colorplatz1_hover = "#D8C317";
$colorplatz2       = "#bab4a2";  // wenn nicht dann frei lassen -> $colorplatz2="";
$colorplatz2_hover = "#A39986";
$colorplatz3       = "#cc9b18";  // wenn nicht dann frei lassen -> $colorplatz3="";
$colorplatz3_hover = "#E3BD0F";

// Anzahl der anzuzeigenden Tipper festlegen
$showtipper = -1;  // -1=keine Begrenzung

// Sollen Tipper die noch keinen Tipp abgegeben haben angezeigt werden?
$shownichttipper = 0;  // 0=nein - 1=ja

// Soll die Auswertung nur für eingeloggte User sichtbar sein?
$show_regist_only = 0;  // 0=nein - 1=ja

// Soll der angezeigte Tippspielername begrenz werden?
$max_laenge_name = 20;  // 0 = nein; sonst länge des namens

// Ist es gestattet die Tabelle zu sortieren?
$sortieren_erlauben = 1;  // 0=nein, 1=ja

// Soll die akutelle gesamt.aus mit in die Auswertung eingebunden werden?
$aktuelle_saison_einbinden = 0;  // 0=nein, 1=ja

// Was Soll bei der Auswertung angezeigt werden?  1 = anzeigen; 0 nicht anzeigen
$show_sp_ges  = 1;  // Anzahl Spiele getippt
$show_sp_proz = 1;  // Quote richtiger Tipps - oder Punkte pro spiel
$show_joker   = 1;  // Jokerpunkte
$show_punkte  = 1;  // Anzahl Punkte -> hier ist die 1 empfohlen
$show_team    = 1;  // Teamnamen anzeigen

// Zeichen im Tabellenkopf bei der Ausgabe einstellen - Variablen anpassen
$var_spiele             = "Sp";  // Anzahl Spiele getippt - Standard "Sp"
$var_spiele_title       = "Anzahl Spiele getippt";
$var_joker              = "JP";  // durch Joker dazugewonnene Punkte - standard "JP"
$var_prozrichtig        = "Pkt ø";  // bei Tendenztipp
$var_prozrichtig_title  = "Punkte pro Spiel";
$var_tippsrichtig       = "Pkt";  // Anzahl Tipps richtig - Standard "P"
$var_tippsrichtig_title = "Anzahl Tipps richtig";


// Seitentitel
$title = "www.bcerlbach.de - Ewige Tippspieltabelle";

/***** ab hier nichts mehr ändern *********************************************/



require_once(dirname(__FILE__).'/init.php');

// falls die Auswertung nur eingeloggt User zu Gesicht bekommen sollen:
if(($show_regist_only == 1) and ($_SESSION["lmotipperok"] != 5)) {
  die("Sorry, aber der Zugang ist nur für eingeloggte User möglich!");
}

//$zeit1 = microtime();  //zeit nehmen start


if(!defined('PATH_TO_TIPPARCHIV'))  define('PATH_TO_TIPPARCHIV', PATH_TO_ADDONDIR . "/tipp/archiv/");

if(!is_dir(PATH_TO_TIPPARCHIV)) {
  die("Pfad zum Tippspielarchiv " . PATH_TO_TIPPARCHIV . " ist nicht vorhanden!<br>Den Ordner nicht erstellt?");
}

$gesamtfiles = array();

$tipparchiv = opendir(PATH_TO_TIPPARCHIV);

while($file = readdir($tipparchiv)) {
  if($file != "." && $file != ".." && $file != "index.htm") {
    // Prüfen ob die Archivdatei richtig benannt ist - Form gesamtXXYY.aus
    if((strlen($file) == 14) and (substr($file, 0, 6) == "gesamt") and (substr($file, 10, 4) == ".aus")) {
      array_push($gesamtfiles, $file);
    }
  }
}
closedir($tipparchiv);

if(sizeof($gesamtfiles) == 0) {
  die("Es ist keine Auswertungsfiles *.aus im Ordner " . PATH_TO_TIPPARCHIV . " vorhanden!");
}

// Sortieren nach Datum
array_multisort($gesamtfiles, SORT_ASC, SORT_STRING, $gesamtfiles);

if(($aktuelle_saison_einbinden == 1) or ((isset($_GET['aktuell'])) and (filter_var($_GET['aktuell'], FILTER_VALIDATE_INT) === 1))) {
  array_push($gesamtfiles, PATH_TO_ADDONDIR . "/tipp/tipps/auswert/gesamt.aus");
  $aktuelle_saison_einbinden = 1;  // Falls der Parameter genutzt wird
}


// Gesamtarray mit allen Daten bilden
for($s = 0; $s < count($gesamtfiles); $s ++) {
  if(($aktuelle_saison_einbinden == 1) and ($s+1 == count($gesamtfiles))) {
    $auswertdatei = PATH_TO_ADDONDIR . "/tipp/tipps/auswert/gesamt.aus";
    /*$j1 = substr($gesamtfiles[$s-1], 6, 2) + 1;
    $j2 = $j1 + 1;
    $saison = (strlen($j1) != 1 ? $j1 : "0" . $j1) . (strlen($j2) != 1 ? $j2 : "0" . $j2);*/
    $saison = "xxyy";
  }
  else {
    // Datei gesamt.aus in Array einlesen... evtl. Pfad anpassen
    $auswertdatei = PATH_TO_TIPPARCHIV . $gesamtfiles[$s];  //"gesamt2324.aus";
    $saison = substr($gesamtfiles[$s], 6, 4);
  }

  $array = @file($auswertdatei);


  // Anzahl der Tippligen ermitteln
  $zeile = trim($array[1]);  // unnötige Zeilenumbrüche ... entfernen
  $anzligen = (int)substr($zeile, 9, strlen($zeile));  // ->eigentlich immer ab der 10. stelle

  $ligenkurz = array();  // Beinhaltet Kürzel für Ligen
  $anzgetipptkurz = array();  // Beinhaltet Kürzel für Anzahl getippter Spiele
  for($i=1; $i<=$anzligen; $i++) {
    array_push($ligenkurz, 'TP'.$i);      //-- ligenkürzel = 'TP'.$i
    array_push($anzgetipptkurz, 'SG'.$i);  //-- kürzel = 'SG'.$i
  }

  $anztipper=0;
  $tipper=array();
  $spiele=array();
  $punkte=array();

  for($i = $anzligen+3; $i < sizeof($array); $i++) {

    // Usernamen ermitteln, wenn gefunden, in Array speichern
    $posname = strpos($array[$i], "[");
    if($posname !== false) {
      // Gefundenen Namen ins Array speichern
      $tipper[++$anztipper] = trim($array[$i]);
      $spiele[$anztipper] = $punkte[$anztipper] = 0;
    }


    // foreach1 ermittelt die erzielten Punkte
    foreach($ligenkurz as $value) {
      $value = $value."=";  // = muss stehen da bei TP1 auch TP10 TP11 erfasst
      $pos1 = strpos($array[$i], $value);
      if($pos1 !== false) {
        // Punkte gleich Array dazu addieren
        $punkte[$anztipper] += (int)ltrim(strrchr($array[$i],'='),'=');
      }
    }
    // foreach1 end

    // foreach2 ermittelt die Anzahl an getippten Spielen
    foreach($anzgetipptkurz as $value) {
      $value = $value."=";  // = muss stehen da bei TP1 auch TP10 TP11 erfasst
      $pos1 = strpos($array[$i], $value);
      if($pos1 !== false) {
        // Anzahl getippter Spiele gleich Array dazu addieren
        $spiele[$anztipper] += (int)ltrim(strrchr($array[$i],'='),'=');
      }
    }
    // foreach2 end

  }
  // for - Anzahl Tipper ermitteln
  // echo "<pre>";
  // echo ($tipper);

  //array_multisort($punkte, SORT_DESC, SORT_NUMERIC, $spiele, SORT_DESC, SORT_NUMERIC, $tipper, SORT_DESC, SORT_STRING);
  array_multisort($punkte, SORT_DESC, SORT_NUMERIC, $spiele, SORT_ASC, SORT_NUMERIC, $tipper, SORT_ASC, SORT_STRING);

  for($i=0; $i<count($tipper); $i++) {
    $name=$tipper[$i];
    $gesamt[$name]['gesamt_spiele'] += $spiele[$i];  // Spiele werden aufaddiert = gesamtspiele
    $gesamt[$name]['gesamt_punkte'] += $punkte[$i];  // Punkte werden aufaddiert = gesamtpunkte
    $gesamt[$name]['spiele'.$saison] = $spiele[$i];  // Für jede Saison wird ein neuer Eintrag angelegt
    $gesamt[$name]['punkte'.$saison] = $punkte[$i];

    if($punkte[$i] > $punkte_max) { $punkte_max = $punkte[$i]; }  // Höchsten Punktewerte ermitteln
  }
}

unset($punkte);
unset($spiele);

//echo "<pre>";
//echo($gesamt);


// Gesamt Array ist nun mit alles Saisons gefüllt
// nun geht es ans sortieren....
$z = 0;
$tipper = array();
$quote = array();

// Dieses foreach stellt Werte für die Usersortierung bereit
foreach($gesamt as $name => $inhalt) {
  if(($shownichttipper != 0) or ($inhalt['gesamt_spiele'] > 0)) {
    $punkte[$name] = $inhalt['gesamt_punkte'];
    $spiele[$name] = $inhalt['gesamt_spiele'];
    $tipper[] = strtolower($name);
    if($inhalt['gesamt_spiele'] > 0) {
      $quote[] = $inhalt['gesamt_punkte']/$inhalt['gesamt_spiele'];
    }
    else {
      $quote[] = 0;
    }
  }
}

array_multisort($punkte, SORT_DESC, SORT_NUMERIC);
$i = 1;
foreach($punkte as $name => $anzpunkte) {
  switch ($i) {
    case 1: $best1 = $name; break;  // Platz 1
    case 2: $best2 = $name; break;  // Platz 2
  }
  if($i ++ == 2) { break; }
}

$sort = strip_tags($_GET['sort']);
switch ($sort) {
  case "nameauf": array_multisort($tipper, SORT_ASC, SORT_STRING, $punkte, SORT_DESC, SORT_NUMERIC, $spiele, SORT_ASC, SORT_NUMERIC, $punkte); break;
  case "nameab": array_multisort($tipper, SORT_DESC, SORT_STRING, $punkte, SORT_DESC, SORT_NUMERIC, $spiele, SORT_ASC, SORT_NUMERIC, $punkte); break;
  case "punkteauf": array_multisort($punkte, SORT_ASC, SORT_NUMERIC, $spiele, SORT_ASC, SORT_NUMERIC, $punkte); break;
  case "punkteab": array_multisort($punkte, SORT_DESC, SORT_NUMERIC, $spiele, SORT_ASC, SORT_NUMERIC, $punkte); break;
  case "quoteauf": array_multisort($quote, SORT_ASC, SORT_STRING, $punkte, SORT_DESC, SORT_NUMERIC, $spiele, SORT_ASC, SORT_NUMERIC, $punkte); break;
  case "quoteab": array_multisort($quote, SORT_DESC, SORT_STRING, $punkte, SORT_DESC, SORT_NUMERIC, $spiele, SORT_ASC, SORT_NUMERIC, $punkte); break;
  case "spieleauf": array_multisort($spiele, SORT_ASC, SORT_NUMERIC, $punkte, SORT_DESC, SORT_NUMERIC, $punkte); break;
  case "spieleab": array_multisort($spiele, SORT_DESC, SORT_NUMERIC, $punkte, SORT_DESC, SORT_NUMERIC, $punkte); break;
  //case "punkteab": echo break;
  default : array_multisort($punkte, SORT_DESC, SORT_NUMERIC, $spiele, SORT_ASC, SORT_NUMERIC, $punkte);
}
unset($tipper);
unset($quote);

// Eingeloggten User ermitteln
$username = "";
if((isset($_SESSION['lmotippername']) && $_SESSION['lmotippername'] != "") && (isset($_SESSION['lmotipperok']) && $_SESSION['lmotipperok'] > 0) ) {
  $username = "[". $_SESSION['lmotippername'] ."]";
}

$punkte_max = ceil($punkte_max / 100);  // ceil - aufrunden
$popbreite = ((sizeof($gesamtfiles) + 1) * 30) + 35 + (176 * 2) + (6 * 5) + 35;
$pophoehe = (($punkte_max + 1) * 30) + 47 + 73;
//$popbreite = 700;
//$pophoehe = 400;

$ausgabe = '<h2>Ewige Tipptabelle</h2>
<div class="zentriert"><a href="javascript:void(0);" class="auswert" onclick="window.open(\'ewigpopup.php?best1='.$best1.'&amp;best2='.$best2.'\',\'_blank\',\'width='.$popbreite.',height='.$pophoehe.',left=0,top=0,scrollbars=yes\')" title="zum Tippspielervergleich">Tippspielervergleich</a>
<br /><br />
</div>
<table class="auswert">
<tr class="nohover"><th>&nbsp;Platz</th>';

if($sortieren_erlauben == 1) {

$ausgabe .= '<th>Tippspieler<a href="'.$_SERVER['SCRIPT_NAME'].'?sort=nameauf"><img src="'.URL_TO_IMGDIR.'/pfeil_hoch.png" class="hoch" alt="hoch" title="nach Name aufsteigend sortieren" /></a> <a href="'.$_SERVER['SCRIPT_NAME'].'?sort=nameab"><img src="'.URL_TO_IMGDIR.'/pfeil_runter.png" alt="runter" class="runter" title="nach Name absteigend sortieren" /></a></th>

<th>&nbsp;<acronym title="'.$var_spiele_title.'">'.$var_spiele.'</acronym><a href="'.$_SERVER['SCRIPT_NAME'].'?sort=spieleauf"><img src="'.URL_TO_IMGDIR.'/pfeil_hoch.png"  class="hoch" alt="hoch" title="nach Anzahl Spiele aufsteigend sortieren" /></a> <a href="'.$_SERVER['SCRIPT_NAME'].'?sort=spieleab"><img src="'.URL_TO_IMGDIR.'/pfeil_runter.png" alt="runter" class="runter" title="nach Anzahl Spiele absteigend sortieren" /></a></th>

<th>&nbsp;&nbsp;<acronym title="'.$var_prozrichtig_title.'">'.$var_prozrichtig.'</acronym><a href="'.$_SERVER['SCRIPT_NAME'].'?sort=quoteauf"><img src="'.URL_TO_IMGDIR.'/pfeil_hoch.png" alt="hoch" class="hoch" title="nach Punkte pro Spiel aufsteigend sortieren" /></a> <a href="'.$_SERVER['SCRIPT_NAME'].'?sort=quoteab"><img src="'.URL_TO_IMGDIR.'/pfeil_runter.png" alt="runter" class="runter" title="nach Punkte pro Spiel absteigend sortieren" /></a></th>

<th>&nbsp;&nbsp;<acronym title="'.$var_tippsrichtig_title.'">'.$var_tippsrichtig.'</acronym><a href="'.$_SERVER['SCRIPT_NAME'].'?sort=punkteauf"><img src="'.URL_TO_IMGDIR.'/pfeil_hoch.png" alt="hoch" class="hoch" title="nach Punkte aufsteigend sortieren" /></a> <a href="'.$_SERVER['SCRIPT_NAME'].'?sort=punkteab"><img src="'.URL_TO_IMGDIR.'/pfeil_runter.png" alt="runter" class="runter" title="nach Punkte absteigend sortieren" /></a></th>';

}
else {

$ausgabe .= '<th>Tippspieler</th>
<th>&nbsp;<acronym title="'.$var_spiele_title.'">'.$var_spiele.'</acronym>&nbsp;</th>
<th>&nbsp;&nbsp;<acronym title="'.$var_prozrichtig_title.'">'.$var_prozrichtig.'</acronym>&nbsp;</th>
<th>&nbsp;&nbsp;<acronym title="'.$var_tippsrichtig_title.'">'.$var_tippsrichtig.'</acronym>&nbsp;</th>';

}  //if sort_allow

$ausgabe .= '<th>&nbsp;</th></tr>';

$platz = 0;
$platz2 = 0;
$pkttmp = 0;
$sptmp = 0;

// Ausgabe aufbereiten
foreach($punkte as $name => $anzpunkte) {
  $platz++;
  $showplatz=true;

  if($platz-1 == $showtipper) {
    break;
  }

  // Bedingung, die alle Nicht-Tipper rausfiltert falls gewünscht
  if(($shownichttipper != 0) or ($spiele[$name] != 0)) {

    // Ausgabe der Platzierung wenn:
    // 1. Punkte ungleich dem Vorgänger
    // 2. Punkte gleich, aber Anzahl getippter Spiele unterschiedlich
    if(($punkte[$name] != $pkttmp) or (($punkte[$name] == $pkttmp) and (($spiele[$name] != $sptmp)))) {

      $platz2++;

      switch ($platz) {
        case 1 : $ausgabe .= '<tr class="colorplatz1 '; break;
        case 2 : $ausgabe .= '<tr class="colorplatz2 '; break;
        case 3 : $ausgabe .= '<tr class="colorplatz3 '; break;
        default: $ausgabe .= '<tr class="';
      }  // switch end

    }
    else {  // wenn Gleichheit bei Punkten und Spielen besteht

      switch ($platz2) {
        case 1 : $ausgabe .= '<tr class="colorplatz1 '; break;
        case 2 : $ausgabe .= '<tr class="colorplatz2 '; break;
        case 3 : $ausgabe .= '<tr class="colorplatz3 '; break;
        default: $ausgabe .= '<tr class="';
      }  // switch end

      $showplatz = false;
    }

    $ausgabe .= 'klick" id="tr'.$platz.'">';

    // Auf Länge des Namens prüfen
    if((strlen($name)-2 > $max_laenge_name) and ($max_laenge_name > 0)) {
      $name_tmp = '<acronym title="'.$name.'">' . substr($name, 0, $max_laenge_name) . '&hellip;]</acronym>';
    }
    else { $name_tmp = $name; }

    // Falls User eingeloggt -> username bold
    if($username == $name) {
      $ausgabe .=  '<td class="fett"><a name="to'.$platz.'"></a>'. ($showplatz ? $platz : '&nbsp;') . '</td><td class="fett">';  $ausgabe .= $name_tmp . '</td><td class="fett">&nbsp;&nbsp;'. $spiele[$name]. '&nbsp;&nbsp;</td><td class="fett">&nbsp;&nbsp;';
      if($spiele[$name] > 0) {
        $quo = round($anzpunkte/$spiele[$name], 2); $ausgabe .= ($quo == "0.5" ? "0.50" : $quo);
      }
      $ausgabe .=  '&nbsp;&nbsp;</td><td class="fett">&nbsp;&nbsp;'.$anzpunkte . '&nbsp;&nbsp;</td><td><img src="img/mehr1.png" id="imgtr'.$platz.'" alt="zeige alle Saisons" title="zeige alle Saisons von '.$name.'" /></td></tr>';
    }
    else {
      $ausgabe .=  '<td><a name="to'.$platz.'"></a>'. ($showplatz ? $platz : '&nbsp;') . '</td><td>';  $ausgabe .= $name_tmp . '</td><td>&nbsp;&nbsp;'. $spiele[$name]. '&nbsp;&nbsp;</td><td>&nbsp;&nbsp;';
      if($spiele[$name] > 0) {
        $quo = round($anzpunkte/$spiele[$name], 2); $ausgabe .= ($quo == "0.5" ? "0.50" : $quo);
      }
      $ausgabe .=  '&nbsp;&nbsp;</td><td>&nbsp;&nbsp;'.$anzpunkte . '&nbsp;&nbsp;</td><td><img src="'.URL_TO_IMGDIR.'/mehr1.png" id="imgtr'.$platz.'" alt="zeige alle Saisons" title="zeige alle Saisons von '.$name.'" /></td>
      </tr>';
    }

    $sptmp = $spiele[$name];
    $pkttmp = $punkte[$name];
    $platztmp = $platz;

    $ausgabe .=  '<tr class="toshow nohover a'.$platz.'"><td colspan="7">
  <table border="0" class="saisons">
  <tr><th>&nbsp;Saison&nbsp;</th><th>&nbsp;'.$var_spiele.'&nbsp;</th><th>&nbsp;'.$var_prozrichtig.'&nbsp;</th><th>&nbsp;'.$var_tippsrichtig.'&nbsp;</th></tr>';

    $ungerade = 0;

    // Einzelne Saisons aufbereiten
    foreach($gesamt[$name] as $value => $inhalt) {
      if(($value != "gesamt_spiele") and ($value != "gesamt_punkte")) {
        if(++$ungerade == 1) {
          // Spiele
          if($value == "spielexxyy") {
            $ausgabe .= '<tr><td><acronym title="Aktuelle Tippsaison (ist noch nicht beendet)">aktuell</acronym></td><td>&nbsp;'. $spiele_tmp = $inhalt .'&nbsp;</td>';
          }
          else {
            $ausgabe .= '<tr><td>'.substr($value, 6, 2) . "/" . substr($value, 8, 2).'</td><td>&nbsp;'. $spiele_tmp = $inhalt .'&nbsp;</td>';
          }
        }
        else {
          // Punkte
          $ausgabe .= '<td>&nbsp;';
          if($spiele_tmp > 0) {
            $quo = round($inhalt/$spiele_tmp, 2);
            $ausgabe .= ($quo == "0.5" ? "0.50" : $quo);
          }
          $ausgabe .= '&nbsp;</td><td>&nbsp;'. $inhalt .'&nbsp;</td></tr>';
          $ungerade = 0;
        }
      }
    }

    $ausgabe .=  '</table>
  </td></tr>';

  }  // if shownichttipper end
} //foreach Ausgabe aufbereiten end

$ausgabe .=  "</table>";

unset($spiele);
unset($punkte);




// Eingabemaske zusammenbasteln und ausgeben
// $htmlhead = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
// "http://www.w3.org/TR/html4/loose.dtd">
$htmlhead = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
 <title>'.$title.'</title>
<style type="text/css">
body {
font-size: '. $fontsize .'pt;
font-family: '. $fontfamily .';
color: '. $fontcolor .';
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
}

p {
  font-size: '. $fontsize .'pt;
}

/* nur für bcerlbach.de */
h2 {
background-image:url(../images/h2_hint.jpg);
background-position:center center;
background-repeat:no-repeat;
color:#D8E4EC;
height:36px;
line-height:36px;
margin:3px 0 20px 0;
text-align:center;
}

a img {
  border:0;
}
a img.hoch {
  padding-left: 3px;
}

a.auswert { text-decoration: overline,underline; color: #3E4753; font-size: 10pt;}
a.auswert:visited { text-decoration: underline; color: #3E4753; }
a.auswert:hover  { text-decoration: underline; color: #104E8B; }
a.auswert:active { text-decoration: underline; color: #D8E4EC; }
a.auswert.foot { text-decoration: overline,underline; color: #3E4753; font-size: 8pt;}

.zentriert { text-align: center; }
.rechts { text-align:right; }
.fett { font-weight: bold; }
.unterstrichen { text-decoration: underline;  }

table.auswert {
  font-size: '. $tablefontsize .'pt;
  color: '. $tablefontcolor .';
  background-color: '. $tablebackcolor .';
  text-align: center;
  border: #8CA0B4 1px dotted;
  border-collapse: collapse;
  margin: 0 auto;
}
table.auswert th {
  background-color: '. $tableheadercolor .';
  padding: 5px 0;
}
table.auswert tr {
  /*border: 1px solid '.$tablebackcolor.';*/
  border: 1px solid #b9c4cc;
}
table.auswert td img {
/*  padding-top: 2px;*/
  vertical-align: middle;
}

tr.colorplatz1 { background-color: '.$colorplatz1.'; }
tr.colorplatz2 { background-color: '.$colorplatz2.'; }
tr.colorplatz3 { background-color: '.$colorplatz3.'; }
tr.colorplatz1 > td, tr.colorplatz2 > td, tr.colorplatz3 > td { padding: 4px 0; }
tr.normal { background-color: ' . $tablebackcolor . '; }

table.saisons {
  font-size:8pt;
  border-collapse: collapse;
  margin: 10px auto 20px auto;
  cursor:default;
}
.saisons td {
  border-top: 1px solid #ABABCC;
}
tr.toshow {
  display:none;
}
tr.klick {
  cursor:pointer;
}

acronym {
  cursor:help;
  border-bottom:1px dotted;
}
acronym.copy {
  cursor:pointer;
  border-bottom:1px dotted;
}

.foot {
  font-size: 8pt;
}
div.info {
  font-size: 8pt;
  width: 500px;
  margin: 0 auto;
}

#copy { text-decoration:none; cursor:pointer; }


</style>


<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js"></script>
<!--
<script type="text/javascript" src="../js/jquery.min.js"></script>
-->

<script type="text/javascript">
    $(document).ready(function(){

    $("tr").hover( function(){
      //nur hover wenn keine klasse angegeben ist
      if( (!$(this).hasClass("nohover")) && (!$(this).hasClass("colorplatz1")) && (!$(this).hasClass("colorplatz2")) && (!$(this).hasClass("colorplatz3")) ) {
        $(this).css("background-color", "#99A8B4");
      }
      else if($(this).hasClass("colorplatz1"))
      {
        $(this).css("background-color", "'.$colorplatz1_hover.'");
      }
      else if($(this).hasClass("colorplatz2"))
      {
        $(this).css("background-color", "'.$colorplatz2_hover.'");
      }
      else if($(this).hasClass("colorplatz3"))
      {
        $(this).css("background-color", "'.$colorplatz3_hover.'");
      }

    },function(){
      if( (!$(this).hasClass("nohover")) && (!$(this).hasClass("colorplatz1")) && (!$(this).hasClass("colorplatz2")) && (!$(this).hasClass("colorplatz3")) ) {
       $(this).css("background-color", "'. $tablebackcolor .'");
      }
      else if($(this).hasClass("colorplatz1"))
      {
        $(this).css("background-color", "'.$colorplatz1.'");
      }
      else if($(this).hasClass("colorplatz2"))
      {
        $(this).css("background-color", "'.$colorplatz2.'");
      }
      else if($(this).hasClass("colorplatz3"))
      {
        $(this).css("background-color", "'.$colorplatz3.'");
      }
    });

    /*$("a").click( function(){

      id = "."+$(this).attr("id");
      sh_id = "#showhide"+$(this).attr("id");

      if($(id).hasClass("toshow")) {
        //$(id).show();
        //$(this).next("tr").show();
        $(id).removeClass("toshow");
        $(id).addClass("hide");
        //$(sh_id).hide();
        $(sh_id).attr("src", "img/weniger1.png");
      }
      else {
        //$(id).hide();
        //$(this).next("tr").hide();
        $(id).removeClass("hide");
        $(id).addClass("toshow");
        $(sh_id).attr("src", "img/mehr1.png");
      }
    });*/

    $("tr.klick").click(function(){

      id = "#img"+$(this).attr("id");

      if($(this).next("tr").is(":visible")) {
        $(this).next("tr").hide();
        $(id).attr("src", "'.URL_TO_IMGDIR.'/mehr1.png");
      }
      else {
        $(this).next("tr").show();
        $(id).attr("src", "'.URL_TO_IMGDIR.'/weniger1.png");
      }
    });

    $("img.hoch").hover( function(){
       $(this).attr("src", "'.URL_TO_IMGDIR.'/pfeil_hoch2.png");
    },function(){
      $(this).attr("src", "'.URL_TO_IMGDIR.'/pfeil_hoch.png");
    });
    $("img.runter").hover( function(){
       $(this).attr("src", "'.URL_TO_IMGDIR.'/pfeil_runter2.png");
    },function(){
      $(this).attr("src", "'.URL_TO_IMGDIR.'/pfeil_runter.png");
    });


    });  //main
</script>

</head>
<body>';


$htmlfoot = '<div class="rechts"><a href="http://forum.bcerlbach.de/downloads.php?cat=7" onclick="window.open(this.href,\'_blank\');return false;"><small>[<acronym class="copy" title="Auswertskript von Marcus - www.bcerlbach.de">&copy;</acronym>]</small></a></div>
</body></html>';


//******************************************************************************


$htmlbody .= $ausgabe;

/*$htmlbody .= '<p align="center">Stand vom '.date("d.m.Y").' - '.date("H:i") .' Uhr</p>
<p align="center"><a href="auswert.php">zurück zur Auswahl</a>&nbsp;|&nbsp;<a href="lmo.php?action=tipp">zurück zum Tippspiel</a></p>';
*/
$htmlbody .= '<div class="zentriert info"><br />Bei dieser Auswertung wurden ' . sizeof($gesamtfiles) . ' Saisons zusammengefasst:<br />';

$i = 0;
foreach($gesamtfiles as $value) {
  if(($aktuelle_saison_einbinden == 1) and ($i+1 == count($gesamtfiles))) {
    $htmlbody .= "aktuell";  //substr($value, 6, 2) . "/" . substr($value, 8, 2);
  }
  else {
    $htmlbody .= substr($value, 6, 2) . "/" . substr($value, 8, 2);
    if(++$i<sizeof($gesamtfiles)) { $htmlbody .= ", "; }
  }
}

$htmlbody .= '<br />Eine laufende Saison wird erst nach deren Ende ins Archiv aufgenommen.';

if($shownichttipper == 0) {
  $htmlbody .= '<br />Tippspieler, die keinen Tipp abgegeben haben, werden nicht angezeigt.';
}

$htmlbody .= '</div>';

//$zeit2 = microtime();  //stopp
//$htmlbody .= "<br />Berechnung dauerte " . ($zeit2-$zeit1);


// Ausgabe HTML Code an Browser
echo $htmlhead . $htmlbody . $htmlfoot;

clearstatcache();

?>
