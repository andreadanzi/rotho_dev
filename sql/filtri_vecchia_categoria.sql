UPDATE
vtiger_cvcolumnlist
SET vtiger_cvcolumnlist.columnname = 'vtiger_account:account_client_type:account_client_type:Accounts_Tipo_Cliente:V'
 from 
vtiger_cvcolumnlist
join vtiger_customview vtiger_customview_cat on vtiger_customview_cat.cvid = vtiger_cvcolumnlist.cvid 
join vtiger_cvcolumnlist cvc_rat on cvc_rat.cvid = vtiger_customview_cat.cvid 
where 
vtiger_cvcolumnlist.columnname = 'vtiger_accountscf:cf_762:cf_762:Accounts_Categoria:V';

UPDATE
vtiger_cvadvfilter
SET vtiger_cvadvfilter.columnname = 'vtiger_account:account_client_type:account_client_type:Accounts_Tipo_Cliente:V',
vtiger_cvadvfilter.value = 
CASE 
	WHEN vtiger_cvadvfilter.value = 'RC / CARP' THEN 'UTILIZZATORE'
	WHEN vtiger_cvadvfilter.value = 'CARP' THEN 'UTILIZZATORE'
	WHEN vtiger_cvadvfilter.value = 'RD / DIST' THEN 'RIVENDITORE'
	WHEN vtiger_cvadvfilter.value = 'RP / PROG' THEN 'PROGETTISTA'
	WHEN vtiger_cvadvfilter.value = 'prog' THEN 'PROGETTISTA'
	WHEN vtiger_cvadvfilter.value = 'RP / PROG' THEN 'PROGETTISTA'
	WHEN vtiger_cvadvfilter.value = 'RE / ALTRO,RP / PROG' THEN 'PROGETTISTA'
    WHEN vtiger_cvadvfilter.value = 'RC / CARP,RD / DIST,RS / SAFE,RP / PROG' THEN 'UTILIZZATORE,RIVENDITORE,PROGETTISTA'
    WHEN vtiger_cvadvfilter.value = 'RD,GDO,SAFE' THEN 'RIVENDITORE,UTILIZZATORE'
    WHEN vtiger_cvadvfilter.value = 'RC / CARP,RD / DIST,RS / SAFE,RP / PROG,RE / ALTRO' THEN 'UTILIZZATORE,RIVENDITORE,PROGETTISTA,INFLUENZATORE'
    WHEN vtiger_cvadvfilter.value = 'RD,GDO' THEN 'RIVENDITORE'
    WHEN vtiger_cvadvfilter.value = 'RC / CARP,RS / SAFE,RP / PROG' THEN 'UTILIZZATORE,PROGETTISTA'
    WHEN vtiger_cvadvfilter.value = 'RC / CARP,RS / SAFE,RP / PROG,RE / ALTRO' THEN 'UTILIZZATORE,PROGETTISTA,INFLUENZATORE'
    WHEN vtiger_cvadvfilter.value = 'GDO,DIST' THEN 'RIVENDITORE'
    
END

 from 
vtiger_cvadvfilter
join vtiger_customview on vtiger_customview.cvid = vtiger_cvadvfilter.cvid 
where 
vtiger_cvadvfilter.columnname = 'vtiger_accountscf:cf_762:cf_762:Accounts_Categoria:V' AND
vtiger_cvadvfilter.value in ( 'RC / CARP', 'CARP', 'RD / DIST', 'RP / PROG', 'prog', 'RP / PROG', 'RE / ALTRO,RP / PROG', 'RC / CARP,RD / DIST,RS / SAFE,RP / PROG', 'RD,GDO,SAFE', 'RC / CARP,RD / DIST,RS / SAFE,RP / PROG,RE / ALTRO', 'RD,GDO', 'RC / CARP,RS / SAFE,RP / PROG', 'RC / CARP,RS / SAFE,RP / PROG,RE / ALTRO', 'GDO,DIST' );

-- SELECT vtiger_customview.cvid,
vtiger_customview.viewname,
vtiger_cvadvfilter.columnname,
vtiger_cvadvfilter.comparator,
vtiger_cvadvfilter.value

 from 
vtiger_cvadvfilter
join vtiger_customview on vtiger_customview.cvid = vtiger_cvadvfilter.cvid 
where 
vtiger_cvadvfilter.columnname = 'vtiger_accountscf:cf_762:cf_762:Accounts_Categoria:V' 
order by vtiger_cvadvfilter.value 

"comparator";"value";"cnts" AUTOMATICO
"e";"RP / PROG";"95" AUTOMATICO
"e";"RC / CARP";"28" AUTOMATICO
"c";"prog";"26" AUTOMATICO
"e";"RD / DIST";"22" AUTOMATICO
"c";"CARP";"20"
"e";"RC / CARP,RD / DIST,RS / SAFE,RP / PROG";"16"
"k";"RD,GDO,SAFE";"13"
"e";"RC / CARP,RD / DIST,RS / SAFE,RP / PROG,RE / ALTRO";"12"
"k";"RD,GDO";"12"
"e";"RC / CARP,RS / SAFE,RP / PROG";"11"
"e";"RC / CARP,RS / SAFE,RP / PROG,RE / ALTRO";"7"
"c";"SAFE";"7"
"e";"RS / SAFE";"6"
"e";"RE / ALTRO";"4"
"k";"GDO,DIST";"3"
"c";"DIST";"2"
"e";"---,RA / ALTRO,RE / ALTRO,AG / AGENTE,GD / GDO,ORG / ORGANIZZAZIONE,ZZZ-RE / ***ALTRO";"2"
"e";"RA / ASS";"2"
"e";"RC / CARP,RD / DIST,RS / SAFE,RE / ALTRO";"2"
"e";"RE / ALTRO,RP / PROG";"2"
"c";"RD,GDO";"2"
"n";"RD / DIST";"2"
"n";"RD / DIST,RS / SAFE,GD / GDO";"1"
"k";"RD,GDO,AGENTE";"1"
"e";"RC / CARP,RP / PROG";"1"
"n";"RP / PROG";"1"
"e";"RP / PROG,RE / ALTRO";"1"
"k";"RD,GDO,SAFE,AGENTE";"1"
"k";"prog";"1"
"e";"RC / CARP,RD / DIST,RE / ALTRO,RP / PROG";"1"
"e";"RC / CARP,RD / DIST,RP / PROG,RS / SAFE";"1"
"e";"RC / CARP,RD / DIST,RS / SAFE";"1"
"e";"---,RE / ALTRO,AG / AGENTE,GD / GDO,ORG / ORGANIZZAZIONE,ZZZ-RE / ***ALTRO";"1"
"e";"AG / AGENTE";"1"
"c";"AGENTE";"1"
"c";"ALTRO";"1"
"c";"dELHI";"1"
"e";"SAFE";"1"

