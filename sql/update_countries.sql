-- danzi.tn@20131206 procedura cambio codici nazione
-- Stato fatturazione
update 
vtiger_accountbillads
set 
vtiger_accountbillads.bill_country = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_accountbillads on vtiger_accountbillads.bill_country = temp_country_codes.old_country;

-- Stato spedizione
update 
vtiger_accountshipads
set 
vtiger_accountshipads.ship_country = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_accountshipads on vtiger_accountshipads.ship_country = temp_country_codes.old_country;

-- Leads
update 
vtiger_leadaddress
set 
vtiger_leadaddress.country = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_leadaddress on vtiger_leadaddress.country = temp_country_codes.old_country;

-- Contatti spedizione
update 
vtiger_contactaddress
set 
vtiger_contactaddress.mailingcountry = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_contactaddress on vtiger_contactaddress.mailingcountry = temp_country_codes.old_country;

-- Contatti altro stato
update 
vtiger_contactaddress
set 
vtiger_contactaddress.othercountry = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_contactaddress on vtiger_contactaddress.othercountry = temp_country_codes.old_country;

-- Vendors
update 
vtiger_vendor
set 
vtiger_vendor.country = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_vendor on vtiger_vendor.country = temp_country_codes.old_country;

-- Preventivi fatturazione
update 
vtiger_quotesbillads
set 
vtiger_quotesbillads.bill_country  = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_quotesbillads on vtiger_quotesbillads.bill_country  = temp_country_codes.old_country;

-- Preventivi spedizione
update 
vtiger_quotesshipads
set 
vtiger_quotesshipads.ship_country  = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_quotesshipads on vtiger_quotesshipads.ship_country  = temp_country_codes.old_country;

-- potentials
update
vtiger_pobillads
set 
vtiger_pobillads.bill_country   = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_pobillads on vtiger_pobillads.bill_country   = temp_country_codes.old_country;

update 
vtiger_poshipads
set 
vtiger_poshipads.ship_country   = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_poshipads on vtiger_poshipads.ship_country   = temp_country_codes.old_country;

update 
vtiger_sobillads
set 
vtiger_sobillads.bill_country   = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_sobillads on vtiger_sobillads.bill_country   = temp_country_codes.old_country;

update 
vtiger_soshipads
set 
vtiger_soshipads.ship_country   = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_soshipads on vtiger_soshipads.ship_country    = temp_country_codes.old_country;

update 
vtiger_invoicebillads
set 
vtiger_invoicebillads.bill_country   = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_invoicebillads on vtiger_invoicebillads.bill_country   = temp_country_codes.old_country;

update 
vtiger_invoiceshipads
set 
vtiger_invoiceshipads.ship_country    = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_invoiceshipads on   vtiger_invoiceshipads.ship_country   = temp_country_codes.old_country;

update 
vtiger_users
set 
vtiger_users.address_country   = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_users on vtiger_users.address_country    = temp_country_codes.old_country;

update 
vtiger_consulenza
set 
vtiger_consulenza.consulenzacountry    = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_consulenza on  vtiger_consulenza.consulenzacountry    = temp_country_codes.old_country;

update 
vtiger_consulenzacountry
set 
vtiger_consulenzacountry.consulenzacountry    = temp_country_codes.new_country
from temp_country_codes
JOIN vtiger_consulenzacountry on  vtiger_consulenzacountry.consulenzacountry    = temp_country_codes.old_country;