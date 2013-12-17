Set WshShell = CreateObject("WScript.Shell")
WshShell.Run chr(34) & "C:\xampp\htdocs\plugins\erpconnector\Contacts_import\Contacts.bat" & Chr(34), 0
Set WshShell = Nothing