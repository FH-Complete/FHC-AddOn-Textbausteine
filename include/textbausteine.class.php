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
require_once(dirname(__FILE__).'/../../../include/basis_db.class.php');

class textbausteine extends basis_db
{
	public $new;				//  boolean
	public $result = array();	//  adresse Objekt
	public $gruppen = array();	//  adresse Objekt

	//Tabellenspalten
	public $textbausteine_id; //  integer
	public $sql; //  text
	public $pfad; //  varchar(512)
	public $name; //  varchar(256)
	public $bezeichnung; //  varchar(512)
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;

	/**
	 * Konstruktor
	 * @param $textbausteine_id ID die geladen werden soll (Default=null)
	 */
	public function __construct($textbausteine_id=null)
	{
		parent::__construct();
		
		if(!is_null($textbausteine_id))
			$this->load($textbausteine_id);
	}

	/**
	 * Laedt Eintrag der ID $textbausteine_id
	 * @param  $adress_id ID der zu ladenden Adresse
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($textbausteine_id)
	{
		//Pruefen ob textbausteine_id eine gueltige Zahl ist
		if(!is_numeric($textbausteine_id) || $textbausteine_id == '')
		{
			$this->errormsg = 'textbausteine_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM addon.tbl_textbausteine WHERE textbausteine_id=".$this->db_add_param($textbausteine_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->textbausteine_id	= $row->textbausteine_id;
			$this->sql = $row->sql;
			$this->pfad = $row->pfad;
			$this->name = $row->name;
			$this->gruppe =$row->gruppe;
			$this->bezeichnung = $row->bezeichnung;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Laedt alle vorhandenen Textbausteine
	 *
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM addon.tbl_textbausteine ORDER BY gruppe, bezeichnung";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new textbausteine();

				$obj->textbausteine_id	= $row->textbausteine_id;
				$obj->sql = $row->sql;
				$obj->pfad = $row->pfad;
				$obj->name = $row->name;
				$obj->gruppe =$row->gruppe;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;

				$this->result[] = $obj;
				$this->textbausteine[$row->gruppe][]=$obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

	}
}
?>
