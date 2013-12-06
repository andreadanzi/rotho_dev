-- danzi.tn@20131206 procedura cambio codici nazione
SELECT DISTINCT TT.old_country FROM (
select distinct vtiger_accountbillads.bill_country AS old_country
from vtiger_accountbillads
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_accountbillads.accountaddressid and deleted =0


UNION

select distinct vtiger_accountshipads.ship_country AS old_country
from vtiger_accountshipads
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_accountshipads.accountaddressid and deleted =0

UNION

select distinct vtiger_leadaddress.country AS old_country
from vtiger_leadaddress
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_leadaddress.leadaddressid and deleted =0

UNION

select distinct vtiger_contactaddress.mailingcountry AS old_country
from vtiger_contactaddress
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_contactaddress.contactaddressid and deleted =0

UNION

select distinct vtiger_contactaddress.othercountry AS old_country
from vtiger_contactaddress
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_contactaddress.contactaddressid and deleted =0

UNION

select distinct vtiger_vendor.country AS old_country
from vtiger_vendor
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_vendor.vendorid and deleted =0

UNION

select distinct vtiger_quotesbillads.bill_country AS old_country
from vtiger_quotesbillads
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_quotesbillads.quotebilladdressid and deleted =0

UNION

select distinct vtiger_quotesshipads.ship_country AS old_country
from vtiger_quotesshipads
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_quotesshipads.quoteshipaddressid and deleted =0

UNION

select distinct vtiger_pobillads.bill_country AS old_country
from vtiger_pobillads
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_pobillads.pobilladdressid and deleted =0

UNION

select distinct vtiger_poshipads.ship_country AS old_country
from vtiger_poshipads
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_poshipads.poshipaddressid and deleted =0

UNION

select distinct vtiger_sobillads.bill_country AS old_country
from vtiger_sobillads
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_sobillads.sobilladdressid and deleted =0

UNION


select distinct vtiger_soshipads.ship_country AS old_country
from vtiger_soshipads
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_soshipads.soshipaddressid and deleted =0

UNION

select distinct vtiger_invoicebillads.bill_country AS old_country
from vtiger_invoicebillads
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_invoicebillads.invoicebilladdressid and deleted =0

UNION

select distinct vtiger_invoiceshipads.ship_country AS old_country
from vtiger_invoiceshipads
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_invoiceshipads.invoiceshipaddressid and deleted =0

UNION

select distinct vtiger_users.address_country AS old_country
from vtiger_users
where deleted =0

UNION

select distinct vtiger_consulenza.consulenzacountry AS old_country
from vtiger_consulenza
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_consulenza.consulenzaid and deleted =0
) AS TT
ORDER BY TT.old_country ASC