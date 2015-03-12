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
 * Entfernt alte CSV Dateien
 */
require_once(dirname(__FILE__).'/../../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../../config/global.config.inc.php');

if ($handle = opendir(ADDON_TEXTBAUSTEINE_SERVER_CSV_PFAD)) 
{
    while (false !== ($entry = readdir($handle))) 
	{
		if($entry!='.' && $entry!='..' && mb_strstr($entry,'.csv')!==false)
		{
			$moddate = filemtime(ADDON_TEXTBAUSTEINE_SERVER_CSV_PFAD.$entry);
			$diff = time()-$moddate;
			if($diff>86400) // aelter als 1 Tag
			{
				echo "<br>Remove old CSV $entry";
				unlink(ADDON_TEXTBAUSTEINE_SERVER_CSV_PFAD.$entry);
			}
		}
    }

    closedir($handle);
}
?>
