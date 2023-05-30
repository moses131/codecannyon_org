<?php

namespace query;

class pages extends \util\db {

    private $id;
    protected $info;
    private $meta;
    private $categories;
    private $orderby        = [];
    private $items_per_page = 10;
    private $current_page   = false;
    private $pagination     = [];
    private $count          = false;
    // db query
    protected $select       = 'p.*';
    protected $selectKey    = 'id';

    function __construct( int $id = 0 ) {
        parent::__construct();

        $this->setId( $id );
        $this->orderby  = $this->filters->do_filter( 'pages_default_order_by', [ 'id_desc' ] );
    }

    public function setId( int $id ) {
        $this->id = $id;
        return $this;
    }

    public function setLanguage( string $lang = '' ) {
        if( $lang == '' )
        $this->conditions['lang'] = [ 'p.lang', '=', getUserLanguage( 'locale_e' ) ];
        else
        $this->conditions['lang'] = [ 'p.lang', '=', $lang ];
        return $this;
    }

    public function setCategoryId( int $category = NULL ) {
        $this->categories = $category ? [ $category ] : NULL;
        return $this;
    }

    public function setCategoryIds( array $categories = NULL ) {
        $this->categories = $categories ? array_map( 'intval', $categories ): NULL;
        return $this;
    }

    public function setType( string $type ) {
        $this->conditions['type'] = [ 'p.type', '=', $type ];
        return $this;
    }

    public function setRelationPage( int $page ) {
        $this->conditions['relation'] = [ 'p.relation', '=', $page ];
        return $this;
    }

    public function excludeId( int $id ) {
        $this->conditions['exclude_' . $id] = [ 'p.id', '!=', $id ];
        return $this;
    }

    public function setSlug( string $slug ) {
        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'pages p' ); 
        $query .= ' WHERE p.slug = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 's', $slug );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            $this->id   = $fields->id;
            $this->info = $this->filters->do_filter( 'pages_info_values', $fields );

