/*!
 *  Lang.js for Laravel localization in JavaScript.
 *
 *  @version 1.1.0
 *  @license MIT
 *  @site    https://github.com/rmariuzzo/Laravel-JS-Localization
 *  @author  rmariuzzo
 */

'use strict';

(function(root, factory) {

    if (typeof define === 'function' && define.amd) {
        // AMD support.
        define([], factory);
    } else if (typeof exports === 'object') {
        // NodeJS support.
        module.exports = new(factory())();
    } else {
        // Browser global support.
        root.Lang = new(factory())();
    }

}(this, function() {

    // Default options //

    var defaults = {
        defaultLocale: 'en' /** The default locale if not set. */
    };

    // Constructor //

    var Lang = function(options) {
        options = options || {};
        this.defaultLocale = options.defaultLocale || defaults.defaultLocale;
    };

    // Methods //

    /**
     * Set messages source.
     *
     * @param messages {object} The messages source.
     *
     * @return void
     */
    Lang.prototype.setMessages = function(messages) {
        this.messages = messages;
    };

    /**
     * Returns a translation message.
     *
     * @param key {string} The key of the message.
     * @param replacements {object} The replacements to be done in the message.
     *
     * @return {string} The translation message, if not found the given key.
     */
    Lang.prototype.get = function(key, replacements) {
        if (!this.has(key)) {
            return key;
        }

        var message = this._getMessage(key, replacements);
        if (message === null) {
            return key;
        }

        if (replacements) {
            message = this._applyReplacements(message, replacements);
        }

        return message;
    };

    /**
     * Returns true if the key is defined on the messages source.
     *
     * @param key {string} The key of the message.
     *
     * @return {boolean} true if the given key is defined on the messages source, otherwise false.
     */
    Lang.prototype.has = function(key) {
        if (typeof key !== 'string' || !this.messages) {
            return false;
        }
        return this._getMessage(key) !== null;
    };

    /**
     * Gets the plural or singular form of the message specified based on an integer value.
     *
     * @param key {string} The key of the message.
     * @param count {integer} The number of elements.
     * @param replacements {object} The replacements to be done in the message.
     *
     * @return {string} The translation message according to an integer value.
     */
    Lang.prototype.choice = function(key, count, replacements) {
        // Set default values for parameters replace and locale
        replacements = typeof replacements !== 'undefined' ? replacements : {};
        
        // The count must be replaced if found in the message
        replacements['count'] = count;

        // Message to get the plural or singular
        var message = this.get(key, replacements);

        // Check if message is not null or undefined
        if (message === null || message === undefined) {
            return message;
        }

        // Separate the plural from the singular, if any
        var messageParts = message.split('|');

        // Get the explicit rules, If any
        var explicitRules = [];
        var regex = /{\d+}\s(.+)|\[\d+,\d+\]\s(.+)|\[\d+,Inf\]\s(.+)/;

        for (var i = 0; i < messageParts.length; i++) {
            messageParts[i] = messageParts[i].trim();

            if (regex.test(messageParts[i])) {
                var messageSpaceSplit = messageParts[i].split(/\s/);
                explicitRules.push(messageSpaceSplit.shift());
                messageParts[i] = messageSpaceSplit.join(' ');
            }
        }

        // Check if there's only one message
        if (messageParts.length === 1) {
            // Nothing to do here
            return message;
        }

        // Check the explicit rules
        for (var i = 0; i < explicitRules.length; i++) {
            if (this._testInterval(count, explicitRules[i])) {
                return messageParts[i];
            }
        }

        // Standard rules
        if (count > 1) {
            return messageParts[1];
        } else {
            return messageParts[0];
        }
    };

    /**
     * Set the current locale.
     *
     * @param locale {string} The locale to set.
     *
     * @return void
     */
    Lang.prototype.setLocale = function(locale) {
        this.locale = locale;
    };

    /**
     * Get the current locale.
     *
     * @return {string} The current locale.
     */
    Lang.prototype.getLocale = function() {
        return this.locale || this.defaultLocale;
    };

    /**
     * Parse a message key into components.
     *
     * @param key {string} The message key to parse.
     *
     * @return {object} A key object with source and entries properties.
     */
    Lang.prototype._parseKey = function(key) {
        if (typeof key !== 'string') {
            return null;
        }
        var segments = key.split('.');
        return {
            source: this.getLocale() + '.' + segments[0],
            entries: segments.slice(1)
        };
    };

    /**
     * Returns a translation message. Use `Lang.get()` method instead, this methods assumes the key exists.
     *
     * @param key {string} The key of the message.
     *
     * @return {string} The translation message for the given key.
     */
    Lang.prototype._getMessage = function(key) {

        key = this._parseKey(key);

        // Ensure message source exists.
        if (this.messages[key.source] === undefined) {
            return null;
        }

        // Get message text.
        var message = this.messages[key.source];
        while (key.entries.length && (message = message[key.entries.shift()]));

        if (typeof message !== 'string') {
            return null;
        }

        return message;
    };

    /**
     * Apply replacements to a string message containing placeholders.
     *
     * @param message {string} The text message.
     * @param replacements {object} The replacements to be done in the message.
     *
     * @return {string} The string message with replacements applied.
     */
    Lang.prototype._applyReplacements = function(message, replacements) {
        for (var replace in replacements) {
            message = message.split(':' + replace).join(replacements[replace]);
        }
        return message;
    };

    /**
     * Checks if the given `count` is within the interval defined by the {string} `interval`
     *
     * @param  count {int}  The amount of items.
     * @param  interval {string}    The interval to be compared with the count.
     * @return {boolean}    Returns true if count is within interval; false otherwise.
     */
    Lang.prototype._testInterval = function(count, interval) {
        /**
         * From the Symfony\Component\Translation\Interval Docs
         *
         * Tests if a given number belongs to a given math interval.
         * An interval can represent a finite set of numbers: {1,2,3,4}
         * An interval can represent numbers between two numbers: [1, +Inf] ]-1,2[
         * The left delimiter can be [ (inclusive) or ] (exclusive).
         * The right delimiter can be [ (exclusive) or ] (inclusive).
         * Beside numbers, you can use -Inf and +Inf for the infinite.
         */

        return false;
    };

    return Lang;

}));


