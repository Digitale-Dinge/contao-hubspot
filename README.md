<h1 align="center">Contao HubSpot</h1>

<p align="center">
    <a href="https://packagist.org/packages/digitaledinge/contao-hubspot"><img src="https://img.shields.io/packagist/v/digitaledinge/contao-hubspot" alt="packagist version"/></a>
    <a href="https://packagist.org/packages/digitaledinge/contao-hubspot"><img src="https://img.shields.io/packagist/dt/digitaledinge/contao-hubspot?color=f47c00" alt="amount of downloads"/></a>
    <a href="https://packagist.org/packages/digitaledinge/contao-hubspot"><img src="https://img.shields.io/packagist/dependency-v/digitaledinge/contao-hubspot/php?color=474A8A" alt="minimum php version"></a>
</p>

<p align="center">
    <b>English</b> · <a href="README.de.md">Deutsch</a>
</p>

---

## Description

Connects forms from the Contao form generator with HubSpot. When a visitor submits the form, the fields you choose are
sent to a HubSpot form, including consent checkboxes and privacy notices. Everything is set up in the Contao back end on
the form and its fields. The fields can also be imported from an existing HubSpot form.

Requires PHP `^8.4` and Contao `^5.7 || ^6.0`.

---

+ [Getting started](#getting-started)
+ [Form settings](#form-settings)
+ [Field settings](#field-settings)
+ [Import from HubSpot](#import-from-hubspot)
+ [Consent](#consent)
+ [When data is sent](#when-data-is-sent)
+ [Troubleshooting](#troubleshooting)
+ [Liability and legal security](#liability-and-legal-security)
+ [Further reading](#further-reading)

---

## Getting started

1. **Install the bundle**

   ```bash
   composer require digitaledinge/contao-hubspot
   ```

   Then update the database with `bin/console contao:migrate`.

2. **Connect the form**

   Edit the form under Content → Forms, check "Send data to HubSpot" and enter the portal ID and the form ID of the
   HubSpot form. See [Form settings](#form-settings).

3. **Set up the fields**

   Click "Import from HubSpot" to create the fields from the HubSpot form, or check "Send to HubSpot" on your existing
   fields. See [Field settings](#field-settings).

4. **Test the form**

   Submit the form on the website and check the submission in HubSpot.

---

## Form settings

The settings are in the form configuration, right after "Store data in the session":

| Setting              | Description                                       |
|----------------------|---------------------------------------------------|
| Send data to HubSpot | Sends every submission of this form to HubSpot    |
| HubSpot portal ID    | The portal ID (Hub ID) of the HubSpot account     |
| HubSpot form ID      | The ID of the HubSpot form                        |

Both IDs are part of the HubSpot form's URL and embed code.

---

## Field settings

Each field has a "HubSpot settings" section. Only fields with "Send to HubSpot" checked are sent.

| Setting         | Description                                                                                 |
|-----------------|---------------------------------------------------------------------------------------------|
| Send to HubSpot | Sends the field to HubSpot                                                                  |
| HubSpot object  | Where HubSpot stores the value: **Contact** (`0-1`, default) or **Company** (`0-2`)         |
| Legal consent   | Checkbox menus and explanations only: sends the field as [consent](#consent)                |

The field name must be the internal name of the HubSpot property, e.g. `email`, `firstname` or `company`. You find it in
HubSpot under Settings → Properties.

"Send to HubSpot" is available for these field types: text field, textarea, select menu, radio button menu, checkbox
menu, range slider, hidden field and explanation.

> [!IMPORTANT]
> Only check "Send to HubSpot" on fields that exist in the HubSpot form, including hidden fields such as UTM parameters.
> HubSpot does not assign unknown fields to a property and, depending on the form, rejects the whole submission with
> `FIELD_NOT_IN_FORM_DEFINITION`.

Empty fields are not sent, because HubSpot keeps a property's existing value when it receives an empty one.

> [!NOTE]
> Fields with several values, such as a checkbox menu with multiple options, are sent as one value separated by `;`.

---

## Import from HubSpot

Instead of creating the fields by hand, import them from the HubSpot form. Once the portal ID and the form ID are set,
the form list and the field list show an "Import from HubSpot" button. The import first shows a preview of the fields it
will create and only creates them after you confirm. No HubSpot access token is needed.

| HubSpot                                     | Contao                                                                     |
|---------------------------------------------|----------------------------------------------------------------------------|
| Text, email, phone, number, multi-line text | Text field or textarea                                                     |
| Dropdown, radio, checkboxes                 | Select menu, radio button menu or checkbox menu with the HubSpot options   |
| Hidden field                                | Hidden field with the default value                                        |
| Rich text and headings                      | Explanation                                                                |
| Notice and consent                          | Explanations and checkbox menus with "Legal consent" set                   |
| Submit button                               | Submit field                                                               |

All imported fields that HubSpot should receive have "Send to HubSpot" checked. They are added after the existing fields
of the form. Check the result afterwards, especially mandatory fields and validation.

Some HubSpot fields cannot be imported. They are skipped and listed in the preview:

- Field types the import does not know, such as file uploads
- Properties of objects other than contacts and companies, such as deals or tickets (e.g. `0-3/dealname`)

If the HubSpot form uses a CAPTCHA (reCAPTCHA), the preview shows an error. HubSpot rejects submissions from Contao as
long as the CAPTCHA is turned on, so turn it off in the settings of the form in HubSpot.

> [!NOTE]
> The import reads the form the same way HubSpot's embed code does. These URLs are not part of HubSpot's documented
> API and may change. If the form cannot be loaded, the preview shows a warning.

---

## Consent

HubSpot stores consent separately from the other fields, together with the text the visitor saw. Mark the checkbox menu
or explanation the visitor sees with "Legal consent":

| On the website          | Field type    | Legal consent           | Additional settings               |
|-------------------------|---------------|-------------------------|-----------------------------------|
| Privacy checkbox        | Checkbox menu | Consent to process data |                                   |
| Privacy notice          | Explanation   | Consent to process data |                                   |
| Subscription checkboxes | Checkbox menu | Consent to communicate  | Subscription type ID              |
| Contact notice          | Explanation   | Legitimate interest     | Legal basis, subscription type ID |

How to set up each of these fields, where the consent text comes from and what exactly is sent is described in the
consent guide: [English](docs/en/consent.md) · [Deutsch](docs/de/consent.md)

---

## When data is sent

The data is sent in the `processFormData` hook, after the form has been validated. It does not depend on "Store data in
the session", "Store data" or "Send form data via e-mail". Forms submitted via Ajax are sent as well.

Besides the fields, the extension sends the page URL and, if present, the HubSpot tracking cookie (`hubspotutk`), so the
submission is linked to the visitor's previous page views.

---

## Troubleshooting

Failed submissions are logged in the Contao system log under System → System log, with the HTTP status code and
HubSpot's response. Common errors:

| Error                                | Likely cause                                                         |
|--------------------------------------|----------------------------------------------------------------------|
| `FIELD_NOT_IN_FORM_DEFINITION`       | A field with "Send to HubSpot" does not exist in the HubSpot form    |
| `MISSING_PROCESSING_CONSENT`         | Subscription checkboxes without a privacy checkbox or privacy notice |
| `MISSING_PROCESSING_CONSENT_TEXT`    | The privacy checkbox or privacy notice has no text                   |
| `MISSING_COMMUNICATION_CONSENT_TEXT` | A subscription checkbox has no label                                 |
| `MISSING_LEGITIMATE_INTEREST_TEXT`   | The contact notice is empty                                          |

---

## Liability and legal security

We do not guarantee legal security. Use this extension at your own risk and contact a lawyer if you are not sure
which notices and checkboxes your form needs or how they should be worded. Please understand that we cannot answer any
legal questions.

---

## Further reading

| Link                                                                                                                                     | Content                                               |
|------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------|
| [Forms API: Submit data](https://developers.hubspot.com/docs/api-reference/legacy/marketing/forms/v3-legacy/submit-data-unauthenticated) | Request format, consent options and error codes       |
| [Add notice and consent information](https://knowledge.hubspot.com/forms/add-notice-and-consent-information-to-your-hubspot-form)        | The consent settings of HubSpot forms                 |
