# security für REDAXO CMS ^5.17

AddOn, das einen einfachen Weg bietet, das REDAXO-Backend mit verschiedenen Methoden zu sichern.

* Frontend-Passwort für Staging- oder Entwicklersysteme
* Fehlerberichterstattung per E-Mail. Direkt oder als gesammelte Pakete
* IP-Zugangskontrolle: IPs und IP-Bereiche zum Blockieren und Zulassen von Frontend und Backend
* BackendSession-Konfiguration: Sitzungsdauer, KeepAlivePing, maximale Sitzungsdauer
* Backend-Benutzerprotokoll: Protokollierung aller Aktionen der Backend-Benutzer
* Header-Sicherheit: Strict-Transport-security, X-Frame-Options, X-XSS-Protection ... (in Bearbeitung)
* Checkliste: Überprüfung der REDAXO-Installation auf Sicherheitsprobleme mit externen Tools.

## Installation

* Nutzung des REDAXO-Installers, sobald eine Version verfügbar ist
* oder Klonen dieses Repositorys in die REDAXO-Installation

## Dokumentation

### Frontend-Passwort

#### Konsole

* Übersicht/Info: bin/console security:fe_access -i
* Hilfe: bin/console security:fe_access --help
* Aktivieren: bin/console security:fe_access -s 1
* Deaktivieren: bin/console security:fe_access -s 0

### IP-Zugangskontrolle

#### Konsole

* Übersicht/Info: bin/console security:ip_access -l
* Hilfe: bin/console security:ip_access --help
* IP hinzufügen: bin/console security:ip_access -a
* IP löschen: bin/console security:ip_access -d