(function(root) {
    Lang.setMessages({"dk.labels":{"login":"Log ind","logout":"Log ud","contract":"Kontrakt","contracts":"Kontrakter","invoice":"Faktura","invoices":"Fakturaer","dashboard":"Dashboard","calendar":"Kalender","company-name":"Navn","company-address":"Addresse","company-email":"e-mail","company-phone":"Telefon","city":"By","phone":"Telefonnummer","contact-person":"Kontaktperson","contact-phone":"Telefonnummer","contact-email":"Kontakt email","contact-birthday":"F\u00f8dselsdag","contact-info":"Kontakt info","salesman":"S\u00e6lger","last-call":"Sidste opkald","create-client":"Opret kunde","edit-profile":"Rediger profil","my-account":"Min konto","help":"Hj\u00e6lp","all-messages":"Se alle beskeder","comments":"\u041aommentarer","comment":"\u041aommentar","save":"Gem","edit":"Rediger","set-paid":"S\u00e6t betalt","make-creditnote":"Lav Kreditnota","invoice-number":"Faktura","pay-date":"Betalt dato","invoice-info":"Faktura info","edit-invoice":"Rediger faktura","sub-total":"Subtal","discount":"Rabat","tax":"Moms","product-description":"Produkt","quantity":"Antal","unit-net-price":"Pris","total-net-amount":"Nettobel\u00f8b","invoice-date":"Faktura dato","due-date":"Forfald","customer-number":"Kunde nummer","paid":"Betalt","username":"Brugernavn","password":"Kodeord","debtor-name":"Firma","created-date":"Oprettet","create-invoice":"Opret nyt faktura","debtor-info":"Debitor info","submission-date":"Indsendelse Dato","product":"Produkt","products":"Produkter","options":"Optioner","confirmed":"Bekr\u00e6ftet","ci-number":"CVR-nr.","homepage":"Hjemmeside","address":"Adresse","break-duration":"Pause varighed","check-in":"Tjekke ind","begin-break":"Begynd pause","end-break":"End pause","check-out":"Tjekke ud","client":"Kunde","client-info":"Kunde Info.","client-cvr":"Kunde CVR-nr.","post-number":"Postnummer","create-alias":"Opret alias","order":"Ordre Info","orders":"Ordrere","edit-order":"Rediger ordre","approve-order":"Godkende ordre","approved":"Godkend","approve":"Godkende","user":"Bruger","count":"Antal","duration":"Varighed","minutes":":minutes minutter","seconds":":seconds sekunder","name":"Navn","email":"Email","see-client":"Vis kunde","phone-screen":"Telefonens sk\u00e6rm","users-online":"Brugere p\u00e5 arbejde","order-status":"Ordrestatus","seller":"S\u00e6lger","assigned-to":"Tildelt til","setting":"Indstilling","settings":"Indstillingere","value":"Value","model":"Model","active":"Aktiv","order-types":"Ordretyper","order-type":"Ordretype","actions":"Aktioner","tasks":"Opgaver","task":"Opgave","title":"Titel","item-nr":"Item nr","start-date":"Startdato","description":"Beskrivelse","create-task":"Opret opgave","all-tasks":"Alle opgaver","time":"Tid","at-work-today":"P\u00e5 arbejde i dag","checked-out":"Tjekke ud","checked-in":"Tjekke ind","break":"Pause","are-logged-in":"Er logget ind","country":"Land","end-date":"Slutdato","next-optimization":"N\u00e6ste optimering","next-invoice":"N\u00e6ste faktura","adwords-id":"Adwords id","contract-number":"Kontrakt nummer : :number","contract-action":"Order H\u00e5ndtering","create-setting":"Opret indstillinger","edit-task":"Rediger opgave","item":"Item","number":"Nummer","start-time":"Starttid","end-time":"Sluttid","due-time":"Indtil den","update":"Opdater","order-nr":"Ordre nr: :number","fields":"Felter","remove-field":"Fjern field","add-field":"Opret field","display-name":"Visningsnavn","required":"Kr\u00e6ves","order-field":"Ordre felt","create-order-field":"Opret ordre felt","special":"Speciel","type":"Typen","users-screen":"Brugere sk\u00e6rm","select-user":"V\u00e6lg bruger","create":"Opret","contacts":"Kontakter","zip":"Postnummer","ci-numbers":"CI numre","user-roles":"Bruger roller","user-permissions":"Bruger tilladelser","id":"Id","add-to-role":"Tilf\u00f8j til rollen","remove-from-role":"Fjern fra rollen","allow-permission":"Tillade","forbid-permission":"Forbyd","controller":"Controller","method":"Method","allowed":"Allowed","status":"Status","confirmed-date":"Bekr\u00e6ftet dato","orders-for-approval":"Ordrer til godkendelse","order-date":"Ordre dato","order-confirmation-nr":"Ordrebekr\u00e6ftelse nummer","resend-order":"Gensende orden","print-order":"Udskriv ordre","order-confirmation":"Ordrebekr\u00e6ftelse","agreement-between":"Aftale mellem","and":"og","terms":"Bindingsperiode","monthly-price":"M\u00e5nedlig pris","total":"Antal","add-fields":"Tilf\u00f8je felter","create-field":"Oprette felter","save-comment":"Gem kommentar","dismiss":"Afskedige","renew":"Forny","edit-client":"Rediger kudne","edit-alias":"Rediger alias","create-contact":"Oprette kontakt","back":"Tilbage","clients":"Kunder","my-tasks":"Min opgaver","item-tasks":"Item opgaver","search":"S&oslash;g:","processing":"Henter...","length-menu":"Vis _MENU_ linjer","zero-records":"Ingen linjer matcher s&oslash;gningen","info":"Viser _START_ til _END_ af _TOTAL_ linjer","info-empty":"Viser 0 til 0 af 0 linjer","info-filtered":"(filtreret fra _MAX_ linjer)","info-post-fix":"","first":"F&oslash;rste","previous":"Forrige","next":"N&aelig;ste","last":"Sidste"},"dk.messages":{"dashboard-welcome":"Velkommen til dit personlige dashboard","invoice-was-paid":"Denne faktura blev udbetalt den","input-problems":"Der var nogle problemer med dit input.","check-in":"Velkommen til at arbejde. KICK ASS!!","end-break":"Velkommen tilbage til arbejdet. Break tid tilbage :timeLeft minutes.","check-out":"Du blev tjekket ud. KICK ASS!!","client-not-set":"Kunde er ikke indstillet!","phone-not-set":"Telefonnumer er ikke indstillet","email-not-set":"Email er ikke indstillet","approve-success":"Ordre blev godkendt","field-removed":"Field blev fjernet","order-field-value":"V\u00e6rdien kan bruges til at gruppere omr\u00e5der sammen. Hvis du \u00f8nsker, at banen for at blive forbundet med en anden, skal du bruge de samme v\u00e6rdier","field-is-special":"Dette felt er speciel. Hvis det er fyldt ordren skal godkendes af admin, f\u00f8r behandling.","client-created":"Kunde blev opret","role-was-added":"Rolle blev tilsat","role-was-removed":"Rolle blev fjernet","awaiting-confirmation":"Afventer bekr\u00e6ftelse","no-results":"Ingen resultater","update-was-successful":"Opdateret med succes","client-created-successfully":"Kunde blev oprettet"},"en.labels":{"login":"Log in","logout":"Log out","contract":"Contract","contracts":"Contracts","invoice":"Invoice","invoices":"Invoices","dashboard":"Dashboard","calendar":"Calendar","company-name":"Company name","company-address":"Company address","company-email":"Company email","company-phone":"Company phone","city":"City","phone":"Phone","contact-person":"Contact person","contact-phone":"Contact Number","contact-email":"Contact email","contact-info":"Contact info","salesman":"Salesman","last-call":"Last call","create-client":"Create client","edit-profile":"Edit profile","my-account":"My account","help":"Help","all-messages":"View all messages","comments":"Comments","comment":"Comment","save":"Save","edit":"Edit","set-paid":"Set Paid","make-creditnote":"Make Creditnote","invoice-number":"Invoice Number","pay-date":"Pay date","invoice-info":"Invoice info","edit-invoice":"Edit Invoice","sub-total":"Sub total","discount":"Discount","tax":"VAT","product-description":"Product description","quantity":"Quantity","unit-net-price":"Unit net price","total-net-amount":"Total net amount","invoice-date":"Invoice date","due-date":"Due date","customer-number":"Customer number","paid":"Paid","username":"Username","password":"Password","debtor-name":"Debtor name","created-date":"Created date","create-invoice":"Create new invoice","debtor-info":"Debtor info","submission-date":"Submission date","product":"Product","options":"Options","confirmed":"Confirmed","ci-number":"CI Number","homepage":"Homepage","address":"Address","break-duration":"Break duration","check-in":"Check in","begin-break":"Begin break","end-break":"End break","check-out":"Check out","client":"Client","post-number":"Post number","create-alias":"Create alias","order":"Order","orders":"Orders","edit-order":"Edit order","approve-order":"Approve order","approved":"Approved","approve":"Approve","user":"User","count":"Count","duration":"Duration","minutes":":minutes minutes","seconds":":seconds seconds","name":"Name","email":"Email","see-client":"See client","phone-screen":"Phone screen","users-online":"Users at work","order-status":"Order status","assigned-to":"Assigned to","setting":"Setting","settings":"Settings","value":"Value","model":"Model","active":"Active","order-types":"Order types","order-type":"Order type","actions":"Actions","tasks":"Tasks","task":"Task","title":"Title","item-nr":"Item nr","start-date":"Start date","description":"Description","create-task":"Create Task","all-tasks":"All tasks","time":"Time","at-work-today":"At work today","checked-out":"Checked Out","checked-in":"Checked In","break":"Break","are-logged-in":"Are logged in","seller":"Seller","country":"Country","end-date":"End date","next-optimization":"Next optimization","next-invoice":"Next invoice","adwords-id":"Adwords id","contract-number":"Contract number : :number","create-setting":"Create setting","edit-task":"Edit task","item":"Item","number":"Number","start-time":"Start time","end-time":"End time","due-time":"Due time","update":"Update","order-nr":"Order nr: :number","fields":"Fields","remove-field":"Remove field","add-field":"Add field","display-name":"Display name","required":"Required","order-field":"Order field","create-order-field":"Create order field","special":"Special","type":"Type","users-screen":"Users screen","select-user":"Select User","create":"Create","contacts":"Contacts","zip":"Zip","ci-numbers":"Ci numbers","user-roles":"User roles","user-permissions":"User permissions","id":"Id","add-to-role":"Add to role","remove-from-role":"Remove from role","allow-permission":"Allow","forbid-permission":"Forbid","controller":"Controller","method":"Method","allowed":"Allowed","status":"Status","confirmed-date":"Confirmed date","orders-for-approval":"Orders for approval","order-date":"Order date","order-confirmation-nr":"Order confirmation number","resend-order":"Resend order","print-order":"Print order","order-confirmation":"Order confirmation","agreement-between":"Agreement between","and":"and","terms":"Terms","monthly-price":"Monthly price","total":"Total","add-fields":"Add fields","create-fields":"Create a field","save-comment":"Save comment","dismiss":"Dismiss","renew":"Renew","edit-client":"Edit client","edit-alias":"Edit alias","create-contact":"Create contact","back":"Back","clients":"Clients","my-tasks":"My tasks","item-tasks":"Item tasks","search":"Search","processing":"Processing...","length-menu":"Show _MENU_ records","zero-records":"No matching records found","info":"Showing _START_ to _END_ of _TOTAL_ records","info-empty":"Showing 0 to 0 of 0 records","info-filtered":"(filtered from _MAX_ records)","info-post-fix":"","first":"First","previous":"Previous","next":"Next","last":"Last"},"en.messages":{"dashboard-welcome":"Welcome to your personal dashboard","invoice-was-paid":"This invoice was paid on","input-problems":"There were some problems with your input.","check-in":"Welcome to work. KICK-ASS!","end-break":"Welcome back to work. Break time left :timeLeft minutter","check-out":"You were checked out. KICK ASS!","client-not-set":"Client is not set","phone-not-set":"Phone number not set","email-not-set":"Email is not set","approve-success":"Order was approved.","field-removed":"Field was removed","order-field-value":"The value is used to group fields together. If you want the field to be associated with another one,use the same values","field-is-special":"This field is special. If it is filled the order needs to be approved by admin, before processing. ","client-created":"Client was created","role-was-added":"Role was added","role-was-removed":"Role was removed","awaiting-confirmation":"Awaiting confirmation","no-results":"No results","update-was-successful":"Updated successfully","client-created-successfully":"Client was created"},"en.pagination":{"previous":"&laquo; Previous","next":"Next &raquo;"},"en.passwords":{"password":"Passwords must be at least six characters and match the confirmation.","user":"We can't find a user with that e-mail address.","token":"This password reset token is invalid.","sent":"We have e-mailed your password reset link!","reset":"Your password has been reset!"},"en.validation":{"accepted":"The :attribute must be accepted.","active_url":"The :attribute is not a valid URL.","after":"The :attribute must be a date after :date.","alpha":"The :attribute may only contain letters.","alpha_dash":"The :attribute may only contain letters, numbers, and dashes.","alpha_num":"The :attribute may only contain letters and numbers.","array":"The :attribute must be an array.","before":"The :attribute must be a date before :date.","between":{"numeric":"The :attribute must be between :min and :max.","file":"The :attribute must be between :min and :max kilobytes.","string":"The :attribute must be between :min and :max characters.","array":"The :attribute must have between :min and :max items."},"boolean":"The :attribute field must be true or false.","confirmed":"The :attribute confirmation does not match.","date":"The :attribute is not a valid date.","date_format":"The :attribute does not match the format :format.","different":"The :attribute and :other must be different.","digits":"The :attribute must be :digits digits.","digits_between":"The :attribute must be between :min and :max digits.","email":"The :attribute must be a valid email address.","filled":"The :attribute field is required.","exists":"The selected :attribute is invalid.","image":"The :attribute must be an image.","in":"The selected :attribute is invalid.","integer":"The :attribute must be an integer.","ip":"The :attribute must be a valid IP address.","max":{"numeric":"The :attribute may not be greater than :max.","file":"The :attribute may not be greater than :max kilobytes.","string":"The :attribute may not be greater than :max characters.","array":"The :attribute may not have more than :max items."},"mimes":"The :attribute must be a file of type: :values.","min":{"numeric":"The :attribute must be at least :min.","file":"The :attribute must be at least :min kilobytes.","string":"The :attribute must be at least :min characters.","array":"The :attribute must have at least :min items."},"not_in":"The selected :attribute is invalid.","numeric":"The :attribute must be a number.","regex":"The :attribute format is invalid.","required":"The :attribute field is required.","required_if":"The :attribute field is required when :other is :value.","required_with":"The :attribute field is required when :values is present.","required_with_all":"The :attribute field is required when :values is present.","required_without":"The :attribute field is required when :values is not present.","required_without_all":"The :attribute field is required when none of :values are present.","same":"The :attribute and :other must match.","size":{"numeric":"The :attribute must be :size.","file":"The :attribute must be :size kilobytes.","string":"The :attribute must be :size characters.","array":"The :attribute must contain :size items."},"unique":"The :attribute has already been taken.","url":"The :attribute format is invalid.","timezone":"The :attribute must be a valid zone.","custom":{"attribute-name":{"rule-name":"custom-message"}},"attributes":[]}});
})(window);
