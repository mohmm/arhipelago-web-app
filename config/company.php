<?php

/*
|--------------------------------------------------------------------------
| Who the customer is actually contracting with
|--------------------------------------------------------------------------
|
| Smartsense is the product; ALTFACTOR SRL is the legal person behind it. These
| details are quoted in the legal documents, the website footer and the footer
| of every email, and Law 365/2002 art. 5 requires them to be permanently
| accessible — so they live in one place rather than being retyped.
|
| The website reads them from the `company` prop that HandleInertiaRequests
| shares with every page.
|
*/

return [

    'legal_name' => 'ALTFACTOR SRL',

    'trade_register' => 'J17/917/1999',

    // Two different numbers that get used as one. The CUI identifies the
    // company; the VAT ID is the CUI with the country prefix and only exists
    // while the company is registered for VAT. Publishing one under the other's
    // label is what every Romanian site does, and it is still wrong.
    'cui' => '12476351',

    'vat_id' => 'RO12476351',

    // Law 31/1990 art. 74(5) requires an SRL with its own website to publish
    // its share capital. Fill this in and it appears in the website footer;
    // left empty, nothing is rendered rather than something invented.
    'share_capital' => '',

    'email' => 'welcome@altfactor.ro',

    // Where a customer with a problem should write. Deliberately separate from
    // `email`, which is the legal contact address quoted in the published Terms
    // and DPA and must keep saying what those documents say.
    'support_email' => 'support@smartsense.ro',

    'phone' => '+40 751 082 609',

    'address' => 'Str. 8 Martie nr. 29, 800258 Galați',

    'country' => 'România',

    'website' => 'https://altfactor.ro',

];
