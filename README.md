# Using Backup Script 

### Class require

require_once 'libs/class.MySql_Backup_Workflow.php';

### Databasebackup

$mysqlWorkflow = new MySql_Backup_Workflow('host','database','user','password');

$mysqlWorkflow->setDownloadPath('/backups/download/musterwebsite/sql/');

$mysqlWorkflow->databaseExport();
