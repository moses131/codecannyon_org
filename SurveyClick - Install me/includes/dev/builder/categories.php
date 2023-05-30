<?php

namespace dev\builder;

class categories extends \util\db {

    private $type;
    private $customPost;
    private $items;
    private $save       = [];
    private $object;
    private $values;

    public function setType( string $type ) {
        $this->type = $type;
        return $this;
    }
    
    public function getType() {
        return $this->type;
    }

    public function setObject( $category ) {
        if( is_numeric( $category ) )
        $this->object = categories( $category );
        else
        $this->object = $category;
        return $this;
    }

    public function checkType() {
        if( !$this->type ) {
            throw new \Exception( t( 'Invalid type' ) );
        } else if( $this->type !== 'website' ) {
            $customPost = $GLOBALS['developer']->getCustomPost( $this->type );
            if( !$customPost ) {
                throw new \Exception( t( 'Invalid type' ) );
            }
            $this->customPost = $customPost;
        }
        return true;
    }

    public function useCategories() {
        return ( !$this->customPost || isset( $this->customPost['category_use'] ) || isset( $this->customPost['use']['categories'] ) || ( isset( $this->customPost['use'] ) && array_search( 'categories', $this->customPost['use'] ) !== false ) );
    }

    public function getCategoriesObject() {
        if( !$this->object )
        $this->object = categories();
        return $this->object;
    }

    public function getTitle() {
        return ( isset( $this->customPost['name'] ) ? sprintf( t( 'Categories &mdash; %s' ), esc_html( $this->customPost['name'] ) ) : t( 'Categories' ) );
    }

    private function mainHeader() {
        $this->items = [
            'name'      => [ 'item', 'name', 'w150p' ],
            'slug'      => [ 'item', 'slug' ],
            'lang'      => [ 'item', 'lang', 'tc' ],
            'options'   => [ 'item', 'options', 'df' ]
        ];
        return [
            t( 'Slug' )     => '',
            t( 'Language' ) => 'tc',
            ''              => ''
        ];
    }

    private function defaultHeader() {
        $this->items    = [
            'name'      => [ 'item', 'name', 'w150p' ]
        ];
        $header         = [];

        if( isset( $this->customPost['category_use'] ) && array_search( 'slug', $this->customPost['category_use'] ) !== false ) {     
            $this->items['slug']        = [ 'item', 'slug' ];
            $header[t( 'Slug' )]        = '';
        }
        
        if( isset( $this->customPost['category_use'] ) && array_search( 'lang', $this->customPost['category_use'] ) !== false ) {
            $this->items['lang']        = [ 'item', 'lang', 'tc' ];
            $header[t( 'Language' )]    = 'tc';
        }

        $this->items['options'] = [ 'item', 'options', 'df' ];

        return $header;
    }

    private function customHeader() {
        $this->items    = [ 'name' => [ 'item', 'name', 'w150p' ] ];
        $header         = [];
        foreach( $this->customPost['use']['categories'] as $key => $col ) {
            switch( $key ) {
                case 'slug':
                    $this->items['slug']        = [ 'item', 'slug', ( $col[0] ?? '' ) ];
                    $header[ t( 'Slug' ) ]      = ( $col[0] ?? '' );
                break;

                case 'lang':
                    $this->items['lang']        = [ 'item', 'lang', ( $col[0] ?? 'tc' ) ];
                    $header[ t( 'Language' ) ]  = ( $col[0] ?? 'tc' );
                break;
                
                default:
                    if( isset( $col[1] ) ) {
                        $this->items[$key]              = [ 'meta', $key, ( $col[0] ?? '' ) ];
                        $header[ esc_html( $col[1] ) ]  = ( $col[0] ?? '' );
                    }
            }
        }
        $this->items['options'] = [ 'item', 'options', 'df' ];
        return $header;
    }

