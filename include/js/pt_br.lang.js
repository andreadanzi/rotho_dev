﻿/*********************************************************************************

** The contents of this file are subject to the vtiger Crmvillage.biz Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  Crmvillage.biz Open Source
 * The Initial Developer of the Original Code is Crmvillage.biz.
 * Portions created by vtiger are Copyright (C) Crmvillage.biz.
 * All Rights Reserved.
 ********************************************************************************/

var alert_arr = {       
	DELETE:'Deseja realmente apagar o registro selecionado ',
	RECORDS:' registros?',
	SELECT:"Por favor, selecione pelo menos uma entidade'",
	DELETE_ACCOUNT:"Apagando esta(s) Conta(s) serão removidas as Cotações e Oportunidades relacionadas. Deseja realmente apagar o registro selecionado?",
	DELETE_VENDOR:"Apagando este(s) Fornecedor(s) serão removidos os Pedidos de Compras relacionados. Deseja realmente apagar o registro selecionado? ",
	SELECT_MAILID:'Por favor selecionar um identificativo de Email Válido',
	//crmv@13847
	OVERWRITE_EXISTING_ACCOUNT1:"Sobrescrever o endereço existente com o da Conta selecionada (",
	OVERWRITE_EXISTING_ACCOUNT2:') selecionado/a? \ clicando Anular o elemento será do mesmo jeito relacionado mantenendo indepedente os endereços.',
	//crmv@13847e
	MISSING_FIELDS:'Campos requeridos ausentes:',
	NOT_ALLOWED_TO_EDIT:'você não tem permissão para editar este campo',
	NOT_ALLOWED_TO_EDIT_FIELDS:'você não tem permissão para o(s) campo(s)',
	COLUMNS_CANNOT_BE_EMPTY:'A coluna selecionada não pode estar vazia',
	CANNOT_BE_EMPTY:" não pode estar vazio",
	VISITREPORTNOTHINGFOUND:'Invalid calendar entry',
	VISITREPORTWRONGTYPE:'Invalid Event Type or Account', 
	CANNOT_BE_NONE:" não pode ser nula",
	ENTER_VALID:'Por favor, digite um registro válido ',
	SHOULDBE_LESS:' deve ser menos que',
	SHOULDBE_LESS_EQUAL:' deve ser menos que ou igual a ',
	SHOULDBE_EQUAL:' deve ser igual a',
	SHOULDBE_GREATER:' deve ser maior que ',
	SHOULDBE_GREATER_EQUAL:' deve ser maior que ou igual a ',
	INVALID:'Inválido ',
	EXCEEDS_MAX:' limite máximo excedido ',
	OUT_OF_RANGE:' está fora do limite ',
	SHOULDNOTBE_EQUAL:' não deve ser igual a',
	PORTAL_PROVIDE_EMAILID:"Por gentileza, forneça um e-mail válido para habilitar Usuário Portal",
	ADD_CONFIRMATION:'Deseja realmente adicionar o registro selecionado ',
	ACCOUNTNAME_CANNOT_EMPTY:"Nome Conta não pode estar vazio",
	CANT_SELECT_CONTACTS:"você não pode selecionar contatos relacionados do Lead",
	LBL_THIS:'Este ',
	DOESNOT_HAVE_MAILIDS:" não tem nenhum e-mail",
	ARE_YOU_SURE:'Você tem certeza?',
	DOESNOT_HAVE_AN_MAILID:'" '+"não possui uma identificação de e-mail",
	MISSING_REQUIRED_FIELDS:'Campos requeridos ausentes: ',
	READONLY:"somente para leitura",
	SELECT_ATLEAST_ONE_USER:'Por favor, selecione pelo menos um Usuário',
	DISABLE_SHARING_CONFIRMATION:'Deseja realmente desabilitar compartilhamento para o registro selecionado ',
	USERS:' usuário(s) ?',
	ENDTIME_GREATER_THAN_STARTTIME:'Hora Final deve ser maior que a Hora Inicial ',
	FOLLOWUPTIME_GREATER_THAN_STARTTIME:'Hora do Followup deve ser maior que Hora Inicial ',
	MISSING_EVENT_NAME:"Nome do Evento ausente",
	EVENT_TYPE_NOT_SELECTED:"Tipo Evento não está selecionado",
	OPPORTUNITYNAME_CANNOT_BE_EMPTY:"O nome da Oportunidade não pode estar vazio",
	CLOSEDATE_CANNOT_BE_EMPTY:"Campo Data Fechamento não pode estar vazio",
	SITEURL_CANNOT_BE_EMPTY:"URL do Site não pode estar vazia",
	SITENAME_CANNOT_BE_EMPTY:"Nome do Site não pode estar vazio",
	LISTPRICE_CANNOT_BE_EMPTY:"Lista de Preços não pode estar vazia",
	INVALID_LIST_PRICE:'Lista de Preços inválida',
	PROBLEM_ACCESSSING_URL:"Problema acessando a URL: ",
	CODE:' Código: ',
	WISH_TO_QUALIFY_MAIL_AS_CONTACT:'Deseja realmente qualificar este Email conforme Contato?',
	SELECT_ATLEAST_ONEMSG_TO_DEL:'Por favor, selecione pelo menos uma mensagem para apagar',
	ERROR:'Erro',
	FIELD_TYPE_NOT_SELECTED:'O Tipo de Campo não está selecionado',
	SPECIAL_CHARACTERS_NOT_ALLOWED:'Caracteres Especiais não são permitidos no campo Rótulo',
	PICKLIST_CANNOT_BE_EMPTY:"Valor da Lista de Opções não pode estar vazio",
	DUPLICATE_VALUES_FOUND:' valor duplicado não foi encontrado',
	DUPLICATE_MAPPING_ACCOUNTS:'Duplicar mapeamento para Contas!',
	DUPLICATE_MAPPING_CONTACTS:'Duplicar mapeamento para Contatos!',
	DUPLICATE_MAPPING_POTENTIAL:"Duplicar mapeamento para Oportunidades'!",
	ERROR_WHILE_EDITING:'Erro durante Edição',
	CURRENCY_CHANGE_INFO:"Mudanças na Moeda realizadas com Sucesso",
	CURRENCY_CONVERSION_INFO:'você deseja utilizar o Dolar US$ como Moeda? \n Clique OK para permanecer como US$, Cancelar para mudar taxa de câmbio da Moeda.',
	THE_EMAILID: "O ID do e-mail \'",
	EMAIL_FIELD_INVALID:"\' no campo do e-mail é inválido.",
	MISSING_REPORT_NAME:'Nome do Relatório ausente',
	REPORT_NAME_EXISTS:"Nome do Relatório já existe, tente novamente...",
	WANT_TO_CHANGE_CONTACT_ADDR:'Deseja mudar o endereço do Contato relacionado com esta Conta?',
	SURE_TO_DELETE:'Deseja realmente apagar ?',
	NO_PRODUCT_SELECTED:'Nenhum produto foi selecionado. Selecione pelo menos um Produto',
	VALID_FINAL_PERCENT:'Digite percentual de Desconto Final válido',
	VALID_FINAL_AMOUNT:"Digite o Total do Desconto Final",
	VALID_SHIPPING_CHARGE:'Digite um valor de Frete válido',
	VALID_ADJUSTMENT:"Digite um Ajuste válido",
	WANT_TO_CONTINUE:'Deseja Continuar?',
	ENTER_VALID_TAX:'Por favor, digite um valor de Imposto válido',
	VALID_TAX_NAME:'Digite um nome de Imposto válido',
	CORRECT_TAX_VALUE:'Digite um valor de Imposto correto',
	ENTER_POSITIVE_VALUE:'Por favor, digite um valor positivo',
	LABEL_SHOULDNOT_EMPTY:"O nome do Rótulo do Imposto não pode estar vazio",
	NOT_VALID_ENTRY:"não é uma entrada válida. Por favor, digite o valor correto",
	VALID_DISCOUNT_PERCENT:'Digite um percentual de Desconto válido',
	VALID_DISCOUNT_AMOUNT:"Digite um total de Desconto válido",
	SELECT_TEMPLATE_TO_MERGE:'Por favor, selecione um modelo para mesclar',
	SELECTED_MORE_THAN_ONCE:"Você selecionou o(s) seguinte(s) produto(s) mais de uma vez.",
	YES:"sim'",
	NO:'não',
	MAIL:'correio',
	EQUALS:'igual',
	NOT_EQUALS_TO:'diferente de',
	STARTS_WITH:'iniciar com',
	CONTAINS:'contem',	
	DOES_NOT_CONTAINS:'não contem',
	LESS_THAN:'menor que',
	GREATER_THAN:'maior que',
	LESS_OR_EQUALS:'menor ou igual',
	GREATER_OR_EQUALS:'maior ou igual',
	NO_SPECIAL_CHARS:'Caracteres Especiais não são permitos na Série da Fatura',
	SHARED_EVENT_DEL_MSG:" O Usuário não tem permissaõ para apagar o registro",
	PLS_SELECT_VALID_FILE:'Por favor, selecione um arquivo com a seguinte extensão:\n',
	NO_SPECIAL:'Caracteres Especiais não são permitidos',
	NO_QUOTES:'Apóstrofo (\'), Aspas (") e o símbolo de soma (+) não são permitidos ',
	IN_PROFILENAME:' no Nome do Perfil',
	IN_GROUPNAME:' no Nome do Grupo',
	IN_ROLENAME:' no Nome da Função',
	VALID_TAX_PERCENT:'Digite um percentual de Imposto válido',
	VALID_SH_TAX:'Digite um imposto válido para Fretes ',
	ROLE_DRAG_ERR_MSG:"você não pode mover um Nó Pai sob um Nó Filho",
	LBL_DEL:'apagar',
	VALID_DATA :' Digite dados válidos, por favor, tente novamente... ',
	STDFILTER : 'Filtros Padrões',
	STARTDATE : 'Data Inicial',
	ENDDATE : 'Data Final',
	START_DATE_TIME : 'Data e Hora Inicial',
	START_TIME : 'Hora Inicial',
	DATE_SHOULDNOT_PAST :"Data e hora atuais para Atividades com status de Planejado",
	TIME_SHOULDNOT_PAST :"Hora atual para Atividades com status de Planejado",
	LBL_AND : 'E',
	LBL_ENTER_VALID_PORT: 'Por favor, digite um número de porta válido',
	IN_USERNAME :' em Nome Usuário ',
	LBL_ENTER_VALID_NO: 'Por favor, digite um número válido',
	LBL_PROVIDE_YES_NO: ' Valor inválido.\n Por favor, informe Sim ou não',
	LBL_SELECT_CRITERIA: ' Critério inválido.\n Por favor selecione o critério',
	LBL_WRONG_IMAGE_TYPE: 'Tipo de arquivo imagem permitidos para Contatos - jpeg, png, jpg, pjpeg, x-png or gif',
	SELECT_MAIL_MOVE: 'Por favor, selecione uma mensagem e então mova..',
	LBL_NOTSEARCH_WITHSEARCH_ALL: 'você não utilizou a pesquisa. Todos os dados serão Exportados em',
	LBL_NOTSEARCH_WITHSEARCH_CURRENTPAGE: 'você não utilizou a função de pesquisa. Mas selecionou com Opções de página pesquisa&página. Desta forma os registros na página atual serão Exportados em ',
	LBL_NO_DATA_SELECTED: 'Nenhum registro selecionado. Selecione pelo menos um registro para Exportar.',
	LBL_SEARCH_WITHOUTSEARCH_ALL: 'você utilizou a opção pesquisar mas não selecionou as Opções pesquisar & todos.\nvocê pode clicar em [ok] para exportar todos os dados ou pode clicar em [cancelar] e tentar novamente outro critério para exportar.',
	STOCK_IS_NOT_ENOUGH : 'A quantidade em Estoque não é suficiente',
	INVALID_QTY : 'Qde. inválida',
	LBL_SEARCH_WITHOUTSEARCH_CURRENTPAGE: 'você utilizou a opção pesquisar mas não selecionou as Opções pesquisa & página atual.\nvocê pode clicar em [ok] para exportar os dados da página atual ou\n você pode clicar em [cancelar] e tentar novamente outro critério para exportar.',
	LBL_SEARCH_WITHOUTSEARCH_ALL:'Você utilizou a opção pesquisar mas não selecionou sem pesquisa e todas as opções.\n você pode clicar em [ok] para exportar todos os dados ou\n clicar em [cancelar] e tentar novamente outro critério para exportar',
	LBL_SEARCH_WITHOUTSEARCH_CURRENTPAGE:'Você utilizou a opção pesquisar mas não selecionou sem pesquisa e opções para a página atual.\n você pode Clicar [ok] para exportar os dados da página atual ou\n clicar em [cancelar] e tentar novamente outro critério para exportar.',
	LBL_SELECT_COLUMN: 'Coluna inválida.\n Por gentileza, selecione a coluna',
	LBL_NOT_ACCESSIBLE : 'não Acessível',
	LBL_FILENAME_LENGTH_EXCEED_ERR: 'Nome do arquivo não pode exceder a 255 caracteres',
	LBL_DONT_HAVE_EMAIL_PERMISSION : 'você não tem permissão para campo Email logo não pode selecionar o Email',
	LBL_NO_FEEDS_SELECTED: 'Nenhum Alimentador Selecionado',
	LBL_SELECT_PICKLIST:'Por favor, selecione pelo menos um valor para apagar',
	LBL_CANT_REMOVE:'você não pode remover todos os valores',
	LBL_UTF8:'Por favor modificar o arquivo de configuração (situado na root de VTE CRM, com o nome config-inc.php) para o suporte ao set de caracteres UTF-8 e ebntão atualize a página',
	//------------------crmvillage 504 release start----------------------
	SPECIAL_CHARACTERS:'Os caracteres especiais',
        NOT_ALLOWED:'não são admitidos no rótulo do campo. Por favor, tente novamente com outros valores',
	LBL_NONE:'Nenhum',
        ENDS_WITH:'termina com',
	//------------------crmvillage 504 release stop----------------------
	POTENTIAL_AMOUNT_CANNOT_BE_EMPTY: 'Potential amount cannot be empty',	
	//crmv@7213
	LBL_ALERT_EXT_CODE: "Foi encontrado uma Conta com o mesmo código externo, deseja mesclar as duas Contas?",
	LBL_ALERT_EXT_CODE_NOTFOUND: " Não foi encontrada nenhuma Conta importada com este código externo, operação anulada",
	LBL_ALERT_EXT_CODE_COMMIT: " Mesclagem das Contas realizada com sucesso, será carregada a página da Conta importada",
	LBL_ALERT_EXT_CODE_FAIL:'Operação falida',
	LBL_ALERT_EXT_CODE_DUPLICATE: " Se você está tentando de refazer a mesclagem com aquele código ou de utilizar o código de uma Conta cancelada,a operação foi anulada. Limpar a lixeira e tentar novamente.",	//crmv@19653
	LBL_ALERT_EXT_CODE_SAVE: ' Deseja salvar as modificações?',
	LBL_ALERT_EXT_CODE_NOTFOUND_SAVE: "Não foi encontrada nenhuma Conta importada com este código externo, deseja salvar do mesmo modo código externo?",
	LBL_ALERT_EXT_CODE_NOTFOUND_SAVE2: "Não foi encontrada nenhuma Conta importada com este código externo, deseja salvar do mesmo modo as modificações à Conta?",
	LBL_ALERT_EXT_CODE_NO_PERMISSION: " Existe já uma Conta com o mesmo código atribuído a um outro Usuário,você portanto não tem permissão para executar a mesclagem.",	//crmv@19653
	//crmv@7213e
	//crmv@7216
	DOESNOT_HAVE_AN_FAXID:'" '+"não possui um número de fax",
	LBL_DONT_HAVE_FAX_PERMISSION:" Você não tem permissão para o campo Fax portanto não pode selecionar o número de Fax",
	//crmv@7216e
	//crmv@7217
	DOESNOT_HAVE_AN_SMSID:'" '+"Não possui um número de celular",
	LBL_DONT_HAVE_SMS_PERMISSION:" Você não tem permissão para o campo Celular portanto não pode selecionar o numero de sms",
	//crmv@7217e				
	
	NO_RULES_FOUND: 'Nenhuma regra encontrada para este módulo, você será endereçado ao formulário de criação de uma nova regra',
	//crmv@7221	
	//crmv@8719
	SAME_GROUPS: 'você terá que selecionar um registro de um mesmo grupo para mesclar',
	ATLEAST_TWO: 'Selecione pelo menos dois registros para mesclar',
	MAX_THREE: 'você tem permissão para selecionar no máximo três registros',
	MAX_RECORDS: 'você tem permissão para selecionar no máximo quatro registros',
	CON_MANDATORY: 'Selecione o campo obrigatório Sobrenome"',
	LE_MANDATORY: 'Selecione os campos obrigatórios Sobrenome e Empresa"',
	ACC_MANDATORY: 'Selecione o campo obrigatório Conta"',
	PRO_MANDATORY: 'Selecione o campo obrigatório "Nome Produto"',
	TIC_MANDATORY: 'Selecione o campo obrigatório "Titulo Ticket"',
	POTEN_MANDATORY: 'Selecione o campo obrigatório "Nome Oportunidade"',
	VEN_MANDATORY: 'Selecione o campo obrigatório "Nome fornecedor"',
	DEL_MANDATORY: 'você não tem permissão para apagar o campo obrigatório',
	//crmv@8719e	
		
	LBL_HIDEHIERARCH: 'Ocultar hierarquia',
	LBL_SHOWHIERARCH: 'Visualizar hierarquia',
	
	 LBL_NO_ROLES_SELECTED : 'Nenhuma Função foi selecionada, você deseja continuar?',
	 LBL_DUPLICATE_FOUND : 'Entrada duplicada encontrada para o valor ',
	 LBL_CANNOT_HAVE_EMPTY_VALUE : 'não é permitido valor vazio. Para apagar retorne e clique sobre o botão apagar.',
	 LBL_DUPLICATE_VALUE_EXISTS : 'Existe valor duplicado',
	 LBL_WANT_TO_DELETE : 'Esta ação apagará o(s) valor(es) da Lista de Opções selecionada para todas as Funções. você tem certeza que deseja continuar? ',
	 LBL_DELETE_ALL_WARNING : 'você selecionou todos os valores para serem apagados. você deseja continuar?',
	 LBL_PLEASE_CHANGE_REPLACEMENT : 'por gentileza, altere o valor substituido; ele também foi selecionado para ser apagado',
	 LBL_BLANK_REPLACEMENT : 'não é permitido selecionar valores em branco para substituição',
	 LBL_PLEASE_SELECT:'--por favor selecionar--',
	 MUST_BE_CHECKED: "Deve esatr selecionado",
	 CHARACTER: "caracteres",
	 LENGTH: "cumprimento",
	 	 
	/* For Multi-Currency Support */
	 MSG_CHANGE_CURRENCY_REVISE_UNIT_PRICE : 'O Preço Unitário de todas as Moedas serão corrigidos tomando-se por base a Moeda selecionada. Deseja confirmar?',
	
	 Select_one_record_as_parent_record : 'Selecionar um registro como registro pai',
	 RECURRING_FREQUENCY_NOT_PROVIDED : 'Frequência retorno não fornecida',
	 RECURRING_FREQNECY_NOT_ENABLED : 'Frequência retorno fornecida, mas retorno não está habilitado',
	/* Added for Documents module */
	 NO_SPECIAL_CHARS_DOCS : 'Caracteres especiais tais como aspas, contrabarra, símbolo de +, % e ? não são permitidos',
	 FOLDER_NAME_TOO_LONG : 'Nome Pasta é muito longo. Tente novamente!',
	 FOLDERNAME_EMPTY : 'O nome da Pasta não pode estar vazio',
	 DUPLICATE_FOLDER_NAME : 'você está tentando duplicar um nome de Pasta existente. Por gentileza, tente novamente !',
	 FOLDER_DESCRIPTION_TOO_LONG : 'Descrição da Pasta é muito longa. Tente novamente!',
	 NOT_PERMITTED : 'você não tem permissão para executar esta operação.',
	
	 ALL_FILTER_CREATION_DENIED : 'não é possível criar Visualização Personalizada utilizando o nome "Todos", tente utilizando um nome de Visualização diferente',
	 OPERATION_DENIED : 'você não tem permissão para realizar esta operação',
	 EMAIL_CHECK_MSG : 'Desabilitar acesso ao Portal para salvar o campo de e-mail vazio',
	 IS_PARENT : 'Este Produto possui Sub Produtos, você não tem permissão para selecionar um Pai para este Produto',
	
	/*layout Editor changes*/
	 BLOCK_NAME_CANNOT_BE_BLANK : 'Nome do Bloco não pode estar vazio',
	 ARE_YOU_SURE_YOU_WANT_TO_DELETE : 'você tem certeza que deseja apagar ?',
	 PLEASE_MOVE_THE_FIELDS_TO_ANOTHER_BLOCK : 'Por gentileza, mova o campo para outro Bloco',
	 ARE_YOU_SURE_YOU_WANT_TO_DELETE_BLOCK : 'você tem certeza que deseja apagar o Bloco ?',
	 LABEL_CANNOT_NOT_EMPTY : 'O Rótulo não pode ficar vazio',
	 LBL_TYPEALERT_1 : 'Desculpe, você não pode mapear o',
	 LBL_WITH : 'com',
	 LBL_TYPEALERT_2 : 'Tipo de dados. Por gentileza, mapeie os mesmos tipos de dados.',
	 LBL_LENGTHALERT : 'Desculpe, você não pode mapear campos com tamanho de caracteres diferentes. Por gentileza, mapeie os dados com pelo menos o mesmo tamanho de caracteres.',
	 LBL_DECIMALALERT : 'Desculpe, você não pode mapear campos com casas decimais diferentes. Por gentileza, mapeie os dados com pelo menos o mesmo número de casas decimais.',
	 FIELD_IS_MANDATORY : 'Campo obrigatório',
	 FIELD_IS_ACTIVE : 'O Campo está disponível para uso',
	 FIELD_IN_QCREATE : 'Presente no Criar Rápido',
	 FIELD_IS_MASSEDITABLE : 'Disponível para Edição em Massa',	
		
	 IS_MANDATORY_FIELD : 'É um Campo obrigatório',
	 CLOSEDATE_CANNOT_BE_EMPTY:"Fechando Dados não pode estar vazio",	 
	 AMOUNT_CANNOT_BE_EMPTY : 'Valor não pode estar vazio',
	 ARE_YOU_SURE:'você tem certeza que deseja apagar?',	 
	 LABEL_ALREADY_EXISTS : 'O Rótulo já existe. Por gentileza, especifique um Rótulo diferente',
	 LENGTH_OUT_OF_RANGE : 'O comprimento do Bloco deve ter menos de 50 caracteres',
	 LBL_SELECT_ONE_FILE : 'Por gentileza, selecione pelo menos um Arquivo',
	 LBL_UNABLE_TO_ADD_FOLDER : 'Impossível adicionar Pasta. Por favor, tente novamente.',
	 LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE_FOLDER : 'Tem certeza que deseja apagar a Pasta?',
	 LBL_ERROR_WHILE_DELETING_FOLDER : 'Erro enquanto a Pastaestava sendo apagada. Por favor, tente novamente.',
	 LBL_FILE_CAN_BE_DOWNLOAD : 'O Arquivo está disponível para Download',
	 LBL_DOCUMENT_LOST_INTEGRITY : 'Estes Documentos não estão disponíveis. Eles serão marcados como Inativos',
	 LBL_DOCUMENT_NOT_AVAILABLE : 'Este Documento não está disponível para Download',
	 LBL_FOLDER_SHOULD_BE_EMPTY : 'A Pasta deve estar limpa para ser removida!',
	
	 LBL_PLEASE_SELECT_FILE_TO_UPLOAD : 'Por gentileza, selecione o Arquivo para transferência.',
	 LBL_ARE_YOU_SURE_TO_MOVE_TO : 'Tem certeza que deseja mover o(s) Arquivo(s) para ',
	 LBL_FOLDER : ' pasta',
	 LBL_UNABLE_TO_UPDATE : 'Impossível atualizar! Por favor, tente novamente.',
	 LBL_BLANK_REPLACEMENT : 'não é permitido selecionar valores vazios para substituição',
	
	 LBL_IMAGE_DELETED : 'Imagem apagada',
	
	/* Tooltip management */
	ERR_FIELD_SELECTION : 'Algum erro na seleção do campo',
	
	/* Inventory validation strings */
	 NO_LINE_ITEM_SELECTED : 'Nenhum item da linha foi selecionado. Por favor, selecione pelo menos um item da linha.',
	 LINE_ITEM : 'Item Linha',
	 LIST_PRICE : 'Lista de Preços',
	
	/* Webmails */
	 LBL_PRINT_EMAIL : 'Imprimir',
	 LBL_DELETE_EMAIL : 'Apagar',
	 LBL_DOWNLOAD_ATTACHMENTS : 'Download Anexos',
	 LBL_QUALIFY_EMAIL : 'Qualificar',
	 LBL_FORWARD_EMAIL : 'Encaminhar',
	 LBL_REPLY_TO_SENDER : 'Responder Remetente',
	 LBL_REPLY_TO_ALL : 'Responder Todos',
	
	 LBL_WIDGET_HIDDEN : 'Widget Oculto',
	 LBL_RESTORE_FROM_PREFERENCES : 'você pode restaurá-lo para suas preferências',
	 ERR_HIDING : 'Erro enquanto ocultava o widget',
	 MSG_TRY_AGAIN : 'Por favor, tente novamente',
	
	 MSG_ENABLE_SINGLEPANE_VIEW : 'Habilitar Visualização Monolítica',
	 MSG_DISABLE_SINGLEPANE_VIEW : 'Desabilitar Visualização Monolítica',
	
	 MSG_FTP_BACKUP_DISABLED : 'Desabilitar Backup FTP',
	 MSG_LOCAL_BACKUP_DISABLED : 'Backup Local Desabilitado',
	 MSG_FTP_BACKUP_ENABLED : 'Backup FTP Habilitado',
	 MSG_LOCAL_BACKUP_ENABLED : 'Backup Local Habilitado',
	 MSG_CONFIRM_PATH : 'confirme com os detalhes do Path',
	 MSG_CONFIRM_FTP_DETAILS : 'confirme com os detalhes do FTP',
	
	 START_PERIOD_END_PERIOD_CANNOT_BE_EMPTY : 'Período inicial ou final não pode estar vazio',
	
	/* added to fix i18n issues with home page*/
	 LBL_ADD : 'Adicionar ',
	 Module : 'Módulo',
	 DashBoard : 'Painel',
	 RSS : 'RSS',
	 Default : 'Padrão',
	 Notebook : 'Bloco Notas',
	 SPECIAL_CHARS : '\ / < > + \' " ',
	 
	//------------------crmvillage 510 release start----------------------
	SPECIAL_CHARACTERS:'Os caracteres especiais',
    NOT_ALLOWED:'não são admitidos no rótulo do campo. POr favor tente novamente com outros valores',
	LBL_NONE:'Nenhum',
    ENDS_WITH:'termina com',
	ERR_PIVA:'CPJ inválido !',
	ERR_CF:'CPF inválido !',
	//------------------crmvillage 510 release stop----------------------	 	
	
	EXISTING_RECORD: 'Registro já existente no sistema com dados: ',
	EXISTING_SAVE: 'Deseja salvar do mesmo modo?',
	EXISTING_SAVE_CONVERTLEAD: ' Continuando o Contato e a eventual Oportunidade serão relacionadas a Conta existente.',	//crmv@19438
	no_valid_extension:'Extensão do arquivo inválida. As extensões permitidas são pdf,ps e tiff',
	//vtc
	PRODUCT_LINE_NAME:'Nome Produto',
	BUDGET_VALUE:'Valor Custo',
	//vtc end
	BETWEEN: 'entre',
	BEFORE: 'antes',
	AFTER: 'depois',
	'ERROR_DELETING_TRY_AGAIN': 'Erro durante a cancelação. Por favor, tente novamente.',
	'LBL_ENTER_WINDOW_TITLE': 'Por favor digitar o título da janela.',
	'LBL_SELECT_ONLY_FIELDS': 'Por favor selecione somente 2 campos.',
	'LBL_ENTER_RSS_URL':'Por favor digitar o endereço do RSS',
	'LBL_ENTER_URL':'Por favor digitar o endereço do website',
	'LBL_DELETED_SUCCESSFULLY':'Widget apagado com sucesso.',
	'LBL_ADD_HOME_WIDGET': 'Não foi possível adicionar o widget!! Por favor tente novamente.',
	//crmv@9434	
	LBL_STATUS_CHANGING: 'Modificar o status em ',
	LBL_STATUS_CHANGING_MOTIVATION: ' nota :',
	LBL_STATUS_PLEASE_SELECT_A_MODULE : 'Selecionar um módulo',
	LBL_STATUS_PLEASE_SELECT_A_ROLE : "Selecionar uma função",
	//crmv@9434 e
	//crmv@17749
	OVERWRITE_EXISTING_CONTACT1:"Sobrescrever o endereço existente com o endereço do Contato (",
	OVERWRITE_EXISTING_CONTACT2:') selecionado/a? \nClicando Anular o elemento será relacionado, mantendo independentes os endereços.',
	//crmv@17749e
	//crmv@27096
	LBL_MASS_EDIT_WITHOUT_WF_1:'Você selecionou mais de itens ',
	LBL_MASS_EDIT_WITHOUT_WF_2:', isso pode sobrecarregar o servidor. Vá para a update excluindo o fluxo de Workflow?',
	//crmv@27096e
	//crmv@16703
	SELECT_SMSID:'Por favor selecionar uma identificação de sms válido',
	NOTVALID_SMSID:'Número de Celular inválido',
	NULL_SMSID:'Nenhum número de Celular especificado'
	//crmv@16703e
};