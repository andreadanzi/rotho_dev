Set WshShell = CreateObject("WScript.Shell")
WshShell.Run chr(34) & "C:\xampp\htdocs\plugins\erpconnector\Accounts_import\Accounts.bat" & Chr(34), 0
Set WshShell = Nothing