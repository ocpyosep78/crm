<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



oSQL();

class PDF_TechVisits_Provider extends SQL{

    private $visit;

    /**
     * Initialize PDF object
     */
    public function __construct( $id ){

        if( empty($id) ) die('Faltan parámetros para procesar su solicitud');

        parent::__construct();

        $this->visit = $this->getTechVisit( $id );

        if( empty($this->visit['id_sale']) ) die('No se encontró la constancia técnica buscada.');

    }


/***************
** E X T E R N A L   S O U R C E S
***************/

    public function getData(){

        return $this->visit;

    }

    public function getCustomerInfo(){

        $v = $this->visit + array('installDate' => NULL);

        # Calculate warranty void status
        if( empty($v['installDate']) || empty($v['warranty']) ) {
            $warranty = false;
        }else{
            $void = date('Y-m-d', strtotime("{$v['installDate']} + {$v['warranty']} months"));
            $warranty = $void > $v['date'] ? 1 : 0;
        }

        # Fix install date in its parts
        list($insY, $insM, $insD) = $v['installDate']
            ? explode('-', $v['installDate'])
            : array('----', '--', '--');
        $insY = substr($insY, 2);

        return array(
            'name'			=> $v['customer'],
            'contact'		=> $v['contact'],
            'address'		=> $v['address'],
            'phone'			=> $v['phone'],
            'number'		=> $v['custNumber'],
            'subscribed'	=> $v['subscribed'],
            'warranty'		=> $warranty,
            'installDate'	=> array('d' => $insD, 'm' => $insM, 'y' => $insY),
        );

    }

    public function getBodyInfo(){

        $v = $this->visit + array('costDollars' => NULL);

        return array(
            'system'	=> $v['system'],
            'reason'	=> $v['reason'],
            'outcome'	=> $v['outcome'],
            'complete'	=> $v['complete'],
            'excuse'	=> $v['ifIncomplete'],
            'used'		=> $v['usedProducts'],
            'cost'		=> $v['cost'],
            'costUSS'	=> $v['costDollars'],
            'invoice'	=> $v['invoice'],
            'order'		=> $v['order'],
            'pending'	=> $v['pendingEstimate'],
            'quality'	=> $v['quality'],
        );

    }

    public function getRelatedInvoice(){

        $v = $this->visit;

        return array(
            'cost'			=> $v['currency'] == '$' ? $v['cost'] : '',
            'costDollars'	=> $v['currency'] == 'U$S' ? $v['cost'] : '',
            'invoice'		=> $v['invoice'],
        );

    }

    public function getPeriod(){
        $this->visit['starts'] || ($this->visit['starts'] = ':');
        $this->visit['ends'] || ($this->visit['ends'] = ':');
        
        list($times['startsH'], $times['startsM']) = explode(':', $this->visit['starts']);
        list($times['endsH'], $times['endsM']) = explode(':', $this->visit['ends']);

        return $times;
    }

}