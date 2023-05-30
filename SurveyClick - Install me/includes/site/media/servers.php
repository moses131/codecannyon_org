<?php

namespace site\media;

class servers extends helper {

    protected $files;
    protected $uFiles;
    protected $attachment;
    protected $server;
    protected $server_id;
    protected $servers;
    protected $type;
    protected $typeId;
    protected $user;
    protected $limit        = 1;
    protected $options      = [];
    protected $isImage      = false;
    protected $imageSize    = '200x200';

    function __construct( array $files, int $attachment = NULL ) {
        parent::__construct();

        $this->files        = $files;
        $this->attachment   = $attachment;
        $this->server_id    = filters()->do_filter( 'use_media_server', 'default' );
        $this->servers      = mediaServers();
        $this->server       = $this->servers[$this->server_id] ?? NULL;
    }

    private function doUpload( array $files = NULL ) {
        if( !$this->server )
        throw new \Exception( t( 'Invalid server for media files' ) );

        if( $files )
        $this->files = array_merge( $this->files, $files );

        // remove files that are not accepted
        if( isset( $this->options['media-allowed'] ) ) {
            $extensions = array_map( 'trim', explode( ',', $this->options['media-allowed'] ) );
            foreach( $this->files as $file_id => $file ) {
                $file_ext = '.' . pathinfo( $file['name'], PATHINFO_EXTENSION );
                if( !in_array( $file_ext, $extensions ) ) {
                    unset( $this->files[$file_id] );
                }
            }
        }

        $max_size   = call_user_func( $this->server['max_size'] );
        $size       = array_sum( array_column( $this->files, 'size' ) );

        if( $size > $max_size ) {
            throw new \Exception( t( 'Your files are too big' ) );
        }

        try {
            if( $this->isImage )
            return call_user_func( $this->server['upload_image'], $this->files, $this->imageSize, $this->limit );

            return call_user_func( $this->server['upload_file'], $this->files, $this->limit );
        }

        catch( \Exception $e ) {
            throw new \Exception( $e->getMessage() );
        }
    }

    public function isImage( bool $isImage = true ) {
        $this->isImage = $isImage;
        return $this;
    }

    public function imageSize( string $size ) {
        $this->imageSize = $size;
        return $this;
    }

    public function setServer( int $server ) {
        $newServer = $this->servers[$server] ?? NULL;
        if( $newServer) {
            $this->server_id    = $server;
            $this->server       = $newServer;
        }

        return $this;
    }

    public function setAttachmentId() {
        $this->attachment = $id;
        return $this;
    }

    public function setOwnerId( int $user ) {
        $this->user = $user;
        return $this;
    }

    public function setType( int $type ) {
        $this->type = $type;
        return $this;
    }

    public function setTypeId( int $type_id ) {
        $this->typeId = $type_id;
        return $this;
    }

    public function setLimit( int $limit ) {
        $this->limit = $limit;
        return $this;
    }

    public function setOptions( array $options ) {
        $this->options = $options;
        return $this;
    }

    public function getUploadId( array $file = NULL ) {
        $this->uFiles = $this->doUpload( $file );
        return $this->insertFiles();
    }

}