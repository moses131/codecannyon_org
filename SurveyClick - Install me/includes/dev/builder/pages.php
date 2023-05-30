<?php

namespace dev\builder;

class pages extends \util\db {

    private $type;
    private $customPost;
    private $items;
    private $save       = [];
    private $object;
    private $templates;
    private $values;

    public function setType( string $type ) {
        $this->type = $type;
        return $this;
    }

    public function getType() {
        return $this->type;
    }
    
    public function setObject( $page ) {
        if( is_numeric( $page ) )
        $this->object = pages( $page );
        else
        $this->object = $page;
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

    public function getPagesObject() {
        if( !$this->object )
        $this->object = pages();
        return $this->object;
    }

    public function getTitle() {
        return ( isset( $this->customPost['name'] ) ? esc_html( $this->customPost['name'] ) : t( 'Pages' ) );
    }

    public function addButton() {
        return ( isset( $this->customPost['new'] ) ? esc_html( $this->customPost['new'] ) : t( 'Add page' ) );
    }

    public function getTemplates() {
        if( $this->templates )
        return $this->templates;

        if( $this->type == 'website' )
            $templates = $GLOBALS['developer']->templatesForType( 'website' );
        else
            $templates = ( !empty( $this->customPost['templates'] ) ? $this->customPost['templates'] : [] );
        
        foreach( $templates as $template ) {
            $this->templates[esc_html( $template['file'] )] = esc_html( $template['name'] );
        }

        return $this->templates;
    }

    public function getDefaultTemplate() {
        if( isset( $this->customPost['default_template'] ) && ( $templates = $this->getTemplates() ) && isset( $templates[$this->customPost['default_template']] ) )
        return $this->customPost['default_template'];
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

        if( isset( $this->customPost['use'] ) && array_search( 'slug', $this->customPost['use'] ) !== false ) {     
            $this->items['slug']        = [ 'item', 'slug' ];
            $header[t( 'Slug' )]        = '';
        }
        
        if( isset( $this->customPost['use'] ) && array_search( 'lang', $this->customPost['use'] ) !== false ) {
            $this->items['lang']        = [ 'item', 'lang', 'tc' ];
            $header[t( 'Language' )]    = 'tc';
        }

        $this->items['options'] = [ 'item', 'options', 'df' ];

        return $header;
    }

    private function customHeader() {
        $this->items    = [ 'name' => [ 'item', 'name', 'w150p' ] ];
        $header         = [];
        foreach( $this->customPost['pages'] as $key => $col ) {
            switch( $key ) {
                case 'slug':
                    $this->items['slug']        = [ 'item', 'slug', ( $col[0] ?? '' ) ];
                    $header[ t( 'Slug' ) ]      = ( $col[0] ?? '' );
                break;

                case 'thumb':
                    $this->items['thumb']       = [ 'item', 'thumb', ( $col[0] ?? '' ) ];
                    $header[ esc_html( ( $col[1] ?? '' ) ) ] = ( $col[0] ?? '' );
                break;

                case 'lang':
                    $this->items['lang']        = [ 'item', 'lang', ( $col[0] ?? 'tc' ) ];
                    $header[ t( 'Language' ) ]  = ( $col[0] ?? 'tc' );
                break;

                case 'template':
                    $this->items['template']    = [ 'item', 'template', ( $col[0] ?? '' ) ];
                    $header[ t( 'Template' ) ]  = ( $col[0] ?? '' );
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
        else if( !empty( $this->customPost['pages'] ) )
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

    public function getItem( object $page ) {
        $entry = [];
        foreach( $this->items as $itemName => $item ) {
            switch( $itemName ) {
                case 'name':
                    $entry['name'] = '<strong>' . esc_html( $page->getTitle() ) . '</strong>';
                break;

                case 'slug':
                    $entry['slug'] = esc_html( $page->getSlug() );
                break;

                case 'thumb':
                    $entry['thumb'] = '';
                    if( ( $thumbs = $page->getThumbnails() ) )
                    $entry['thumb'] = '<div class="wa m0 df sav"><img src="' . esc_url( current( $thumbs ) ) . '" alt="" /></div>';
                break;

                case 'lang':
                    $entry['lang'] = $page->getLanguage( 'short' );
                break;

                case 'template':
                    $templates = $this->getTemplates();
                    $entry['template'] = ( $templates[$page->getTemplate()] ?? '-' );
                break;

                case 'options':
                    $entry['options'] = '
                    <ul class="btnset mla">
                        <li>
                            <a href="' . admin_url( 'page/' . $page->getId() ) . '" data-to="page" data-options=\'' . ( cms_json_encode( [ 'id' => $page->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a>
                        </li>
                        <li class="vopts">
                            <a href="#">' . t( 'Options' ) . '</a>
                        </li>
                    </ul>

                    <div class="dd-o">
                        <ul class="btnset">
                            <li><a href="#" data-ajax="website-actions2" data-data=\'' . cms_json_encode( [ 'action' => 'delete-page', 'page' => $page->getId() ] ) . '\'>' . t( 'Delete' ) . '</a></li>
                        </ul>
                    </div>';
                break;

                default:
                    if( $item[0] == 'meta' ) {
                        $metaValue          = $page->getMeta( $itemName );
                        $metaBox            = $this->customPost['metaboxes'][$itemName] ?? NULL;
                        if( $metaBox )
                        $entry[$itemName]   = isset( $metaBox['list_view'] ) ? call_user_func( $metaBox['list_view'], ( $metaValue[$itemName] ?? NULL ), ( $metaBox['def'] ?? NULL ), $page ) : esc_html( ( $metaValue[$itemName] ?? ( $metaBox['def'] ?? '-' ) ) );
                    }
            }
        }
        return $entry;
    }

    public function manageFields( &$fields ) {
        if( $this->customPost ) {
            $oldFields  = $fields;
            $newFields  = [ 'title' ];

            if( isset( $this->customPost['use'] ) ) {
                if( isset( $this->customPost['pages']['thumb'] ) || ( isset( $this->customPost['use'] ) && array_search( 'thumb', $this->customPost['use'] ) !== false ) )
                array_push( $newFields, 'thumb' );

                if( isset( $this->customPost['pages']['lang'] ) || ( isset( $this->customPost['use'] ) && array_search( 'lang', $this->customPost['use'] ) !== false ) )
                array_push( $newFields, 'lang' );

                if( isset( $this->customPost['pages']['slug'] ) || ( isset( $this->customPost['use'] ) && array_search( 'slug', $this->customPost['use'] ) !== false ) )
                array_push( $newFields, 'slug' );

                if( isset( $this->customPost['use'] ) && array_search( 'meta', $this->customPost['use'] ) !== false )
                array_push( $newFields, 'meta' );

                if( array_search( 'hide-templates', $this->customPost['use'] ) === false )
                array_push( $newFields, 'template' );

                if( isset( $this->customPost['use']['categories'] ) ) {
                    array_push( $newFields, 'categories' );

                    $categories = [];

                    if( $this->object ) {
                        $categories = array_map( function( $v ) {
                            return $v->category;
                        }, $this->object->getCategories()->selectKey( 'category' )->fetch( -1 ) );
                    }

                    $fields['categories']   = [
                        'label'     => t( 'Categories' ),
                        'type'      => 'checkboxes',
                        'options'   => array_map( function( $v ) {
                            return $v->name;
                        }, categories()->setType( $this->type )->fetch( -1 ) ),
                        'value'     => $categories
                    ];
                }
            }

            $fields = array_intersect_key( $fields, array_flip( $newFields ) );

            if( isset( $this->customPost['defaults'] ) )
            $fields += array_intersect_key( $oldFields, array_flip( $this->customPost['defaults'] ) );

            if( isset( $this->customPost['metaboxes'] ) ) {
                foreach( $this->customPost['metaboxes'] as $key => $field ) {
                    unset( $field['list_view'] );
                    $fields[$key] = $field;
                }
            }
        }

        return $this;
    }

    public function manageValues( &$values = [] ) {
        if( $this->object ) {
            if( isset( $this->customPost['metaboxes'] ) ) {
                $metaValues = $this->object->getMeta( array_keys( $this->customPost['metaboxes'] ) );

                foreach( $this->customPost['metaboxes'] as $key => $opts ) {
                    if( isset( $metaValues[$key] ) )
                        $values[$key] = isset( $opts['vle'] ) && is_callable( $opts['vle'] ) ? call_user_func( $opts['vle'], $metaValues[$key] ) : $metaValues[$key];
                    else if( isset( $opts['def'] ) )
                        $values[$key] = $opts['def'];
                }
            }
        }

        $this->values = $values;

        return $this;
    }

    public function manageDefaultValues( &$values ) {
        if( isset( $this->customPost['metaboxes'] ) ) {
            foreach( $this->customPost['metaboxes'] as $key => $opts ) {
                if( isset( $opts['def'] ) )
                $values[$key] = $opts['def'];
            }
        }

        return $this;
    }

    public function checkData( array $data, array $media ) {
        if( isset( $this->customPost['metaboxes'] ) ) {
            foreach( $this->customPost['metaboxes'] as $key => $opts ) {
                $idata = $data[$key] ?? NULL;
                if( isset( $opts['vld'] ) ) {
                    if( !isset( $opts['def'] ) || $opts['def'] !== $idata ) {
                        $value = call_user_func( $opts['vld'], $idata, $this->object, $data, $media );
                        if( $value !== NULL )
                        $this->save[$key] = [ 'save', $value ];
                    }
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
                        $query  = 'INSERT INTO ' . $this->table( 'meta' ) . ' (type, type_id, meta_id, value) VALUES (2, ?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)';
                        $stmt   ->prepare( $query );
                        $stmt   ->bind_param( 'iss', $catId, $key, $data[1] );
                        $stmt   ->execute();
                    break;

                    case 'delete':
                        $query  = 'DELETE FROM ' . $this->table( 'meta' ) . ' WHERE type = 2 AND type_id = ? AND meta_id = ?';
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

        if( $use_categories && isset( $options['category'] ) )
        $this->object->setCategoryId( (int) $options['category'] );

        if( isset( $options['lang'] ) )
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
        $use_pages = $this->customPost['pages'] ?? NULL;

        if( !$use_pages || isset( $use_pages['lang'] ) ) {
            $filters['lang'] = [ 'type' => 'select', 'after_label' => '<i class="fas fa-globe"></i>', 'options' => ( [ '' => t( 'Any' ) ] + array_map( function( $language ) {
                return esc_html( $language['name_en'] );
            }, getLanguages() ) ), 'placeholder' => t( 'Language' ), 'position' => 1.1 ];
        }

        if( isset( $this->customPost['use']['categories'] ) ) {
            $categories = categories();
            $categories ->setType( $this->type );
            $filters['category'] = [ 'type' => 'select', 'after_label' => '<i class="fas fa-tag"></i>', 'options' => ( [ '' => t( 'Any' ) ] + array_map( function( $category ) {
                return esc_html( $category->name );
            }, $categories->fetch( -1 ) ) ), 'placeholder' => t( 'Category' ), 'position' => 1.2 ];
        }

        return $this;
    }

}