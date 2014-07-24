SELECT
		temp_acc_ratings.categoria,
		temp_acc_ratings.gruppo,
		temp_acc_ratings.eventdatetime,
		sum(temp_acc_ratings.valore) as sumvalore,
		vtiger_account.accountname,
		vtiger_account.account_no
		from 
		 temp_acc_ratings 
		join vtiger_account ON vtiger_account.accountid = temp_acc_ratings.accountid 
		 WHERE temp_acc_ratings.accountid = ?
		group by 
		temp_acc_ratings.categoria,
		temp_acc_ratings.gruppo,
		temp_acc_ratings.eventdatetime,
		vtiger_account.accountname,
		vtiger_account.account_no
		ORDER BY 
		temp_acc_ratings.categoria, temp_acc_ratings.eventdatetime, sumvalore