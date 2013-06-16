<?php
/**
 * Class used to parse the QBXML item array
 *
 * @package     QBWC
 * @subpackage  QBXMLParser
 * @copyright   2013 IATSTUTI
 * @author      Michael Dyrynda <michael@iatstuti.net>
 */

class QBXML_Parser
{
    /**#@+
     * Private class variable
     *
     * @access  private
     */

    /** Store the QBXML envelope */
    private $qbxml;

    /** Store the parsed SimpleXML object */
    private $qb;

    /** Store the parsed objects */
    private $products;

    /** Store a count of all the products */
    private $product_count;
    /**#@-*/


    /**
     * Class constructor
     *
     * @access  public
     * @param   string $qbxml Raw QBXML string from connector
     */
    public function __construct($qbxml)
    {
        $this->qbxml = $qbxml;
    }


    /**
     * Parse the raw XML string into a SimpleXML object
     *
     * @access  public
     * @return  QBXML_Parser
     */
    public function parse()
    {
        $this->qb            = simplexml_load_string($this->qbxml);
        $this->products      = $this->qb
                                    ->QBXMLMsgsRs
                                    ->ItemInventoryQueryRs
                                    ->ItemInventoryRet;
        $this->product_count = count($this->products);

        return $this;
    }


    /**
     * Return the SimpleXML object
     *
     * @access  public
     * @return  object The parsed SimplXML object
     */
    public function getObject()
    {
        return $this->qb;
    }


    /**
     * The parsed products
     *
     * @access  public
     * @return  array
     */
    public function getProducts()
    {
        return $this->products;
    }


    /**
     * Return a count of returned products
     *
     * @access  public
     * @return  int
     */
    public function productCount()
    {
        return $this->product_count;
    }


}
