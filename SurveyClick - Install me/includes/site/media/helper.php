<?php

namespace site\media;

class helper extends \util\db {

    function __construct( int $id = 0 ) {
        parent::__construct();
    }

    private function insertFile( string $local, array $file ) {
        $query  = 'INSERT INTO ' . $this->table( 'media' );
        $query .= ' (type, type_id, owner, mtype, ftype, src, size, extension, server) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $type       = $file['type'];
        $size       = $file['size'];
        $ftype      = $file['ftype'];
        $extension  = pathinfo( $file['name'], PATHINFO_EXTENSION );

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iiisssiss', $this->type, $this->typeId, $this->user, $type, $ftype, $local, $size, $extension, $this->server_id );
        $e  = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        if( $e ) {
            return $id;
        }

        return false;
    }

    protected function insertFiles() {
        $e_files = [];
        foreach( $this->uFiles as $files ) {
            $insert = false;
            foreach( $files['local'] as $file ) {
                if( ( $fid = $this->insertFile( $file, $files['file'] ) ) )
                $insert = true;
            }
            if( $insert )
            $e_files[$fid] = call_user_func( $this->server['file_preview'], $files, $this->options );
        }

        return $e_files;
    }

}