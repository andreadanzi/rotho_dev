select 
vtiger_crmentity.crmid,
vtiger_crmentity.deleted,
vtiger_account.accountname,
vtiger_account.account_no

 from vtiger_account
join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid 
where vtiger_account.account_no in 
('ACC41','ACC56','ACC98','ACC219','ACC252','ACC390','ACC742','ACC821','ACC893','ACC942','ACC966','ACC1850','ACC2155','ACC2206','ACC2296','ACC2345','ACC2346','ACC2354','ACC2359','ACC2430','ACC2733','ACC2796','ACC3077','ACC3171','ACC3245','ACC3258','ACC3509','ACC3734','ACC3741','ACC3816','ACC3836','ACC3982','ACC4171','ACC4328','ACC4429','ACC4449','ACC4552','ACC4563','ACC4666','ACC4693','ACC5063','ACC5089','ACC5154','ACC5265','ACC5317','ACC5485','ACC5486','ACC5719','ACC5798','ACC6067','ACC6167','ACC6206','ACC6532','ACC6849','ACC6978','ACC7007','ACC7039','ACC7061','ACC7096','ACC7103','ACC7270','ACC7311','ACC7428','ACC7526','ACC7614','ACC7622','ACC7651','ACC7745','ACC8030','ACC8250','ACC8301','ACC8384','ACC8386','ACC8874','ACC9009','ACC9011','ACC9026','ACC9027','ACC9066','ACC9163','ACC9179','ACC9242','ACC9269','ACC9323','ACC9384','ACC9387','ACC9554','ACC9741','ACC9825','ACC9927','ACC9978','ACC9997','ACC10000','ACC10070','ACC10236','ACC10299','ACC11185','ACC11636','ACC11749','ACC11796','ACC12062','ACC12078','ACC12404','ACC12566','ACC12834','ACC12910','ACC12959','ACC12970','ACC13036','ACC13240','ACC13545','ACC13640','ACC13831','ACC13902','ACC13955','ACC14004','ACC14298','ACC14359','ACC14664','ACC14863','ACC15194','ACC15459','ACC15492','ACC15622','ACC15887','ACC16012','ACC16115','ACC16650','ACC16857','ACC16935','ACC17338','ACC17417','ACC17556','ACC17586','ACC18170','ACC18313','ACC18314','ACC18388','ACC18581','ACC18593','ACC18960','ACC19415','ACC19665','ACC19786','ACC19903','ACC20013','ACC20015','ACC20308','ACC20312','ACC20321','ACC20337','ACC20743','ACC20980','ACC21184','ACC21826','ACC22038','ACC22039','ACC22152','ACC22200','ACC22266','ACC22274','ACC22562','ACC22668','ACC22772','ACC22799','ACC22805','ACC23247','ACC23295','ACC23448','ACC24124','ACC24397','ACC30192','ACC30198','ACC30222','ACC30241','ACC30250','ACC30292','ACC30307','ACC30349','ACC30352','ACC30359','ACC30391','ACC30393','ACC30405','ACC30425','ACC30469','ACC30494','ACC30497','ACC30553','ACC30579','ACC30650','ACC30670','ACC30683','ACC30689','ACC30721','ACC30734','ACC30774','ACC30822','ACC30837','ACC30840','ACC30852','ACC30861','ACC30899','ACC30950','ACC30984','ACC31013','ACC31024','ACC31041','ACC31076','ACC31149','ACC31162','ACC31202','ACC31271','ACC31313','ACC31319','ACC31364','ACC31438','ACC31487','ACC31554','ACC31578','ACC31607','ACC31627','ACC31645','ACC31646','ACC31655','ACC31689','ACC31715','ACC31766','ACC31787','ACC31811','ACC31830','ACC31843','ACC31870','ACC31873','ACC31881','ACC31914','ACC31934','ACC32004','ACC32029','ACC32061','ACC32079','ACC32130','ACC32146','ACC32177','ACC32205','ACC32231','ACC32237','ACC32250','ACC32270','ACC32290','ACC32372','ACC32463','ACC32464','ACC32498','ACC32508','ACC32529','ACC32618','ACC32649','ACC32664','ACC32666','ACC32694','ACC32752','ACC32765','ACC32771','ACC32824','ACC32827','ACC32838','ACC32863','ACC32866','ACC32871','ACC32878','ACC32912','ACC32919','ACC32963','ACC32984','ACC32989','ACC33046','ACC33072','ACC33153','ACC33172','ACC33224','ACC33334','ACC33390','ACC33535','ACC33981','ACC34049','ACC34093','ACC34118','ACC34131','ACC34138','ACC34144','ACC34147','ACC34176','ACC34230','ACC34324','ACC34382','ACC34509','ACC34565','ACC35084','ACC35094','ACC35366','ACC35651','ACC35671','ACC35749','ACC35758','ACC35832','ACC35850','ACC36569','ACC36682','ACC36736','ACC36737','ACC36755','ACC36921','ACC37084','ACC37526','ACC37572','ACC37645','ACC37664','ACC37691','ACC37700','ACC37725','ACC37750','ACC37862','ACC53743','ACC54376','ACC54439','ACC54551','ACC54844','ACC55044','ACC55248','ACC55527','ACC56244','ACC57559','ACC58061','ACC58163','ACC58241','ACC58347','ACC58464','ACC58503','ACC58592','ACC59100','ACC59108','ACC59169','ACC59197','ACC59246','ACC59263','ACC59437','ACC59508','ACC59758','ACC59809','ACC59984','ACC60027','ACC60046','ACC60392','ACC60550','ACC60596','ACC60705','ACC60905','ACC60945','ACC61189','ACC61395','ACC61451','ACC61659','ACC61772','ACC61778','ACC61784','ACC61910','ACC62223','ACC62483','ACC62788','ACC62831','ACC62947','ACC63059','ACC63490','ACC63629','ACC63811','ACC63852','ACC64099','ACC64138','ACC64547','ACC64676','ACC64712','ACC64787','ACC64840','ACC65057','ACC65178','ACC65803','ACC66023','ACC66084','ACC66974','ACC66978','ACC67159','ACC67285','ACC67967','ACC68268','ACC69681','ACC69803','ACC70262','ACC71939','ACC72130','ACC72657','ACC73400','ACC73439','ACC73468','ACC73615','ACC73825','ACC73985','ACC76010','ACC76013','ACC76014','ACC76022','ACC76173','ACC76244','ACC76266','ACC76958','ACC77649','ACC77653','ACC77775','ACC77840','ACC77847','ACC77859','ACC77882','ACC77900','ACC77960','ACC78021','ACC78097','ACC78149','ACC78197','ACC78202','ACC78353','ACC78356','ACC78370','ACC78371','ACC78390','ACC78411','ACC78437','ACC78454','ACC78464','ACC78483','ACC78515','ACC78619','ACC78707','ACC78712','ACC78720','ACC78721','ACC78727','ACC78889','ACC78926','ACC78952','ACC79051','ACC79069','ACC79070','ACC80363','ACC85723','ACC85733','ACC85736','ACC86008','ACC86010','ACC86023','ACC86029','ACC86069','ACC86072','ACC86078','ACC88593','ACC88611','ACC90796','ACC92002','ACC92152','ACC92265','ACC92297','ACC92300','ACC93323','ACC93482','ACC93623','ACC93655','ACC93785','ACC94005','ACC94635','ACC95585','ACC95701','ACC96269','ACC98589','ACC98689','ACC100854','ACC100983','ACC101153','ACC101417','ACC101455','ACC101508','ACC101570','ACC102006','ACC102029','ACC102064','ACC103495','ACC103533','ACC104888','ACC104917','ACC105500','ACC105516','ACC107286','ACC107314','ACC107448','ACC107512','ACC107530','ACC107539','ACC107625','ACC108605','ACC108822','ACC108824','ACC108848','ACC108879','ACC110352','ACC110353','ACC111812','ACC111947','ACC111949','ACC112095','ACC112119','ACC112120','ACC112361','ACC112396','ACC112420','ACC112636','ACC112645','ACC112698','ACC112709','ACC112747','ACC112759','ACC112808','ACC113011','ACC113128','ACC113163','ACC113201','ACC113613','ACC113820','ACC113824','ACC113836','ACC113867','ACC113920','ACC114084','ACC114224','ACC114440','ACC114511','ACC114513','ACC114809','ACC115287','ACC115292','ACC115305','ACC115432','ACC115433','ACC115442','ACC115483','ACC115723','ACC115799','ACC115816','ACC115820','ACC116203','ACC116678','ACC117502','ACC117506','ACC117509','ACC117534','ACC117561','ACC117572','ACC117620','ACC117636','ACC117669','ACC117719','ACC117737','ACC117755','ACC117785','ACC117821','ACC117870','ACC117883','ACC117901','ACC117903','ACC117955','ACC117957','ACC117984','ACC118047','ACC118051','ACC118093','ACC118100','ACC118149','ACC118172','ACC118179','ACC118264','ACC118578','ACC118623','ACC118632','ACC118651','ACC118660','ACC118698','ACC118799','ACC118818','ACC118825','ACC118826','ACC118836','ACC118901','ACC118926','ACC118933','ACC118969','ACC118977','ACC118988','ACC119066','ACC119079','ACC119261','ACC119393','ACC119657','ACC119705','ACC119752','ACC119923','ACC120170','ACC120176','ACC120189','ACC120196','ACC120207','ACC120291','ACC120304','ACC120328','ACC120333','ACC120339','ACC120466','ACC120494','ACC120594','ACC120605','ACC120650','ACC120691','ACC120694','ACC120740','ACC120803','ACC120814','ACC120935','ACC120957','ACC120984','ACC121147','ACC121359','ACC121361','ACC121568','ACC122034','ACC122061','ACC122164','ACC122503','ACC122755','ACC122939','ACC123077','ACC123322','ACC123413','ACC124362','ACC124856','ACC124897','ACC126330','ACC126356','ACC126553','ACC126611','ACC127963','ACC127968','ACC128103','ACC128446','ACC128463','ACC128476','ACC128639','ACC128929','ACC129084','ACC129218','ACC129937','ACC129961','ACC129990','ACC130000','ACC130282','ACC130457','ACC130465','ACC130476','ACC130487','ACC130495','ACC130499','ACC130520','ACC130640','ACC131969','ACC132002','ACC132007','ACC132166','ACC132417','ACC134264','ACC134358','ACC134417','ACC134466','ACC134467','ACC134468','ACC134471','ACC134472','ACC134481','ACC134482','ACC134485','ACC134488','ACC134489','ACC134490','ACC134495','ACC134496','ACC134498','ACC134501','ACC134503','ACC134525','ACC134543','ACC134544','ACC134545','ACC134546','ACC134569','ACC134570','ACC134571','ACC134573','ACC134574','ACC134577','ACC134579','ACC134581','ACC134582','ACC134589','ACC134590','ACC134591','ACC134592','ACC134593','ACC134594','ACC134598','ACC134599','ACC134600','ACC134616','ACC134620','ACC134621','ACC134623','ACC134624','ACC134625','ACC134627','ACC134631','ACC134632','ACC134637','ACC134638','ACC134639','ACC134640','ACC134643','ACC134646','ACC134652','ACC134656','ACC134657','ACC134658','ACC134662','ACC134663','ACC134664','ACC134669','ACC134671','ACC134713','ACC134715','ACC134744','ACC134764','ACC134780','ACC134781','ACC134783','ACC134784','ACC134786','ACC134788','ACC134838','ACC134858','ACC134859','ACC134860','ACC134906','ACC134938','ACC134939','ACC134942','ACC134945','ACC134946','ACC135174','ACC135176','ACC135178','ACC135179','ACC135180','ACC135181','ACC135182','ACC135183','ACC135184','ACC135185','ACC135192','ACC135194','ACC135195','ACC135196','ACC135208','ACC135209','ACC135210','ACC135212','ACC135214','ACC135215','ACC135218','ACC135225','ACC135609','ACC135610','ACC135611','ACC135612','ACC135613','ACC135614','ACC135616','ACC135621','ACC135653','ACC135660','ACC135662','ACC135663','ACC135665','ACC135666','ACC135667','ACC135669','ACC135673','ACC135693','ACC135734','ACC135736','ACC135741','ACC135743','ACC135746','ACC135749','ACC135752','ACC135764','ACC135766','ACC135771','ACC135775','ACC135776','ACC135779','ACC135780','ACC135797','ACC135798','ACC135800','ACC135801','ACC135805','ACC135806','ACC135809','ACC135811','ACC135813','ACC135816','ACC135825','ACC135828','ACC135829','ACC135831','ACC135840','ACC135852','ACC135859','ACC135879','ACC135880','ACC135882','ACC135886','ACC135887','ACC135932','ACC135975','ACC136009','ACC136010','ACC136014','ACC136016','ACC136018','ACC136020','ACC136023','ACC136035','ACC136036','ACC136038','ACC136105','ACC136109','ACC136124','ACC136125','ACC136127','ACC136128','ACC136129','ACC136152','ACC136153','ACC136156','ACC136177','ACC138284','ACC138597','ACC138599','ACC138600','ACC138627','ACC138631','ACC138632','ACC138634','ACC138637','ACC138639','ACC138640','ACC138641','ACC138642','ACC138644','ACC138645','ACC138646','ACC138650','ACC138652','ACC138653','ACC138654','ACC138655','ACC138656','ACC138657','ACC138659','ACC138660','ACC138662','ACC138663','ACC138669','ACC138671','ACC138689','ACC138691','ACC138692','ACC138694','ACC138702','ACC138735','ACC138737','ACC138739','ACC138740','ACC138741','ACC138742','ACC138745','ACC138746','ACC138810','ACC138812','ACC138813','ACC138816','ACC138817','ACC138819','ACC138821','ACC138822','ACC138823','ACC138826','ACC138827','ACC138828','ACC138830','ACC138831','ACC138834','ACC138835','ACC138836','ACC138837','ACC138838','ACC138839','ACC138840','ACC138841','ACC138842','ACC138843','ACC138844','ACC138846','ACC138848','ACC138851','ACC138860','ACC139087','ACC139089')
order by vtiger_crmentity.deleted desc, vtiger_account.account_no