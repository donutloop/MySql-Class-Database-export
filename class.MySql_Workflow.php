<?php

class MySql_Workflow {

    private $driver = 'mysql';
    private $host = null;
    private $user = null;
    private $database = null;
    private $password = null;
    private $downloadPath = null;
    private $pdo = null;

    public function __construct( $host, $database, $user, $password, $driver = null ) {
        $this->host = $host;
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
        
        $this->setDownloadPath('/backups/download/');

        if( $driver != null ) {
            $this->driver = $driver;
        }

        $dsn = $this->driver . ':dbname=' . $this->database . ";host=" . $this->host;

        $this->pdo = new PDO( $dsn, $this->user, $this->password );
    }
    
    public function setDownloadPath($path){
        $this->downloadPath = dirname( dirname( __FILE__ ) ) . $path;
    }

    public function databaseExport() {
        $today = date( "Y-m-d" );
        $backupfile = fopen( $this->downloadPath. 'backup_' . $today . '.sql', 'x' );
        $tables = $this->pdo->query( 'SHOW TABLES' );
        $sql = null;

        foreach( $tables as $table ) {
            $create = $this->pdo->query( 'SHOW CREATE TABLE `' . $table[0] . '`' )->fetch();
            $sql .= $create['Create Table'] . ';' . PHP_EOL;
            fwrite( $backupfile, $sql );

            $rows = $this->pdo->query( 'SELECT * FROM `' . $table[0] . '`' );
            $rows->setFetchMode( PDO::FETCH_ASSOC );
            foreach( $rows as $row ) {
                $row = array_map( array( $this->pdo, 'quote' ), $row );
                $sql = 'INSERT INTO `' . $table[0] . '` (`' . implode( '`, `', array_keys( $row ) ) . '`) VALUES (' . implode( ', ', $row ) . ');' . PHP_EOL;
                fwrite( $backupfile, $sql );
            }
            
            $sql = PHP_EOL;
           fwrite( $backupfile, $sql );
    }
    fclose( $backupfile );        
    }

}
