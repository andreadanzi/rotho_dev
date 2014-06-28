select  vtiger_targets.targetsid, tarent.setype, vtiger_campaign.campaignid  , cament.setype
from vtiger_campaign, vtiger_targets, vtiger_crmentity as cament, vtiger_crmentity as tarent, vtiger_crmentityrel 
where 
--vtiger_campaign.campaignname ='14 NL MYPROJECT NUOVA RELEASE 2.0 ES SOUTH AM' AND vtiger_targets.targetname = 'RC CARP/ SAFE/ PROG SOUTH AM'
vtiger_campaign.campaignname ='14 NL MYPROJECT NUOVA RELEASE 2.0 ES new' AND vtiger_targets.targetname = 'RC CARP/ SAFE/ PROG ES'
--vtiger_campaign.campaignname ='14 NL MYPROJECT NUOVA RELEASE 2.0 IT' AND vtiger_targets.targetname = 'SAFE ITALIA'
--vtiger_campaign.campaignname ='14 NL MYPROJECT NUOVA RELEASE 2.0 IT' AND vtiger_targets.targetname = 'CARP ITALIA'
--vtiger_campaign.campaignname ='14 NL MYPROJECT NUOVA RELEASE 2.0 IT' AND vtiger_targets.targetname = 'PROG ITALIA'
--vtiger_campaign.campaignname ='14 NL MYPROJECT NUOVA RELEASE 2.0 DE' AND vtiger_targets.targetname = 'RC CARP/ SAFE/ PROG AT'
--vtiger_campaign.campaignname ='14 NL MYPROJECT NUOVA RELEASE 2.0 DE' AND vtiger_targets.targetname = 'RC CARP/ SAFE/ PROG DE/CH'
--vtiger_campaign.campaignname ='14 NL MYPROJECT NUOVA RELEASE 2.0 PT' AND vtiger_targets.targetname = 'RC CARP/ SAFE/ PROG PT'
--vtiger_campaign.campaignname ='14 NL MYPROJECT NUOVA RELEASE 2.0 PT BRASILE' AND vtiger_targets.targetname = 'RC CARP/ SAFE/ PROG PT BRASIL'
--vtiger_campaign.campaignname ='14 NL MYPROJECT NUOVA RELEASE 2.0 FR' AND vtiger_targets.targetname = 'RC CARP/ SAFE/ PROG FR'
--vtiger_campaign.campaignname ='14 NL MYPROJECT NUOVA RELEASE 2.0 NL' AND vtiger_targets.targetname = 'RC CARP/ SAFE/ PROG NL'
 AND  cament.crmid = vtiger_campaign.campaignid AND cament.deleted = 0
 AND  tarent.crmid = vtiger_targets.targetsid AND tarent.deleted = 0
 AND vtiger_crmentityrel.crmid = tarent.crmid AND  vtiger_crmentityrel.relcrmid = cament.crmid 
 AND vtiger_crmentityrel.relmodule IS NULL