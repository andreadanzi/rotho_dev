-- danzi.tn@20131206 procedura cambio codici nazione
-- Stato fatturazione
update 
vtiger_accountbillads
set 
vtiger_accountbillads.bill_country = 'XY'
WHERE vtiger_accountbillads.bill_country = 'ZZ'

-- Stato spedizione
update 
vtiger_accountshipads
set 
vtiger_accountshipads.ship_country = 'XY'
WHERE vtiger_accountshipads.ship_country  = 'ZZ'

-- Leads
update 
vtiger_leadaddress
set 
vtiger_leadaddress.country = 'XY'
WHERE vtiger_leadaddress.country  = 'ZZ'

-- Contatti spedizione
update 
vtiger_contactaddress
set 
vtiger_contactaddress.mailingcountry = 'XY'
WHERE vtiger_contactaddress.mailingcountry = 'ZZ'

-- Contatti altro stato
update 
vtiger_contactaddress
set 
vtiger_contactaddress.othercountry = 'XY'
WHERE vtiger_contactaddress.othercountry = 'ZZ'

-- Vendors
update 
vtiger_vendor
set 
vtiger_vendor.country = 'XY'
WHERE vtiger_vendor.country  = 'ZZ'

-- Preventivi fatturazione
update 
vtiger_quotesbillads
set 
vtiger_quotesbillads.bill_country  = 'XY'
WHERE vtiger_quotesbillads.bill_country = 'ZZ'

-- Preventivi spedizione
update 
vtiger_quotesshipads
set 
vtiger_quotesshipads.ship_country  = 'XY'
WHERE vtiger_quotesshipads.ship_country  = 'ZZ'

-- potentials
update
vtiger_pobillads
set 
vtiger_pobillads.bill_country   = 'XY'
WHERE vtiger_pobillads.bill_country = 'ZZ'

update 
vtiger_poshipads
set 
vtiger_poshipads.ship_country   = 'XY'
WHERE vtiger_poshipads.ship_country= 'ZZ'

update 
vtiger_sobillads
set 
vtiger_sobillads.bill_country   = 'XY'
WHERE vtiger_sobillads.bill_country  = 'ZZ'

update 
vtiger_soshipads
set 
vtiger_soshipads.ship_country   = 'XY'
WHERE vtiger_soshipads.ship_country  = 'ZZ'

update 
vtiger_invoicebillads
set 
vtiger_invoicebillads.bill_country   = 'XY'
WHERE vtiger_invoicebillads.bill_country  = 'ZZ'

update 
vtiger_invoiceshipads
set 
vtiger_invoiceshipads.ship_country    = 'XY'
WHERE vtiger_invoiceshipads.ship_country = 'ZZ'

update 
vtiger_users
set 
vtiger_users.address_country   = 'XY'
WHERE vtiger_users.address_country  = 'ZZ'

update 
vtiger_consulenza
set 
vtiger_consulenza.consulenzacountry    = 'XY'
WHERE vtiger_consulenza.consulenzacountry = 'ZZ'

update 
vtiger_consulenzacountry
set 
vtiger_consulenzacountry.consulenzacountry    = 'XY'
WHERE vtiger_consulenzacountry.consulenzacountry = 'ZZ'