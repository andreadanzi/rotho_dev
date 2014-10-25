

ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  rilavorazione NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  logistica NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  magazzino NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  acquisto NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  gestione NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  dati_comm_omaggio NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  dati_comm_fermo_can NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  dati_comm_note_acc NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  dati_comm_fatt_dann NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  danno_comm_varie NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  danno_comm_dann_imm NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  danno_comm_perd_fatt NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  danno_comm_perd_cli NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  danno_comm_perd_mar NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  danno_comm_entr_conc NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  danno_comm_perd_ord NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  dati_comm NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  danno_comm NUMERIC(10,2) NULL;
ALTER TABLE vtiger_nonconformities
	ALTER COLUMN  totale_valutazione NUMERIC(10,2) NULL;


-- danzi.tn@20141023 gestione custom valutazione
-- Readoly sulla DetailView
UPDATE
vtiger_field 
SET vtiger_field.readonly = 99
where tabid=66 and columnname in ('danno_comm','dati_comm','gestione','totale_valutazione')

-- Elminare dalla DetailView
UPDATE
vtiger_field 
SET vtiger_field.readonly = 100
where tabid=66 and columnname in ('danno_comm_perd_ord','dati_comm_fatt_dann','dati_comm_note_acc','dati_comm_fermo_can','dati_comm_omaggio','danno_comm_entr_conc','danno_comm_perd_mar','danno_comm_perd_cli','danno_comm_perd_fatt','danno_comm_dann_imm','danno_comm_varie')

