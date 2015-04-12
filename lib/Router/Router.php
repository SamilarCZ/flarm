<?php

    namespace Router;


    use FLaRM\FLaRM;

class Router
{

    private $urlBits = array();
    private $urlPath;

    public function __construct(FLaRM $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Nastaví adresu URL
     * @param String $path adresa URL
     */
    public function setURLPath($path)
    {
        $this->urlPath = $path;
    }

    /**
     * Získá data z aktuálního požadavku
     * @return void
     */
    public function getURLData()
    {
        $urldata = (isset($_GET['page'])) ? $_GET['page'] : '';
        $this->urlPath = $urldata;
        if ($urldata == '') {
            $this->urlBits[] = '';
            $this->urlPath = '';
        } else {
            $data = explode('/', $urldata);
            while (!empty($data) && strlen(reset($data)) === 0) {
                array_shift($data);
            }
            while (!empty($data) && strlen(end($data)) === 0) {
                array_pop($data);
            }
            print_r($this->array_trim($data));
            $this->urlBits = $this->array_trim($data);
        }
    }

    public function getURLBits()
    {
        return $this->urlBits;
    }

    public function getURLBit($whichBit)
    {
        return (isset($this->urlBits[$whichBit])) ? $this->urlBits[$whichBit] : 0;
    }

    public function getURLPath()
    {
        return $this->urlPath;
    }

    private function array_trim($array)
    {
        while (!empty($array) && strlen(reset($array)) === 0) {
            array_shift($array);
        }

        while (!empty($array) && strlen(end($array)) === 0) {
            array_pop($array);
        }

        return $array;
    }

    public function buildURL($bits, $qs, $admin)
    {
        $admin = ($admin == 1) ? $this->registry->getSetting('admin_folder') . '/' : '';
        $the_rest = '';
        foreach ($bits as $bit) {
            $the_rest .= $bit . '/';
        }
        $the_rest = ($qs != '') ? $the_rest . '?&' . $qs : $the_rest;
        return $this->registry->getSetting('siteurl') . $admin . $the_rest;

    }


}

?>