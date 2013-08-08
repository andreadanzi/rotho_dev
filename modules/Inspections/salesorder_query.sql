select 
vte_inspections.inspectionsid,
'Revisione Periodica' AS inspection_name,
vte_salesorder.accountid AS accountid,
vte_salesorder.salesorderid AS salesorderid,
vte_crmentity.smownerid AS smownerid,
case when vte_products.productid is not null then vte_products.productid else vte_service.serviceid end as productid, 
case when vte_products.productid is not null then vte_products.productname else vte_service.servicename end as product_description, 
case when vte_productcf.productid is not null then vte_productcf.cf_796 else 'ND' end as product_category, -- cf_803
case when vte_products.productid is not null then vte_products.vendor_id else 'ND' end as vendor_id, 
vte_salesorder.duedate AS due_date, -- Verificare qual'è la data dell'Ordine di vendita e come calcolare la due_date
'Aperta' AS inspection_state, -- Eventualmente calcolare se in scadenza
NULL AS inspection_date, -- a NULL perchè non è stata fatta
vte_salesorder.duedate AS salesorder_date, -- Verificare qual'è la data dell'Ordine di vendita e come calcolare la due_date
vte_accountscf.cf_799 AS account_cat, -- cf_762 per rotho
case when vte_products.productid is not null then vte_products.inspection_frequency else 'ND' end as inspection_frequency, 
vte_inventoryproductrel.quantity as inspection_qty,
case when vte_products.productid is not null then 'Products' else 'Services' end as entitytype, 
case when vte_products.productid is not null then vte_products.unit_price else vte_service.unit_price end as unit_price, 
case when vte_products.productid is not null then vte_products.productcode else vte_service.service_no end as productcode, 
case when vte_products.productid is not null then vte_products.qtyinstock else 0 end as qtyinstock, 
vte_salesorder.subject
-- ,vte_inventoryproductrel.*  
from vte_inventoryproductrel 
left join vte_crmentity on vte_crmentity.crmid = vte_inventoryproductrel.id 
left join vte_products on vte_products.productid=vte_inventoryproductrel.productid 
left join vte_productcf on vte_productcf.productid=vte_inventoryproductrel.productid 
left join vte_service on vte_service.serviceid=vte_inventoryproductrel.productid  
left join vte_salesorder on vte_salesorder.salesorderid=vte_inventoryproductrel.id  
left join vte_inspections on vte_inspections.salesorderid = vte_salesorder.salesorderid 
left join vte_account on vte_account.accountid = vte_salesorder.accountid 
left join vte_accountscf on vte_accountscf.accountid = vte_account.accountid
where 
vte_crmentity.deleted =0 
AND vte_products.inspection_frequency IS NOT NULL AND vte_products.inspection_frequency <>'lbl_nd'
AND vte_inspections.inspectionsid IS  NULL
ORDER BY vte_salesorder.salesorderid, sequence_no

