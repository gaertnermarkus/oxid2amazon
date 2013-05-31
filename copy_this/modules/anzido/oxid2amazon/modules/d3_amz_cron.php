<?php

ini_set("display_errors", 1);

class d3_amz_cron extends d3_amz_cron_parent
{

    public function d3_exportProducts()
    {
        $this->blD3 = true;
        $this->exportProducts();
        $this->showStatus();
        echo "\n";

        //sofern die Anlage der Datei erfolgreich war, wird noch ein upload gemacht
        if ($this->_blStatus)
        {
            $this->uploadProducts();
            $this->showStatus();
            echo "\n";
        }
    }

    public function d3_exportProductImages()
    {
        $this->blD3 = true;
        $this->exportProductImages();
        $this->showStatus();
        echo "\n";

        //sofern die Anlage der Datei erfolgreich war, wird noch ein upload gemacht
        if ($this->_blStatus)
        {
            $this->uploadProductImages();
            $this->showStatus();
            echo "\n";
        }
    }

    public function d3_exportProductPrices()
    {
        $this->blD3 = true;
        $this->exportProductPrices();
        $this->showStatus();
        echo "\n";

        //sofern die Anlage der Datei erfolgreich war, wird noch ein upload gemacht
        if ($this->_blStatus)
        {
            $this->uploadProductPrices();
            $this->showStatus();
            echo "\n";
        }
    }

    public function d3_exportInventory()
    {
        $this->blD3 = true;
        $this->exportInventory();
        $this->showStatus();
        echo "\n";

        //sofern die Anlage der Datei erfolgreich war, wird noch ein upload gemacht
        if ($this->_blStatus)
        {
            $this->uploadInventory();
            $this->showStatus();
            echo "\n";
        }
    }

    public function d3_exportRelations()
    {
        $this->blD3 = true;
        $this->exportRelations();
        $this->showStatus();
        echo "\n";

        //sofern die Anlage der Datei erfolgreich war, wird noch ein upload gemacht
        if ($this->_blStatus)
        {
            $this->uploadRelations();
            $this->showStatus();
            echo "\n";
        }
    }

    public function render()
    {
        if ($this->blD3)
            die("finished");
        else
            parent::render();
    }

}