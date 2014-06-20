CREATE TABLE vtiger_mailchimpsyncdiff (
  crmid int(11) NOT NULL,
  module varchar(100) NOT NULL,
  relcrmid int(11) NOT NULL,
  relmodule varchar(100) NOT NULL
) 

CREATE TABLE vtiger_mailchimp_settings (
  id int(11) NOT NULL,
  apikey varchar(100) NOT NULL,
  listid varchar(50) NOT NULL,
  newsubscribertype varchar(50) NOT NULL,
  lastsyncdate varchar(25) NOT NULL DEFAULT '1970-01-01 09:30:00'
) 