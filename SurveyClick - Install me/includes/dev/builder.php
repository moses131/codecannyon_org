<?php

namespace dev;

class builder {

    private static $customPosts     = [];
    private static $customBlocks    = [];
    private static $customTemplates = [];

    public function customPosts( array $posts ) {
        self::$customPosts += $posts;
    }

    public function getCustomPost( string $post ) {
        $customPost = self::$customPosts[$post] ?? NULL;
        if( $customPost ) {
            $customPost['templates'][] = [
                'name'  => t( 'Default template' ),
                'file'  => 'page.php'
            ];
            if( !empty( self::$customTemplates ) ) {
                foreach( self::$customTemplates as $template ) {
                    if( array_search( $post, $template['useFor'] ) !== false )
                    $customPost['templates'][] = $template;
                }
            }
        }
        return $customPost;
    }

    public function customBlocks( array $blocks ) {
        self::$customBlocks += $blocks;
    }

    public function getCustomBlock( string $block ) {
        return ( self::$customBlocks[$block] ?? NULL );
    }

    public function customTemplates( array $templates ) {
        self::$customTemplates += $templates;
    }

    public function getCustomTemplates( string $template ) {
        return ( self::$customTemplates[$template] ?? NULL );
    }

    public static function owner_nav( $filter, $links ) {
        foreach( self::$customPosts as $post_id => $post ) {
            $links[$post_id] = [
                'type'      => 'link', 
                'url'       => '#', 
                'label'     => esc_html( $post['name'] ), 
                'icon'      => ( $post['icon'] ?? '<i class="fas fa-scroll"></i>' ), 
                'position'  => ( 99 + ( $post['position'] ?? 0 ) ),
                'parent_id' => 'content'
            ];

            if( isset( $post['use']['categories'] ) || ( isset( $post['use'] ) && array_search( 'categories', $post['use'] ) !== false ) ) {
                $links[$post_id . '_cats'] = [
                    'type'      => 'link', 
                    'url'       => admin_url( 'categories/' . $post_id ), 
                    'label'     => esc_html( ( $post['cats'] ?? t( 'Categories' ) ) ), 
                    'position'  => ( 1 + ( $post['position'] ?? 0 ) ),
                    'parent_id' => $post_id, 
                    'attrs'     => [ 'data-to' => 'categories', 'data-options' => [ 'type' => $post_id ] ]
                ];       
            }

            $links[$post_id . '_view'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'pages/' . $post_id ), 
                'label'     => esc_html( ( $post['view'] ?? t( 'View' ) ) ), 
                'position'  => ( 2 + ( $post['position'] ?? 0 ) ),
                'parent_id' => $post_id, 
                'attrs'     => [ 'data-to' => 'pages', 'data-options' => [ 'type' => $post_id ] ]
            ];

            $links[$post_id . '_add'] = [
                'type'      => 'link', 
                'url'       => admin_url( 'page/new/' . $post_id ), 
                'label'     => esc_html( ( $post['new'] ?? t( 'Add' ) ) ), 
                'position'  => ( 3 + ( $post['position'] ?? 0 ) ),
                'parent_id' => $post_id, 
                'attrs'     => [ 'data-to' => 'page', 'data-options' => [ 'id' => 'new', 'type' => $post_id ] ]
            ];
        }

        return $links;
    }

    public function blocksForType( string $type ) {
        if( !isset( self::$customPosts[$type] ) )
            $types = [ 'website' ];
        else {
            $types = [ $type ];
            if( !isset( self::$customPosts[$type]['defaultBlocks'] ) || self::$customPosts[$type]['defaultBlocks'] )
            $types[] = 'website';
        }
        $available = [];
        foreach( self::$customBlocks as $blockId => $block ) {
            if( !empty( array_intersect( $block['useFor'], $types ) ) )
            $available[$blockId] = $block;
        }

        return $available;
    }

    public function getBlock( string $block, string $type ) {
        $block = self::$customBlocks[$block] ?? NULL;
        if( $block ) {
            if( !isset( self::$customPosts[$type] ) )
                $types = [ 'website' ];
            else {
                $types = [ $type ];
                if( !isset( self::$customPosts[$type]['defaultBlocks'] ) || self::$customPosts[$type]['defaultBlocks'] )
                $types[] = 'website';
            }

            if( !empty( array_intersect( $block['useFor'], $types ) ) )
            return $block;
        }

        return false;
    }

    private function registerBlocks() {
        $newBlocks = self::$customBlocks;
        filters()->add_filter( 'blocks', function( $filter, $default, $type ) use ( $newBlocks ) {
            foreach( $newBlocks as $blockId => $block ) {
                if( isset( $block['useFor'] ) && array_search( $type, $block['useFor'] ) !== false ) {
                    $default[$blockId] = $block;
                }
            }

            return $default;
        } );
    }

    public function templatesForType( string $type ) {
        $available = [];

        if( !isset( self::$customPosts[$type] ) )
            $types = [ 'website' ];
        else {
            $types = [ $type ];

            if( !empty( self::$customPosts[$type]['templates'] ) )
            $available = self::$customPosts[$type]['templates'];

            if( empty( self::$customPosts[$type]['defaultTemplate'] ) )
            $types[] = 'website';
        }

        if( array_search( 'website', $types ) !== false ) {
            $available[] = [
                'name'  => t( 'Default template' ),
                'file'  => 'page.php'
            ];
        }

        foreach( self::$customTemplates as $template ) {
            if( !empty( array_intersect( $template['useFor'], $types ) ) )
            $available[] = $template;
        }

        return $available;
    }

    public function use( array $use ) {
        foreach( $use as $u ) {
            switch( $u ) {
                case 'blocks':
                    $this->registerBlocks();
                break;
            }
        }
    }

    public function inFrontEnd() {
    }

    public function inBackEnd() {
        filters()->add_filter( [ 'admin_nav', 'owner_nav' ], [ $this, 'owner_nav' ] );
    }

}