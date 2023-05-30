<?php

namespace util;

class upload {

    private $uploads    = [];
    private $files      = [];
    private $uploadedf  = [];
    private $limit      = 99;
    private $accept     = [];
    private $errors     = [];
    
    function __construct( array $uploads, int $limit = 99 ) {
        $this->limit = $limit;
        $this->addUploads( $uploads );
    }

    public function setLimit( int $limit = 99 ) {
        $this->limit = $limit;
        return $this;
    }

    public function addUploads( array $uploads ) {
        $this->uploads = array_merge( $this->uploads, $uploads );
        array_slice( $this->uploads, 0, $this->limit );
        return $this;
    }

    public function accept( string $type, array $sizes = [ 'full' ] ) {
        $this->accept[$type] = $sizes;
        return $this;
    }

    public function acceptRemove( string $type ) {
        unset( $this->accept[$type] );
        return $this;
    }

    public function upload() {
        foreach( $this->uploads as $ufile ) {
            $this->addFile( $ufile );
        }

        foreach( $this->files as $type => $files ) {
            switch( $type ) {
                case 'image':
                    $proc = 'uploadImage';
                break;

                default:
                    $proc = 'uploadFile';
            }

            foreach( $files as $files_id => $file ) {
                call_user_func( [ $this, $proc ], $files_id, $file );
            }
        }

        return $this;
    }

    public function getUploadedFiles() {
        return $this->uploadedf;
    }

    public function getUploadedFilesCount() {
        return count( $this->uploadedf );
    }

    public function getUploadedFilesSerialized() {
        if( count( $this->uploadedf ) == 1 ) {
            return serialize( current( $this->uploadedf ) );
        } else if( count( $this->uploadedf ) ) {
            return serialize( $this->uploadedf );
        } else 

        return serialize( [] );
    }

    private function uploadImage( $files_id, $file ) {
        if( $file['extension'] == 'svg' )
        return $this->uploadFile( $files_id, $file );

        $handler    = new \util\image_handler( $file['tmp_name'], DIR );
        $sizes      = [ 'full' => [ 'makeImageMax' => [ 1000, implode( '/', [ UPLOADS_DIR, 'images', uniqid() ] ) ] ] ];
        if( isset( $this->accept['image'] ) && ( $usizes = $this->accept['image'] ) && !empty( $usizes ) && is_array( $usizes ) ) {
            $nsizes  = [];
            foreach( $usizes as $size ) {
                $dim = explode( 'x', $size );
                if( count( $dim ) == 2 ) {
                    $nsizes[$size] = [ (int) $dim[0], (int) $dim[1], implode( '/', [ UPLOADS_DIR, 'images2', uniqid() ] ) ];
                } else if( count( $dim ) == 1 ) {
                    if( isset( $sizes[$size] ) ) {
                        $nsizes[$size] = $sizes[$size];
                    } else {
                        $nsizes[$size] = [ 'makeImageMax' => [ (int) $size, implode( '/', [ UPLOADS_DIR, 'images2', uniqid() ] ) ] ];
                    }
                }
            }

            $sizes = $nsizes;
        }

        $this->uploadedf[$files_id] = [ 'local' => $handler->makeImageArray( $sizes ), 'file' => $file ];
    }

    private function uploadFile( $files_id, $file ) {
        if( move_uploaded_file( $file['tmp_name'], ( $file_loc = implode( '/', [ UPLOADS_DIR, 'media', uniqid() ] ) . '.' . $file['extension'] ) ) ) {
            $this->uploadedf[$files_id] = [ 'local' => [ $file_loc ], 'file' => $file ];
        } else {
            $this->errors[] = t( 'File cannot be uploaded' );
        }
    }

    private function addFile( array $file ) {
        if( ( $info = $this->MIMEtype( $file['type'] ) ) ) {
            if( empty( $this->accept ) || in_array( $info['type'], array_keys( $this->accept ) ) ) {
                $file['extension']              = $info['extension'];
                $file['ftype']                  = $info['type'];
                $this->files[$info['type']][]   = $file;
            } else
                $this->errors[] = t( 'File type not accepted' );
        } else
            $this->errors[] = t( 'Extension not accepted' );
    }

    private function MIMEtype( string $find = '' ) {
        $types = [
            'image/svg+xml'         => [ 'type' => 'image',     'extension' => 'svg' ],
            'image/png'             => [ 'type' => 'image',     'extension' => 'png' ],
            'image/gif'             => [ 'type' => 'image',     'extension' => 'gif' ],
            'image/jpeg'            => [ 'type' => 'image',     'extension' => 'jpeg' ],
            'image/bmp'             => [ 'type' => 'image',     'extension' => 'bmp' ],
            'image/webp'            => [ 'type' => 'image',     'extension' => 'webp' ],
            'video/mp4'             => [ 'type' => 'video',     'extension' => 'mp4' ],
            'video/mpeg'            => [ 'type' => 'video',     'extension' => 'mpeg' ],
            'video/ogg'             => [ 'type' => 'video',     'extension' => 'ogg' ],
            'video/webm'            => [ 'type' => 'video',     'extension' => 'webm' ],
            'video/x-msvideo'       => [ 'type' => 'video',     'extension' => 'avi' ],
            'audio/mpeg'            => [ 'type' => 'audio',     'extension' => 'mp3' ],
            'audio/ogg'             => [ 'type' => 'audio',     'extension' => 'ogg' ],
            'audio/wav'             => [ 'type' => 'audio',     'extension' => 'wav' ],
            'audio/aac'             => [ 'type' => 'audio',     'extension' => 'acc' ],
            'audio/webm'            => [ 'type' => 'audio',     'extension' => 'webm' ],
            'application/pdf'       => [ 'type' => 'doc',       'extension' => 'pdf'],
            'application/msword'    => [ 'type' => 'doc',       'extension' => 'doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                                    => [ 'type' => 'doc',       'extension' => 'docx'],
            'application/zip'       => [ 'type' => 'archive',   'extension' => 'zip' ],
            'application/x-zip-compressed' 
                                    => [ 'type' => 'archive',   'extension' => 'zip' ],
            'application/vnd.rar'   => [ 'type' => 'archive',   'extension' => 'rar' ],
            'application/x-bzip'    => [ 'type' => 'archive',   'extension' => 'bz' ],
            'application/x-bzip2'   => [ 'type' => 'archive',   'extension' => 'bz2' ]
        ];

        if( $find !== '' ) {
            if( isset( $types[$find] ) )
            return $types[$find];

            return false;
        }

        return $types;
    }

}