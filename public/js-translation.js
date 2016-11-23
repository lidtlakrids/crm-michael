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
    Lang.setMessages({"dk.labels":{"search":"S&oslash;g:","processing":"Henter...","length-menu":"Vis _MENU_ linjer","zero-records":"Ingen linjer matcher s&oslash;gningen","info":"Viser _START_ til _END_ af _TOTAL_ linjer","info-empty":"Viser 0 til 0 af 0 linjer","info-filtered":"(filtreret fra _MAX_ linjer)","info-post-fix":"","first":"F&oslash;rste","previous":"Forrige","next":"N&aelig;ste","last":"Sidste","login":"Log ind","logout":"Log ud","contract":"Kontrakt","contracts":"Kontrakter","invoice":"Faktura","invoices":"Fakturaer","dashboard":"Dashboard","calendar":"Kalender","company-name":"Navn","company-address":"Addresse","company-email":"e-mail","company-phone":"Telefon","city":"By","phone":"Telefonnummer","contact-person":"Kontaktperson","contact-phone":"Telefonnummer","contact-email":"Kontakt email","contact-birthday":"F\u00f8dselsdag","contact-info":"Kontakt info","salesman":"S\u00e6lger","last-call":"Sidste opkald","create-client":"Opret kunde","edit-profile":"Rediger profil","my-account":"Min konto","help":"Hj\u00e6lp","all-messages":"Se alle beskeder","comments":"\u041aommentarer","comment":"\u041aommentar","save":"Gem","edit":"Rediger","set-paid":"S\u00e6t betalt","make-creditnote":"Lav Kreditnota","invoice-number":"Faktura","pay-date":"Betalt dato","invoice-info":"Faktura info","edit-invoice":"Rediger faktura","sub-total":"Subtal","discount":"Rabat","tax":"Moms","product-description":"Produkt","quantity":"Antal","unit-net-price":"Pris","total-net-amount":"Nettobel\u00f8b","invoice-date":"Faktura dato","due-date":"Forfald","customer-number":"Kunde nummer","paid":"Betalt","username":"Brugernavn","password":"Kodeord","debtor-name":"Firma","created-date":"Oprettet","create-invoice":"Opret nyt faktura","debtor-info":"Debitor info","submission-date":"Indsendelse Dato","product":"Produkt","products":"Produkter","options":"Optioner","confirmed":"Bekr\u00e6ftet","ci-number":"CVR-nr.","homepage":"Hjemmeside","address":"Adresse","break-duration":"Pause varighed","check-in":"Tjekke ind","begin-break":"Begynd pause","end-break":"End pause","check-out":"Tjekke ud","client":"Kunde","client-info":"Kunde Info.","client-cvr":"Kunde CVR-nr.","post-number":"Postnummer","create-alias":"Opret alias","order":"Ordre Info","orders":"Ordrere","edit-order":"Rediger ordre","approve-order":"Godkende ordre","approved":"Godkend","approve":"Godkende","user":"Bruger","count":"Antal","duration":"Varighed","minutes":":minutes minutter","seconds":":seconds sekunder","name":"Navn","email":"Email","see-client":"Vis kunde","phone-screen":"Telefonens sk\u00e6rm","users-online":"Brugere p\u00e5 arbejde","order-status":"Ordrestatus","seller":"S\u00e6lger","assigned-to":"Tildelt til","setting":"Indstilling","settings":"Indstillingere","value":"Value","model":"Model","active":"Aktiv","order-types":"Ordretyper","order-type":"Ordretype","actions":"Aktioner","tasks":"Opgaver","task":"Opgave","title":"Titel","item-nr":"Item nr","start-date":"Startdato","description":"Beskrivelse","create-task":"Opret opgave","all-tasks":"Alle opgaver","time":"Tid","at-work-today":"P\u00e5 arbejde i dag","checked-out":"Tjekke ud","checked-in":"Tjekke ind","break":"Pause","are-logged-in":"Er logget ind","country":"Land","countries":"Lande","end-date":"Slutdato","next-optimization":"N\u00e6ste optimering","next-invoice":"N\u00e6ste faktura","adwords-id":"Adwords id","contract-number":"Kontrakt nummer","contract-action":"Order H\u00e5ndtering","create-setting":"Opret indstillinger","edit-task":"Rediger opgave","item":"Item","number":"Nummer","start-time":"Starttid","end-time":"Sluttid","due-time":"Indtil den","update":"Opdater","order-nr":"Ordre nr: :number","fields":"Felter","remove-field":"Fjern field","add-field":"Opret field","display-name":"Visningsnavn","required":"Kr\u00e6ves","order-field":"Ordre felt","create-order-field":"Opret ordre felt","special":"Speciel","type":"Typen","users-screen":"Brugere sk\u00e6rm","select-user":"V\u00e6lg bruger","create":"Opret","contacts":"Kontakter","zip":"Postnummer","ci-numbers":"CI numre","user-roles":"Bruger roller","user-permissions":"Bruger tilladelser","id":"Id","add-to-role":"Tilf\u00f8j til rollen","remove-from-role":"Fjern fra rollen","allow-permission":"Tillade","forbid-permission":"Forbyd","controller":"Controller","method":"Method","allowed":"Allowed","status":"Status","confirmed-date":"Bekr\u00e6ftet dato","orders-for-approval":"Ordrer til godkendelse","order-date":"Ordre dato","order-confirmation-nr":"Ordrebekr\u00e6ftelse nummer","resend-order":"Gensende orden","print-order":"Udskriv ordre","order-confirmation":"Ordrebekr\u00e6ftelse","agreement-between":"Aftale mellem","and":"og","terms":"Bindingsperiode","monthly-price":"M\u00e5nedlig pris","total":"Antal","add-fields":"Tilf\u00f8je felter","create-field":"Oprette felter","save-comment":"Gem kommentar","dismiss":"Afskedige","renew":"Forny","edit-client":"Rediger kudne","edit-alias":"Rediger alias","create-contact":"Oprette kontakt","back":"Tilbage","clients":"Kunder","my-tasks":"Min opgaver","item-tasks":"Item opgaver","roles":"Roller","create-user":"Opret bruger","users":"Brugere","user-info":"Bruger info","created-by":"Oprettet af","completed":"Afsluttet","remove":"Slet","edit-field":"Rediger felt","create-order-type":"Opret ordretype","edit-order-type":"Rediger ordretype","update-order-type":"Opdatering ordretype","all-leads":"Alle Leads","lead":"Lead","leads":"Leads","create-country":"Opret land","see-lead":"Vis Lead","country-code":"Land kode","phone-code":"Telefon kode","vat":"VAT","drafts":"Kladder","draft":"Kladde","edit-user":"Rediger bruger","draft-lines":"Kladde linjer","draft-line":"Kladde line","see-draft":"Vis Kladde","add-to-draft":"F\u00f8je til kladde","edit-contract":"Rediger kontrakt","see-contract":"Vis kontrakt","create-draft":"Opret kladde","assign-contracts":"Tildel Kontrakter","assign":"Tildel","sub-tasks":"Underopgaver","create-sub-tasks":"Opret underopgaver","select-template":"V\u00e6lg skabelon","complete-task":"Afslutte opgave","delete":"Slet","teams":"Holdene","see-team":"Vis hold","team":"Hold","client-manager":"Kundemanager","select":"V\u00e6lg","sub-contracts":"Underkontrakter","users-in-team":"Brugere i hold","create-team":"Opret hold","edit-team":"Rediger hold","create-lead":"Opret Lead","enter-company-name":"Indtast Firma Navn","enter-homepage":"Indtast Hjemmeside","enter-phone":"Indtast Telefonnummer","enter-city":"Indast By","enter-name":"Indast navn","enter-title":"Indast titel","enter-email":"Indast email","source":"Kilde","edit-lead":"Rediger Lead","birthdate":"F\u00f8dselsdag","edit-contact":"Rediger kontakt","priority":"Prioritet","payment-terms":"Betalingsbetingelser","starting":"Starter","optimize":"Optimerer","all":"Alle","role-permissions":"Roller tilladelser","create-order":"Opret ordre","denied":"Afvist","edit-product":"Rediger produkt","cost-price":"Kostpris","creation-fee":"Oprettelse","sale-price":"Salgspris","optimize-interval":"Optimer interval","commission":"Provision","product-type":"Typen","product-types":"Typer","edit-type":"Rediger typen","create-type":"Opret typen","product-department":"Afdeling","product-departments":"Afdelinger","recommended-price":"Anbefales pris","department":"Afdeling","create-department":"Opret afdeling","edit-department":"Rediger afdeling","template":"Skabelon","templates":"Skabeloner","task-template":"Opgave skabelon","task-templates":"Opgave skabeloner","create-product":"Opret produkt","yes":"Ja","no":"Nej","edit-template":"Rediger skabelon","create-task-template":"Opret opgave skabelon","edit-task-template":"Rediger opgave skabelon","select-team":"V\u00e6lg hold","create-role":"Opret rolle","default":"Misligholdelse","nearest-relatives":"N\u00e6rmeste p\u00e5r\u00f8rende","error":"Fejl","titles":"Titler","create-title":"Opret titel","edit-title":"Rediger titel","notifications":"Notifikationer","notification":"Notifikation","unit-price":"Enhedspris","our-reference":"Vores ref.","client-number":"Kunde nummer","notify-creator":"Underrette skaberen","information":"Information","appointments":"Aftaler","progress":"Fremskridt","timeline":"Forl\u00f8b","files":"Filer","waiting-approval":"Venter p\u00e5 godkendelse","contract-actions":"Kotrakt aktioner","approved-by":"Godkendt af","start":"Starte","show-hidden-comments":"Vise skjulte kommentarer","production":"Produktion","main-contact":"Prim\u00e6re kontakt","call-main-contact":"Ring til prim\u00e6re kontakt","go-to-adwords-account":"G\u00e5 til Adwords konto","start-optimize":"Start Optimering","add-comment":"Tilf\u00f8j kommentar","what-was-optimized":"Hvad har du optimere?","worked-on":"Arbejdet p\u00e5","create-notification":"Opret notifikation","client-cvrs":"Kunde CVR-numre","mark-all-seen":"Marker alle som set","success":"Success","payment-status":"Betalingsstatus","mark-as-unseen":"Mark som ikke set","see-all-notifications":"Vis alle notifikationer","stopped":"Holdt op","keywords":"S\u00f8geord","end-optimize":"Slut optimere","contact-persons":"Kontaktpersoner","owner":"Ejer","sales":"Salg","management":"Ledelse","accounting":"Regnskab","company-information":"Virksomhedsoplysninger","unknown":"Ukendt","upload":"Upload","drop-here-to-upload":"Slippe filer her, for at uploade","appointment":"Aftale","search-user":"S\u00f8g bruger","for-who":"For hvem","appointment-time":"Aftale tid","introduction-call":"Introduktion opkald","follow-up-call":"F\u00f8lgende opkald","closing-call":"Lukning opkald","category":"","create-appointment":"","change-password":"","confirm-password":"","product-package":"","product-packages":"","assign_leads":"","leads-were-moved":"","notify-attendees":"","attendees":"","add-attendee":"","add         ":"","already-attendee":"","create-product-package":"","max-budget":"","add-ons-count":"","product-info":"","package-info":"","allowed-products":"","max-add-ons":"","administration-fee":"","class":"Class","potential":"Potential","add-product":"","add-package":"","runlength":"Bindingsperiode","not-completed":"","move-leads":"","select-all":""},"dk.messages":{"dashboard-welcome":"Velkommen til dit personlige dashboard","invoice-was-paid":"Denne faktura blev udbetalt den","input-problems":"Der var nogle problemer med dit input.","check-in":"Velkommen til at arbejde. KICK ASS!!","end-break":"Velkommen tilbage til arbejdet. Break tid tilbage :timeLeft minutes.","check-out":"Du blev tjekket ud. KICK ASS!!","client-not-set":"Kunde er ikke indstillet!","phone-not-set":"Telefonnumer er ikke indstillet","email-not-set":"Email er ikke indstillet","approve-success":"Ordre blev godkendt","field-removed":"Field blev fjernet","order-field-value":"V\u00e6rdien kan bruges til at gruppere omr\u00e5der sammen. Hvis du \u00f8nsker, at banen for at blive forbundet med en anden, skal du bruge de samme v\u00e6rdier","field-is-special":"Dette felt er speciel. Hvis det er fyldt ordren skal godkendes af admin, f\u00f8r behandling.","client-created":"Kunde blev opret","role-was-added":"Rolle blev tilsat","role-was-removed":"Rolle blev fjernet","awaiting-confirmation":"Afventer bekr\u00e6ftelse","no-results":"Ingen resultater","update-was-successful":"Opdateret med succes","client-created-successfully":"Kunde blev oprettet","order-not-set":"Ingen orden er forbundet","field-is-not-active":"Field er ikke aktiv","contract-is-addon":"Denne kontrakt er Add-on til : :parent","this-contract-is-suspended":"Denne kontrakt er suspended","this-contract-is-standby":"Denne kontrakt er standby","this-contract-is-active":"Denne kontrakt er aktiv","waiting-approval":"Venter p\u00e5 godkendelse","start-without-comment":"Har du lyst til at forlade kommentar tom?","comment-was-hid":"Kommentar var skjult","new-notifications":"Du har :count nye notifikation(er)","invalid-email":"Indtast venligst en gyldig e-mail"},"en.labels":{"search":"Search","processing":"Processing...","length-menu":"Show _MENU_ records","zero-records":"No matching records found","info":"Showing _START_ to _END_ of _TOTAL_ records","info-empty":"Showing 0 to 0 of 0 records","info-filtered":"(filtered from _MAX_ records)","info-post-fix":"","first":"First","previous":"Previous","next":"Next","last":"Last","login":"Log in","logout":"Log out","contract":"Contract","contracts":"Contracts","invoice":"Invoice","invoices":"Invoices","dashboard":"Dashboard","calendar":"Calendar","company-name":"Company name","company-address":"Company address","company-email":"Company email","company-phone":"Company phone","city":"City","phone":"Phone","contact-person":"Contact person","contact-phone":"Contact Number","contact-email":"Contact email","contact-info":"Contact info","salesman":"Salesman","last-call":"Last call","create-client":"Create client","edit-profile":"Edit profile","my-account":"My account","help":"Help","all-messages":"View all messages","comments":"Comments","comment":"Comment","save":"Save","edit":"Edit","set-paid":"Set Paid","make-creditnote":"Make Creditnote","invoice-number":"Invoice Number","pay-date":"Pay date","invoice-info":"Invoice info","edit-invoice":"Edit Invoice","sub-total":"Sub total","discount":"Discount","tax":"VAT","product-description":"Product description","quantity":"Quantity","unit-net-price":"Unit net price","total-net-amount":"Total net amount","invoice-date":"Invoice date","due-date":"Due date","customer-number":"Customer number","paid":"Paid","username":"Username","password":"Password","debtor-name":"Debtor name","created-date":"Created date","create-invoice":"Create new invoice","debtor-info":"Debtor info","submission-date":"Submission date","product":"Product","products":"Products","options":"Options","confirmed":"Confirmed","ci-number":"CI Number","homepage":"Homepage","address":"Address","break-duration":"Break duration","check-in":"Check in","begin-break":"Begin break","end-break":"End break","check-out":"Check out","client":"Client","post-number":"Post number","create-alias":"Create alias","order":"Order","orders":"Orders","edit-order":"Edit order","approve-order":"Approve order","approved":"Approved","approve":"Approve","user":"User","count":"Count","duration":"Duration","minutes":":minutes minutes","seconds":":seconds seconds","name":"Name","email":"Email","see-client":"See client","phone-screen":"Phone screen","users-online":"Users at work","order-status":"Order status","assigned-to":"Assigned to","setting":"Setting","settings":"Settings","value":"Value","model":"Model","active":"Active","order-types":"Order types","order-type":"Order type","actions":"Actions","tasks":"Tasks","task":"Task","title":"Title","item-nr":"Item nr","start-date":"Start date","description":"Description","create-task":"Create Task","all-tasks":"All tasks","time":"Time","at-work-today":"At work today","checked-out":"Checked Out","checked-in":"Checked In","break":"Break","are-logged-in":"Are logged in","seller":"Seller","country":"Country","countries":"Countries","end-date":"End date","next-optimization":"Next optimization","next-invoice":"Next invoice","adwords-id":"Adwords id","contract-number":"Contract number","create-setting":"Create setting","edit-task":"Edit task","item":"Item","number":"Number","start-time":"Start time","end-time":"End time","due-time":"Due time","update":"Update","order-nr":"Order nr: :number","fields":"Fields","remove-field":"Remove field","add-field":"Add field","display-name":"Display name","required":"Required","order-field":"Order field","create-order-field":"Create order field","special":"Special","type":"Type","users-screen":"Users screen","select-user":"Select User","create":"Create","contacts":"Contacts","zip":"Zip","ci-numbers":"CI numbers","user-roles":"User roles","user-permissions":"User permissions","id":"Id","add-to-role":"Add to role","remove-from-role":"Remove from role","allow-permission":"Allow","forbid-permission":"Forbid","controller":"Controller","method":"Method","allowed":"Allowed","status":"Status","confirmed-date":"Confirmed date","orders-for-approval":"Orders for approval","order-date":"Order date","order-confirmation-nr":"Order confirmation number","resend-order":"Resend order","print-order":"Print order","order-confirmation":"Order confirmation","agreement-between":"Agreement between","and":"and","terms":"Terms","monthly-price":"Monthly price","total":"Total","add-fields":"Add fields","create-fields":"Create a field","save-comment":"Save comment","dismiss":"Dismiss","renew":"Renew","edit-client":"Edit client","edit-alias":"Edit alias","create-contact":"Create contact","back":"Back","clients":"Clients","my-tasks":"My tasks","item-tasks":"Item tasks","roles":"Roles","create-user":"Create user","users":"Users","user-info":"User info","created-by":"Created by","completed":"Completed","remove":"Remove","edit-field":"Edit field","create-order-type":"Create order type","edit-order-type":"Edit order type","update-order-type":"Update order type","all-leads":"All Leads","lead":"Lead","leads":"Leads","create-country":"Create country","see-lead":"See Lead","country-code":"Country Code","phone-code":"Phone Code","vat":"VAT","drafts":"Drafts","draft":"Draft","edit-user":"Edit user","draft-lines":"Draft lines","draft-line":"Draft line","see-draft":"See Draft","add-to-draft":"Add to draft","edit-contract":"Edit contract","see-contract":"See contract","create-draft":"Create draft","assign-contracts":"Assign Contracts","assign":"Assign","sub-tasks":"Sub tasks","create-sub-tasks":"Create sub task","select-template":"Select template","complete-task":"Complete task","delete":"Delete","teams":"Teams","see-team":"See team","team":"Team","client-manager":"Client Manager","select":"Select","sub-contracts":"Sub contracts","users-in-team":"Users in team","create-team":"Create team","edit-team":"Edit team","create-lead":"Create Lead","enter-company-name":"Enter company name","enter-homepage":"Enter homepage","enter-phone":"Enter phone","enter-city":"Enter city","enter-name":"Enter name","enter-title":"Enter title","enter-email":"Enter email","source":"Source","edit-lead":"Edit Lead","birthdate":"Birth date","edit-contact":"Edit contact","priority":"Priority","payment-terms":"Payment terms","starting":"Starting","optimize":"Optimize","all":"All","role-permissions":"Role permissions","create-order":"Create order","denied":"Denied","edit-product":"Edit product","creation-fee":"Creation fee","cost-price":"Cost price","sale-price":"Sale price","optimize-interval":"Optimize interval","commission":"Commission","product-type":"Type","product-types":"Types","edit-type":"Edit type","create-type":"Create type","product-department":"Department","product-departments":"Departments","recommended-price":"Recommended price","department":"Department","create-department":"Create department","edit-department":"Edit department","template":"Template","templates":"Templates","task-template":"Task template","task-templates":"Task templates","create-product":"Create product","yes":"Yes","no":"No","edit-template":"Edit template","create-task-template":"Create task template","edit-task-template":"Edit task template","select-team":"Select team","create-role":"Create role","default":"Default","nearest-relatives":"Nearest relatives","error":"Error","titles":"Titles","create-title":"Create title","edit-title":"Edit title","notifications":"Notifications","notification":"Notification","unit-price":"Unit price","our-reference":"Our reference","client-number":"Client number","notify-creator":"Notify creator","information":"Information","appointments":"Appointments","progress":"Progress","timeline":"Timeline","files":"Files","waiting-approval":"Waiting for approval","contract-actions":"Contract actions","approved-by":"Approved by","start":"Start","show-hidden-comments":"Show hidden comments","production":"Production","main-contact":"Main contact","call-main-contact":"Call the main contact","go-to-adwords-account":"Go to AdWords account","start-optimize":"Start Optimize","add-comment":"Add comment","what-was-optimized":"What did you optimize?","worked-on":"Worked on","create-notification":"Create notification","client-cvrs":"Client CI numbers","mark-all-seen":"Mark all Seen","create-order-for":"Create order for","success":"Success","payment-status":"Payment status","mark-as-unseen":"Mark as unseen","see-all-notifications":"See all notifications","stopped":"Stopped","keywords":"Keywords","end-optimize":"End optimization","contact-persons":"Contact persons","owner":"Owner","sales":"Sales","management":"Management","accounting":"Accounting","company-information":"Company info.","unknown":"Unknown","upload":"Upload","drop-here-to-upload":"Drop files here, to upload","appointment":"Appointment","search-user":"Search user","for-who":"For who","appointment-time":"Appointment time","introduction-call":"Introduction call","follow-up-call":"Follow-up call","closing-call":"Closing call","category":"Category","create-appointment":"Create appointment","change-password":"Change password","confirm-password":"Confirm password","product-package":"Product package","edit-product-package":"Edit Package","product-packages":"Product packages","assign_leads":"Assign Leads","leads-were-moved":"Leads were moved","notify-attendees":"Notify attendees?","attendees":"Attendees","add-attendee":"Add Attendee","add":"Add","already-attendee":"Already attending","create-product-package":"Create product package","max-budget":"Max budget","add-ons-count":"Add ons","product-info":"Product info.","package-info":"Package info.","allowed-products":"Allowed products","max-add-ons":"Max add-ons","administration-fee":"Administration Fee","class":"Class","potential":"Potential","add-to-order":"Add to Order","admin":"Admin","enter-comment":"Enter your comment here","add-product":"Add product","add-package":"Add package","runlength":"Run length","not-completed":"Not completed","move-leads":"Move Leads","select-all":"Select all","deselect-all":"Unselect all"},"en.messages":{"dashboard-welcome":"Welcome to your personal dashboard","invoice-was-paid":"This invoice was paid on","input-problems":"There were some problems with your input.","check-in":"Welcome to work. KICK-ASS!","end-break":"Welcome back to work. Break time left :timeLeft minutter","check-out":"You were checked out. KICK ASS!","client-not-set":"Client is not set","phone-not-set":"Phone number not set","email-not-set":"Email is not set","approve-success":"Order was approved.","field-removed":"Field was removed","order-field-value":"The value is used to group fields together. If you want the field to be associated with another one,use the same values","field-is-special":"This field is special. If it is filled the order needs to be approved by admin, before processing. ","client-created":"Client was created","role-was-added":"Role was added","role-was-removed":"Role was removed","awaiting-confirmation":"Awaiting confirmation","no-results":"No results","update-was-successful":"Updated successfully","client-created-successfully":"Client was created","order-not-set":"No order is associated","field-is-not-active":"Field is not active","contract-is-addon":"This contract is Add-on to : :parent","this-contract-is-suspended":"This contract is suspended","this-contract-is-standby":"This contract is standby","this-contract-is-active":"This contract is active","waiting-approval":"Waiting for approval","start-without-comment":"Do you want to leave the comment empty?","comment-was-hid":"Comment was hid","new-notifications":"You have :count new notification(s)","invalid-email":"Please, enter a valid Email"},"en.pagination":{"previous":"&laquo; Previous","next":"Next &raquo;"},"en.passwords":{"password":"Passwords must be at least six characters and match the confirmation.","user":"We can't find a user with that e-mail address.","token":"This password reset token is invalid.","sent":"We have e-mailed your password reset link!","reset":"Your password has been reset!"},"en.validation":{"accepted":"The :attribute must be accepted.","active_url":"The :attribute is not a valid URL.","after":"The :attribute must be a date after :date.","alpha":"The :attribute may only contain letters.","alpha_dash":"The :attribute may only contain letters, numbers, and dashes.","alpha_num":"The :attribute may only contain letters and numbers.","array":"The :attribute must be an array.","before":"The :attribute must be a date before :date.","between":{"numeric":"The :attribute must be between :min and :max.","file":"The :attribute must be between :min and :max kilobytes.","string":"The :attribute must be between :min and :max characters.","array":"The :attribute must have between :min and :max items."},"boolean":"The :attribute field must be true or false.","confirmed":"The :attribute confirmation does not match.","date":"The :attribute is not a valid date.","date_format":"The :attribute does not match the format :format.","different":"The :attribute and :other must be different.","digits":"The :attribute must be :digits digits.","digits_between":"The :attribute must be between :min and :max digits.","email":"The :attribute must be a valid email address.","filled":"The :attribute field is required.","exists":"The selected :attribute is invalid.","image":"The :attribute must be an image.","in":"The selected :attribute is invalid.","integer":"The :attribute must be an integer.","ip":"The :attribute must be a valid IP address.","max":{"numeric":"The :attribute may not be greater than :max.","file":"The :attribute may not be greater than :max kilobytes.","string":"The :attribute may not be greater than :max characters.","array":"The :attribute may not have more than :max items."},"mimes":"The :attribute must be a file of type: :values.","min":{"numeric":"The :attribute must be at least :min.","file":"The :attribute must be at least :min kilobytes.","string":"The :attribute must be at least :min characters.","array":"The :attribute must have at least :min items."},"not_in":"The selected :attribute is invalid.","numeric":"The :attribute must be a number.","regex":"The :attribute format is invalid.","required":"The :attribute field is required.","required_if":"The :attribute field is required when :other is :value.","required_with":"The :attribute field is required when :values is present.","required_with_all":"The :attribute field is required when :values is present.","required_without":"The :attribute field is required when :values is not present.","required_without_all":"The :attribute field is required when none of :values are present.","same":"The :attribute and :other must match.","size":{"numeric":"The :attribute must be :size.","file":"The :attribute must be :size kilobytes.","string":"The :attribute must be :size characters.","array":"The :attribute must contain :size items."},"unique":"The :attribute has already been taken.","url":"The :attribute format is invalid.","timezone":"The :attribute must be a valid zone.","custom":{"attribute-name":{"rule-name":"custom-message"}},"attributes":[]}});
})(window);
