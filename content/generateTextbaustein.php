<?php
/* Copyright (C) 2015 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/../../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../../config/global.config.inc.php');
require_once(dirname(__FILE__).'/../../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../include/textbausteine.class.php');
require_once(dirname(__FILE__).'/../../../include/benutzerberechtigung.class.php');

$db = new basis_db();
$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('addon/textbausteine'))
	die('Sie haben keine Berechtigung fuer diesen Vorgang');


$id = $_POST['textbaustein_id'];
$prestudent_id = $_POST['prestudent_id'];
$uid = $_POST['uid'];
$studiengang_kz = $_POST['studiengang_kz'];
$semester = $_POST['semester'];

$prestudent_id_arr = explode(";",$prestudent_id);
$uid_arr = explode(";",$uid);

$textbausteine = new textbausteine();
/*
echo 'Dokument mit ID:'.$id;
echo 'UIDs:'.$uid;
echo 'PrestudentIDs:'.$prestudent_id;
*/

if($textbausteine->load($id))
{
	$qry = $textbausteine->sql;

	// Variablen ersetzen
	$qry = str_replace('$prestudent_id',$db->db_implode4SQL($prestudent_id_arr),$qry);
	$qry = str_replace('$uid',$db->db_implode4SQL($uid_arr),$qry);
	$qry = str_replace('$studiengang_kz',$db->db_add_param($studiengang_kz),$qry);
	$qry = str_replace('$semester',$db->db_add_param($semester),$qry);

	if($result = $db->db_query($qry))
	{
		// Ueberschriften holen
		$spalten = $db->db_num_fields($result);
		for($i=0;$i<$spalten;$i++)
		{
			$name = $db->db_field_name($result, $i);
			$data[0][]=$name;
			$fields[]=$name;
		}

		// Daten holen
		$rowcnt=1;
		while($row = $db->db_fetch_object($result))
		{
			foreach($fields as $field)
				$data[$rowcnt][]=$row->$field;
			$rowcnt++;
		}
		//var_dump($data);

		$serverpfad=ADDON_TEXTBAUSTEINE_SERVER_CSV_PFAD;
		$clientpfad=ADDON_TEXTBAUSTEINE_CLIENT_CSV_PFAD;
		$csvname = uniqid().'.csv';

		if(mssafe_csv($serverpfad.$csvname, $data))
		{
			// Dokument erstellen und ausliefern
			generateDocument($textbausteine->pfad,$textbausteine->name,$clientpfad.$csvname);
		}
		else
		{
			echo 'Fehler beim Schreiben des CSV';
		}
	}
	else
	{
		echo 'Fehler beim Laden der Daten';
	}
}
else
	echo 'Textbaustein nicht gefunden';

// Erstellt eine CSV Datei die auch in MSOffice funktioniert
function mssafe_csv($filepath, $data)
{
	if($fp = fopen($filepath, 'w'))
	{
		reset($data);
		$line = current($data);
		if (!empty($line))
		{
			reset($line);
			$first = current($line);
			if (substr($first, 0, 2)=='ID' && !preg_match('/["\\s,]/', $first) )
			{
				array_shift($data);
				array_shift($line);
				if(empty($line))
				{
					fwrite($fp, "\"{$first}\"\r\n");
				}
				else
				{
					fwrite($fp, "\"{$first}\",");
					fputcsv($fp, $line);
					fseek($fp, -1, SEEK_CUR);
					fwrite($fp, "\r\n");
				}
			}
		}

        foreach ( $data as $line )
		{
            fputcsv($fp, $line);
            fseek($fp, -1, SEEK_CUR);
            fwrite($fp, "\r\n");
        }
        fclose($fp);
    }
	else
	{
		return false;
	}

	return true;
}

// Ersetzt den Pfad zur Datenquelle im Dokument
function generateDocument($pfad, $name, $csvpfad)
{
	// Temporaeren Ordner erstellen
	$tempfolder = '/tmp/'.uniqid();
	mkdir($tempfolder);
	chdir($tempfolder);

	// Vorlage kopieren
	if(copy($pfad, $tempfolder.'/'.$name))
	{
		mkdir('word');
		// settings.xml entpacken
		exec("unzip -p $name word/settings.xml > word/settings.xml");

		mkdir('word/_rels');
		// settings.xml.rels entpacken
		exec("unzip -p $name word/_rels/settings.xml.rels > word/_rels/settings.xml.rels");

		// settings.xml anpassen

		//<w:query w:val="SELECT * FROM http://192.168.56.1/textbausteine/data1.csv"/>
		$xml = new DOMDocument;
		if(!$xml->load($tempfolder.'/word/settings.xml'))
			die('unable to load settings.xml');

		$elements = $xml->getElementsByTagName('query');
	    $element = $elements->item(0);
   		$newelement = $xml->createElement('w:query');
		$newelement->setAttribute('w:val',"SELECT * FROM ".$csvpfad);

	    $element->parentNode->replaceChild($newelement, $element);
		$xml->save('word/settings.xml');

		// settings.xml.rels anpassen
		//<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/mailMergeSource" Target="http://192.168.56.1/textbausteine/data1.csv" TargetMode="External"/>
		$xml = new DOMDocument;
		if(!$xml->load($tempfolder.'/word/_rels/settings.xml.rels'))
			die('unable to load settings.xml.rels');

		$elements = $xml->getElementsByTagName('Relationship');
	    $element = $elements->item(0);
		$element->setAttribute('Target',$csvpfad);
		$xml->save('word/_rels/settings.xml.rels');

		// Zusammenzippen
		exec("zip $name word/settings.xml");
		exec("zip $name word/_rels/settings.xml.rels");

		// Ausliefern

		$fsize = filesize($name);
		$handle = fopen($name,'r');
		header('Content-type: application/vnd.ms-word');
		header('Content-Disposition: attachment; filename="'.$name.'"');
		header('Content-Length: '.$fsize);

	    while (!feof($handle))
	    {
		  	echo fread($handle, 8192);
		}
		fclose($handle);

		// Temp Dateien loeschen

		unlink('word/settings.xml');
		unlink('word/_rels/settings.xml.rels');
		unlink($name);
		rmdir('word/_rels');
		rmdir('word');
		rmdir($tempfolder);
	}
}
?>
