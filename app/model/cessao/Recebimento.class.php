<?php

use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRecord;
use Adianti\Database\TRepository;

/*
 * Copyright (C) 2015 Anderson
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
 * Cessao Active Record
 * @author  Lucas
 */
class Recebimento extends TRecord
{
    const TABLENAME = 'recebimento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $campus;
    private $items;
    private $srp;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('entrada');
        parent::addAttribute('observacao');
        parent::addAttribute('campus_id');
        parent::addAttribute('srp_id');
    }

    
    /**
     * Method set_campus
     * Sample of usage: $cessao->campus = $object;
     * @param $object Instance of Campus
     */
    public function set_campus(Campus $object)
    {
        $this->campus = $object;
        $this->campus_id = $object->id;
    }
    
    /**
     * Method get_campus
     * Sample of usage: $cessao->campus->attribute;
     * @returns Campus instance
     */
    public function get_campus()
    {
        // loads the associated object
        if (empty($this->campus))
            $this->campus = new Campus($this->campus_id);
    
        // returns the associated object
        return $this->campus;
    }
    
    
    /**
     * Method addItem
     * Add a Item to the Cessao
     * @param $object Instance of Item
     */
    public function addItem(Item $object)
    {
        $this->items[] = $object;
    }
    
    /**
     * Method getItems
     * Return the Cessao' Item's
     * @return Collection of Item
     */
    public function getItems()
    {
        return $this->items;
    }
    
    /**
     * Method set_srp
     * Sample of usage: $cessao->srp = $object;
     * @param $object Instance of Srp
     */
    public function set_srp(Srp $object)
    {
        $this->srp = $object;
        $this->srp_id = $object->id;
    }
    
    /**
     * Method get_srp
     * Sample of usage: $cessao->srp->attribute;
     * @returns Srp instance
     */
    public function get_srp()
    {
        // loads the associated object
        if (empty($this->srp))
            $this->srp = new Srp($this->srp_id);
    
        // returns the associated object
        return $this->srp;
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        $this->items = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {
    
        // load the related Item objects
        $repository = new TRepository('ItemRecebimento');
        $criteria = new TCriteria;
        $criteria->setProperty('order', 'item_id');
        $criteria->add(new TFilter('recebimento_id', '=', $id));
        $recebimento_itens = $repository->load($criteria);
        if ($recebimento_itens)
        {
            foreach ($recebimento_itens as $recebimento_item)
            {
                $item = new Item( $recebimento_item->item_id );
                $item->quantidade = $recebimento_item->quantidade;
                $item->total = $item->quantidade * $item->valorUnitario;
                $this->addItem($item);
            }
        }
    
        // load the object itself
        return parent::load($id);
    }

    /**
     * Store the object and its aggregates
     */
    public function store()
    {
        // store the object itself
        parent::store();
    
        // delete the related ItemRecebimento objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('recebimento_id', '=', $this->id));
        $repository = new TRepository('ItemRecebimento');
        $repository->delete($criteria);
        // store the related ItemCessao objects
        if ($this->items)
        {
            foreach ($this->items as $item)
            {
                $recebimento_item = new ItemRecebimento();
                $recebimento_item->item_id = $item->id;
                $recebimento_item->recebimento_id = $this->id;
                $recebimento_item->quantidade = $item->quantidade;
                $recebimento_item->store();
            }
        }
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        $id = isset($id) ? $id : $this->id;
        // delete the related ItemRecebimento objects
        $repository = new TRepository('ItemRecebimento');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('recebimento_id', '=', $id));
        $repository->delete($criteria);
        
    
        // delete the object itself
        parent::delete($id);
    }


}
