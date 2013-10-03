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
 * Library_Sears_Api_Product
 *
 */
class Library_Sears_Api_Product extends Library_Sears_Api {

    /**
     * _load - Overload the _load to properly set the iterator array from
     * the API result.
     *
     * @return void
     */
    protected function _load()
    {
        parent::_load();

        if ($this->success())
        {
            if (isset($this->_object->productdetail->softhardproductdetails) && is_array($this->_object->productdetail->softhardproductdetails))
            {
                $this->_data =& $this->_object
                    ->productdetail
                    ->softhardproductdetails[1];

                $this->_total_rows = 1;
            }
            elseif ($this->_object->productdetail)
            {
                $this->_data = array(& $this->_object->productdetail);
                $this->_total_rows = 1;
            }
        }
    }

    /**
     * get - Get a product from the sears API.
     *
     *  // Typical response from the product object current metod.
     *  object stdClass(67) {
     *       public catalogid => string(5) "12605"
     *       public swatches => object stdClass(0) {
     *       }
     *       public soldby => string(5) "SEARS"
     *       public productvariants => object stdClass(0) {
     *       }
     *       public skudiff => string(0) ""
     *       public mapindicator => string(0) ""
     *       public giftwrap => string(0) ""
     *       public maintenanceagreement => string(3) "YES"
     *       public longdescription => string(1919) "<div id=&#034;detailBullets&#034;><div class=&#034;k_bullets&#034;><p><img src=&#034;http//download.sears.com/misc/Tim.jpg&#034; height=&#034;53&#034; width=&#034;62&#034; alt=&#034;Save Time&#034;>Find your food quickly - A humidity-controlled crisper, temperature-controlled meat/vegetable drawer, snack drawer, and slide-out freezer bin keep groceries organized</p><p><img src=&#034;http//download.sears.com/misc/Spc.jpg&#034; height=&#034;53&#034; width=&#034;62&#034; alt=&#034;Space Saver&#034;>Store gallon-sized containers in the door - Clear, adjustable door bins free up interior shelf space and offer improved visibility</p><p><img src=&#034;http//download.sears.com/misc/Mon.jpg&#034; height=&#034;53&#034; width=&#034;62&#034; alt=&#034;Save Money&#034;>Save on your energy bills - This ENERGY STAR&amp;#174; qualified appliance uses up to 20% less energy than refrigerators without the ENERGY STAR&amp;#174; rating</p><p><img src=&#034;http//download.sears.com/misc/Env.jpg&#034; height=&#034;35&#034; width=&# …"
     *       public pickupoption => string(1) "1"
     *       public zerofinance => string(0) ""
     *       public storepickupeligible => string(1) "1"
     *       public regularprice => string(7) "1057.99"
     *       public productvariant => string(12) "NONVARIATION"
     *       public mfgpartnumber => string(4) "5942"
     *       public mappricedescription => string(0) ""
     *       public sellercount => string(2) "-1"
     *       public catentryid => string(8) "91240585"
     *       public lmpstoredetails => object stdClass(23) {
     *           public sunopentime => string(0) ""
     *           public ordercutofftime => string(0) ""
     *           public satopentime => string(0) ""
     *           public weekdayopentime => string(0) ""
     *           public leadtime => string(0) ""
     *           public onhandquantity => string(0) ""
     *           public lmpstock => string(0) ""
     *           public satclosetime => string(0) ""
     *           public tomorrowholidayflag => string(0) ""
     *           public storeunitnumber => string(0) ""
     *           public time => object stdClass(3) {
     *               public timez => object stdClass(1) {
     *                   public content => array(2) (
     *                       ...
     *                   )
     *               }
     *               public timey => object stdClass(1) {
     *                   public content => array(2) (
     *                       ...
     *                   )
     *               }
     *               public timex => object stdClass(1) {
     *                   public content => array(2) (
     *                       ...
     *                   )
     *               }
     *           }
     *           public standardtimezone => string(0) ""
     *           public preptime => string(0) ""
     *           public mailable => string(0) ""
     *           public spu => string(0) ""
     *           public ldflag => string(0) ""
     *           public sres => string(0) ""
     *           public sunclosetime => string(0) ""
     *           public weekdayclosetime => string(0) ""
     *           public storezipcode => string(0) ""
     *          public respffm => string(0) ""
     *           public getitnow => string(0) ""
     *           public todayholidayflag => string(0) ""
     *       }
     *       public mappricevaliddate => string(0) ""
     *       public skulist => object stdClass(1) {
     *           public sku => array(2) (
     *               0 => bool TRUE
     *               1 => array(1) (
     *                   ...
     *               )
     *           )
     *       }
     *       public webstatus => string(1) "0"
     *       public checkoutenable => string(0) ""
     *       public energyguideurl => string(38) "http//c.sears.com/assets/eg/477891.pdf"
     *       public shortdescription => string(424) "Enjoy fresh, filtered water with these Kenmore&amp;reg; side by side refrigerators that offer PUR&amp;reg; Ultimate II water filtration to ensure great-tasting beverages. The exterior ice/water dispenser features control lockout and a light so that the paddles are visible even in low light. The adjustable door bins free up interior shelf space since they allow you to move gallon-sized containers to the refrigerator door."
     *       public productprotectionplan => string(0) ""
     *       public rating => string(3) "4.0"
     *       public clicktotalk => string(4) "true"
     *       public specialoffer => string(5) "false"
     *       public smartplan => string(0) ""
     *       public isfrequencymodel => string(1) "1"
     *       public ksnvalue => string(8) "65016020"
     *       public relatedurl => string(2) "{}"
     *       public storedistance => string(0) ""
     *       public installationkit => string(3) "YES"
     *       public usermanual => string(39) "http//c.shld.net/assets/own/477891e.pdf"
     *       public partnumber => string(12) "04659422000P"
     *       public savestory => string(345) "<div class=&#034;origPrice&#034;><span class=&#034;text&#034;>Reg Price </span><span class=&#034;pricing&#034;><del> $1057.99</del></span></div><div class=&#034;youPay&#034;><span class=&#034;pricing&#034;> $799.88</span></div><div class=&#034;callout&#034;><p> While quantities last </p><p> Intermediate markdowns may have been taken </p></div>"
     *       public fitmentrequired => string(0) ""
     *       public followitflag => string(4) "true"
     *       public connection => string(3) "YES"
     *        public othercpcmerchants => object stdClass(0) {
     *        }
     *       public optiontab => string(4) "true"
     *       public automotivedivision => string(5) "false"
     *       public haulaway => string(3) "YES"
     *       public instock => string(1) "1"
     *       public storeid => string(5) "10153"
     *       public mailinrebate => string(1) "0"
     *       public imageurls => object stdClass(1) {
     *           public imageurl => array(2) (
     *               0 => bool TRUE
     *               1 => array(4) (
     *                   ...
     *               )
     *           )
     *       }
     *       public otherfbmmerchants => object stdClass(0) {
     *       }
     *       public descriptionname => string(78) "25 cu. ft. Side-By-Side Refrigerator w/ PUR&amp;#174; Water Filtration  (5942)"
     *       public mappedpriceindicator => string(0) ""
     *       public viewonly => string(5) "false"
     *       public arrivalmethods => object stdClass(1) {
     *           public arrivalmethod => array(2) (
     *               0 => bool TRUE
     *               1 => array(2) (
     *                   ...
     *               )
     *           )
     *       }
     *       public sreseligible => string(1) "1"
     *       public nddavailablity => string(0) ""
     *       public langid => string(0) ""
     *       public expresscheckouteligible => string(1) "N"
     *       public accessory => string(0) ""
     *       public mainimageurl => string(43) "http//s.shld.net/is/image/Sears/04659422000"
     *       public saleprice => string(6) "799.88"
     *       public distributioncenter => string(3) "DDC"
     *       public groupdescription => string(0) ""
     *       public iskmartspu => string(5) "false"
     *       public regavlmainflag => string(5) "false"
     *       public variant => string(12) "NONVARIATION"
     *       public brandname => string(7) "Kenmore"
     *   }
     *
     *
     * @param string $id = NULL Sears part number.
     * @return void
     */
    public function get($id = NULL)
    {
        if ($id === NULL AND $this->_parent)
        {
            $id = $this->_parent->partnumber;
        }

        $this
            ->method('productdetails')
            ->param('partNumber', $id);

        return $this;
    }

