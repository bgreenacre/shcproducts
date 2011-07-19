<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * Sears Holding Company Products Wordpress plugin.
 *
 * Provides the ability to import products via the Sears API and storing in
 * wordpress as custom post type.
 *
 * @author Brian Greenacre and Kyla Klein
 * @package shcproducts
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Wed 15 Jun 2011 07:32:09 PM
 */

// -----------------------------------------------------------------------------

/**
 * Library_Sears_Api_Cart
 *
 */
class Library_Sears_Api_Cart extends Library_Sears_Api {

    public $products_to_add = array(
        'catalogId'     => array(),
        'catentry_id'   => array(),
        'quantity'      => array(),
    );

    public function __construct($group = NULL, $parent = NULL)
    {
        parent::__construct($group, $parent);

        $this->content_type = 'xml';
    }

    protected function _load()
    {
        parent::_load();

        if ($this->success())
        {
            // Cache the session key for the cart.
            if ($this->method() == 'AddtoCart' AND ! Library_Sears_Api::session())
            {
                Library_Sears_Api::session((string) $this->_object->ServiceHeaders->clientSessionKey);
            }

            if (empty($this->_object->Shoppingcart))
            {
                $this->_total_rows = 0;
                $this->_data = array();
            }
            else
            {
                $this->_total_rows = 1;
                $this->_data = array(& $this->_object->Shoppingcart);
            }
        }
    }

    public function view()
    {
        $this->method('ViewCart');

        return $this;
    }

    /**
     * add
     *
     *  // Typical response from addtocart current() method.
     *       object SimpleXMLElement(6) {
     *           public OrderId => string(9) "243090446"
     *           public CatalogId => string(5) "12605"
     *           public OrderItems => object SimpleXMLElement(1) {
     *               public OrderItem => object SimpleXMLElement(26) {
     *                   public OOSMessage => object SimpleXMLElement(0)
     *                   public ImageURL => string(173) "http://c.shld.net/rpx/i/s/pi/mp/1645/218839052p?src=http%3A%2F%2Fimagehost.vendio.com%2Fa%2F35058039%2Fview%2FAEYBOAF8-P184118.jpg&d=b03c0e9d77614c6a438f1cbd3551ed1f99659c0b"
     *                   public PartNo => string(12) "SPM218839052"
     *                   public ManufacturePartNo => string(9) "MOV232562"
     *                   public DisplayPartNumber => string(13) "SPM218839052P"
     *                   public Qty => string(1) "1"
     *                   public Soldby => string(24) "Sold by Prints and Stuff"
     *                   public ArrivalMethods => object SimpleXMLElement(2) {
     *                       public AvailableArrivalMethod => object SimpleXMLElement(2) {
     *                           public AvailableArrivalMethodName => string(4) "Ship"
     *                           public AvailableFFMCenter => string(2) "VD"
     *                       }
     *                       public SelectedArrivalMethod => object SimpleXMLElement(3) {
     *                           public SelectedArrivalMethodName => string(4) "Ship"
     *                           public SelectedFFMCenter => string(2) "VD"
     *                           public SelectedStore => object SimpleXMLElement(0)
     *                       }
     *                   }
     *                   public ParentPartNumber => string(12) "SPM218839052"
     *                   public Price => string(6) "$25.00"
     *                   public SalePrice => string(5) "25.00"
     *                   public RegularPrice => string(5) "25.00"
     *                   public MappedPrice => object SimpleXMLElement(0)
     *                   public MapIndicator => string(1) "0"
     *                   public MapPriceDescription => object SimpleXMLElement(0)
     *                   public TotalPrice => string(6) "$25.00"
     *                   public CatEntryId => string(10) "1605698544"
     *                   public ItemDescription => object SimpleXMLElement(0)
     *                   public BrandName => object SimpleXMLElement(0)
     *                   public OrderItemID => string(9) "856606857"
     *                   public Variant => string(12) "NONVARIATION"
     *                   public PromotionDetails => object SimpleXMLElement(0)
     *                   public ItemTotal => string(6) "$25.00"
     *                   public GiftRegistryInfo => object SimpleXMLElement(0)
     *                   public AvailableProductOptions => object SimpleXMLElement(0)
     *                   public SelectedProductOptions => object SimpleXMLElement(0)
     *               }
     *           }
     *           public CartErrorMessage => object SimpleXMLElement(0)
     *           public IsPayPalEligible => string(1) "Y"
     *           public Summary => object SimpleXMLElement(9) {
     *               public AssociateDiscount => string(5) "$0.00"
     *               public EstimatedPreTaxTotal => string(6) "$31.25"
     *               public SubTotal => string(6) "$25.00"
     *               public EstimatedDeliveryCharge => string(5) "$0.00"
     *               public OversizedDeliveryCharge => string(5) "$0.00"
     *               public StandardShippingCharge => string(5) "$6.25"
     *               public ShippingSavings => string(5) "$0.00"
     *               public CouponDiscount => string(5) "$0.00"
     *               public TotalSavings => string(5) "$0.00"
     *           }
     *       }
     *   }
     *
     * @param int $quantity = 1
     * @param string $catalog_id = NULL
     * @param string $catentry_id = NULL
     * @return void
     */
    public function add($quantity = 1, $catalog_id = NULL, $catentry_id = NULL)
    {
        if ($catentry_id === NULL)
        {
            if ($this->_parent)
            {
                $this->products_to_add['catalogId'][] = $this->_parent->catalogid;
                $this->products_to_add['catentry_id'][] = $this->_parent->catentryid;
                $this->products_to_add['quantity'][] = $quantity;
            }
            else
            {
                throw new Exception('No product catalog ID given for add to cart');
            }
        }
        else
        {
            $this->products_to_add['catalogId'][] = $catalog_id;
            $this->products_to_add['catentry_id'][] = $catentry_id;
            $this->products_to_add['quantity'][] = $quantity;
        }

        $this->method('AddtoCart');

        return $this;
    }

