CREATE TABLE vtiger_mailchimpsyncdiff (
  crmid INT NOT NULL,
  module varchar(100) NOT NULL,
  relcrmid INT NOT NULL,
  relmodule varchar(100) NOT NULL
) 

CREATE TABLE vtiger_mailchimp_settings (
  id INT NOT NULL,
  apikey varchar(100) NOT NULL,
  listid varchar(50) NOT NULL,
  newsubscribertype varchar(50) NOT NULL,
  lastsyncdate varchar(25) NOT NULL DEFAULT '1970-01-01 09:30:00'
) 