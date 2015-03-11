# FHC-AddOn-Textbausteine
FH-Complete Addon zur Erstellung von Serienbriefen

Mit diesem Addon können Serienbriefe direkt aus dem FAS erstellt werden.
Die erstellten Serienbriefe sind bereits mit der entsprechenden Datenquelle verbunden. 

Die Datenquelle für die Serienbriefe wird dabei auf einem Netzlaufwerk oder einem Webserver abgelegt.

## Einstellungen
Damit die CSV Datenquelle für den Serienbrief korrekt geladen werden kann, muss der Ablageort der CSV Dateien als Vertrauenswürdige Datenquelle eingetragen werden.
Werden die Daten auf einen Netzlaufwerk abgelegt sind folgende Einstellungen nötig:
Internet Explorer->Extras->Internetoptionen->Sicherheit->Lokales Intranet->Sites
Das Häkchen bei "Intranetnetzwerk automatisch ermitteln" entfernen. Die restlichen 3 Häkchen müssen angehakt werden.

Bei Ablage der Daten direkt auf dem Webserver muss die Web-Adresse als Vertrauenswürdige Seite eingetragen werden:
Internet Explorer->Extras->Internetoptionen->Sicherheit->Vertrauenswürdige Sites->Sites
Hier muss die Web-Adresse hinzugefügt werden an der die CSV Dateien abgelegt sind.

### SQL Sicherheitswarnung deaktivieren
Beim Öffnen des Serienbriefs erscheint eine Meldung ob die Daten aus dem CSV geladen werden sollen. Um diese Meldung zu aktivieren kann ein Eintrag in der Registry vorgenommen werden.

siehe: http://support.microsoft.com/kb/825765

HKEY_CURRENT_USER\Software\Microsoft\Office\15.0\Word\Options
"SQLSecurityCheck" = DWORD: 00000000 

