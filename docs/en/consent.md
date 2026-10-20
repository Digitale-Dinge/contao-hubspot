# Consent

[Deutsche Version](../de/consent.md)

In Contao, consent is part of the form: a checkbox the visitor ticks or an explanation they read before submitting. This
guide shows how to set up these fields in the form generator, so HubSpot stores them as consent on the contact and not as
ordinary values.

> [!IMPORTANT]
> This is a technical guide, not legal advice.
> We do not guarantee legal security. Use this extension at your own risk and contact a lawyer if you are not
> sure which notices and checkboxes your form needs or how they should be worded. Please understand that we cannot
> answer any legal questions.

+ [Overview](#overview)
+ [Set up a consent field](#set-up-a-consent-field)
+ [Privacy checkbox](#privacy-checkbox)
+ [Privacy notice](#privacy-notice)
+ [Subscription checkboxes](#subscription-checkboxes)
    + [Subscription type ID](#subscription-type-id)
+ [Contact notice](#contact-notice)
+ [Consent text](#consent-text)
+ [Match the HubSpot form](#match-the-hubspot-form)
+ [What is sent](#what-is-sent)
+ [Tips](#tips)
+ [Common errors](#common-errors)
+ [Further reading](#further-reading)

---

## Overview

| On the website                                      | Field type    | Legal consent           | Additional settings               |
|-----------------------------------------------------|---------------|-------------------------|-----------------------------------|
| [Privacy checkbox](#privacy-checkbox)               | Checkbox menu | Consent to process data |                                   |
| [Privacy notice](#privacy-notice)                   | Explanation   | Consent to process data |                                   |
| [Subscription checkboxes](#subscription-checkboxes) | Checkbox menu | Consent to communicate  | Subscription type ID              |
| [Contact notice](#contact-notice)                   | Explanation   | Legitimate interest     | Legal basis, subscription type ID |

A form with subscription checkboxes always needs a privacy checkbox or a privacy notice as well. HubSpot only accepts
consent to communicate together with consent to process data.

---

## Set up a consent field

1. Go to Content → Forms and edit the fields of the form.
2. Edit the checkbox menu or explanation the visitor sees.
3. Under "HubSpot settings", check "Send to HubSpot".
4. Choose the type under "Legal consent" and fill in the additional settings.

Or let the [import](../../README.md#import-from-hubspot) create the fields. It takes all of these settings from the
HubSpot form, including the subscription type IDs.

---

## Privacy checkbox

A checkbox menu with one option, e.g. "I agree to the storage and processing of my data." The visitor has to tick it, so
make it a mandatory field. An unticked box is sent as "no consent".

| Setting         | Value                   |
|-----------------|-------------------------|
| Field type      | Checkbox menu           |
| Mandatory field | Yes                     |
| Legal consent   | Consent to process data |

---

## Privacy notice

An explanation above the submit field, e.g. "By clicking submit, you consent to the storage and processing of your
data." There is nothing to tick: submitting the form counts as consent.

| Setting       | Value                   |
|---------------|-------------------------|
| Field type    | Explanation             |
| Legal consent | Consent to process data |

---

## Subscription checkboxes

One checkbox menu per subscription the visitor can choose, e.g. "Newsletter" or "Event invitations". These boxes are
optional, so do not make them mandatory. An unticked box is sent as "no consent", which does not unsubscribe a contact
who subscribed earlier.

| Setting              | Value                                          |
|----------------------|------------------------------------------------|
| Field type           | Checkbox menu                                  |
| Legal consent        | Consent to communicate                         |
| Subscription type ID | The HubSpot subscription type of this checkbox |

### Subscription type ID

HubSpot stores consent per subscription type, not as "the visitor wants e-mails" in general. The ID tells HubSpot which
subscription the checkbox stands for, so it shows up in the subscriptions section of the contact. Without it, HubSpot
cannot assign the checkbox to anything.

You find the ID in two places:

- **Import:** the preview shows it in the HubSpot column, and the import sets it on the field.
- **HubSpot:** under Settings → Marketing → Email → Subscription types, see
  [Set up email subscription types](https://knowledge.hubspot.com/marketing-email/set-up-email-subscription-types).

---

## Contact notice

An explanation that tells the visitor you will contact them about your products and services and that they can
unsubscribe at any time. There is nothing to tick: HubSpot records legitimate interest instead of consent.

| Setting              | Value                                                        |
|----------------------|--------------------------------------------------------------|
| Field type           | Explanation                                                  |
| Legal consent        | Legitimate interest                                          |
| Legal basis          | **Lead** for prospects, **Customer** for existing customers  |
| Subscription type ID | The subscription type the contact receives communication for |

---

## Consent text

HubSpot stores the text the visitor saw together with the consent. The extension takes it from the field:

| Field type    | Text                                                          |
|---------------|---------------------------------------------------------------|
| Checkbox menu | The option labels, or the field label if there are no options |
| Explanation   | The text of the explanation                                   |

Insert tags are replaced and HTML is removed, so HubSpot gets the text as the visitor read it.

---

## Match the HubSpot form

The HubSpot form has its own consent setting. Use the matching fields in Contao:

| Setting in HubSpot                                                       | Fields in Contao                           |
|--------------------------------------------------------------------------|--------------------------------------------|
| Implicit processing consent and individual checkboxes for communications | Privacy notice + subscription checkboxes   |
| Separate consents (individual checkboxes per consent)                    | Privacy checkbox + subscription checkboxes |
| Legitimate interest                                                      | Contact notice                             |

---

## What is sent

For a form with a privacy checkbox and two subscription checkboxes, the extension sends:

```json
{
    "fields": [
        {"objectTypeId": "0-1", "name": "email", "value": "jane@example.com"}
    ],
    "legalConsentOptions": {
        "consent": {
            "consentToProcess": true,
            "text": "I agree to allow Example Inc. to store and process my personal data.",
            "communications": [
                {"value": true, "subscriptionTypeId": 123, "text": "I agree to receive the newsletter."},
                {"value": false, "subscriptionTypeId": 456, "text": "I agree to receive event invitations."}
            ]
        }
    }
}
```

With a contact notice, `legalConsentOptions` contains `legitimateInterest` instead:

```json
{
    "legitimateInterest": {
        "value": true,
        "subscriptionTypeId": 123,
        "legalBasis": "LEAD",
        "text": "We will contact you about our products and services. You can unsubscribe at any time."
    }
}
```

---

## Tips

- **Start with the import.** It creates the consent fields with the texts and subscription type IDs of the HubSpot form.
- **Change texts in Contao.** HubSpot stores the text Contao sends, not the text of the HubSpot form. If the wording
  changes, change it on the Contao field.
- **Do not preselect consent checkboxes.** Leave the default value of the checkbox menu empty.
- **Check the result on the contact.** After a test submission, open the contact in HubSpot: the subscriptions section
  shows the subscriptions, the activity timeline shows the form submission.

---

## Common errors

Failed submissions are logged in the Contao system log with HubSpot's response.

| Error                                | Likely cause                                                         |
|--------------------------------------|----------------------------------------------------------------------|
| `MISSING_PROCESSING_CONSENT`         | Subscription checkboxes without a privacy checkbox or privacy notice |
| `MISSING_PROCESSING_CONSENT_TEXT`    | The privacy checkbox or privacy notice has no text                   |
| `MISSING_COMMUNICATION_CONSENT_TEXT` | A subscription checkbox has no label                                 |
| `MISSING_LEGITIMATE_INTEREST_TEXT`   | The contact notice is empty                                          |

---

## Further reading

| Link                                                                                                                                                   | Content                                                     |
|--------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------|
| [Add notice and consent information to your HubSpot form](https://knowledge.hubspot.com/forms/add-notice-and-consent-information-to-your-hubspot-form) | The consent settings of HubSpot forms                       |
| [Set up email subscription types](https://knowledge.hubspot.com/marketing-email/set-up-email-subscription-types)                                       | Creating and editing subscription types                     |
| [Turn on data privacy settings](https://knowledge.hubspot.com/privacy-and-consent/how-do-i-turn-on-gdpr-functionality-in-my-hubspot-account)           | GDPR features in HubSpot                                    |
| [Track lawful basis of processing](https://knowledge.hubspot.com/contacts/how-can-i-track-lawful-basis-of-processing-in-hubspot)                       | How HubSpot stores the legal basis on the contact           |
| [Forms API: Submit data](https://developers.hubspot.com/docs/api-reference/legacy/marketing/forms/v3-legacy/submit-data-unauthenticated)               | Request format of `legalConsentOptions` and the error codes |
