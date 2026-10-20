# Einwilligungen

[English version](../en/consent.md)

In Contao sind Einwilligungen Teil des Formulars: eine Checkbox, die der Besucher anhakt, oder eine Erklärung, die er
vor dem Absenden liest. Diese Anleitung zeigt, wie Sie diese Felder im Formulargenerator einrichten, damit HubSpot sie
als Einwilligung am Kontakt speichert und nicht als normale Werte.

> [!IMPORTANT]
> Dies ist eine technische Anleitung, keine Rechtsberatung.
> Wir garantieren keine Rechtssicherheit. Die Nutzung dieser Erweiterung erfolgt auf eigene Gefahr.
> Wenden Sie sich an einen Anwalt, wenn Sie nicht sicher sind, welche Hinweise und Checkboxen Ihr Formular braucht und
> wie sie formuliert sein sollen. Bitte haben Sie Verständnis, dass wir keine rechtlichen Fragen beantworten können.

+ [Übersicht](#übersicht)
+ [Einwilligungsfeld einrichten](#einwilligungsfeld-einrichten)
+ [Datenschutz-Checkbox](#datenschutz-checkbox)
+ [Datenschutzhinweis](#datenschutzhinweis)
+ [Abonnement-Checkboxen](#abonnement-checkboxen)
    + [Abonnementtyp-ID](#abonnementtyp-id)
+ [Kontakthinweis](#kontakthinweis)
+ [Einwilligungstext](#einwilligungstext)
+ [Passend zum HubSpot-Formular](#passend-zum-hubspot-formular)
+ [Was übermittelt wird](#was-übermittelt-wird)
+ [Tipps](#tipps)
+ [Häufige Fehler](#häufige-fehler)
+ [Weiterführende Links](#weiterführende-links)

---

## Übersicht

| Auf der Website                                 | Feldtyp       | Rechtliche Einwilligung            | Zusätzliche Einstellungen         |
|-------------------------------------------------|---------------|------------------------------------|-----------------------------------|
| [Datenschutz-Checkbox](#datenschutz-checkbox)   | Checkbox-Menü | Einwilligung zur Datenverarbeitung |                                   |
| [Datenschutzhinweis](#datenschutzhinweis)       | Erklärung     | Einwilligung zur Datenverarbeitung |                                   |
| [Abonnement-Checkboxen](#abonnement-checkboxen) | Checkbox-Menü | Einwilligung zur Kommunikation     | Abonnementtyp-ID                  |
| [Kontakthinweis](#kontakthinweis)               | Erklärung     | Berechtigtes Interesse             | Rechtsgrundlage, Abonnementtyp-ID |

Ein Formular mit Abonnement-Checkboxen braucht immer auch eine Datenschutz-Checkbox oder einen Datenschutzhinweis.
HubSpot akzeptiert die Einwilligung zur Kommunikation nur zusammen mit der Einwilligung zur Datenverarbeitung.

---

## Einwilligungsfeld einrichten

1. Gehen Sie zu Inhalte → Formulare und bearbeiten Sie die Felder des Formulars.
2. Bearbeiten Sie das Checkbox-Menü oder die Erklärung, die der Besucher sieht.
3. Aktivieren Sie unter „HubSpot-Einstellungen“ die Option „An HubSpot senden“.
4. Wählen Sie unter „Rechtliche Einwilligung“ die Art aus und füllen Sie die zusätzlichen Einstellungen aus.

Oder lassen Sie die Felder vom [Import](../../README.de.md#import-aus-hubspot) anlegen. Er übernimmt alle diese
Einstellungen aus dem HubSpot-Formular, einschließlich der Abonnementtyp-IDs.

---

## Datenschutz-Checkbox

Ein Checkbox-Menü mit einer Option, z. B. „Ich stimme der Speicherung und Verarbeitung meiner Daten zu.“ Der Besucher
muss sie anhaken, machen Sie das Feld also zum Pflichtfeld. Eine nicht angehakte Checkbox wird als „keine Einwilligung“
übermittelt.

| Einstellung             | Wert                               |
|-------------------------|------------------------------------|
| Feldtyp                 | Checkbox-Menü                      |
| Pflichtfeld             | Ja                                 |
| Rechtliche Einwilligung | Einwilligung zur Datenverarbeitung |

---

## Datenschutzhinweis

Eine Erklärung über dem Absendefeld, z. B. „Mit dem Absenden des Formulars stimmen Sie der Speicherung und Verarbeitung
Ihrer Daten zu.“ Es gibt nichts anzuhaken: Das Absenden des Formulars gilt als Einwilligung.

| Einstellung             | Wert                               |
|-------------------------|------------------------------------|
| Feldtyp                 | Erklärung                          |
| Rechtliche Einwilligung | Einwilligung zur Datenverarbeitung |

---

## Abonnement-Checkboxen

Ein Checkbox-Menü pro Abonnement, das der Besucher wählen kann, z. B. „Newsletter“ oder „Veranstaltungseinladungen“.
Diese Checkboxen sind freiwillig, machen Sie sie also nicht zum Pflichtfeld. Eine nicht angehakte Checkbox wird als
„keine Einwilligung“ übermittelt. Ein Kontakt, der sich früher angemeldet hat, wird dadurch nicht abgemeldet.

| Einstellung             | Wert                                           |
|-------------------------|------------------------------------------------|
| Feldtyp                 | Checkbox-Menü                                  |
| Rechtliche Einwilligung | Einwilligung zur Kommunikation                 |
| Abonnementtyp-ID        | Der HubSpot-Abonnementtyp dieser Checkbox      |

### Abonnementtyp-ID

HubSpot speichert Einwilligungen pro Abonnementtyp, nicht allgemein als „der Besucher möchte E-Mails erhalten“. Über die
ID weiß HubSpot, für welches Abonnement die Checkbox steht, und zeigt es im Bereich „Abonnements“ des Kontakts an. Ohne
ID kann HubSpot die Checkbox keinem Abonnement zuordnen.

Die ID finden Sie an zwei Stellen:

- **Import:** Die Vorschau zeigt sie in der Spalte „HubSpot“, und der Import trägt sie am Feld ein.
- **HubSpot:** unter Einstellungen → Marketing → E-Mail → Abonnementtypen, siehe
  [E-Mail-Abonnementtypen einrichten](https://knowledge.hubspot.com/de/marketing-email/set-up-email-subscription-types).

---

## Kontakthinweis

Eine Erklärung, die dem Besucher mitteilt, dass Sie ihn zu Ihren Produkten und Dienstleistungen kontaktieren und er sich
jederzeit abmelden kann. Es gibt nichts anzuhaken: HubSpot erfasst ein berechtigtes Interesse statt einer Einwilligung.

| Einstellung             | Wert                                                        |
|-------------------------|-------------------------------------------------------------|
| Feldtyp                 | Erklärung                                                   |
| Rechtliche Einwilligung | Berechtigtes Interesse                                      |
| Rechtsgrundlage         | **Lead** für Interessenten, **Kunde** für bestehende Kunden |
| Abonnementtyp-ID        | Der Abonnementtyp, für den der Kontakt Kommunikation erhält |

---

## Einwilligungstext

HubSpot speichert zur Einwilligung den Text, den der Besucher gesehen hat. Die Erweiterung nimmt ihn aus dem Feld:

| Feldtyp       | Text                                                                       |
|---------------|----------------------------------------------------------------------------|
| Checkbox-Menü | Die Bezeichnungen der Optionen, ohne Optionen die Feldbezeichnung          |
| Erklärung     | Der Text der Erklärung                                                     |

Insert-Tags werden ersetzt und HTML entfernt, damit HubSpot den Text so erhält, wie der Besucher ihn gelesen hat.

---

## Passend zum HubSpot-Formular

Das HubSpot-Formular hat eine eigene Einstellung für Einwilligungen. Verwenden Sie in Contao die passenden Felder:

| Einstellung in HubSpot                                                                | Felder in Contao                                  |
|---------------------------------------------------------------------------------------|---------------------------------------------------|
| Implizite Einwilligung zur Verarbeitung und einzelne Checkboxen für die Kommunikation | Datenschutzhinweis + Abonnement-Checkboxen        |
| Separate Einwilligungen (einzelne Checkboxen pro Einwilligung)                        | Datenschutz-Checkbox + Abonnement-Checkboxen      |
| Berechtigtes Interesse                                                                | Kontakthinweis                                    |

---

## Was übermittelt wird

Für ein Formular mit einer Datenschutz-Checkbox und zwei Abonnement-Checkboxen übermittelt die Erweiterung:

```json
{
    "fields": [
        {"objectTypeId": "0-1", "name": "email", "value": "jane@example.com"}
    ],
    "legalConsentOptions": {
        "consent": {
            "consentToProcess": true,
            "text": "Ich stimme der Speicherung und Verarbeitung meiner personenbezogenen Daten durch Example GmbH zu.",
            "communications": [
                {"value": true, "subscriptionTypeId": 123, "text": "Ich möchte den Newsletter erhalten."},
                {"value": false, "subscriptionTypeId": 456, "text": "Ich möchte Veranstaltungseinladungen erhalten."}
            ]
        }
    }
}
```

Bei einem Kontakthinweis enthält `legalConsentOptions` stattdessen `legitimateInterest`:

```json
{
    "legitimateInterest": {
        "value": true,
        "subscriptionTypeId": 123,
        "legalBasis": "LEAD",
        "text": "Wir kontaktieren Sie zu unseren Produkten und Dienstleistungen. Sie können sich jederzeit abmelden."
    }
}
```

---

## Tipps

- **Mit dem Import beginnen.** Er legt die Einwilligungsfelder mit den Texten und Abonnementtyp-IDs des
  HubSpot-Formulars an.
- **Texte in Contao ändern.** HubSpot speichert den Text, den Contao sendet, nicht den Text des HubSpot-Formulars. Ändert
  sich die Formulierung, ändern Sie sie am Contao-Feld.
- **Einwilligungs-Checkboxen nicht vorauswählen.** Lassen Sie den Standard-Wert des Checkbox-Menüs leer.
- **Ergebnis am Kontakt prüfen.** Öffnen Sie nach einer Testübermittlung den Kontakt in HubSpot: Der Bereich
  „Abonnements“ zeigt die Abonnements, die Aktivitäten zeigen die Formularübermittlung.

---

## Häufige Fehler

Fehlgeschlagene Übermittlungen werden im Contao-Systemlog mit der Antwort von HubSpot protokolliert.

| Fehler                               | Wahrscheinliche Ursache                                                       |
|--------------------------------------|-------------------------------------------------------------------------------|
| `MISSING_PROCESSING_CONSENT`         | Abonnement-Checkboxen ohne Datenschutz-Checkbox oder Datenschutzhinweis       |
| `MISSING_PROCESSING_CONSENT_TEXT`    | Die Datenschutz-Checkbox oder der Datenschutzhinweis hat keinen Text          |
| `MISSING_COMMUNICATION_CONSENT_TEXT` | Eine Abonnement-Checkbox hat keine Bezeichnung                                |
| `MISSING_LEGITIMATE_INTEREST_TEXT`   | Der Kontakthinweis ist leer                                                   |

---

## Weiterführende Links

| Link                                                                                                                                                           | Inhalt                                                 |
|----------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------|
| [Hinweise und Einwilligungen zu HubSpot-Formularen hinzufügen](https://knowledge.hubspot.com/de/forms/add-notice-and-consent-information-to-your-hubspot-form) | Die Einwilligungseinstellungen von HubSpot-Formularen  |
| [E-Mail-Abonnementtypen einrichten](https://knowledge.hubspot.com/de/marketing-email/set-up-email-subscription-types)                                          | Abonnementtypen anlegen und bearbeiten                 |
| [Datenschutzeinstellungen aktivieren](https://knowledge.hubspot.com/de/privacy-and-consent/how-do-i-turn-on-gdpr-functionality-in-my-hubspot-account)          | DSGVO-Funktionen in HubSpot                            |
| [Rechtsgrundlage der Verarbeitung nachverfolgen](https://knowledge.hubspot.com/de/contacts/how-can-i-track-lawful-basis-of-processing-in-hubspot)              | Wie HubSpot die Rechtsgrundlage am Kontakt speichert   |
| [Forms API: Daten übermitteln](https://developers.hubspot.com/docs/api-reference/legacy/marketing/forms/v3-legacy/submit-data-unauthenticated) (Englisch)      | Aufbau von `legalConsentOptions` und die Fehlercodes   |