    public function getHeader() {
        $header                 = [];
        $header[ t( 'Name') ]   = 'tl w150p';
        if( !$this->customPost )
            $header += $this->mainHeader();
        else if( !empty( $this->customPost['use']['categories'] ) )
            $header += $this->customHeader();
        else
            $header += $this->defaultHeader();
        $header[ '' ] = '';
        return $header;
    }

    public function getItems( $class ) {
        foreach( $this->items as $itemName => $item ) {
            $class->add( '{' . $itemName . '}', ( $item[2] ?? '' ) );
        }
    }

    public function getItem( object $category ) {
        $entry = [];
        foreach( $this->items as $itemName => $item ) {
            switch( $itemName ) {
                case 'name':
                    $entry['name'] = '<strong>' . esc_html( $category->getName() ) . '</strong>';
                break;

                case 'slug':
                    $entry['slug'] = esc_html( $category->getSlug() );
                break;

                case 'lang':
                    $entry['lang'] = $category->getLanguage( 'short' );
                break;

                case 'options':
                    $entry['options'] = '
                    <ul class="btnset mla">
                        <li>
                            <a href="#" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'edit-category', 'category' => $category->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a>
                        </li>
                        <li class="vopts">
                            <a href="#">' . t( 'Options' ) . '</a>
                        </li>
                    </ul>
                    
                    <div class="dd-o">
                        <ul class="btnset">
                            <li><a href="#" data-ajax="website-actions2" data-data=\'' . cms_json_encode( [ 'action' => 'delete-category', 'category' => $category->getId() ] ) . '\'>' . t( 'Delete' ) . '</a></li>
                        </ul>
                    </div>';
                break;

                default:
                    if( $item[0] == 'meta' ) {
                        $metaValue          = $category->getMeta( $itemName );
                        $metaBox            = $this->customPost['category_metaboxes'][$itemName] ?? NULL;
                        if( $metaBox )
                        $entry[$itemName]   = isset( $metaBox['list_view'] ) ? call_user_func( $metaBox['list_view'], ( $metaValue[$itemName] ?? NULL ), ( $metaBox['def'] ?? NULL ), $category ) : esc_html( ( $metaValue[$itemName] ?? ( $metaBox['def'] ?? '-' ) ) );
                    }
            }
        }
        return $entry;
    }

    public function manageFields( &$fields ) {
        if( $this->customPost ) {
            $oldFields  = $fields;
            $newFields  = [ 'name' ];
            $category_u = isset( $this->customPost['category_use'] );

            // Custom view list
            if( isset( $this->customPost['use']['categories']['lang'] ) || ( $category_u && array_search( 'lang', $this->customPost['category_use'] ) !== false ) )
            array_push( $newFields, 'lang' );

            if( isset( $this->customPost['use']['categories']['slug'] ) || ( $category_u && array_search( 'slug', $this->customPost['category_use'] ) !== false ) )
            array_push( $newFields, 'slug' );

            if( isset( $this->customPost['use']['categories']['parent'] ) || ( $category_u && array_search( 'parent', $this->customPost['category_use'] ) !== false ) )
            array_push( $newFields, 'parent' );
            
            $fields = array_intersect_key( $fields, array_flip( $newFields ) );

            if( isset( $this->customPost['category_defaults'] ) )
            $fields += array_intersect_key( $oldFields, array_flip( $this->customPost['category_defaults'] ) );

            if( isset( $this->customPost['category_metaboxes'] ) ) {
                foreach( $this->customPost['category_metaboxes'] as $key => $field ) {
                    unset( $field['list_view'] );
                    $fields[$key] = $field;
                }
            }
        }

        return $this;
    }

    public function manageValues( &$values ) {
        if( isset( $this->customPost['category_metaboxes'] ) ) {
            $metaValues = $this->object->getMeta( array_keys( $this->customPost['category_metaboxes'] ) );

            foreach( $this->customPost['category_metaboxes'] as $key => $opts ) {
                if( isset( $metaValues[$key] ) )
                    $values[$key] = isset( $opts['vle'] ) && is_callable( $opts['vle'] ) ? call_user_func( $opts['vle'], $metaValues[$key] ) : $metaValues[$key];
                else if( isset( $opts['def'] ) )
                    $values[$key] = $opts['def'];
            }
        }

        $this->values = $values;

        return $this;
    }

