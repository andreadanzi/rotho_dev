UPDATE 
vtiger_targetscf
set
vtiger_targetscf.cf_1225 = vtiger_campaignscf.cf_759, 
vtiger_targetscf.cf_1226 = vtiger_campaignscf.cf_745
from vtiger_targetscf
JOIN vtiger_crmentity as targetcf_entity on targetcf_entity.crmid = vtiger_targetscf.targetsid and targetcf_entity.deleted=0
JOIN vtiger_crmentityrel on vtiger_crmentityrel.crmid = vtiger_targetscf.targetsid and vtiger_crmentityrel.relmodule = 'Campaigns'
JOIN vtiger_campaignscf on vtiger_campaignscf.campaignid =  vtiger_crmentityrel.relcrmid
JOIN vtiger_crmentity as campaignscf_entity on  campaignscf_entity.crmid = vtiger_campaignscf.campaignid and campaignscf_entity.deleted=0