            return $fields->id;
        }

        return false;
    }

    public function userView( string $type = '' ) {
        if( $type !== '' )
        $this->setType( $type );
        $this->setLanguage();
        return $this;
    }

    public function search( string $title ) {
        if( $title !== '' ) {

            filters()->add_filter( 'pages_order_by_values', function( $f, $list ) {
                $list['relevance']      = 'relevance';
                $list['relevance_desc'] = 'relevance DESC';
                return $list;
            } );

            $this->select .= ', MATCH(p.title) AGAINST ("*' . $this->dbp( $title ) . '*" IN BOOLEAN MODE) as relevance';
            $this->conditions['search_title'] = [ 'MATCH(p.title)', 'AGAINST', '*' . $title . '*' ];
        }
        return $this;
    }

    public function setMeta( array $conds = NULL ) {
        if( $conds ) {
            foreach( $conds as $meta_id => $cond ) {
                $econd  = explode( ':', $cond );
                $op     = array_shift( $econd );

                if( !empty( $econd ) ) {
                    $op = strtoupper( $op );
                    if( in_array( $op, [ '=', '!=', 'LIKE', 'IN', 'NOT IN' ] ) ) {
                        switch( $op ) {
                            case '=':
                            case '!=':
                                $this->meta[$this->dbp( $meta_id )] = ' ' . $op . ' ' . $this->dbp( $econd[0] );
                            break;

                            case 'LIKE':
                                $this->meta[$this->dbp( $meta_id )] = ' LIKE "%' . $this->dbp( $econd[0] ) . '%"';
                            break;

                            case 'IN':
                            case 'NOT IN':
                                $this->meta[$this->dbp( $meta_id )] = ' ' . $op . ' (' . implode( ', ', array_map( function( $v ) {
                                    return '"' . $this->dbp( trim( $v ) ) . '"';
                                }, explode( ',', $econd[0] ) ) ) . ')';
                            break;
                        }
                    }
                } else 
                    $this->meta[$this->dbp( $meta_id )] = ' = ' . $this->dbp( $cond );
            }
        } else
            $this->meta = NULL;

        return $this;
    }

    public function setObject( $info ) {
        $this->info = $info;
        return $this;
    }

    public function getObject() {
        if( empty( $this->info ) ) {
            $this->info = $this->info();
        }
        return $this->info;
    }

    public function getId() : int {
        return ( $this->info->id ?? $this->id );
    }

    public function getTitle() {
        return $this->info->title;
    }

    public function getUserId() {
        return $this->info->user;
    }

    public function getText() {
        return $this->info->text;
    }

    public function getTextBlocks() {
        return ( $this->info->text ? json_decode( $this->info->text, true ) : [] );
    }

    public function getContent() {
        $builder    = new \dev\builder\blocks;
        if( !$builder->setPage( $this ) ) return '';
        return $builder->render();
    }

    public function getBBText() {
        $text = new \util\text( $this->info->text );
        return $text->fromBB();
    }

    public function getSlug() {
        return $this->info->slug;
    }

    public function getTemplate() {
        return $this->info->template;
    }

    public function getLastUpdateUserId() {
        return $this->info->lu_user;
    }

    public function getType() {
        return $this->info->type;
    }

    public function getLanguageId() {
        return $this->info->lang;
    }

    public function getLanguage( string $key = '' ) {
        if( $key != '' ) {
            $lang = getLanguage( $this->info->lang );
            return ( $lang[$key] ?? '-' );
        }
        return getLanguage( $this->info->lang );
    }

    public function getThumbnails() {
        $thumbs = [];
        if( $this->info->thumb != '' ) {
            foreach( explode( ',', $this->info->thumb ) as $thumb ) {
                $imageURL       = mediaLinks( $thumb )->getItemURL();
                if( $imageURL )
                $thumbs[$thumb] = $imageURL;
            }
        }

        return $thumbs;
    }

    public function getMetaTitle() {
        return $this->info->meta_title;
    }

    public function getMetaKeywords() {
        return $this->info->meta_keywords;
    }

    public function getMetaDesc() {
        return $this->info->meta_desc;
    }

    public function getPermalink() {
        return filters()->do_filter( 'page-permalink', '', $this->info->slug, $this->info->id );
    }

    public function getDate() {
        return $this->info->date;
    }

    public function resetInfo() {
        $this->info = [];
        return $this;
    }

    public function getCategories() {
        if( empty( $this->info ) && !$this->getObject() ) return ;
        $categories = new category_pages;
        $categories ->setPageId( $this->info->id );
        return $categories;
    }

    public function getCategoriesPages() {
        if( empty( $this->info ) && !$this->getObject() ) return ;
        $categories = new category_with_pages;
        $categories ->setPageId( $this->info->id );
        return $categories;
    }

    public function getRelatedPages() {
        if( empty( $this->info ) && !$this->getObject() ) return ;
        $categories = new category_pages;
        $categories ->setPageId( $this->info->id );
        $categories ->select( [ 'category' ] )
                    ->selectKey( 'category' );
        $pages      = new pages;
        $pages      ->setCategoryIds( array_keys( $categories->fetch( -1 ) ) )
                    ->excludeId( $this->info->id );
        return $pages;
    }
    
    public function getLastUpdateUser() {
        if( empty( $this->info ) && !$this->getObject() ) return ;
        $users  = new users;
        $users  ->setId( $this->info->lu_user );
        return $users;
    }

    private function orderBy_values() {
        $list                   = [];
        $list['id']             = 'id';
        $list['id_desc']        = 'id DESC';
        $list['title']          = 'title';
        $list['title_desc']     = 'title DESC';
        $list['date']           = 'date';
        $list['date_desc']      = 'date DESC';
        $list['rand']           = 'RAND()';

        return $this->filters->do_filter( 'pages_order_by_values', $list );
    }

    public function orderBy( $values ) {
        if( is_string( $values ) ) {
            $values = [ $values ];
        }
        $this->orderby = array_intersect( $values, array_keys( $this->orderBy_values() ) );
        return $this;
    }

    private function setPagination( $pagination ) {
        $this->pagination = $pagination;
        return $this;
    }

    public function getPagination() {
        return $this->pagination;
    }

    public function setPage( int $page ) {
        $this->current_page = $page;
        return $this;
    }

    public function setItemsPerPage( int $items = 10 ) {
        $this->items_per_page = $items;
        return $this;
    }

    public function itemsPerPage() {
        return $this->filters->do_filter( 'pages_per_page', $this->items_per_page );
    }

    public function pagination() {
        if( !$this->count ) {
            return false;
        }
        $pagination = new \markup\front_end\pagination( 
            $this->pagination['total_pages'], 
            $this->pagination['items_per_page'], 
            $this->pagination['current_page'] 
        );
        return $pagination;
    }

    public function count() {
        if( ( $count = $this->filters->do_filter( 'pages_set_count', $this->count ) ) !== false ) {
            return $count;
        }

        $query = 'SELECT COUNT(*) FROM ';
        $query .= $this->table( 'pages p' );

        if( !empty( $this->meta ) ) {
            $query  .= ' INNER JOIN ';
            $query  .= $this->table( 'meta m' );
            $query  .= ' ON m.type = 2 AND m.type_id = p.id AND (';
            $conds  = [];
            foreach( $this->meta as $key => $cond ) {
                $conds[] = 'm.meta_id = "' . $key . '" AND m.value ' . $cond;
            }
            $query  .= implode( ' OR ', $conds );
            $query  .= ')';
        }

        $query .= $this->finalCondition();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $stmt->bind_result( $count );
        $stmt->fetch();
        $stmt->close();

        $this->count = $count;

        if( $count > 0 ) return $this->filters->do_filter( 'pages_count', $count );

        return false;
    }

    // Get information as object
    public function info( int $id = 0 ) {
        if( empty( $id ) ) {
            $id = $this->id;
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'pages p' );
        $query .= ' WHERE p.id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $fields = $result->fetch_object();
        $stmt->close();

        if( $fields ) {
            return $this->filters->do_filter( 'pages_info_values', $fields );
        }

        return false;
    }

    // Fetch entries
    public function fetch( int $max = 0, int $offset = 0 ) {
        $limit = '';
        
        if( $max != 0 ) {
            if( $max > 0 )
            $limit = ' LIMIT ' . ( $offset ? $offset . ',' : '' ) . $max;
        } else {
            $count = $this->count();
                
            if( !$count ) {
                return [];
            }

            $items_per_page = $this->itemsPerPage();

            if( $items_per_page ) {
                $per_page       = $this->itemsPerPage();
                $total_pages    = ceil( $count / $per_page );
                $current_page   = ( $this->current_page !== false ? $this->current_page : ( !empty( $_GET['page'] ) && $_GET['page'] > 0 ? (int) $_GET['page'] : 1 ) );
                $current_page   = min( $current_page, $total_pages );

                $this->pagination = [
                    'items_per_page'=> $per_page,
                    'total_pages'   => $total_pages,
                    'current_page'  => $current_page
                ];

                $this->setPagination( $this->pagination );

                $limit = ' LIMIT ' . ( ( $current_page - 1 ) * $per_page ) . ', ' . $per_page;
            }
        }

        $query = 'SELECT ' . $this->select . ' FROM ';
        $query .= $this->table( 'pages p' );

        if( !empty( $this->meta ) ) {
            $query  .= ' INNER JOIN ';
            $query  .= $this->table( 'meta m' );
            $query  .= ' ON m.type = 2 AND m.type_id = p.id AND (';
            $conds  = [];
            foreach( $this->meta as $key => $cond ) {
                $conds[] = 'm.meta_id = "' . $key . '" AND m.value ' . $cond;
            }
            $query  .= implode( ' OR ', $conds );
            $query  .= ')';
        }

        if( !empty( $this->categories ) ) {
            $query  .= ' INNER JOIN ';
            $query  .= $this->table( 'category_pages c' );
            $query  .= ' ON c.category IN (' . implode( ',', $this->categories ) . ') AND c.page = p.id';
        }

        $query .= $this->finalCondition();

        if( !empty( $this->orderby ) ) {
            $order  = array_flip( $this->orderby );
            $query .= ' ORDER BY ' . implode( ', ', array_intersect_key( array_replace( $order, $this->orderBy_values() ), $order ) );
        }

        $query .= $limit;

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];

        while( ( $row = $result->fetch_assoc() ) ) {
            if( $this->selectKey )
                $data[$row[$this->selectKey]] = $this->filters->do_filter( 'pages_info_values', (object) $row );
            else
                $data[] = $this->filters->do_filter( 'pages_info_values', (object) $row );
        }

        $stmt->close();

        return $data;
    }

    public function getMeta( $metaId, int $id = 0 ) {
        if( empty( $id ) )
        $id = $this->info->id ?? $this->id;

        $query  = 'SELECT `meta_id`, `value` FROM ';
        $query  .= $this->table( 'meta' );
        $stmt   = $this->db->stmt_init();

        if( gettype( $metaId ) == 'array' ) {
            $query  .= ' WHERE type = 2 AND type_id = ? AND meta_id IN (' . implode( ', ', array_map( function( $v ) {
                return '"' .  $this->dbp( $v ) . '"';
            }, $metaId ) ) . ')';
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
        } else {
            $query  .= ' WHERE type = 2 AND type_id = ? AND meta_id = ?';
            $stmt->prepare( $query );
            $stmt->bind_param( 'is', $id, $metaId );
        }

        $stmt   ->execute();
        $result = $stmt->get_result();
        $data   = [];

        while( ( $row = $result->fetch_assoc() ) ) {
            $data[$row['meta_id']]  = $row['value'];
        }

        $stmt->close();

        return $data;
    }

}