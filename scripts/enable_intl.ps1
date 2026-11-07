(Get-Content 'C:\Program Files\php\php.ini') -replace ';extension=intl', 'extension=intl' | Set-Content 'C:\Program Files\php\php.ini'
