<?php

$item->object = surveys();
if( isset( $item->params[0] ) ) {
    $item->object->setId( (int) $item->params[0] );
}
$item->object->getObject();

function survey() {
    global $item;
    return $item->object;
}

function exists() {
    global $item;
    $info = $item->object->getObject();
    return !empty( $info ) ?: 0;
}