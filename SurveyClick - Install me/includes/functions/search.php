<?php

$item->object = surveys();
if( isset( $_GET['q'] ) ) {
    $item->object->search( $_GET['q'] );
}

function searched_string() {
    return ( isset( $_GET['q'] ) ? esc_html( $_GET['q'] ) : null );  
}

function results() {
    global $item;
    return $item->object;
}

function count_results() {
    global $item;
    return $item->object->count();
}

function exists() {
    return (bool) count_results();
}