    public function manageDefaultValues( &$values ) {
        if( isset( $this->customPost['category_metaboxes'] ) ) {
            foreach( $this->customPost['category_metaboxes'] as $key => $opts ) {
                if( isset( $opts['def'] ) )
                $values[$key] = $opts['def'];
            }
        }

        return $this;
    }

    public function checkData( array $data ) {
        if( isset( $this->customPost['category_metaboxes'] ) ) {
            foreach( $this->customPost['category_metaboxes'] as $key => $opts ) {
                $idata = $data[$key] ?? NULL;
                if( isset( $opts['vld'] ) ) {
                    if( !isset( $opts['def'] ) || $opts['def'] !== $idata )
                    $this->save[$key] = [ 'save', call_user_func( $opts['vld'], $idata, $this->object, $data ) ];
                } else if( $idata !== NULL && ( !isset( $opts['def'] ) || $opts['def'] !== $idata ) )
                    $this->save[$key] = [ 'save', $idata ];

                if( !isset( $this->save[$key] ) )
                $this->save[$key] = [ 'delete' ]; 
            }
        }

        return $this;
    }

    public function savedata() {
        if( $this->save ) {
            $stmt   = $this->db->stmt_init();
            $catId  = $this->object->getId();

            foreach( $this->save as $key => $data ) {
                switch( $data[0] ) {
                    case 'save':
                        $query  = 'INSERT INTO ' . $this->table( 'meta' ) . ' (type, type_id, meta_id, value) VALUES (1, ?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)';
                        $stmt   ->prepare( $query );
                        $stmt   ->bind_param( 'iss', $catId, $key, $data[1] );
                        $stmt   ->execute();
                    break;

                    case 'delete':
                        $query  = 'DELETE FROM ' . $this->table( 'meta' ) . ' WHERE type = 1 AND type_id = ? AND meta_id = ?';
                        $stmt   ->prepare( $query );
                        $stmt   ->bind_param( 'is', $catId, $key );
                        $stmt   ->execute();
                    break;
                }
            }

            $stmt->close();
        }

        return $this;
    }

    public function filters( array $options ) {
        $use_categories = $this->customPost['use']['categories'] ?? NULL;

        if( ( !$use_categories || isset( $use_categories['parent'] ) || ( isset( $this->customPost['category_use'] ) ? array_search( 'parent', $this->customPost['category_use'] ) !== false : false ) ) && isset( $options['category'] ) )
        $this->object->setParentId( (int) $options['category'] );

        if( ( !$use_categories || isset( $use_categories['lang'] ) || ( isset( $this->customPost['category_use'] ) ? array_search( 'lang', $this->customPost['category_use'] ) !== false : false ) ) && isset( $options['lang'] ) )
        $this->object->setLanguage( $options['lang'] );

        if( isset( $options['search'] ) ) {
            $this->object->search( $options['search'] );
            if( !isset( $options['orderby'] ) )
            $this->object->orderBy( 'relevance_desc' ); 
        }

        if( isset( $options['orderby'] ) )
        $this->object->orderBy( $options['orderby'] );
    }

    public function manageFilters( &$filters ) {
        $use_categories = $this->customPost['use']['categories'] ?? NULL;

        if( !$use_categories || isset( $use_categories['lang'] ) || ( isset( $this->customPost['category_use'] ) ? array_search( 'lang', $this->customPost['category_use'] ) !== false : false ) ) {
            $filters['lang'] = [ 'type' => 'select', 'after_label' => '<i class="fas fa-globe"></i>', 'options' => ( [ '' => t( 'Any' ) ] + array_map( function( $language ) {
                return esc_html( $language['name_en'] );
            }, getLanguages() ) ), 'placeholder' => t( 'Language' ), 'position' => 1.1 ];
            return $this;
        }
    }

}