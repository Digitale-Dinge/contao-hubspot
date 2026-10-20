<h1 align="center">Contao HubSpot</h1>

<p align="center">
    <a href="https://packagist.org/packages/digitaledinge/contao-hubspot"><img src="https://img.shields.io/packagist/v/digitaledinge/contao-hubspot" alt="packagist version"/></a>
    <a href="https://packagist.org/packages/digitaledinge/contao-hubspot"><img src="https://img.shields.io/packagist/dt/digitaledinge/contao-hubspot?color=f47c00" alt="amount of downloads"/></a>
    <a href="https://packagist.org/packages/digitaledinge/contao-hubspot"><img src="https://img.shields.io/packagist/dependency-v/digitaledinge/contao-hubspot/php?color=474A8A" alt="minimum php version"></a>
</p>

<p align="center">
    <a href="README.md">English</a> · <b>Deutsch</b>
</p>

---

## Beschreibung

Verbindet Formulare aus dem Contao-Formulargenerator mit HubSpot. Schickt ein Besucher das Formular ab, werden die
gewählten Felder an ein HubSpot-Formular gesendet, auch Einwilligungs-Checkboxen und Datenschutzhinweise. Eingerichtet
wird alles im Contao-Backend am Formular und seinen Feldern. Die Felder lassen sich auch aus einem bestehenden
HubSpot-Formular importieren.

Benötigt PHP `^8.4` und Contao `^5.7 || ^6.0`.

---

