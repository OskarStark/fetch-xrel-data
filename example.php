<?php

include_once('xRel.class.php');

$xrel = new xRel();

// select movie, game etc.
$xrel->setSection('movie');

// set the search string
$xrel->setSearchString('zoow');

// limit of products
//$xrel->setLimit(10);

// if you just want to get product data, set this to false, otherwise you will get all informations from all releases which are found
$xrel->getReleases(true);

// if you want to get releases, the you can set a limit for releases per product.
//$xrel->setReleasesLimit(5);

// perform the search
$xrel->doSearch();


// show you the used params
var_dump($xrel->getSearchparams());

// your result
print_r($xrel->getResults());
