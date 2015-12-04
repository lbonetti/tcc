<?php

use Adianti\Control\TPage;
use Adianti\Widget\Container\TTable;
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
        $campusNome = new Adianti\Widget\Form\TEntry('campusNome');
        
        $row = $table->addRow();
        $row->addCell(new TLabel('Câmpus:'));
        $box = new Adianti\Widget\Container\THBox;
        $box->add($campusID);
        $box->add($campusNome)->style = 'width: 75%; display: inline-block';
        $row->addCell($box);
        
        $this->form->setFields(array($campusID, $campusNome));
        
        parent::add($this->form);
        
    }

}
