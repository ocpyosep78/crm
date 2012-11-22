<?php

function page_products() { return SNP::snp('commonList', 'Product.Regular'); }
function page_materials(){ return SNP::snp('commonList', 'Product.Material'); }
function page_services() { return SNP::snp('commonList', 'Product.Service'); }
function page_others()   { return SNP::snp('commonList', 'Product.Others'); }

function page_createProducts() { return SNP::snp('createItem', 'Product.Regular'); }
function page_createMaterials(){ return SNP::snp('createItem', 'Product.Material'); }
function page_createServices() { return SNP::snp('createItem', 'Product.Service'); }
function page_createOthers()   { return SNP::snp('createItem', 'Product.Others'); }

function page_productsInfo($id){ return SNP::snp('viewItem', 'Product'); }