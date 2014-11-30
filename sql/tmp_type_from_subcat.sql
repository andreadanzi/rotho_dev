
CREATE TABLE tmp_type_from_subcat (
	account_main_activity VARCHAR(50) NULL DEFAULT NULL,
	subcat VARCHAR(50) NOT NULL DEFAULT NULL,
	subname VARCHAR(50) NULL DEFAULT NULL,
	account_client_type VARCHAR(50) NULL DEFAULT NULL,
	subfield VARCHAR(50) NULL DEFAULT NULL,
	oldcat VARCHAR(50) NULL DEFAULT NULL
);

INSERT INTO tmp_type_from_subcat (account_main_activity, subcat, subname, account_client_type, subfield, oldcat) VALUES
	('CARPENTERIA', 'COPERTURE', 'Sub 1', 'UTILIZZATORE', 'cf_894', 'RC / CARP'),
	('PICCOLE STRUTTURE', 'GAZEBI E PERGOLE', 'Sub 1', 'UTILIZZATORE', 'cf_894', 'RC / CARP'),
	('FACCIATE E RIVESTIMENTI', 'TERRAZZE E FACCIATE', 'Sub 1', 'UTILIZZATORE', 'cf_894', 'RC / CARP'),
	('IMPEDILE', 'IMPRESA EDILE', 'Sub 1', 'UTILIZZATORE', 'cf_894', 'RC / CARP'),
	('RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI ', 'RIVENDITORE DI LEGNAME / SEGHERIA', 'Sub 1', 'UTILIZZATORE', 'cf_894', 'RC / CARP'),
	('GENERAL CONTRACTOR / IMMOBILIARE', 'Immobiliare', 'Sub 1', 'UTILIZZATORE', 'cf_894', 'RC / CARP'),
	('GENERAL CONTRACTOR / IMMOBILIARE', 'GENERAL CONTRACTOR', 'Sub 1', 'UTILIZZATORE', 'cf_894', 'RC / CARP'),
	('FALEGNAME/SERRAMENTISTA', 'FALEGNAME', 'Sub 1', 'UTILIZZATORE', 'cf_894', 'RC / CARP'),
	('FALEGNAME/SERRAMENTISTA', 'PAVIMENTISTA', 'Sub 1', 'UTILIZZATORE', 'cf_894', 'RC / CARP'),
	('LATTONIERE', 'LATTONIERE', 'Sub 1', 'UTILIZZATORE', 'cf_894', 'RC / CARP'),
	('CONCIATETTO', 'CONCIATETTO', 'Sub 1', 'UTILIZZATORE', 'cf_894', 'RC / CARP'),
	('RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA', 'FERRAMSPEC', 'Sub 2', 'RIVENDITORE', 'cf_902', 'RD / DIST'),
	('FERRAMENTA E ATTREZZATURE', 'FERRAMGEN', 'Sub 2', 'RIVENDITORE', 'cf_902', 'RD / DIST'),
	('GROSSISTA', 'GROSSISTA', 'Sub 2', 'RIVENDITORE', 'cf_902', 'RD / DIST'),
	('RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI) ', 'RIVELEGNAME', 'Sub 2', 'RIVENDITORE', 'cf_902', 'RD / DIST'),
	('RIVEMATED', 'RIVEMATED', 'Sub 2', 'RIVENDITORE', 'cf_902', 'RD / DIST'),
	('---', 'GRUPPIACQ', 'Sub 2', 'RIVENDITORE', 'cf_902', 'RD / DIST'),
	('GDO', 'GDO', 'Sub 2', 'RIVENDITORE', 'cf_902', 'RD / DIST'),
	('---', 'ELETTROMEC', 'Sub 2', 'RIVENDITORE', 'cf_902', 'RD / DIST'),
	('FERRAMENTA E ATTREZZATURE', 'RIVEATTRED', 'Sub 2', 'RIVENDITORE', 'cf_902', 'RD / DIST'),
	('---', 'NOLEGGI', 'Sub 2', 'RIVENDITORE', 'cf_902', 'RD / DIST'),
	('---', 'GARDEN', 'Sub 2', 'RIVENDITORE', 'cf_902', 'RD / DIST'),
	('---', 'CONAGR', 'Sub 2', 'RIVENDITORE', 'cf_902', 'RD / DIST'),
	('---', 'RIVENDITORE MACCHINE LEGNO', 'Sub 2', 'RIVENDITORE', 'cf_902', 'RD / DIST'),
	('RIVENDITE SISTEMI ANTICADUTA', 'RIVEANTICAD', 'Sub 3', 'RIVENDITORE', 'cf_903', 'RP / PROG'),
	('INDEXTRALEGNO', 'INDEXTRALEGNO', 'Sub 3', 'UTILIZZATORE', 'cf_903', 'RP / PROG'),
	('CARPMET', 'CARPMET', 'Sub 3', 'UTILIZZATORE', 'cf_903', 'RP / PROG'),
	('IMPEDILE', 'IMPEDILE', 'Sub 3', 'UTILIZZATORE', 'cf_903', 'RP / PROG'),
	('LATTONERIA', 'LATTONERIA', 'Sub 3', 'UTILIZZATORE', 'cf_903', 'RP / PROG'),
	('IMPERM', 'IMPERM', 'Sub 3', 'UTILIZZATORE', 'cf_903', 'RP / PROG'),
	('AMIANTO', 'AMIANTO', 'Sub 3', 'UTILIZZATORE', 'cf_903', 'RP / PROG'),
	('IMPIANTISTI', 'IMPIANTISTI', 'Sub 3', 'UTILIZZATORE', 'cf_903', 'RP / PROG'),
	('MONTANTICAD', 'MONTANTICAD', 'Sub 3', 'UTILIZZATORE', 'cf_903', 'RP / PROG'),
	('AMMINISTRCOND', 'AMMINISTRCOND', 'Sub 3', 'INFLUENZATORI', 'cf_903', 'RP / PROG'),
	('PROGANTICAD', 'PROGANTICAD', 'Sub 3', 'PROGETTISTI', 'cf_903', 'RP / PROG'),
	('---', 'RESPSICUR', 'Sub 3', 'PROGETTISTI', 'cf_903', 'RP / PROG'),
	('---', 'CLIFINALE', 'Sub 3', 'INFLUENZATORI', 'cf_903', 'RP / PROG'),
	('ING.', 'ING.', 'Sub 4', 'PROGETTISTI', 'cf_895', 'Università'),
	('ARCH.', 'ARCH.', 'Sub 4', 'PROGETTISTI', 'cf_895', 'Università'),
	('GEOM.', 'GEOM.', 'Sub 4', 'PROGETTISTI', 'cf_895', 'Università'),
	('---', 'PRIVATI', 'Sub 5', 'INFLUENZATORI', 'cf_904', 'RE / ALTRO'),
	('IMPEDILE', 'EILDENOTETTI', 'Sub 5', 'UTILIZZATORE', 'cf_904', 'RE / ALTRO'),
	('FALEGNAME/SERRAMENTISTA', 'CARTONG', 'Sub 5', 'UTILIZZATORE', 'cf_904', 'RE / ALTRO'),
	('UNIVERSITA', 'UNIVERSITA', 'Sub 5', 'INFLUENZATORI', 'cf_904', 'RE / ALTRO'),
	('SCUOLEPROF', 'SCUOLEPROF', 'Sub 5', 'INFLUENZATORI', 'cf_904', 'RE / ALTRO'),
	('ENTEPUBB', 'ENTEPUBB', 'Sub 5', 'INFLUENZATORI', 'cf_904', 'RE / ALTRO'),
	('---', 'COOPERAT', 'Sub 5', 'INFLUENZATORI', 'cf_904', 'RE / ALTRO'),
	('ORDINI CATEGORIA/ASSOCIAZIONI', 'ORDINI DI CATEGORIA', 'Sub 5', 'INFLUENZATORI', 'cf_904', 'RE / ALTRO'),
	('ORDINI CATEGORIA/ASSOCIAZIONI', 'ASSOCIAZIONE', 'Sub 5', 'INFLUENZATORI', 'cf_904', 'RE / ALTRO'),
	('---', '', '---', '---', 'cf_894', 'RC / CARP'),
	('---', '', '---', '---', 'cf_895', 'Università'),
	('---', '', '---', '---', 'cf_902', 'RD / DIST'),
	('---', '', '---', '---', 'cf_903', 'RP / PROG'),
	('---', '', '---', '---', 'cf_904', 'RE / ALTRO'),
	('---', '---', '---', '---', 'cf_894', 'RC / CARP'),
	('---', '---', '---', '---', 'cf_895', 'Università'),
	('---', '---', '---', '---', 'cf_902', 'RD / DIST'),
	('---', '---', '---', '---', 'cf_903', 'RP / PROG'),
	('---', '---', '---', '---', 'cf_904', 'RE / ALTRO')