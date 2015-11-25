SELECT 
				log_script_content.id,
				log_script_content.type,
				log_script_content.total_records,
				log_script_content.records_created,
				log_script_content.records_updated,
				log_script_content.date_start,
				log_script_content.date_end,
				DATEDIFF(minute,log_script_content.date_start, log_script_content.date_end) mins ,
				DATEDIFF(minute,log_script_content.date_start, GETDATE()) as mins_from_start
				FROM log_script_content 
				WHERE 
				log_script_content.date_start BETWEEN DATEADD(day, -7, GETDATE()) AND GETDATE()
				AND type in ('SalesOrder')
				ORDER BY id