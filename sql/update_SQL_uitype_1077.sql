-- danzi.tn@20140220
UPDATE vtiger_ws_fieldtype_seq SET id=43 WHERE  id=42
INSERT INTO vtiger_ws_fieldtype (fieldtypeid, uitype, fieldtype) VALUES (42, 1077, 'reference')
INSERT INTO vtiger_ws_referencetype (fieldtypeid, type) VALUES (42, 'Users')
UPDATE vtiger_field SET uitype=1077 WHERE  fieldid=832