    /**
     * compare - Compare multiple products. This method can take an arbitrary
     * amount of arguments. Each argument should be either an Library_Sears_Api_Product
     * object OR a string containing the product number.simba
     *
     *  // Example using only product numbers
     *  $compare = Library_Sears_Api::factory('product')
     *      ->compare('SPM218839052', 'SPM218814077')
     *      ->load();
     *
     *  // Example comparing a loaded product to other products
     *  $product = Library_Sears_Api::factory('product')
     *      ->get('SPM218839052')
     *      ->load();
     *  $compare = $product
     *      ->compare('SPM218814077')
     *      ->load();
     *
     *  // Using objects as arguments and mixing product numbers
     *  $product1 = Library_Sears_Api::factory('product')
     *      ->get('SPM218814077')
     *      ->load();
     *
     *  $product2 = Library_Sears_Api::factory('product')
     *      ->get('SPM218839052')
     *      ->load();
     *
     *  $compare = Library_Sears_Api::factory('product')
     *      ->compare($product1, $product2, 'SPM218839054')
     *      ->load();
     *
     * @return void
     */
    public function compare()
    {
        $count = func_num_args();

        if ($count > 0)
        {
            $product_numbers = array();

            if ($this->count() > 0)
            {
                $product_numbers[] = $this->current()->productnumber;
            }

            foreach (func_get_args() as $product)
            {
                if ($product instanceof Library_Sears_Api)
                {
                    $product_numbers[] = $product->current()->partnumber;
                }
                else
                {
                    $product_numbers[] = $product;
                }
            }

            $this
                ->method('CompareProducts')
                ->param('prodCount', count($product_numbers))
                ->param('partNumber', implode(',', $product_numbers));
        }

        return $this;
    }

    /**
     * add_to_cart
     *
     * @return void
     */
    public function add_to_cart()
    {
        $this->load();

        return Library_Sears_Api::factory('cart', $this->_group, $this->current())
            ->add();
    }
    
    
    /**
    *	get_product_data - Return product data as an array.
    *
    *	@return array 
    */
    public function get_product_details() {
    	if(isset($this->_data) && is_array($this->_data)) {
    		return $this->_data;
    	} else {
    		return false;
    	}
    }

}
