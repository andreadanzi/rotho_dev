<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************
*  Module       : Helpdesk
*  Language     : Español
*  Version      : 5.4.0
*  Created Date : 2007-03-30
*  Author       : Rafael Soler
*  Last change  : 2012-02-27
*  Author       : Joe Bordes
 ********************************************************************************/

$mod_strings = Array(
// Added in release 4.0
'LBL_MODULE_NAME' => 'Incidencias',
'LBL_GROUP' => 'Grupo',
'LBL_ACCOUNT_NAME' => 'Nombre de Cuenta',
'LBL_CONTACT_NAME' => 'Nombre de Contacto',
'LBL_SUBJECT' => 'Asunto',
'LBL_NEW_FORM_TITLE' => 'Nuevo Parte',
'LBL_DESCRIPTION' => 'Descripción',
'NTC_DELETE_CONFIRMATION' => '¿Está seguro que desea eliminar este registro?',
'LBL_CUSTOM_FIELD_SETTINGS' => 'Configuración de campos personalizados:',
'LBL_PICKLIST_FIELD_SETTINGS' => 'Configuración de Campos de Lista:',
'Leads' => 'Pre-Contacto',
'Accounts' => 'Cuenta',
'Contacts' => 'Contacto',
'Opportunities' => 'Oportunidad',
'LBL_CUSTOM_INFORMATION' => 'Información personalizada',
'LBL_DESCRIPTION_INFORMATION' => 'Incidencia a Resolver',

'LBL_ACCOUNT' => 'Cuenta',
'LBL_OPPURTUNITY' => 'Oportunidad',
'LBL_PRODUCT' => 'Producto',

'LBL_COLON' => ':',
'LBL_TICKET' => 'Incidencia',
'LBL_CONTACT' => 'Contacto',
'LBL_STATUS' => 'Estado',
'LBL_ASSIGNED_TO' => 'Asignado a',
'LBL_FAQ' => 'FAQ',
'LBL_VIEW_FAQS' => 'Ver FAQs',
'LBL_ADD_FAQS' => 'Añadir FAQs',
'LBL_FAQ_CATEGORIES' => 'Categorías FAQs',

'LBL_PRIORITY' => 'Prioridad',
'LBL_CATEGORY' => 'Categoría',

'LBL_ANSWER' => 'Respuesta',
'LBL_COMMENTS' => 'COMENTARIOS',

'LBL_AUTHOR' => 'Autor',
'LBL_QUESTION' => 'Pregunta',

//Added vtiger_fields for File Attachment and Mail send in Tickets
'LBL_ATTACHMENTS' => 'Adjuntos',
'LBL_NEW_ATTACHMENT' => 'Nuevo Adjunto',
'LBL_SEND_MAIL' => 'Enviar Email',

//Added vtiger_fields for search option  in TicketsList -- 4Beta
'LBL_CREATED_DATE' => 'Fecha de Creación',
'LBL_IS' => 'es',
'LBL_IS_NOT' => 'no es',
'LBL_IS_BEFORE' => 'es antes',
'LBL_IS_AFTER' => 'es después',
'LBL_STATISTICS' => 'Estadísticas',
'LBL_TICKET_ID' => 'Nº de Parte',
'LBL_MY_TICKETS' => 'Mis Partes',
'LBL_MY_FAQ' => 'Mis Faq\'s',
'LBL_ESTIMATED_FINISHING_TIME' => 'Tiempo estimado de resolución',
'LBL_SELECT_TICKET' => 'Seleccionar Parte',
'LBL_CHANGE_OWNER' => 'Modificar Propietario',
'LBL_CHANGE_STATUS' => 'Modificar Estado',
'LBL_TICKET_TITLE' => 'Referencia',
'LBL_TICKET_DESCRIPTION' => 'Explicación',
'LBL_TICKET_CATEGORY' => 'Categoría',
'LBL_TICKET_PRIORITY' => 'Prioridad',

//Added vtiger_fields after 4 -- Beta
'LBL_NEW_TICKET' => 'Nuevo Parte',
'LBL_TICKET_INFORMATION' => 'Información del Parte',

'LBL_LIST_FORM_TITLE' => 'Lista de Partes',
'LBL_SEARCH_FORM_TITLE' => 'Buscar Parte',

//Added vtiger_fields after RC1 - Release
'LBL_CHOOSE_A_VIEW' => 'Seleccionar una vista...',
'LBL_ALL' => 'Todos',
'LBL_LOW' => 'Baja',
'LBL_MEDIUM' => 'Media',
'LBL_HIGH' => 'Alta',
'LBL_CRITICAL' => 'Crítica',
//Added vtiger_fields for 4GA
'Assigned To' => 'Asignado a',
'Contact Name' => 'Nombre de Contacto',
'Priority' => 'Prioridad',
'Status' => 'Estado',
'Category' => 'Categoría',
'Update History' => 'Histórico de Actualizaciones',
'Created Time' => 'Fecha de Creación',
'Modified Time' => 'Última Modificación',
'Title' => ' Referencia',
'Description' => 'Incidencia',

'LBL_TICKET_CUMULATIVE_STATISTICS' => 'Estadísticas acumuladas de Incidencias:',
'LBL_CASE_TOPIC' => 'Tópico de Incidentes',
'LBL_OPEN' => 'Abierto',
'LBL_CLOSED' => 'Cerrado',
'LBL_TOTAL' => 'Total',
'LBL_TICKET_HISTORY' => 'Historia del Parte:',
'LBL_CATEGORIES' => 'Categorías',
'LBL_PRIORITIES' => 'Prioridades',
'LBL_SUPPORTERS' => 'Agentes',

//Added vtiger_fields after 4_0_1
'LBL_TICKET_RESOLUTION' => 'Solución Propuesta',
'Solution' => 'Solución',
'Add Comment' => 'Añadir comentario',
'LBL_ADD_COMMENT' => 'Añadir comentario',

//Added for 4.2 Release -- CustomView
'Ticket ID' => 'Nº de Parte',
'Subject' => 'Asunto',

//Added after 4.2 alpha
'Severity' => 'Urgencia',
'Product Name' => 'Producto',
'Related To' => 'Relacionado con',
'LBL_MORE' => 'Más',

'LBL_TICKETS' => 'Partes',

//Added on 09-12-2005
'LBL_CUMULATIVE_STATISTICS' => 'Estadísticas Acumuladas',

//Added on 12-12-2005
'LBL_CONVERT_AS_FAQ_BUTTON_TITLE' => 'Convertir en FAQ',
'LBL_CONVERT_AS_FAQ_BUTTON_KEY' => 'C',
'LBL_CONVERT_AS_FAQ_BUTTON_LABEL' => 'Convertir en FAQ',
'Attachment' => 'Adjunto',
'LBL_COMMENT_INFORMATION' => 'Comentarios al Parte',

//Added for existing picklist entries

'Big Problem' => 'Gran Problema',
'Small Problem' => 'Problema Pequeño',
'Other Problem' => 'Otro Problema',
'Low' => 'Baja',

'Normal' => 'Normal',
'High' => 'Alta',
'Urgent' => 'Urgente',

'Minor' => 'Menor',
'Major' => 'Mayor',
'Feature' => 'Característica',
'Critical' => 'Critica',

'Open' => 'Abierta',
'In Progress' => 'En Progreso',
'Wait For Response' => 'Esperando Respuesta',
'Closed' => 'Cerrada',

//added to support i18n in ticket mails
'Hi' => 'Hola',
'Dear' => 'Estimado',
'LBL_PORTAL_BODY_MAILINFO' => 'El Parte ha sido',
'LBL_DETAIL' => ', los detalles son:',
'LBL_REGARDS' => 'Atentamente,',
'LBL_TEAM' => 'Equipo de Soporte Técnico',
'LBL_TICKET_DETAILS' => 'Detalles de Parte',
'LBL_SUBJECT' => 'Asunto : ',
'created' => 'creado',
'replied' => 'respondido',
'reply' => 'Hay una respuesta a',
'customer_portal' => 'en el "Portal del Cliente" en Vtiger',
'link' => 'Utilice el siguiente enlace para ver las respuestas dadas:',
'Thanks' => 'Gracias',
'Support_team' => 'Equipo de Soporte Técnico',
'The comments are' => 'Los comentarios son',
'Ticket Title' => 'Título Incidencia',
'Re' => 'Re :',
// Added/Updated for vtiger CRM 5.0.4

//this label for customerportal.
'LBL_STATUS_CLOSED' =>'Closed',//Do not convert this label. This is used to check the status. If the status 'Closed' is changed in vtigerCRM server side then you have to change in customerportal language file also.
'LBL_STATUS_UPDATE' => 'Estado de Parte actualizado a',
'LBL_COULDNOT_CLOSED' => 'El Parte no puede ser',
'LBL_CUSTOMER_COMMENTS' => 'EL Cliente ha incluido la siguiente información a su respuesta:',
'LBL_RESPOND'=> 'Por favor responde al parte lo más pronto posible.',
'LBL_REGARDS' =>'Saludos Cordiales,',
'LBL_SUPPORT_ADMIN' => 'Atención al Cliente',
'LBL_RESPONDTO_TICKETID' =>'Responde al Nº de Parte',
'LBL_CUSTOMER_PORTAL' => 'en el Portal del Cliente - URGENTE', 
'LBL_LOGIN_DETAILS' => 'Sus datos de conexión al Portal de Cliente son:',
'LBL_MAIL_COULDNOT_SENT' =>'No se puede enviar el correo',
'LBL_USERNAME' => 'Usuario :',
'LBL_PASSWORD' => 'Contraseña :',
'LBL_SUBJECT_PORTAL_LOGIN_DETAILS' => 'Datos de Conexión al Portal del Cliente',
'LBL_GIVE_MAILID' => 'Introduzca dirección de email',
'LBL_CHECK_MAILID' => 'Compruebe su dirección de email para el Portal del Cliente',
'LBL_LOGIN_REVOKED' => 'Datos de Usuario no válidos, consulte con su administrador.',
'LBL_MAIL_SENT' => 'Se le ha enviado un correo con los datos de conexión al Portal del Cliente',
'LBL_ALTBODY' => 'Este es el mensaje de correo para los clientes que no soportan HTML',
'Hours' => 'Horas',
'Days' => 'Días',
// Added after 5.0.4 GA

// Module Sequence Numbering
'Ticket No' => 'Núm. Incidencia',
// END
'From Portal' => 'Proviene del Portal',
'HelpDesk ID' => 'Id Incidencia',

'projects'=>'Proyectos',
'choose'=>'Elegir',
'Internal Project Nummer'=>'Número de proyecto interno',
'External Project Nummer'=>'Número de proyecto externo',
'LBL_TimeCard'=>'Intervención',
'LBL_TimeCards'=>'Intervención',
'LBL_TCDate'=>'Fecha',
'LBL_TCWorker'=>'Usuario',
'LBL_TCUnits'=>'Unidades',
'LBL_TCTime'=>'Tiempo',
'LBL_TCType'=>'Tipo',
'LBL_TCMoveUp'=>'Subir',
'LBL_TCMoveDown'=>'Bajar',
'LBL_NewTC'=>'Nueva intervención',
'LBL_NewState'=>'Cambiar el estado del Ticket en',
'LBL_ReassignTicketTo'=>'Asignar ticket a',
'LBL_CHANGE'=>'Cambio de Propietario',
'TimeCard_DELETE_CONFIRMATION'=>'¿Está seguro que desea eliminar el número de intervención?',
'TimeCard_Question'=>'?',
'LBL_CONVERT_AS_SALESORDER_BUTTON_TITLE'=>'Convertir en pedido de venta',
'LBL_CONVERT_AS_SALESORDER_BUTTON_KEY'=>'S',
'LBL_CONVERT_AS_SALESORDER_BUTTON_LABEL'=>'Convertir en pedido de venta',
'LBL_PDF_WITH_COMMENTS'=>'ticket con comentarios',
'LBL_PDF_WITHOUT_COMMENTS'=>'ticket sin comentarios',
'LBL_AS_PDF'=>'(Pdf)',
'LBL_HelpDesk_Receipt'=>'HOJA DE TRABAJO',
'MSG_NoSalesEntity'=>'No asociada',
'ElapsedTime'=>'Tiempo transcurrido',
'FinishDate'=>'Fecha de fin',
'TimeDedicated'=>'Tiempo Dedicado',
'WorkMaterial'=>'Trabajo y Material',
'sortReturn'=>'Atrás',
'sortReturnKey'=>'V',
'sortClose'=>'Cerrar',
'sortCloseKey'=>'X',
'sortReport'=>'Informe',
'sortReportKey'=>'L',
'sortSeeWO'=>'Ver órdenes de trabajo',
'sortSeeWOKey'=>'V',
'sortUserNotDefined'=>'Usuario no definido',
'sortWO'=>'Clasificar órdenes de trabajo',
'sortWOforUser'=>'Clasificar órdenes de trabajo para el usuario',
'sortNoWOPending'=>'No hay órdenes de trabajo pendientes',
'sortPendingWO'=>'Órdenes de trabajo pendientes para el usuario',
'sortOrder'=>'Pedidos',
'sortTitle'=>'Título',
'sortMaterial'=>'Material',
'Comment'=>'Comentario',
'InvoiceLine'=>'Línea de Factura',
'BlockedComment'=>'Comentario Bloqueado',
'LBL_SUPPORT_EXPIRY_DATE'=>'Apoyar Fecha de caducidad',
'Support Expiry Date'=>'Apoyar Fecha de caducidad',
'CONVERT_SALESORDER'=>'Convertir en orden de venta',
'CONVERT_INVOICE'=>'Convertir en factura',
'LBL_HELPINFO_HOURS'=>'Esto asigna las horas estimadas para el ticket.<br> Cuando el mismo ticket se agrega a un contrato de servicios, con base en la Unidad de Seguimiento del Contrato de Servicio, la venta se actualiza cada vez que un ticket está cerrado.',
'LBL_HELPINFO_DAYS'=>'Esto asigna las horas estimadas para el ticket.<br> Cuando el mismo ticket se agrega a un contrato de servicios, con base en la Unidad de Seguimiento del Contrato de Servicio, la venta se actualiza cada vez que un ticket está cerrado.',
'Ticket created. Assigned to'=>'Ticket creado. Asignado a',
'group'=>'grupo',
'user'=>'usuario',
'by'=>'por',
'on'=>'en',
'Status Changed to'=>'Ticket cambiado a',
'Priority Changed to'=>'Prioridad cambiada a',
'Severity Changed to'=>'Gravedad cambiada a',
'Category Changed to'=>'Categoría cambiada a',
'Transferred to group'=>'Transferido al grupo',
'Transferred to user'=>'Transferido al usuario',
'ProjectPlan'=>'Proyecto',
'ProjectTask'=>'Tareas del Proyecto',
'SLA'=>'Gestión de SLA',
'SINGLE_SLA'=>'Gestión de SLA',
'LBL_SLA'=>'Tiempos de SLA',
'Time Elapsed'=>'Tiempo transcurrido',
'Time remaining'=>'Tiempo restante',
'SLA start date'=>'SLA fecha de inicio',
'SLA end date'=>'SLA fecha de finalización',
'Update Time'=>'Tiempo de actualización',
'SLA Estimated Time'=>'Tiempo estimado SLA',
'Due Date'=>'Fecha de vencimiento',
'Due Time'=>'tiempo (hh: mm)',
'Time Last Status Change'=>'Tiempo último cambio de estado',
'Time Elapsed Last Status Change'=>'Tiempo transcurrido último cambio de estado',
'Reset SLA'=>'Cambiar SLA',
'End SLA'=>'Fin SLA',
'Idle Time Elapsed'=>'tiempo de inactividad transcurrido',
'Out SLA Time Elapsed'=>'Salida SLA Tiempo transcurrido',


);

?>
