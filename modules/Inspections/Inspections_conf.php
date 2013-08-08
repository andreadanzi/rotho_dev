<?php

$cf_product_category='cf_803'; // cf_796 ROTHO -- cf_803
$cf_account_category='cf_762'; // cf_799 ROTHO -- cf_762
$cf_account_base_language = 'cf_1113'; // cf_851  ROTHO = cf_1113 ROTHO_test = cf_1120
$insp_activitytype = 'Revisione (Auto-gen)';
$insp_eventstatus = 'Planned';
function sql_date_add($table_prefix) {
    $mysql_snippet = "
                case 
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_6m' then 
                        date_add(".$table_prefix."_salesorder.data_ordine_ven, INTERVAL 6 MONTH) 
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_12m' then 
                        date_add(".$table_prefix."_salesorder.data_ordine_ven, INTERVAL 12 MONTH) 
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_18m' then 
                        date_add(".$table_prefix."_salesorder.data_ordine_ven, INTERVAL 18 MONTH) 
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_24m' then 
                        date_add(".$table_prefix."_salesorder.data_ordine_ven, INTERVAL 24 MONTH) 
                    else
                        date_add(".$table_prefix."_salesorder.data_ordine_ven, INTERVAL 12 MONTH) 
                    end
                    AS due_date,
                case 
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_6m' then 
                       YEAR( date_add(".$table_prefix."_salesorder.duedate, INTERVAL 6 MONTH) )
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_12m' then 
                       YEAR( date_add(".$table_prefix."_salesorder.duedate, INTERVAL 12 MONTH) )
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_18m' then 
                       YEAR( date_add(".$table_prefix."_salesorder.duedate, INTERVAL 18 MONTH) )
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_24m' then 
                       YEAR( date_add(".$table_prefix."_salesorder.duedate, INTERVAL 24 MONTH) )
                    else
                       YEAR( date_add(".$table_prefix."_salesorder.duedate, INTERVAL 12 MONTH) )
                    end
                    AS year_due_date
    ";
    $mssql_snippet = "
                case 
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_6m' then 
                        dateadd(month,6,".$table_prefix."_salesorder.data_ordine_ven) 
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_12m' then 
                        dateadd(month,12,".$table_prefix."_salesorder.data_ordine_ven) 
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_18m' then 
                        dateadd(month,18,".$table_prefix."_salesorder.data_ordine_ven) 
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_24m' then 
                        dateadd(month,24,".$table_prefix."_salesorder.data_ordine_ven) 
                    else
                        dateadd(month,12,".$table_prefix."_salesorder.data_ordine_ven) 
                    end
                    AS due_date,
                case 
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_6m' then 
                       YEAR( dateadd(month,6,".$table_prefix."_salesorder.data_ordine_ven) )
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_12m' then 
                        YEAR(  dateadd(month,12,".$table_prefix."_salesorder.data_ordine_ven) )
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_18m' then 
                         YEAR( dateadd(month,18,".$table_prefix."_salesorder.data_ordine_ven) )
                    when ".$table_prefix."_products.inspection_frequency = 'lbl_24m' then 
                         YEAR( dateadd(month,24,".$table_prefix."_salesorder.data_ordine_ven) )
                    else
                         YEAR( dateadd(month,12,".$table_prefix."_salesorder.data_ordine_ven) )
                    end
                    AS year_due_date
    ";
    return $mssql_snippet;
}
?>
