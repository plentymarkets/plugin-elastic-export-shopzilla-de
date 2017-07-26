# Release Notes für Elastic Export Shopzilla.de

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
