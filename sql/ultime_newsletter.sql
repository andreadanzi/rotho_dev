-- danzi.tn@20141211 controllo coda newsletter schedulate negli ultimi 5 giorni 

select vtiger_newsletter.newsletterid,
tbl_s_newsletter_queue.crmid,
vtiger_newsletter.newslettername,
vtiger_newsletter.newsletter_no,
tbl_s_newsletter_queue.status, 
tbl_s_newsletter_queue.date_scheduled ,
tbl_s_newsletter_queue.last_attempt,
tbl_s_newsletter_queue.date_sent
from 
tbl_s_newsletter_queue
join vtiger_newsletter on vtiger_newsletter.newsletterid = tbl_s_newsletter_queue.newsletterid
where
tbl_s_newsletter_queue.date_scheduled between DATEADD(day,-5, GETDATE()) and GETDATE()
AND 
(tbl_s_newsletter_queue.last_attempt IS NULL OR tbl_s_newsletter_queue.last_attempt  between DATEADD(hour,-1, GETDATE()) and GETDATE())
order by tbl_s_newsletter_queue.date_scheduled desc, tbl_s_newsletter_queue.last_attempt desc


-- danzi.tn@20141211 riassunto ultime newsletter schedulate negli ultimi 5 giorni 
select 
vtiger_newsletter.newsletterid,
vtiger_newsletter.newslettername,
vtiger_newsletter.newsletter_no,
tbl_s_newsletter_queue.status, 
count(tbl_s_newsletter_queue.crmid) as emails_number,
max(tbl_s_newsletter_queue.date_scheduled) as max_scheduled ,
max(tbl_s_newsletter_queue.date_sent) as last_sent,
sum(tbl_s_newsletter_queue.attempts) as sum_attempts
from 
tbl_s_newsletter_queue
join vtiger_newsletter on vtiger_newsletter.newsletterid = tbl_s_newsletter_queue.newsletterid
where
tbl_s_newsletter_queue.date_scheduled between DATEADD(day,-5, GETDATE()) and GETDATE()
AND 
(tbl_s_newsletter_queue.last_attempt IS NULL OR tbl_s_newsletter_queue.last_attempt  between DATEADD(hour,-1, GETDATE()) and GETDATE())
group by 

vtiger_newsletter.newsletterid,
vtiger_newsletter.newslettername,
vtiger_newsletter.newsletter_no,
tbl_s_newsletter_queue.status
order by 
max(tbl_s_newsletter_queue.date_sent) desc,
max(tbl_s_newsletter_queue.date_scheduled) 


