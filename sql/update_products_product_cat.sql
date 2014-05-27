-- select 
-- vtiger_products.productid,
-- LEFT(vtiger_productcf.cf_803,8), 
-- vtiger_products.product_cat, 
-- vtiger_products.prod_category_desc,
-- erp_temp_crm_classificazioni.class_desc3
UPDATE
vtiger_products
SET 
vtiger_products.product_cat = erp_temp_crm_classificazioni.class3,
vtiger_products.prod_category_desc = erp_temp_crm_classificazioni.class_desc3
from 
vtiger_products
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_products.productid AND vtiger_crmentity.deleted =0
JOIN vtiger_productcf on vtiger_productcf.productid = vtiger_products.productid
JOIN erp_temp_crm_classificazioni on erp_temp_crm_classificazioni.class3 = LEFT(vtiger_productcf.cf_803,8)


