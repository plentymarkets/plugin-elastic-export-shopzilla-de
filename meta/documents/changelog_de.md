# Release Notes für Elastic Export Shopzilla.de

## v1.1.11 (2022-06-14)

### Behoben
- Ein fehlerhafter Link im User Guide wurde korrigiert.

## v1.1.10 (2022-05-26)

### Geändert
- UPDATE - Zusätzliche Updates für Kompatibilität mit PHP 8.

## v1.1.9 (2020-06-24)

### Geändert
- Die Plugin-Beschreibung wurde in das plentymarkets Handbuch umgezogen.

## v1.1.8 (2019-10-11)

### Geändert
- Der User Guide wurde aktualisiert (Form der Anrede geändert, fehlerhafte Links korrigiert).

## v1.1.7 (2019-01-23)

### Geändert
- Ein fehlerhafter Link im User Guide wurde korrigiert.

## v1.1.6 (2018-07-12)

### Geändert
- Ein fehlerhafter Link im User Guide wurde korrigiert.

## v1.1.5 (2018-04-30)

### Geändert
- Laravel 5.5 Update.

## v1.1.4 (2018-04-25)

### Geändert
- Die Filtrierung der Varianten wird durch die Klasse FiltrationService vorgenommen.
- Vorschaubilder aktualisiert.

## v1.1.3 (2018-03-26)

### Hinzugefügt
- Der User Guide wurde erweitert.

## v1.1.2 (2018-02-16)

### Geändert
- Plugin-Kurzbeschreibung wurde angepasst.

## v1.1.1 (2017-08-29)

### Behoben
- Es wurde ein Fehler behoben, bei dem die Versandkosten nicht korrekt zwischengespeichert wurden.
- Es wurde ein Fehler behoben, bei dem der Lagerfilter nicht korrekt funktionierte.

## v1.1.0 (2017-07-26)

### Behoben
- Die Versandkosten werden jetzt korrekt ausgegeben.

### Entfernt
- Die Spalte **Versandgewicht** wurde entfernt, da dieses nicht in der Feed-Spezifikation vorkommt.
- Die Spalte **Werbetext**, die bisher immer mit dem Wert **2** gefüllt wurde, wurde entfernt.
- Die Spalte **Gebot** wurde entfernt.

### Geändert
- Das **Trennzeichen** ist jetzt statt einem Leerzeichen ein Tabulator.
- Die Reihenfolge der Spalten wurde geändert.
- Für die Spalte **Zustand** kann **Gebraucht** übermittelt werden.
- Die Spalte **Bestand** kann nun mit Hilfe der Formateinstellung **Artikelverfügbarkeit überschreiben** bearbeitet werden.
- Die Zeichenbegrenzung auf 256 Zeichen für **Titel** und **Beschreibung** wurde entfernt.
- Die Spalte **Hersteller** wurde zu **Marke** umbenannt.
- Die Spalte **Bezeichnung** wurde zu **Titel** umbenannt.
- Die Spalte **SKU** wurde zu **ID** umbenannt.

### Hinzugefügt
- Die Spalte **Zusätzliche Bild-URL** wurde hinzugefügt.
- Die Spalte **Artikelnummer** wurde hinzugefügt.
- Die Spalte **Geschlecht** wurde hinzugefügt.
- Die Spalte **Altersgruppe** wurde hinzugefügt.
- Die Spalte **Größe** wurde hinzugefügt.
- Die Spalte **Farbe** wurde hinzugefügt.
- Die Spalte **Material** wurde hinzugefügt.
- Die Spalte **Muster** wurde hinzugefügt.
- Die Spalte **Produktgruppe** wurde hinzugefügt.
- Die Spalte **Empfohlener Preis** wurde hinzugefügt.
- **Merkmalverknüpfungen** für Shopzilla wurden hinzugefügt.


## v1.0.3 (2017-07-18)

### Geändert
- Das Plugin Elastic Export ist nun Voraussetzung zur Nutzung des Pluginformats ShopzillaDE.

### Behoben
- Es wurde ein Fehler behoben, der dazu geführt hat, dass bei dem Barcode die Marktplatzverfügbarkeit ignoriert wurde.
- Es wurde ein Fehler behoben, bei dem der Bestand nicht korrekt ausgewertet wurde.
- Es wurde ein Fehler behoben, bei dem Varianten nicht in der richtigen Reihenfolge gelistet wurden.
- Es wurde ein Fehler behoben, der dazu geführt hat, dass das Exportformat Texte in der falschen Sprache exportierte.

## v1.0.2 (2017-05-29)

### Geändert
- Das Plugin Elastic Export ist nun Voraussetzung zur Nutzung des Pluginformats ShopzillaDE.

## v1.0.1 (2017-03-22)

### Behoben
- Es wird nun ein anderes Feld genutzt um die Bild-URLs auszulesen für Plugins die elastic search benutzen.

## v1.0.0 (2017-03-10)

### Hinzugefügt
- Initiale Plugin-Dateien hinzugefügt
