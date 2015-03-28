<?php

class MySql_Workflow {

    private $driver = 'mysql';    # @var string database driver
    private $host = null;         # @var string host adress
    private $user = null;         # @var string database user 
    private $database = null;     # @var string database 
    private $password = null;     # @var string database password
    private $downloadPath = null; # @var string download folder path
    private $pdo = null;          # @var object 
   
     /*
     * @param string $ftpServer
     * @param string $username
     * @param string $password
     * @param int    $port
     * @return void
     */

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
    
    /*
     * @param string $path
     * @return void
     */
    
    public function setDownloadPath($path){
        $this->downloadPath = dirname( dirname( __FILE__ ) ) . $path;
    }
    
    /*
     * @return void
     */

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