+ [Erste Schritte](#erste-schritte)
+ [Formular-Einstellungen](#formular-einstellungen)
+ [Feld-Einstellungen](#feld-einstellungen)
+ [Import aus HubSpot](#import-aus-hubspot)
+ [Einwilligungen](#einwilligungen)
+ [Wann Daten gesendet werden](#wann-daten-gesendet-werden)
+ [Fehlersuche](#fehlersuche)
+ [Haftung und Rechtssicherheit](#haftung-und-rechtssicherheit)
+ [Weiterführende Links](#weiterführende-links)

---

## Erste Schritte

1. **Bundle installieren**

   ```bash
   composer require digitaledinge/contao-hubspot
   ```

   Danach die Datenbank mit `vendor/bin/contao-console contao:migrate` aktualisieren.

2. **Formular verbinden**

   Bearbeiten Sie das Formular unter Inhalte → Formulare, aktivieren Sie „Daten an HubSpot senden“ und tragen Sie die
   Portal-ID und die Formular-ID des HubSpot-Formulars ein. Siehe [Formular-Einstellungen](#formular-einstellungen).

3. **Felder einrichten**

   Klicken Sie auf „Aus HubSpot importieren“, um die Felder aus dem HubSpot-Formular anzulegen, oder aktivieren Sie „An
   HubSpot senden“ bei Ihren bestehenden Feldern. Siehe [Feld-Einstellungen](#feld-einstellungen).

4. **Formular testen**

   Schicken Sie das Formular auf der Website ab und prüfen Sie die Übermittlung in HubSpot.

---

## Formular-Einstellungen

Die Einstellungen stehen in der Formular-Konfiguration, direkt nach „Eingaben in der Session speichern“:

| Einstellung             | Beschreibung                                         |
|-------------------------|------------------------------------------------------|
| Daten an HubSpot senden | Sendet jede Übermittlung dieses Formulars an HubSpot |
| HubSpot-Portal-ID       | Die Portal-ID (Hub-ID) des HubSpot-Accounts          |
| HubSpot-Formular-ID     | Die ID des HubSpot-Formulars                         |

Beide IDs stehen in der URL und im Einbettungscode des HubSpot-Formulars.

---

## Feld-Einstellungen

Jedes Feld hat einen Bereich „HubSpot-Einstellungen“. Nur Felder mit aktiviertem „An HubSpot senden“ werden gesendet.

| Einstellung             | Beschreibung                                                                              |
|-------------------------|-------------------------------------------------------------------------------------------|
| An HubSpot senden       | Sendet das Feld an HubSpot                                                                |
| HubSpot-Objekt          | Wo HubSpot den Wert speichert: **Kontakt** (`0-1`, Standard) oder **Unternehmen** (`0-2`) |
| Rechtliche Einwilligung | Nur Checkbox-Menüs und Erklärungen: sendet das Feld als [Einwilligung](#einwilligungen)   |

Der Feldname muss dem internen Namen der HubSpot-Eigenschaft entsprechen, z. B. `email`, `firstname` oder `company`. Sie
finden ihn in HubSpot unter Einstellungen → Eigenschaften.

„An HubSpot senden“ gibt es bei diesen Feldtypen: Textfeld, Textarea, Select-Menü, Radio-Button-Menü, Checkbox-Menü,
Range-Slider, verstecktes Feld und Erklärung.

> [!IMPORTANT]
> Aktivieren Sie „An HubSpot senden“ nur bei Feldern, die es im HubSpot-Formular gibt, auch bei versteckten Feldern wie
> UTM-Parametern. HubSpot ordnet unbekannte Felder keiner Eigenschaft zu und lehnt je nach Formular die ganze
> Übermittlung mit `FIELD_NOT_IN_FORM_DEFINITION` ab.

Leere Felder werden nicht gesendet, weil HubSpot bei einem leeren Wert den bisherigen Wert der Eigenschaft behält.

> [!NOTE]
> Felder mit mehreren Werten, z. B. ein Checkbox-Menü mit mehreren Optionen, werden als ein Wert mit `;` als Trennzeichen
> gesendet.

---

## Import aus HubSpot

Statt die Felder von Hand anzulegen, können Sie sie aus dem HubSpot-Formular importieren. Sobald die Portal-ID und die
Formular-ID eingetragen sind, zeigen die Formularliste und die Feldliste die Schaltfläche „Aus HubSpot importieren“. Der
Import zeigt zuerst eine Vorschau der Felder, die er anlegen wird, und legt sie erst nach Ihrer Bestätigung an. Ein
HubSpot-Zugangstoken ist nicht nötig.

| HubSpot                                        | Contao                                                                     |
|------------------------------------------------|----------------------------------------------------------------------------|
| Text, E-Mail, Telefon, Zahl, mehrzeiliger Text | Textfeld oder Textarea                                                     |
| Dropdown, Optionsfelder, Checkboxen            | Select-Menü, Radio-Button-Menü oder Checkbox-Menü mit den HubSpot-Optionen |
| Verstecktes Feld                               | Verstecktes Feld mit dem Standard-Wert                                     |
| Rich Text und Überschriften                    | Erklärung                                                                  |
| Hinweis und Einwilligung                       | Erklärungen und Checkbox-Menüs mit gesetzter „Rechtlicher Einwilligung“    |
| Absenden-Button                                | Absendefeld                                                                |

Bei allen importierten Feldern, die HubSpot erhalten soll, ist „An HubSpot senden“ aktiviert. Sie werden nach den
bestehenden Feldern des Formulars angefügt. Prüfen Sie das Ergebnis danach, vor allem Pflichtfelder und
Eingabeprüfungen.

Manche HubSpot-Felder können nicht importiert werden. Sie werden übersprungen und in der Vorschau aufgelistet:

- Feldtypen, die der Import nicht kennt, z. B. Datei-Uploads
- Eigenschaften anderer Objekte als Kontakte und Unternehmen, z. B. Deals oder Tickets (etwa `0-3/dealname`)

> [!NOTE]
> Der Import liest das Formular auf dieselbe Weise wie HubSpots Einbettungscode. Diese URLs gehören nicht zu HubSpots
> dokumentierter API und können sich ändern. Kann das Formular nicht geladen werden, zeigt die Vorschau eine Warnung.

---

## Einwilligungen

HubSpot speichert Einwilligungen getrennt von den übrigen Feldern, zusammen mit dem Text, den der Besucher gesehen hat.
Markieren Sie das Checkbox-Menü oder die Erklärung, die der Besucher sieht, über „Rechtliche Einwilligung“:

| Auf der Website       | Feldtyp       | Rechtliche Einwilligung            | Zusätzliche Einstellungen         |
|-----------------------|---------------|------------------------------------|-----------------------------------|
| Datenschutz-Checkbox  | Checkbox-Menü | Einwilligung zur Datenverarbeitung |                                   |
| Datenschutzhinweis    | Erklärung     | Einwilligung zur Datenverarbeitung |                                   |
| Abonnement-Checkboxen | Checkbox-Menü | Einwilligung zur Kommunikation     | Abonnementtyp-ID                  |
| Kontakthinweis        | Erklärung     | Berechtigtes Interesse             | Rechtsgrundlage, Abonnementtyp-ID |

Wie Sie diese Felder einrichten, woher der Einwilligungstext stammt und was genau übermittelt wird, beschreibt die
Anleitung zu Einwilligungen: [Deutsch](docs/de/consent.md) · [English](docs/en/consent.md)

---

## Wann Daten gesendet werden

Die Daten werden im Hook `processFormData` gesendet, nachdem das Formular geprüft wurde. Das hängt nicht von „Eingaben in
der Session speichern“, „Eingaben speichern“ oder „Per E-Mail versenden“ ab. Per Ajax abgeschickte Formulare werden
ebenfalls gesendet.

Neben den Feldern sendet die Erweiterung die URL der Seite und, falls vorhanden, das HubSpot-Tracking-Cookie
(`hubspotutk`), damit die Übermittlung mit den bisherigen Seitenaufrufen des Besuchers verknüpft wird.

---

## Fehlersuche

Fehlgeschlagene Übermittlungen werden im Contao-Systemlog unter System → System-Log protokolliert, mit dem
HTTP-Statuscode und der Antwort von HubSpot. Häufige Fehler:

| Fehler                               | Wahrscheinliche Ursache                                                 |
|--------------------------------------|-------------------------------------------------------------------------|
| `FIELD_NOT_IN_FORM_DEFINITION`       | Ein Feld mit „An HubSpot senden“ gibt es im HubSpot-Formular nicht      |
| `MISSING_PROCESSING_CONSENT`         | Abonnement-Checkboxen ohne Datenschutz-Checkbox oder Datenschutzhinweis |
| `MISSING_PROCESSING_CONSENT_TEXT`    | Die Datenschutz-Checkbox oder der Datenschutzhinweis hat keinen Text    |
| `MISSING_COMMUNICATION_CONSENT_TEXT` | Eine Abonnement-Checkbox hat keine Bezeichnung                          |
| `MISSING_LEGITIMATE_INTEREST_TEXT`   | Der Kontakthinweis ist leer                                             |

---

## Haftung und Rechtssicherheit

Wir garantieren keine Rechtssicherheit. Die Nutzung dieser Erweiterung erfolgt auf eigene Gefahr.
Wenden Sie sich an einen Anwalt, wenn Sie nicht sicher sind, welche Hinweise und Checkboxen Ihr Formular braucht und wie
sie formuliert sein sollen. Bitte haben Sie Verständnis, dass wir keine rechtlichen Fragen beantworten können.

---

## Weiterführende Links

| Link                                                                                                                                                           | Inhalt                                                    |
|----------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------|
| [Forms API: Daten übermitteln](https://developers.hubspot.com/docs/api-reference/legacy/marketing/forms/v3-legacy/submit-data-unauthenticated) (Englisch)      | Aufbau der Anfrage, Einwilligungsoptionen und Fehlercodes |
| [Hinweise und Einwilligungen zu HubSpot-Formularen hinzufügen](https://knowledge.hubspot.com/de/forms/add-notice-and-consent-information-to-your-hubspot-form) | Die Einwilligungseinstellungen von HubSpot-Formularen     |
