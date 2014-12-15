<?php
require_once('include/database/PearDatabase.php');
require_once 'include/utils/utils.php';
// danzi.tn@20141212 nova classificazione cf_762 sostituito con vtiger_account.account_client_type
function customAccount($entity){
	global $adb;
	$entityArray = get_object_vars($entity);
	// echo 'account_no='. $entity->data['account_no'].'<br/>';
	// echo 'rating='. $entity->data['rating'].'<br/>';
	// echo 'employees='. $entity->data['employees'].'<br/>';
	// echo 'cf_900='. $entity->data['cf_900'].'<br/>';
	// echo 'cf_912='. $entity->data['cf_912'].'<br/>';
	// echo 'cf_907='. $entity->data['cf_907'].'<br/>';
	// echo 'cf_927='. $entity->data['cf_927'].'<br/>';
	// echo 'cf_923='. $entity->data['cf_923'].'<br/>';
	// echo 'cf_924='. $entity->data['cf_924'].'<br/>';
	// echo 'cf_925='. $entity->data['cf_925'].'<br/>';
	// echo 'cf_926='. $entity->data['cf_926'].'<br/>';
	// echo 'cf_927='. $entity->data['cf_927'].'<br/>';
	// result in cf_889
	//This example just sends you an email. Yes, I know that feature is
	// already available, but this is just an example to show you how
	// to set it all up!
	$id = $entity->data['id'];
	$id_splitted = explode('x',$id);
	$id = $id_splitted[1];
	$account_no = $entity->data['account_no'];
	$sql_query = "
SELECT vtiger_account.accountid,
        vtiger_account.accountname,
        vtiger_account.rating as RatingSemiramis,
        vtiger_account.account_no,
        vtiger_account.account_client_type as Categ,
        vtiger_accountscf.cf_1010 as Impiegati,
        vtiger_accountscf.cf_900 as Fatt,
        vtiger_accountscf.cf_912 as AreaExpo,
        vtiger_accountscf.cf_907 as UffTecnico,
        vtiger_accountscf.cf_927 as RatingAttuale,
-- qua split delle option selezionate: se non trova '|##|'
        (DATALENGTH(vtiger_accountscf.cf_1011)-DATALENGTH(REPLACE(convert(varchar(max), vtiger_accountscf.cf_1011),'|##|','')))/DATALENGTH('|##|')+1
        as NumSottocateg,     
CASE
         WHEN (vtiger_account.rating = 'Attivita cessata'
            OR  vtiger_accountscf.cf_927 = 10
            OR  vtiger_accountscf.cf_927 = 20
            OR  vtiger_accountscf.cf_927 = 30)
THEN
        0
ELSE
   CASE
        WHEN (vtiger_account.account_client_type = 'UTILIZZATORE') --VECCHIO CARP
        THEN
            CASE WHEN ((vtiger_accountscf.cf_900 LIKE '%> 10 mio%') OR (vtiger_accountscf.cf_1010 LIKE '> 50' ) or (vtiger_accountscf.cf_907 = 1))
            THEN 7
            ELSE
                CASE WHEN ((vtiger_accountscf.cf_900 LIKE '%2 mio  - 10 mio%') OR (vtiger_accountscf.cf_1010 like '20 - 50' ) OR
                ( (DATALENGTH(vtiger_accountscf.cf_1011)-DATALENGTH(REPLACE(convert(varchar(max), vtiger_accountscf.cf_1011),'|##|','')))/DATALENGTH('|##|')+1 > 2)  )
                THEN 14
                ELSE
                    CASE WHEN ((vtiger_accountscf.cf_900 LIKE '%500.000 - 2 mio%') OR (vtiger_accountscf.cf_1010 like '5 - 20'  )) 
                    THEN 21
                    ELSE
                        CASE WHEN ((vtiger_accountscf.cf_900 LIKE '% < 500.000%') OR (vtiger_accountscf.cf_1010 like '< 5' )
                        OR ((DATALENGTH(vtiger_accountscf.cf_1011)-DATALENGTH(REPLACE(convert(varchar(max), vtiger_accountscf.cf_1011),'|##|','')))/DATALENGTH('|##|')+1 <= 2))
                        THEN 28
                        END
                    end
                END   
            END
        WHEN (vtiger_account.account_client_type = 'RIVENDITORE') -- Vecchio DIST
        THEN
            CASE WHEN ((vtiger_accountscf.cf_900 LIKE '%> 10 mil%') OR (vtiger_accountscf.cf_1010 like '> 50' ))
            THEN 7
            ELSE
                CASE WHEN ((vtiger_accountscf.cf_900 LIKE '%2 mio  - 10 mio%') OR (vtiger_accountscf.cf_1010 like '20 - 50' )
                            OR (vtiger_accountscf.cf_912 like '%> 50 mq%')
                            OR ((DATALENGTH(vtiger_accountscf.cf_1011)-DATALENGTH(REPLACE(convert(varchar(max), vtiger_accountscf.cf_1011),'|##|','')))/DATALENGTH('|##|')+1 > 2))
                THEN 14
                ELSE
                    CASE WHEN ((vtiger_accountscf.cf_900 LIKE '%500.000 - 2 mio%') OR (vtiger_accountscf.cf_1010 like '5 - 20' )
                            OR (vtiger_accountscf.cf_912 like '%< 50 mq%'))
                    THEN 21
                    ELSE
                        CASE WHEN ((vtiger_accountscf.cf_900 LIKE '% < 500.000%') OR (vtiger_accountscf.cf_1010 like '< 5' )
                                OR ((DATALENGTH(vtiger_accountscf.cf_1011)-DATALENGTH(REPLACE(convert(varchar(max), vtiger_accountscf.cf_1011),'|##|','')))/DATALENGTH('|##|')+1 <= 2))
                        THEN 28
                        END
                    END
                END
            END
        WHEN (vtiger_account.account_client_type = 'PROGETTISTA') --PROG
        THEN
            CASE WHEN ((vtiger_accountscf.cf_1010 like '5 - 20' ) OR (vtiger_accountscf.cf_1010 like '20 - 50' ) OR (vtiger_accountscf.cf_1010 like '> 50' ))
            THEN 28
            END
        WHEN (vtiger_account.account_client_type = 'INFLUENZATORE') -- SAFE
        THEN
            CASE WHEN ((vtiger_accountscf.cf_900 LIKE '%> 10 mio%') OR (vtiger_accountscf.cf_1010 like '> 50'))
            THEN 7
            ELSE
                CASE WHEN ((vtiger_accountscf.cf_900 LIKE '%2 mio  - 10 mio%') OR (vtiger_accountscf.cf_1010 like '20 - 50' ) ) 
                THEN 14
                ELSE
                    CASE WHEN ((vtiger_accountscf.cf_900 LIKE '%500.000 - 2 mio%') OR (vtiger_accountscf.cf_1010 like '5 - 20' )) 
                    THEN 21
                    ELSE
                        CASE WHEN ((vtiger_accountscf.cf_900 LIKE '%< 500.000%') OR (vtiger_accountscf.cf_1010 like '< 5'))
                        THEN 28
                        ELSE 365
                        END
                    end
                END   
            END
        ELSE 0
   END
END
AS FP
  FROM vtiger_account 
  inner join vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid
WHERE vtiger_account.accountid = '".$id."'";
	$result = $adb->pquery($sql_query,array());
	$cf_889 = $result->fields['FP'];
	$sql_query = "UPDATE vtiger_accountscf SET cf_889 = '".$cf_889."' WHERE accountid = " . $id;
	$adb->query($sql_query);
	if (empty($_REQUEST['wsname']) && $_REQUEST['file'] != 'MassEdit') {
		//echo "<script type='text/javascript'>document.getElementById('dtlview_Frequenza proposta').innerHTML = '".$cf_889."';</script>";
	}
}
?>
