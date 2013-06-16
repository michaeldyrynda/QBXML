<?php
/**
 * QuickBooks product representation
 *
 * @package    QBWC
 * @subpackage QBProduct
 * @copyright  2013 IATSTUTI
 * @author     Michael Dyrynda <michael@iatstuti.net>
 */

class QBXML_Product
{
    /**#@+
     * Class constant
     */
    /** Text string describing extra data for website category */
    const EXT_WEBSITE_CATEGORY = 'Website Category';

    /** Text string describing extra data for whether a product should be hidden */
    const EXT_WEBSITE_HIDE     = 'Hide From Website';
    /**#@-*/


    /**#@+
     * Private class property
     *
     * @access  private
     */

    /** Temporarily store the product for parsing */
    private $product;

    /** Store product properties */
    private $properties;
    /**#@-*/


    /**
     * Class constructor
     *
     * @param   SimplepXMLElement $product Product to be parsed
     * @return  void
     */
    public function __construct($product)
    {
        $this->product = $product;

        $this->parse();

        unset($this->product);
    }


    /**
     * Overload the __set method
     *
     * @param  string $key   Property key to be set
     * @param  mixed  $value Value to set for the property
     */
    public function __set($key, $value)
    {
        // Cast SimpleXMLElement to string value
        $this->properties[$key] = (string) $value;
    }


    /**
     * Overload the __get method
     *
     * @throws  QBXML_Product_Exception If property does not exist
     * @param   string $key Key to return
     * @return  mixed
     */
    public function __get($key)
    {
        if ( array_key_exists($key, $this->properties) ) {
            return $this->properties[$key];
        }

        throw new QBXML_Product_Exception(
            sprintf('Property "%s" does not exist', $key)
        );
    }


    /**
     * Determine whether or not a product is sellable
     *
     * @return  boolean
     */
    public function isSellable()
    {
        return $this->for_sale == 'Y';
    }


    /**
     * Parse the QBXML data for this product into a simpler format that we can
     * work with
     *
     * @return  boolean
     */
    private function parse()
    {
        $this->validateProduct();

        $this->sku         = strtoupper($this->product->Name);
        $this->description = $this->product->SalesDesc;
        $this->price       = $this->product->SalesPrice;
        $this->for_sale     = 'Y';
        $this->quantity    = $this->product->QuantityOnHand;

        if ( $this->quantity <= 0 ) {
            $this->for_sale = 'N';
        }

        $this->parseVendor();

        if ( isset($this->product->DataExtRet) ) {
            $this->parseDataExtra($this->product->DataExtRet);
        }

        return true;
    }


    /**
     * Ensure that all necessary properties exist for this product
     *
     * @access  private
     * @throws  QBXML_Product_Exception If Name not set
     * @throws  QBXML_Product_Exception If SalesDesc not set
     * @throws  QBXML_Product_Exception If SalesPrice not set
     * @throws  QBXML_Product_Exception If PrefVendorRef not set
     * @throws  QBXML_Product_Exception If PrefVendorRef FullName not set
     * @return  bool
     */
    private function validateProduct()
    {
        if ( ! isset($this->product->Name) ) {
            throw new QBXML_Product_Exception('Product name not set');
        }

        if ( ! isset($this->product->SalesDesc) ) {
            throw new QBXML_Product_Exception(
                sprintf(
                    'SalesDesc not set for "%s"',
                    $this->product->Name
                )
            );
        }

        if ( ! isset($this->product->SalesPrice) ) {
            throw new QBXML_Product_Exception(
                sprintf(
                    'SalesPrice not set for "%s"',
                    $this->product->Name
                )
            );
        }

        if ( ! isset($this->product->PrefVendorRef) ) {
            throw new QBXML_Product_Exception(
                sprintf(
                    'PrefVendorRef does not exist for "%s"',
                    $this->product->Name
                )
            );
        }

        if ( ! isset($this->product->PrefVendorRef->FullName) ) {
            throw new QBXML_Product_Exception(
                sprintf(
                    'PrefVendorRef FullName not set for "%s"',
                    $this->product->Name
                )
            );
        }

        return true;
    }


    /**
     * Set the Vendor ListID and name for this product
     *
     * @access  private
     * @return  bool
     */
    private function parseVendor()
    {
        $this->list_id     = '';
        $vendor            = $this->product->PrefVendorRef;
        $this->vendor_name = $vendor->FullName;

        if ( isset($vendor->ListID) ) {
            $this->list_id = $vendor->ListID;
        }

        return true;
    }


    /**
     * Recursive function to parse the DataExtRet object, to extract the website
     * category and whether or not the product should be hidden
     *
     * @access  private
     * @param   array|object $data_extra If array, recurse through else process as is
     * @return  boolean
     */
    private function parseDataExtra($data_extra)
    {
        if ( isset($data_extra->DataExtName) ) {
            switch ($data_extra->DataExtName) {
                case self::EXT_WEBSITE_HIDE:
                    /*
                     * If this extra data type exists, hide the product from
                     * X-Cart, irrespective of the value is
                     */
                    $this->for_sale = 'N';
                    break;
                case self::EXT_WEBSITE_CATEGORY:
                    /*
                     * Set the website category using the extra data type
                     * value
                     */
                    $this->website_category = $data_extra->DataExtValue;
                    break;
                default:
                    // no-op
                    break;
            }
        } else if ( is_array($data_extra) ) {
            foreach ($data_extra as $extra) {
                $this->parseDataExtra($extra);
            }
        }

        return true;
    }


}
