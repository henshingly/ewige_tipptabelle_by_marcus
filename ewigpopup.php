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

if (!defined('PATH_TO_TIPPARCHIV'))   define('PATH_TO_TIPPARCHIV',    PATH_TO_ADDONDIR . "/tipp/archiv/");

if (!is_dir(PATH_TO_TIPPARCHIV)) {
	die("Pfad zum Tippspielarchiv " . PATH_TO_TIPPARCHIV . " nicht vorhanden!");
}

$gesamtfiles = array();

$tipparchiv = opendir (PATH_TO_TIPPARCHIV);

while ($file = readdir($tipparchiv))     
{
	 if ($file != "." && $file != ".." && $file != "index.htm") {
		 //prüfen ob die datei richtig benannt ist - form gesamtXXYY.aus
		 if ((strlen($file) == 14) and (substr($file, 0, 6) == "gesamt") and (substr($file, 10, 4) == ".aus")) {
		 	array_push($gesamtfiles, $file);
		 }
	}
}
closedir($tipparchiv);

if (sizeof($gesamtfiles) == 0) { 
	die("Keine Auswertungsfiles *.aus im Ordner " . PATH_TO_TIPPARCHIV . " vorhanden!"); 
}

//saisons nach datum sortieren 
array_multisort($gesamtfiles, SORT_ASC, SORT_STRING, $gesamtfiles);

$saisons = array();
$punkte_max = 0;
$gesamt = $platz1 = $platz2 = array();
$tipper = $tipperklein = array();

//gesamtarray mit allen daten bilden
for ($s=0; $s<count($gesamtfiles); $s++) {

	//datei gesamt.aus in array einlesen... evtl. Pfad anpassen
	$auswertdatei = PATH_TO_TIPPARCHIV . $gesamtfiles[$s];//"gesamt0405.aus";
	
	$array = @file($auswertdatei);
	
	
	/* anzahl der tipp-ligen ermitteln */
	$zeile = trim($array[1]); // unnötige zeilenumbrüche ... entfernen
	$anzligen = (int)substr($zeile, 9, strlen($zeile));//->eigentlich immer ab 10. stelle
	
	$ligenkurz = array(); //beinhaltet kürzel für ligen
	$anzgetipptkurz = array(); //beinhaltet kürzel für anzahl getippter spiele
	for ($i=1; $i<=$anzligen; $i++) {
		array_push($ligenkurz, 'TP'.$i); 		//-- ligenkürzel = 'TP'.$i
		array_push($anzgetipptkurz, 'SG'.$i);	//-- kürzel = 'SG'.$i
	}
	

	
	
	for ($i = $anzligen+3; $i < sizeof($array); $i++) {
	
		//usernamen ermitteln, wenn gefunden in array speichern
		$posname = strpos($array[$i], "["); 
		if ($posname !== false) {
			//gefundenen namen ins array speichern - aber nur wenn name noch nicht drin ist
			if (!in_array(trim($array[$i]), $tipper)) {
				$tipper[++$anztipper] = trim($array[$i]);
				$tipperklein[$anztipper] = strtolower(trim($array[$i]));
				//echo "<br>" . strtolower(trim($array[$i]));
			}
		}
		
	}//for - anzahl tipper ermitteln

	//array_multisort($punkte, SORT_DESC, SORT_NUMERIC, $spiele, SORT_DESC, SORT_NUMERIC, $tipper, SORT_DESC, SORT_STRING);
	array_multisort($tipperklein, SORT_ASC, SORT_STRING, $tipper, SORT_ASC, SORT_STRING);


}//for

unset($punkte);
unset($spiele);



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">	  
<html>
<head>
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
/*	background-color:#9F99F9;*/
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
	float:left;
	text-align:center;			
	/*width:197px;*/
	padding: 10px 5px 5px 5px;
	border: 1px dashed silver;
	/*float:right;*/
	-moz-border-radius: 0.4em; /* Ecken gerundet, FF only */
}		

.divrechts {
	float:left;
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

if (isset($_POST['tipper1'])) 
{ 
	$tipper1 = strip_tags($_POST['tipper1']); 
	//$options ='';
}
else
{
	$tipper1 = $_GET['best1'];	
}
if (isset($_POST['tipper2'])) 
{ 
 	$tipper2 = strip_tags($_POST['tipper2']); 
}
else
{
	$tipper2 = $_GET['best2'];	
}

echo '    <div class="divlinks">
<select name="tipper1" >
';

for ($i=0; $i<sizeof($tipperklein); $i++) {
//	if (strlen($tipper[$i])-2 > 20) {
	$name_tmp = (strlen($tipper[$i])-2 > 20) ? substr($tipper[$i], 0, 20) . '&hellip;]' : $tipper[$i];
/*	}
	else 
	{ 
		$name_tmp = $tipper[$i]; 
	}*/
	echo '<option ' . ($tipperklein[$i] == strtolower($tipper1) ? 'selected="selected"' : "") . ' value="'.$tipper[$i].'">'.$name_tmp.'</option>';
}

echo '</select>

</div>
    
	<div class="divmitte"><img src="ewiggraph.php?tipper1='.$tipper1.'&amp;tipper2='.$tipper2.'" alt="" /></div>
	
    <div class="divrechts">
<select name="tipper2" >
';

for ($i=0; $i<sizeof($tipperklein); $i++) {
	$name_tmp = (strlen($tipper[$i])-2 > 20) ? substr($tipper[$i], 0, 20) . '&hellip;]' : $tipper[$i];
	echo '<option ' . ($tipperklein[$i] == strtolower($tipper2) ? 'selected="selected"' : "") . ' value="'.$tipper[$i].'">'.$name_tmp.'</option>';
}

echo '</select>

	</div>';

//echo "<pre>";
//var_dump($tipper);

?>
<div class="absenden"><input type="submit" value="absenden" /></div>
</div>
</form>

</body>
</html>