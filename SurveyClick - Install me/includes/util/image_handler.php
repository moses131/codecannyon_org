<?php

namespace util;

class image_handler {
    private $src;
    private $image;
    private $image_type;
    private $img_created = false;
    private $image_h;
    private $image_w;
    private $path;
    private $fsave = 18;
    private $types = [
        1   => [ 'load' => 'imagecreatefromgif',    'save' => 'imagegif',   'extension' => 'gif',   'quality' => 0  ],
        2   => [ 'load' => 'imagecreatefromjpeg',   'save' => 'imagejpeg',  'extension' => 'jpg',   'quality' => 90 ],
        3   => [ 'load' => 'imagecreatefrompng',    'save' => 'imagepng',   'extension' => 'png',   'quality' => 5  ],
        18  => [ 'load' => 'imagecreatefromwebp',   'save' => 'imagewebp',  'extension' => 'webp',  'quality' => 90 ],
    ];

    function __construct( string $src = '', string $path = '' ) {
        if( $src !== '' ) {
            $this->setImage( $src );
        }
        if( $path !== '' ) {
            $this->setPath( $path );
        }
    }

    public function setPath( string $path ) {
        $this->path = $path;
        return $this;
    }

    public function setQuality( int $quality ) {
        $this->types[$this->image_type]['quality'] = $quality;
        return $this;
    }
    
    public function forceSave( int $type ) {
        if( $type === 0 ) {
            $this->fsave = NULL;
        } else {
            $this->fsave = $type;
        }
        return $this;
    }
    public function saveImage( string $dest ) {
        return $this->makeImage( $this->image_h, 0, $dest );
    }

    public function makeImageArray( array $images ) {
        $class  = $this;
        $exps   = [];

        array_walk( $images, function( $item, $k ) use ( $class, &$exps ) {
            if( is_array( current( $item ) ) ) {
                $exps[$k] = call_user_func_array( [ $this, key( $item ) ], current( $item ) );
            } else if( ( $image = $class->makeImage( $item[0], $item[1], $item[2], ( !empty( $item[3] ) ? $item[3] : 0 ), ( !empty( $item[4] ) ? $item[4] : 0 ) ) ) ) {
                $exps[$k] = $image;
            }
        } );
        
        return $exps;
    }

    public function makeImageMax( int $dimension, string $dest ) {
        $width = $height = 0;
        if( $this->image_w > $this->image_h ) {
            $width  = min( $this->image_w, $dimension );
        } else {
            $height = min( $this->image_h, $dimension );
        }

        return $this->makeImage( $height, $width, $dest );
    }

    public function makeImage( float $height, float $width, string $dest, int $offsetTop = 0, int $offsetLeft = 0 ) {
        $height2    = $height;
        $width2     = $width;

        if( empty( $height ) ) {

            $height = $height2  = $width * ( $this->image_h / $this->image_w );
        
        } else if( empty( $width ) ) {

            $width  = $width2   = $height * ( $this->image_w / $this->image_h );

        } else if( $this->image_w > $this->image_h ) {
    
            $newWidth   = $width * ( $this->image_w / $this->image_h ) * ( $height / $width );
            $offsetLeft = $offsetLeft !== 0 ? $offsetLeft : ( ( $width - $newWidth ) / 2 );
            $width2     = $newWidth;

        } else {
    
            $newHeight  = $height * ( $this->image_h / $this->image_w ) * ( $width / $height );
            $offsetTop  = $offsetTop !== 0 ? $offsetTop : ( ( $height - $newHeight ) / 2 );
            $height2    = $newHeight;
    
        }

        $thumbnail = imagecreatetruecolor( $width, $height );

        imagealphablending( $thumbnail, false );
        imagesavealpha( $thumbnail, true );

        // imagefill( $thumbnail, 0, 0, imagecolorallocate( $thumbnail, 255, 255, 255 ) );

        imagecopyresampled( $thumbnail, $this->image, $offsetLeft, $offsetTop, 0, 0, $width2, $height2, $this->image_w, $this->image_h );
    
        $type = $this->fsave ? $this->fsave : $this->image_type;

        call_user_func( $this->types[$type]['save'], $thumbnail, $this->path . '/' . $dest . '.' . $this->types[$type]['extension'], $this->types[$type]['quality'] );

        imagedestroy( $thumbnail );

        return $dest . '.' . $this->types[$type]['extension'];
    }

    public function setImage( string $src ) {
        $type = exif_imagetype( $src );

        if( !$type || !in_array( $type, array_keys( $this->types ) ) ) {
            return false;
        }

        $this->image_type = $type;
        $this->image = call_user_func( $this->types[$this->image_type]['load'], $src );

        if( !$this->image ) {
            return false;
        }

        $this->image_h  = imagesy( $this->image );
        $this->image_w  = imagesx( $this->image );
        $this->src      = $src;

        $this->img_created = true;

        return $this;
    }

    public function imageCreated() {
        return $this->img_created;
    }
}