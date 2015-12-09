<?php

use Adianti\Control\TPage;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Wrapper\TDBSeekButton;

/*
 * Copyright (C) 2015 Aluno
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * Description of RecebimentoList
 *
 * @author Aluno
 */
class RecebimentoList extends TPage {
    private $form;
    private $datagrid;
    private $pageNavigation;
    private $loaded;
    
    function __construct() {
        parent::__construct();
        
        $this->form = new TForm('form_recebimento');
        $this->form->class = 'tform'; //CSS Class
        
        $table = new TTable;
        $table->width='100%';
        $this->form->add($table);
        
        $row = $table->addRow();
        $row->class = 'tformtitle';
        $row->addCell(new TLabel('Consulta Recebimento'))->colspan=2;
        
        $campusID = new TDBSeekButton('campusID', 'saciq', 'form_recebimento', 'Campus', 'nome', 'campusID', 'campusNome');
        $campusID->setSize(50);
        $campusNome = new TEntry('campusNome');
        $campusNome->setSize('100%');
        
        $row = $table->addRow();
        $row->addCell(new TLabel('Câmpus:'));
        $box = new THBox;
        $box->add($campusID);
        $box->add($campusNome)->style = 'width: 75%; display: inline-block';
        $row->addCell($box);
        
        $this->form->setFields(array($campusID, $campusNome));
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Recebimento_filter_data') );
        
        $btnBusca = new TButton('btnBusca');
        //$btnNovo = newTButton('btnNovo');
        
        $btnBusca->setAction(new Adianti\Control\TAction(array($this, 'onSearch')),'Buscar');
        $btnBusca->setImage('ico_new.png');
        
        $this->form->addField($btnBusca);
        
        $buttons_box = new THBox;
        $buttons_box->add($btnBusca);
        
        $row=$table->addRow();
        $row->class='tformaction'; //CSS
        $row->addCell($buttons_box)->colspan=2;
        
        // creates a Datagrid
        parent::include_css('app/resources/custom-table.css');
        $this->datagrid = new TDataGrid;
        $this->datagrid->class = 'tdatagrid_table customized-table';
        $this->datagrid->setHeight(320);
        $this->datagrid->makeScrollable();
        $this->datagrid->disableDefaultClick();


        // creates the datagrid columns
        $id = new TDataGridColumn('id', 'ID', 'right', 80);
        $srp = new TDataGridColumn('numeroSRP', 'Nº SRP', 'left', 100);
        $campus = new TDataGridColumn('campus', 'Campus', 'left', 250);
        $data = new TDataGridColumn('entrada', 'Data', 'left', 100);


        // add the columns to the DataGrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($srp);
        $this->datagrid->addColumn($campus);
        $this->datagrid->addColumn($data);


        /*// creates datagrid actions
        $action1 = new TDataGridAction(array('DocCessaoForm', 'onReload'));
        $action1->setLabel('Gerar Documento');
        $action1->setImage('fa:file-pdf-o fa-fw');
        $action1->setField('id');*/


        // add the actions to the datagrid
        //$this->datagrid->addAction($action1);

        // create the datagrid model
        $this->datagrid->createModel();


        //limpar a sessao com detalhes de itens e cessao
        //TSession::delValue('cessao_itens');
        //TSession::delValue('SRP_id');
        //TSession::delValue('form_cessao');

        // create the page container
        //$container = TVBox::pack($this->form, $this->datagrid);
        $container = new TTable;       
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->addRow()->addCell($this->form);
        $container->addRow()->addCell($this->datagrid);
        //$container->addRow()->addCell($this->pageNavigation);
        parent::add($container);
        
        //parent::add($this->form);
        
    }
    
    public function onSearch($param){
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('Recebimento_filter_campusID',   NULL);

        if (isset($data->campusID) AND ($data->campusID)) {
            $filter = new TFilter('campus_id', '=', "{$data->campusID}"); // create the filter
            TSession::setValue('Recebimento_filter_campusID',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Recebimento_filter_data', $data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'saciq'
            TTransaction::open('saciq');
            //TTransaction::setLogger(new TLoggerTXT('c:\array\file.txt'));
            
            // creates a repository for Cessao
            $repository = new TRepository('Recebimento');
            //$limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            //$criteria->setProperty('limit', $limit);
            //$criteria->add(new TFilter('aprovado', '=', '0'));
            

            if (TSession::getValue('Recebimento_filter_campusID')) {
                $criteria->add(TSession::getValue('Recebimento_filter_campusID')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $object->entrada = TDate::date2br($object->entrada);
                    $object->numeroSRP = $object->srp->numeroSRP;
                    $object->campus = $object->campus->nome;
                    
                    
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            //$count= $repository->count($criteria);
            
            //$this->pageNavigation->setCount($count); // count of records
            //$this->pageNavigation->setProperties($param); // order, page
            //$this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            if ($e->getCode() == 23000) {
                new TMessage('error', '<b>Registro duplicado</b><br>Verifique os campos inseridos e tente novamente');
            } else
            if ($e->getCode() == 0) {
                new TMessage('error', '<b>Error</b> <br>' . $e->getMessage());
            } else {
                new TMessage('error', '<b>Error Desconhecido</b> <br>Código: ' . $e->getCode());
            }
            // desfazer todas as operacoes pendentes
            TTransaction::rollback();
        }
    }

}
