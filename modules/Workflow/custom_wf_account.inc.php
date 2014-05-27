<?php
require_once('include/database/PearDatabase.php');
require_once 'include/utils/utils.php';
function customAccount($entity){
	global $adb;
	$entityArray = get_object_vars($entity);
	// echo 'account_no='. $entity->data['account_no'].'<br/>';
	// echo 'rating='. $entity->data['rating'].'<br/>';
	// echo 'cf_762='. $entity->data['cf_762'].'<br/>';
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
SELECT A.[accountid],
        A.[accountname],
        A.[rating] as RatingSemiramis,
        A.[account_no],
        B.cf_762 as Categ,
        B.cf_1010 as Impiegati,
        B.cf_900 as Fatt,
        B.cf_912 as AreaExpo,
        B.cf_907 as UffTecnico,
        B.cf_927 as RatingAttuale,
-- qua split delle option selezionate: se non trova '|##|'
        (DATALENGTH(B.cf_1011)-DATALENGTH(REPLACE(convert(varchar(max), B.cf_1011),'|##|','')))/DATALENGTH('|##|')+1
        as NumSottocateg,     
CASE
         WHEN (A.[rating] = 'Attivita cessata'
            OR  B.cf_927 = 10
            OR  B.cf_927 = 20
            OR  B.cf_927 = 30)
THEN
        0
ELSE
        CASE
        when (B.cf_762 = 'RC / CARP') --CARP
        THEN
            CASE WHEN ((B.cf_900 LIKE '%> 10 mio%') OR (B.cf_1010 LIKE '> 50' ) or (B.cf_907 = 1))
            THEN 7
            ELSE
                CASE WHEN ((B.cf_900 LIKE '%2 mio  - 10 mio%') OR (B.cf_1010 like '20 - 50' ) OR
                ( (DATALENGTH(B.cf_1011)-DATALENGTH(REPLACE(convert(varchar(max), B.cf_1011),'|##|','')))/DATALENGTH('|##|')+1 > 2)  )
                THEN 14
                ELSE
                    CASE WHEN ((B.cf_900 LIKE '%500.000 - 2 mio%') OR (B.cf_1010 like '5 - 20'  )) 
                    THEN 21
                    ELSE
                        CASE WHEN ((B.cf_900 LIKE '% < 500.000%') OR (B.cf_1010 like '< 5' )
                        OR ((DATALENGTH(B.cf_1011)-DATALENGTH(REPLACE(convert(varchar(max), B.cf_1011),'|##|','')))/DATALENGTH('|##|')+1 <= 2))
                        THEN 28
                        END
                    end
                END   
            END
        WHEN (B.cf_762 = 'RD / DIST') -- DIST
        THEN
            CASE WHEN ((B.cf_900 LIKE '%> 10 mil%') OR (B.cf_1010 like '> 50' ))
            THEN 7
            ELSE
                CASE WHEN ((B.cf_900 LIKE '%2 mio  - 10 mio%') OR (B.cf_1010 like '20 - 50' )
                            OR (B.cf_912 like '%> 50 mq%')
                            OR ((DATALENGTH(B.cf_1011)-DATALENGTH(REPLACE(convert(varchar(max), B.cf_1011),'|##|','')))/DATALENGTH('|##|')+1 > 2))
                THEN 14
                ELSE
                    CASE WHEN ((B.cf_900 LIKE '%500.000 - 2 mio%') OR (B.cf_1010 like '5 - 20' )
                            OR (B.cf_912 like '%< 50 mq%'))
                    THEN 21
                    ELSE
                        CASE WHEN ((B.cf_900 LIKE '% < 500.000%') OR (B.cf_1010 like '< 5' )
                                OR ((DATALENGTH(B.cf_1011)-DATALENGTH(REPLACE(convert(varchar(max), B.cf_1011),'|##|','')))/DATALENGTH('|##|')+1 <= 2))
                        THEN 28
                        END
                    END
                END
            END
        WHEN (B.cf_762 = 'RP / PROG') --PROG
        THEN
            CASE WHEN ((B.cf_1010 like '5 - 20' ) OR (B.cf_1010 like '20 - 50' ) OR (B.cf_1010 like '> 50' ))
            THEN 28
            END
        WHEN (B.cf_762 = 'RS / SAFE') -- SAFE
        THEN
            CASE WHEN ((B.cf_900 LIKE '%> 10 mio%') OR (B.cf_1010 like '> 50'))
            THEN 7
            ELSE
                CASE WHEN ((B.cf_900 LIKE '%2 mio  - 10 mio%') OR (B.cf_1010 like '20 - 50' ) ) 
                THEN 14
                ELSE
                    CASE WHEN ((B.cf_900 LIKE '%500.000 - 2 mio%') OR (B.cf_1010 like '5 - 20' )) 
                    THEN 21
                    ELSE
                        CASE WHEN ((B.cf_900 LIKE '% < 500.000%') OR (B.cf_1010 like '< 5'))
                        THEN 28
                        END
                    end
                END   
            END
        ELSE
            0
        END
END
AS FP
  FROM [vte40_387].[dbo].[vtiger_account] A
  inner join  [vte40_387].[dbo].[vtiger_accountscf] B on A.accountid = B.accountid
WHERE A.accountid = '".$id."'";
	$result = $adb->pquery($sql_query,array());
	$cf_889 = $result->fields['FP'];
	$sql_query = "UPDATE vtiger_accountscf SET cf_889 = '".$cf_889."' WHERE accountid = " . $id;
	$adb->query($sql_query);
	if (empty($_REQUEST['wsname']) && $_REQUEST['file'] != 'MassEdit') {
		//echo "<script type='text/javascript'>document.getElementById('dtlview_Frequenza proposta').innerHTML = '".$cf_889."';</script>";
	}
}
?>
