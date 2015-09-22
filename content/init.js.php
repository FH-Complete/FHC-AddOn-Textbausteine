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
/**
 * Initialisierung des Addons
 */
require_once(dirname(__FILE__).'/../../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../include/textbausteine.class.php');

$textbausteine = new textbausteine();
$textbausteine->getAll();
?>
addon.push(
{
	init: function()
	{
		// Diese Funktion wird nach dem Laden des FAS aufgerufen

		// Hauptmenuepunkt
		var textbausteinemenue = document.createElement("menu");
		textbausteinemenue.setAttribute("id","addons-textbausteine-menu");
		textbausteinemenue.setAttribute("label","Textbausteine");

		// Menupopup
		var menupopup = document.createElement("menupopup");
		menupopup.setAttribute("id","addons-textbausteine-menupopup");

		<?php
		foreach($textbausteine->textbausteine as $gruppe=>$elem)
		{
			if($gruppe!='')
			{
				echo '
				var gruppenpopup = document.createElement("menupopup");
				gruppenpopup.setAttribute("id","addons-textbausteine-menupopup-'.$gruppe.'");';
			}

			foreach($elem as $row)
			{
				// Menueeintraege

				echo '
					var menuentry = document.createElement("menuitem");
					menuentry.setAttribute("id","addons-textbausteine-'.$row->textbausteine_id.'");
					menuentry.setAttribute("label","'.$row->bezeichnung.'");
					menuentry.addEventListener("command",function() { AddonTextbausteineGenerate('.$row->textbausteine_id.');}, true);';

				if($gruppe!='')
					echo 'gruppenpopup.appendChild(menuentry);';
				else
					echo 'menupopup.appendChild(menuentry);';
			}
			if($gruppe!='')
			{
				echo '
				var grpmenu = document.createElement("menu");
				grpmenu.setAttribute("label","'.$gruppe.'");
				grpmenu.appendChild(gruppenpopup);
				menupopup.appendChild(grpmenu);';
			}
		}
		?>
		textbausteinemenue.appendChild(menupopup);

		var extrasmenu = document.getElementById("menu-extras");
		var menu = document.getElementById("menu");
		menu.insertBefore(textbausteinemenue, extrasmenu);
	},
	selectMitarbeiter: function(person_id, mitarbeiter_uid)
	{
	},
	selectStudent: function(person_id, prestudent_id, student_uid)
	{
	},
	selectVerband: function(item)
	{
	},
	selectInstitut: function(institut)
	{
	},
	selectLektor: function(lektor)
	{
	}
});

function AddonTextbausteineGenerate(id)
{
	var prestudentid='';
	var uid='';

	// Pruefen ob Studenten oder Mitarbeiter Tree offen ist
	if(document.getElementById('main-content-tabs').selectedItem == document.getElementById('tab-studenten'))
	{
		// Studenten Tab ist aktiv -> PrestudentIDs holen
		tree = document.getElementById('student-tree');

		//Markierte Studenten holen
		var start = new Object();
		var end = new Object();
		var numRanges = tree.view.selection.getRangeCount();
		var paramList= '';

		for (var t = 0; t < numRanges; t++)
		{
	  		tree.view.selection.getRangeAt(t,start,end);
			for (var v = start.value; v <= end.value; v++)
			{
				prestudentid = prestudentid+';'+getTreeCellText(tree, 'student-treecol-prestudent_id', v);
			}
		}

	}
	else if(document.getElementById('main-content-tabs').selectedItem == document.getElementById('tab-mitarbeiter'))
	{
		// Mitarbeiter Tab ist aktiv -> Mitarbeiter UIDs holen
		var treeMitarbeiter=document.getElementById('mitarbeiter-tree');
		var numRanges = treeMitarbeiter.view.selection.getRangeCount();
		var start = new Object();
		var end = new Object();
		var anzfault=0;
		//Markierte Datensaetze holen
		for (var t=0; t<numRanges; t++)
		{
	  		treeMitarbeiter.view.selection.getRangeAt(t,start,end);
	  		for (v=start.value; v<=end.value; v++)
	  		{
	  			var col = treeMitarbeiter.columns ? treeMitarbeiter.columns["mitarbeiter-treecol-uid"] : "mitarbeiter-treecol-uid";
	  			if(treeMitarbeiter.view.getCellText(v,col).length>1)
	  			{
					uid=uid+';'+treeMitarbeiter.view.getCellText(v,col);
	  			}
	  		}
		}
	}

	// gewaehlten Studiengang / Semester
	var tree = document.getElementById('tree-verband');
	var studiengang_kz='';
	var semester='';
	if(tree.currentIndex!=-1)
	{
		//Studiengang und Semester holen
		var col;
		col = tree.columns ? tree.columns["stg_kz"] : "stg_kz";
		studiengang_kz=tree.view.getCellText(tree.currentIndex,col);
		col = tree.columns ? tree.columns["sem"] : "sem";
		semester=tree.view.getCellText(tree.currentIndex,col);
	}

	var studiensemester_kurzbz = getStudiensemester();

	newwindow= window.open ("","FAS","width=350, height=350");
	newwindow.document.getElementsByTagName('body')[0].innerHTML = "<form id='postform-form' name='postfrm' action='' method='POST'><input type='hidden' id='postform-textbox-prestudent_id' name='prestudent_id' /><input type='hidden' id='postform-textbox-uid' name='uid' /><input type='hidden' id='postform-textbox-textbaustein_id' name='textbaustein_id' /><input type='hidden' id='postform-textbox-studiengang_kz' name='studiengang_kz' /><input type='hidden' id='postform-textbox-semester' name='semester' /><input type='hidden' id='postform-textbox-studiensemester_kurzbz' name='studiensemester_kurzbz' /></form>";
	newwindow.document.getElementById('postform-textbox-prestudent_id').value=prestudentid;
	newwindow.document.getElementById('postform-textbox-uid').value=uid;
	newwindow.document.getElementById('postform-textbox-textbaustein_id').value=id;
	newwindow.document.getElementById('postform-textbox-studiengang_kz').value=studiengang_kz;
	newwindow.document.getElementById('postform-textbox-semester').value=semester;
	newwindow.document.getElementById('postform-textbox-studiensemester_kurzbz').value=studiensemester_kurzbz;
	newwindow.document.getElementById('postform-form').action='<?php echo APP_ROOT.'/addons/textbausteine/content/generateTextbaustein.php';?>';
	newwindow.document.postfrm.submit();
}