    public function clear($order_id = NULL, $catalog_id = NULL)
    {
        if ($order_id === NULL OR $catalog_id === NULL)
        {
            if ($this->method())
            {
                $this->load();
            }
            else
            {
                $this
                    ->view()
                    ->load();
            }

            if ($this->success() AND $this->current())
            {
                $order_id = $this->current()->OrderId;
                $catalog_id = $this->current()->CatalogId;
            }
            else
            {
                return $this;
            }
        }

        $this->method('EmptyCart')
            ->param('orderId', $order_id)
            ->param('catalogId', $catalog_id);

        return $this;
    }

    public function remove($line_id, $order_id = NULL, $catalog_id = NULL)
    {
        if ($order_id === NULL OR $catalog_id === NULL)
        {
            if ($this->method())
            {
                $this->load();
            }
            else
            {
                $this
                    ->view()
                    ->load();
            }


            if ($this->success())
            {
                $order_id = $this->current()->OrderId;
                $catalog_id = $this->current()->CatalogId;
            }
            else
            {
                throw new Exception('No cart is loaded to remove items from');
            }
        }

        $this->method('DeleteFrmCart')
            ->param('orderId', $order_id)
            ->param('catalogId', $catalog_id)
            ->param('orderItemId', $line_id);

        return $this;
    }

    public function checkout($tracking_id = NULL)
    {
        $this->method('Checkout');

        if ($tracking_id !== NULL)
        {
            $this->param('trackingId', $tracking_id);
        }

        return $this;
    }

    public function update($line_id, $quantity, $order_id = NULL, $catalog_id = NULL)
    {
        if ($order_id === NULL OR $catalog_id === NULL)
        {
            if ($this->method())
            {
                $this->load();
            }
            else
            {
                $this
                    ->view()
                    ->load();
            }

            if ($this->success())
            {
                $order_id = $this->current()->OrderId;
                $catalog_id = $this->current()->CatalogId;
            }
            else
            {
                throw new Exception('No cart is loaded to remove items from');
            }
        }

        $this->method('UpdateCart')
            ->param('orderId', $order_id)
            ->param('catalogId', $catalog_id)
            ->param('orderItemId', $line_id)
            ->param('quantity', (int) $quantity);

        return $this;
    }

    protected function _add_session()
    {
        if ($key = self::session())
        {
            $this->param('sessionKey', $key);
        }
        else
        {
            $this->param('loginId', 'Guest');
        }
    }

    protected function _request()
    {
        if ($this->method() == 'AddtoCart')
        {
            if ($catalog_id = implode(',', $this->products_to_add['catalogId']))
            {
                $this->param('catalogId', $catalog_id);
            }

            $this
                ->param('catentryId', implode(',', $this->products_to_add['catentry_id']))
                ->param('quantity', implode(',', $this->products_to_add['quantity']));
        }

        $this->_add_session();

        return parent::_request();
    }

}
