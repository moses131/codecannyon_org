<?php

namespace markup\front_end;

class form_fields {

    protected $fields   = [];
    private $atts       = [];
    private $targets    = [];
    private $values     = [];
    private $values_arr = [];
    private $frequests  = [];
    private $files      = [];
    private $iname      = 'data';

    function __construct( array $fields = [] ) {
        $this->fields   = $fields;
    }

    public function addFields( array $fields ) {
        $this->fields = array_merge( $this->fields, $fields );
        return $this;
    }

    public function addFieldsAfter( string $key, array $fields ) {
        if( !isset( $this->fields[$key] ) || !isset( $this->fields[$key]['position'] ) ) {
            return $this->addFields( $fields );
        }

        $position = $this->fields[$key]['position']; 

        array_walk( $fields, function( &$v, $k ) use ( &$position ) {
            $position = $position + .001;
            $v['position'] = $position;
        } );

        $this->fields = $this->fields + $fields;
    }

    public function getFields() {
        return $this->fields;
    }

    public function removeFields( array $fields ) {
        foreach( $fields as $field ) {
            unset( $this->fields[$field] );
        }
        return $this;
    }

    private function fieldTypes( ...$atts ) {
        $fields             = [];
        $fields['switcher'] = '
        <div class="form_line%%defClass{ %s}%%classes{ %s}%"%data-id{ id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %description{<span>%s</span>}%
            <div>
                <label for="%id{%s}%">
                    <div class="switcher">
                        <input type="checkbox" id="%id{%s}%" name="%id{%s}%"%input_value{ value="%s"}%%value{ checked}%%required{ required}%>
                        <span>%enabled{%s,Enabled}%</span><span>%disabled{%s,Disabled}%</span>
                    </div>
                </label>
            </div>
        </div>';

        $fields['text'] = '
        <div class="form_line%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %description{<span>%s</span>}%
            <input type="%input_type{%s,text}%" id="%id{%s}%" name="%id{%s}%"%value{ value="%s"}%%placeholder{ placeholder="%s"}%%maxlength{ maxlength="%s"}%%disabled{ disabled}%%required{ required}%%readonly{ readonly}%%autocomplete{ autocomplete="%s"}%>
        </div>';

        $fields['number'] = '
        <div class="form_line%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %description{<span>%s</span>}%
            <input type="number" id="%id{%s}%" name="%id{%s}%"%value{ value="%s"}%%placeholder{ placeholder="%s"}%%maxlength{ maxlength="%s"}%%min{ min="%s"}%%max{ max="%s"}%%step{ step="%s", step=any}%%disabled{ disabled}%%required{ required}%%readonly{ readonly}%%autocomplete{ autocomplete="%s"}%>
        </div>';

        $fields['password'] = '
        <div class="form_line%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %description{<span>%s</span>}%
            <input type="password" id="%id{%s}%" name="%id{%s}%"%value{ value="%s"}%%placeholder{ placeholder="%s"}%%maxlength{ maxlength="%s"}%%required{ required}%%readonly{ readonly}%%autocomplete{ autocomplete="%s"}%>
        </div>';

        $fields['textarea'] = '
        <div class="form_line%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %description{<span>%s</span>}%
            <textarea id="%id{%s}%" name="%id{%s}%"%placeholder{ placeholder="%s"}%%maxlength{ maxlength="%s"}%%required{ required}%%readonly{ readonly}%>%value{%s}%</textarea>
        </div>';

        $fields['repeater'] = '
        <div class="form_line form_repeater%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %description{<span>%s</span>}%
            ' . current( $atts ) . '
            <div class="form_line add_button">
                <a href="#" data-add_button="%id{%s}%">%add_button{%s,Add row}%</a>
            </div>
            ' . next( $atts ) . '
        </div>';

        $fields['checkbox'] = '
        <div class="form_line checkbox%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %description{<span>%s</span>}%
            <div class="chbxes">
                <div>
                    <input type="checkbox" name="%id{%s}%" id="%id{%s}%"%input_value{ value="%s"}%%value{ checked}%%disabled{ disabled}%%checked{ checked}%%required{ required}%>
                    <label for="%id{%s}%"><span></span> %title{%s}%</label>
                </div>
            </div>
        </div>';

        $fields['checkboxes'] = '
        <div class="form_line checkbox checkboxes%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %description{<span>%s</span>}%
            <div class="chbxes">
                <div>
                    %options{<div><input type="checkbox" name="%id[%key]" id="%id[%key]" value="%key"%current%disabled /><label for="%id[%key]">%value</label></div>}%
                </div>
                (search NOT_EMPTY)~<div><input type="text" placeholder="%search{%s}%" data-search /></div>~
            </div>
        </div>';

        $fields['radio'] = '
        <div class="form_line radio%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %description{<span>%s</span>}%
            <div class="chbxes">
                <div>
                    %options{<div><input type="radio" name="%id" id="%id[%key]" value="%key"%current%disabled /><label for="%id[%key]">%value</label></div>}%
                </div>
                (search NOT_EMPTY)~<div><input type="text" placeholder="%search{%s}%" data-search /></div>~
            </div>
        </div>';

        $fields['select'] = '
        <div class="form_line%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %description{<span>%s</span>}%
            <select id="%id{%s}%" name="%id{%s}%"%disabled{ disabled}%%required{ required}%%readonly{ readonly}%>
                %placeholder{<option value="" selected disabled>%s</option>}%
                %options{<option value="%key"%current%disabled>%value</option>}%
            </select>
        </div>';

        $fields['image'] = '
        <div class="form_line clearfix%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %description{<span>%s</span>}%
            <ul class="image-list" data-multi="0" data-id="%id{%s}%">
                %value{<li><div style="background-image:url(\'%value\');"></div><a href="#" class="remove"><i class="fas fa-times"></i></a><input type="hidden" name="%id[%key]"></li>}%
                <li class="new"><a href="#"><i class="fas fa-camera"></i></a></li>
            </ul>
        </div>';

        $fields['images'] = '
        <div class="form_line clearfix%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %description{<span>%s</span>}%
            <ul class="image-list" data-multi="1" data-id="%id{%s}%">
                %value{<li><div style="background-image:url(\'%value\');"></div><a href="#" class="remove"><i class="fas fa-times"></i></a><input type="hidden" name="%id[%key]"></li>}%
                <li class="new"><a href="#"><i class="fas fa-camera"></i></a></li>
            </ul>
        </div>';

        $fields['file'] = '
        <div class="form_line%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %description{<span>%s</span>}%
            <ul class="files-list" data-multi="%multiple{1,null}%" data-id="%id{%s}%">
                %value{<li><div>%value</div><a href="#" class="remove"><i class="fas fa-times"></i></a><input type="hidden" name="%id[%key]" value=""></li>}%
                <li class="new">
                    <a href="#"><i class="fas fa-upload"></i></a>
                    <input type="file" id="%id{%s}%"%disabled{ disabled}%%required{ required}%%accept{ accept="%s"}%%multiple{ multiple}%>
                </li>
            </ul>
        </div>';

        $fields['spacer']   = '
        <div class="form_line line_spacer%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %content{%s}%
        </div>';

        $fields['button']   = '
        <div class="form_line%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            <button>%label{%s}%</button>
        </div>';

        $fields['info']   = '
        <div class="form_line form_line_info%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %value{<div class="info_text">%s</div>}%
        </div>';

        $fields['hidden'] = '
        <input type="hidden" data-id="%id{%s}%" name="%id{%s}%"%value{ value="%s"}%>';

        $fields['custom'] = '
        <div class="form_line%defClass{ %s}%%classes{ %s}%"%id{ data-id="ff-%s"}%%style{%s}%>
            %before_label{%s}%
            (label NOT_EMPTY)~<label for="%id{%s}%">%label{%s}%</label>~
            %after_label{%s}%
            %description{<span>%s</span>}%
            %callback{%s}%
        </div>';

        $fields['custom2'] = '%callback{%s}%';

        return filters()->do_filter( 'default_build_fields', $fields );
    }

    private function fieldConditions( $id, $conditions, $iname ) {
        $cnds = [];
        if( is_array( $conditions[0] ) ) {
            foreach( $conditions as $w ) {
                $itms = count( $w );
                if( $itms == 2 ) {
                    $cnds[] = array_merge( $cnds, [ $w[0], $this->fieldConditions( $id, $w[1], $iname ) ] );
                } else if( $itms == 3 ) {
                    $this->targets['targets'][$w[1]]['target'][] = sprintf( $iname . '[%s]', $id );
                    $cnds[] = [ $w[0], $w[1], $w[2] ];
                }
            }
        } else {
            if( !is_array( $conditions[1] ) || count( $conditions ) == 3 ) {
                $this->targets['targets'][$conditions[1]]['target'][] = sprintf( $iname . '[%s]', $id );
                $cnds[] = [ $conditions[0], $conditions[1], $conditions[2] ?? null ];
            } else if( count( $conditions ) == 2 ) {
                $cnds[] = array_merge( $cnds, [ $conditions[0], $this->fieldConditions( $id, $conditions[1], $iname ) ] );         
            }
        }

        return $cnds;
    }

    private function getConditionsNValues( array $fields, string $iname ) {
        foreach( $fields as $id => $field ) {
            if( isset( $field['when'] ) ) {
                $this->atts['deps'][sprintf( $iname . '[%s]', $id )] = $this->fieldConditions( $id, $field['when'], $iname );
            }

            if( !isset( $this->values[$iname . '[' . $id . ']'] ) ) {
                if( isset( $field['value'] ) ) {
                    $this->setValue( $id, $field['value'] );
                }
            }
        }
    }

    private function anyCondition( array $conditions ) {
        foreach( $conditions as $condition ) {
            switch( $condition[0] ) {
                case '=':
                    if( $condition[2] == $this->values[sprintf( $this->iname, '', $condition[1] )] )
                    return true;
                break;
                case '!=':
                    if( $condition[2] != $this->values[sprintf( $this->iname, '', $condition[1] )] )
                    return true;
                break;
                case '>':
                    if( $this->values[sprintf( $this->iname, '', $condition[1] )] > $condition[2] )
                    return true;
                break;
                case '<':
                    if( $this->values[sprintf( $this->iname, '', $condition[1] )] < $condition[2] )
                    return true;
                break;
                case 'IN':
                    if( in_array( $this->values[sprintf( $this->iname, '', $condition[1] )], $condition[2] ) )
                    return true;
                break;
                case 'NOT_IN':
                    if( !in_array( $this->values[sprintf( $this->iname, '', $condition[1] )], $condition[2] ) )
                    return true;
                break;
            }
        }
        return false;
    }

    private function checkConditions( $id, array $conditions ) {
        if( !is_array( current( $conditions ) ) ) {
            $conditions = [ $conditions ];
        }

        foreach( $conditions as $condition ) {
            $value = $this->values[$condition[1]] ?? NULL;
            switch( $condition[0] ) {
                case '=':
                    if( $condition[2] != $value )
                    return false;
                break;
                case '!=':
                    if( $condition[2] == $value )
                    return false;
                break;
                case '>':
                    if( $value <= $condition[2] )
                    return false;
                break;
                case '<':
                    if( $value >= $condition[2] )
                    return false;
                break;
                case 'IN':
                    if( !in_array( $value, $condition[2] ) )
                    return false;
                break;
                case 'HAS':
                    $vals = $value;
                    foreach( $condition[2] as $val ) {
                        if( array_search( $val, $vals ) === false )
                        return false;
                    }
                break;
                case 'HAS_NOT':
                    $vals = $value;
                    foreach( $condition[2] as $val ) {
                        if( gettype( array_search( $val, $vals ) ) !== 'boolean' )
                        return false;
                    }
                break;
                case 'NOT_IN':
                    if( in_array( $value, $condition[2] ) )
                    return false;
                break;
                case 'EMPTY':
                    if( !empty( $value ) )
                    return false;
                break;
                case 'NOT_EMPTY':
                    if( empty( $value ) )
                    return false;
                break;
                case 'ANY':
                    if( !$this->anyCondition( $condition[1] ) )
                    return false;
                break;
            }
        }
        return true;
    }

    private function currentItem( string $opt_type, string $opt_id, string $list_id ) {
        switch( $opt_type ) {
            case 'select':
                if( isset( $this->values[$opt_id] ) && $this->values[$opt_id] == $list_id )
                return ' selected';
            break;
            case 'radio':
                if( isset( $this->values[$opt_id] ) && $this->values[$opt_id] == $list_id )
                return ' checked';
            break;
            case 'checkboxes':
                if( isset( $this->values[$opt_id . '[' . $list_id . ']' ] ) && !empty( $this->values[$opt_id . '[' . $list_id . ']' ] ) )
                return ' checked';
            break;
        }
        return '';
    }

    private function disableItem( string $type, $current, $value ) {
        switch( $type ) {
            default:
                if( in_array( $current, $value ) )
                return ' disabled';
            break;
        }
        return '';
    }

    private function verifyFiedlCondition( string $st, string $default, array $opts ) {
        $econd  = explode( ' ', $st );
        $cond   = strtolower( $econd[1] );
        $param  = $econd[0];
        switch( strtolower( $cond ) ) {
            case 'empty':
                if( empty( $opts[$param] ) )
                return $default;
            break;
            case 'not_empty':
                if( !empty( $opts[$param] ) )
                return $default;
            break;
        }
        return '';
    }

    private function fieldLine( array $opt, ...$atts ) {
        $types = $this->fieldTypes( ...$atts );
        if( isset( $opt['type'] ) && in_array( $opt['type'], array_keys( $types ) ) ) {
            $content = $types[$opt['type']];
            if( !isset( $opt['noValueHide'] ) || ( $opt['noValueHide'] && !isset( $opt['value'] ) ) ) {

                if( $opt['type'] == 'radio' || $opt['type'] == 'checkboxes' ) {
                    if( !isset( $opt['search'] ) && count( $opt['options'] ) > 10 )
                    $opt['search'] = t( 'Search' );
                }

                preg_match_all( '/\((.*?)\)~([^~]*)/is', $content, $results );
                if( isset( $results[0][0] ) ) {
                    foreach( $results[0] as $k => $line ) {
                        $content = str_replace( $results[0][$k] . '~', $this->verifyFiedlCondition( $results[1][$k], $results[2][$k], $opt ), $content );
                    }
                }

                preg_match_all( '/\%([a-z-_]+)\{(.*?)(\,(.*?))?\}\%/is', $content, $results );
                if( count( $results[0] ) ) {
                    array_map( function( $full, $clean, $replace, $deftitle ) use ( $opt, &$content ) {
                        if( isset( $opt[$clean] ) && $opt[$clean] !== NULL ) {
                            if( is_array( $opt[$clean] ) ) {
                                $list = [];
                                foreach( $opt[$clean] as $list_id => $list_item ) {
                                    $list[] = str_replace( [ '%id', '%key', '%value', '%current', '%disabled' ], [ esc_html( $opt['id'] ), esc_html( $list_id ), $list_item, ( isset( $opt['value'] ) ? $this->currentItem( $opt['type'], $opt['id'], $list_id ) : '' ), ( isset( $opt['disable'] ) ? $this->disableItem( $opt['type'], $list_id, $opt['disable'] ) : '' ) ], $replace );
                                }
                                $content = str_replace( $full, implode( "\n", $list ), $content );
                            } else 
                                $content = str_replace( $full, sprintf( $replace, ( is_object( $opt[$clean] ) ? call_user_func( $opt[$clean], $this->values_arr, $this->values ) : ( $clean == 'value' ? esc_html( $opt[$clean] ) : $opt[$clean] ) ) ), $content );
                        } else if( !empty( $deftitle ) ) {
                            $content = str_replace( $full, esc_html( $deftitle ), $content );
                        } else {
                            $content = str_replace( $full, '', $content );
                        }
                    }, $results[0], $results[1], $results[2], $results[4] );
                }
                return $content;
            }
        }
        return '';
    }

    public function changeInputName( string $iname ) {
        $this->iname = $iname;
        return $this;
    }

    public function setValue( string $id, $value ) {
        $this->setValues( [ $id => $value ] );
        return $this;
    }

    public function setValues( array $values) {
        $this->values_arr = array_merge( $this->values_arr, $values );
        $this->setValues2( $values );
    }

    public function setValues2( array $values, $pid = '' ) {
        foreach( $values as $id => $value ) {
            if( is_array( $value ) ) {
                $this->setValues2( $value, $pid . '[' . $id . ']' );
            } else
            $this->values[$this->iname . $pid . '[' . $id . ']'] = $value;
        }

        return $this;
    }

    public function getValues() {
        return $this->values;
    }

    public function getValuesArray() {
        return \util\etc::filterValues( $this->values_arr );
    }

    public function setAttr( $id, $attr, $value ) {
        $this->fields[sprintf( $this->iname . '[%s]', $id )][$attr] = $value;
        return $this;
    }

    public function formAttributes() {
        if( !empty( $this->targets ) || !empty( $this->atts ) )
        return ' data-fform=\'' . cms_json_encode( array_merge( $this->targets, $this->atts ) ) . '\'';
    }

    private function getFileRecArray( $files, $attr ) {
        $ret = [];
        if( is_array( $files ) ) {
            $current = current( $files );
            if( !is_array( $current ) ) {
                foreach( $files as $key => $file )
                $ret[$key][$attr] = $file;
            } else
                foreach( $files as $k2 => $v2 )
                $ret = array_replace_recursive( $ret, [ $k2 => $this->getFileRecArray( $v2, $attr ) ] );
        } else
        $ret['item'][$attr] = $files;

        return $ret;
    }

    public function getFileRequestsArray() {
        $files  = [];
        if( isset( $_FILES[$this->iname] ) ) {
            foreach( $_FILES[$this->iname] as $attr => $all_files ) {
                foreach( $all_files as $file_name => $file ) {
                    $files = array_replace_recursive( $files, ( [ $this->iname => [ $file_name => $this->getFileRecArray( $file, $attr ) ] ] ) );
                }
            }
        }
        return $files;
    }

    private function getFileRec( $files, $attr, $str ) {
        $ret = [];
        if( is_array( $files ) ) {
            $current = current( $files );
            if( !is_array( $current ) ) {
                foreach( $files as $k1 => $file )
                $ret[$str][$k1][$attr] = $file;
            } else
                foreach( $files as $k2 => $v2 )
                $ret = array_merge_recursive( $ret, $this->getFileRec( $v2, $attr, $str . '[' . $k2 . ']' ) );
        } else
        $ret[$str]['item'][$attr] = $files;

        return $ret;
    }

    public function getFileRequests() {
        $files  = [];
        if( isset( $_FILES[$this->iname] ) ) {
            foreach( $_FILES[$this->iname] as $attr => $all_files ) {
                foreach( $all_files as $file_name => $file ) {
                    $files = array_replace_recursive( $files, ( $this->getFileRec( $file, $attr, $this->iname . '[' . $file_name . ']' ) ) );
                }
            }
        }
        return $files;
    }


    public function uploadFiles( array $newValues ) {
        $uploads    = $this->getFileRequests();
        $uploaded   = $values = [];

        foreach( $this->frequests as $inputId => $fileOptions ) {
            parse_str( $inputId, $val );

            $old_values = \util\etc::searchInArray( $this->values_arr, $val[$this->iname] );
            $values     = !empty( $old_values ) && is_array( $old_values ) ? $old_values : [];
            $new_values = \util\etc::searchInArray( $newValues, $val[$this->iname] );
            $new_values = !empty( $new_values ) && is_array( $new_values ) ? $new_values : [];
            $deleted    = array_diff_key( $values, $new_values );

            // Delete files
            if( !empty( $deleted ) ) {
                foreach( $deleted as $fileId => $file ) {
                    unset( $values[$fileId] );
                    mediaLinks()->deleteItem( $fileId, $fileOptions['category'], $fileOptions['identifierId'] );
                }
            }

            // Check for new uploads
            if( !empty( $uploads[$inputId] ) ) {
                if( !$fileOptions['category'] )
                throw new \Exception( t( 'Wrong media category' ) );

                $rem_count  = count( $values ) - count( $deleted );
                $u_limit    = ( $fileOptions['media-limit'] ?? 1 ) - $rem_count;
                $media      = media( $uploads[$inputId] )
                            ->setType( $fileOptions['category'] )
                            ->setLimit( $u_limit )
                            ->setOptions( $fileOptions );

                if( $fileOptions['identifierId'] )
                $media  ->setTypeId( $fileOptions['identifierId'] );
                
                if( $fileOptions['ownerId'] )
                $media  ->setOwnerId( $fileOptions['ownerId'] );
        
                $image  = $fileOptions['media-image'] ?? NULL;

                if( $image )
                $media  ->isImage();
                if( $image && isset( $image['size'] ) )
                $media  ->imageSize( $image['size'] );

                $values = $values + $media->getUploadId();
            }

            // Update media values
            if( !empty( $values ) )
            $newValues = array_replace_recursive( $newValues, \util\etc::lastLevelValue( $val[$this->iname], $values ) );

            // Set media uploads
            $uploaded[$inputId] = $values;

            // Set the files
            $this->files    += $values;
        }

        // Update the values
        $this->values_arr   = array_replace( $this->values_arr, $newValues );

        return $uploaded;
    }

    public function deleteFiles() {
        foreach( $this->files as $fileId => $fileURL ) {
            mediaLinks()->deleteItem( $fileId );
        }

        return $this;
    }

    public function build( array $fields = [], string $iname = '' ) {
        $markup = '';

        if( empty( $fields ) )
        $fields = $this->fields;
        
        if( $iname === '' )
        $iname = $this->iname;

        $this->getConditionsNValues( $fields, $iname );

        uasort( $fields, function( $a, $b ) {
            if( !isset( $a['position'] ) ) $a = [ 'position' => 99 ];
            if( !isset( $b['position'] ) ) $b = [ 'position' => 99 ];
            if( (double) $a['position'] === (double) $b['position'] ) return 0;
            return ( (double) $a['position'] < (double) $b['position'] ? -1 : 1 );
        } );

        foreach( $fields as $id => $field ) {
            $real_id = sprintf( $iname . '[%s]', $id );

            if( !isset( $field['type'] ) ) {
                // do nothing
            
            // GROUP
            } else if( $field['type'] == 'group' ) {
                $markup .= '<div class="form_line form_lines' . ( isset( $field['classes'] ) ? ' ' . esc_html( $field['classes'] ) : '' ) . ( isset( $field['when'] ) && !$this->checkConditions( $id, $field['when'] ) ? ' hidden" style="display:none;"': ' visible"' ) . ' data-id="ff-' . $real_id . '">';
                
                if( !empty( $field['label'] ) ) {
                    $markup .= '<label>' . esc_html( $field['label'] ) . '</label>';
                }

                if( !empty( $field['description'] ) ) {
                    $markup .= '<span>' . esc_html( $field['description'] ) . '</span>';
                }

                $markup .= $this->build( $field['fields'], $iname . ( !isset( $field['grouped'] ) || $field['grouped'] ? '[' . $id . ']' : '' ) );
                $markup .= '</div>';
            
            // INLINE GRUP
            } else if( $field['type'] == 'inline-group' ) {
                $markup .= '<div class="form_line' . ( isset( $field['classes'] ) ? ' ' . esc_html( $field['classes'] ) : '' ) . ( isset( $field['when'] ) && !$this->checkConditions( $id, $field['when'] ) ? ' hidden" style="display:none;"': ' visible"' ) . ' data-id="ff-' . $real_id . '">';
                
                if( !empty( $field['label'] ) ) {
                    $markup .= '<label>' . esc_html( $field['label'] ) . '</label>';
                }
                
                if( !empty( $field['description'] ) ) {
                    $markup .= '<span>' . esc_html( $field['description'] ) . '</span>';
                }

                $markup .= '<div class="form_group">';
                $markup .= $this->build( $field['fields'], $iname . ( !isset( $field['grouped'] ) || $field['grouped'] ? '[' . $id . ']' : '' ));
                $markup .= '</div>
                </div>';
            
            // DROP DOWN
            } else if( $field['type'] == 'dropdown' ) {
                $markup .= '<div class="form_line form_lines' . ( isset( $field['classes'] ) ? ' ' . esc_html( $field['classes'] ) : '' ) . ( isset( $field['when'] ) && !$this->checkConditions( $id, $field['when'] ) ? ' hidden" style="display:none;"': ' visible"' ) . ' data-fc="' . count( $field['fields'] ) . '" data-id="ff-' . $real_id . '">';
                if( !empty( $field['label'] ) ) {
                    $markup .= '<label>' . esc_html( $field['label'] ) . '</label>';
                }

                foreach( $field['fields'] as $id2 => $fields ) {
                    $newIname   = $iname . ( !isset( $field['grouped'] ) || $field['grouped'] ? '[' . $id . ']' : '' ) . ( !isset( $fields['grouped'] ) || $fields['grouped'] ? '[' . $id2 . ']' : '' );
                    $markup     .= '<div class="form_line form_dropdown">';

                    if( !empty( $fields['label'] ) ) {
                        $markup .= '<div>';

                        if( !empty( $fields['before_label'] ) )
                        $markup .= $fields['before_label'];
                        $title  = $this->values[$newIname . '[title]'] ?? $fields['label'];
                        $markup .= '<span>' . esc_html( $title ) . '</span><i class="fas fa-angle-down"></i></div>';
                    }

                    $markup .= '<div>';

                    if( isset( $fields['title'] ) ) {
                        $markup .= '<div class="fld_t">' . esc_html( $fields['title'] ) . '</div>';
                    }

                    $markup .= $this->build( $fields['fields'], $newIname );

                    $markup .= '</div>';

                    if( !empty( $fields['fieldsOut'] ) ) {
                        $markup .= '<div>';
                        $markup .= $this->build( $fields['fieldsOut'], $newIname );
                        $markup .= '</div>';
                    }

                    $markup .= '</div>';
                }

                $markup .= '
                </div>';
            
            // REPEATER
            } else if( $field['type'] == 'repeater' ) {
                $v_markup = '';
                parse_str( $real_id, $val );
                $values = \util\etc::searchInArray(  $this->values_arr, $val[$this->iname] );

                if( $values ) {
                    $fields = $field['fields'];
                    foreach( $values as $k => $val ) {
                        $v_markup .= '<div class="form_line">
                        <div class="fields_row">';
                        $v_markup .= $this->build( $fields, $iname . '[' . $id . '][' . $k . ']' );
                        $v_markup .= '
                        <div class="form_line l_opts">
                            <a href="#" class="remove ' . ( $field['remove_button']['class'] ?? '' ) . '">' . ( $field['remove_button']['title'] ?? '<i class="fas fa-times"></i>' ) . '</a>
                        </div>';
                        $v_markup .= '</div>
                        </div>';
                    }
                }

                $n_markup = '<div class="form_line">
                <div class="fields_row">';
                $n_markup .= $this->build( $field['fields'], $iname . '[' . $id . '][#NEW#]' );
                $n_markup .= '
                <div class="form_line l_opts">
                    <a href="#" class="remove ' . ( $field['remove_button']['class'] ?? '' ) . '">' . ( $field['remove_button']['title'] ?? '<i class="fas fa-times"></i>' ) . '</a>
                </div>';
                $n_markup .= '</div>
                </div>';
                $vis = !isset( $field['when'] ) || $this->checkConditions( $id, $field['when'] );
                $psv = array_merge( $field, [ 'id' => $real_id, 'cleanid' => $id, 'style' => ( !$vis ? ' style=\'display:none;\'': '' ) ] );
                $psv['defClass'] = ( !$vis ? 'hidden' : 'visible' );

                $markup .= $this->fieldLine( $psv, $v_markup, $n_markup );
            
            // SINGLE
            } else {
                // Lookup for media
                if( $field['type'] == 'image' ) {
                    $this->frequests[$real_id] = [ 'handler' => 'image', 'category' => ( $field['category'] ? site()->app->getMediaCategoryId( $field['category'] ) : NULL ), 'identifierId' => $field['identifierId'] ?? NULL, 'ownerId' => $field['ownerId'] ?? NULL, 'media-limit' => 1, 'media-image' => [ 'size' => '1000' ], 'media-allowed' => ( $field['accept'] ?? filters()->do_filter( 'default-media-image-allowed', '.jpg, .jpeg, .gif, .png, .webp, .svg' ) ) ];
                } else if( $field['type'] == 'images' ) {
                    $this->frequests[$real_id] = [ 'handler' => 'image', 'category' => ( $field['category'] ? site()->app->getMediaCategoryId( $field['category'] ) : NULL ), 'identifierId' => $field['identifierId'] ?? NULL, 'ownerId' => $field['ownerId'] ?? NULL, 'media-limit' => ( $field['media-limit'] ?? 10 ), 'media-image' => [ 'size' => '1000' ], 'media-allowed' => ( $field['accept'] ?? filters()->do_filter( 'default-media-image-allowed', '.jpg, .jpeg, .gif, .png, .webp, .svg' ) ) ];
                } else if( $field['type'] == 'file' ) {
                    $this->frequests[$real_id] = [ 'handler' => 'file', 'category' => ( $field['category'] ? site()->app->getMediaCategoryId( $field['category'] ) : NULL ), 'identifierId' => $field['identifierId'] ?? NULL, 'ownerId' => $field['ownerId'] ?? NULL, 'media-limit' => ( empty( $field['multi'] ) ? 1 : ( $field['media-limit'] ?? 10 ) ), 'media-allowed' => ( $field['accept'] ?? filters()->do_filter( 'default-media-file-allowed', '.pdf, .doc, .docx, .zip, .rar' ) ) ];
                }

                $vis = !isset( $field['when'] ) || $this->checkConditions( $id, $field['when'] );
                $psv = array_merge( $field, [ 'id' => $real_id, 'cleanid' => $id, 'style' => ( !$vis ? ' style=\'display:none;\'': '' ) ] );
                $psv['defClass'] = ( !$vis ? 'hidden' : 'visible' );

                parse_str( $real_id, $val );
                $value = \util\etc::searchInArray( $this->values_arr, $val[$this->iname] );

                if( $value ) {
                    $psv['value'] = $value;
                } else if( isset( $field['value'] ) ) {
                    $psv['value'] = $field['value'];
                } else {
                    $psv['value'] = NULL;
                }

                $markup .= $this->fieldLine( $psv );
            }
        }

        return $markup;
    }

}