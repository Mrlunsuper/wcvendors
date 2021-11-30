<?php 

namespace WCVendors\Vendor; 
use WP_User;

/**
 * The Vendor Object
 * 
 * @since 3.0.0
 */

 class Vendor {

    /**
     * The Vendor ID.
     *
     * @var integer
     */
    public $id = 0;

    /**
     * The store data meta_key. 
     */
    public $meta_key = 'wcvendors_store_data'; 

    /**
     * The underlying WP_User user object. 
     *
     * @var null|WP_User
     */
    public $wp_user = null; 

    /**
	 * Stores if the vendor is enabled. 
	 *
	 * @var string
	 */
	protected $is_enabled;

    /**
     * The vendor store data.
     *
     * @var array
     */
    protected $store_data = []; 
    /**
     * Track the changes to the vendor data.
     *
     * @var array
     */
    private $changes = []; 

    /**
     * Load the vendor data based on how Vendor is called. 
     * 
     * @since 3.0.0     
     * @param WP_User|int $vendor wp user or user_id 
     */
    public function __construct( $vendor = null ){

        if ( $vendor instanceof WP_User ){ 
            $this->set_id( absint( $vendor->ID ) );
            $this->set_wp_user( $vendor ); 
        } elseif( is_numeric( $vendor ) ){
            $wp_user = get_user_by( 'id', $vendor ); 
            if ( $wp_user ){ 
                $this->set_id( $wp_user->ID ); 
                $this->set_wp_user( $wp_user ); 
            }   
        }

        $this->load_store_data(); 
        do_action( $this->get_hook_prefix() . '_loaded', $this ); 
    }

    /** 
     * Provide a prefix for all hooks in the object 
     *
     * @since 3.0.0  
     * @return string hook prefix 
     */
    public function get_hook_prefix(){ 
        return 'wcvendors_vendor'; 
    }

    /**
     * Get the store data 
     *
     * @return void
     */
    public function get_store_data(){
        return $this->store_data; 
    }

    /**
     * Is this user a vendor.
     *
     * @since 3.0.0 
     * @return boolean
     */
    public function is_vendor(){ 
        return wcv_is_vendor( $this->get_wp_user() );
    }

    /**
     * Is the vendor enabled and able to sell.
     *
     * @since 3.0.0 
     * @return boolean
     */
    public function is_enabled(){
        return wcv_is_vendor_enabled( $this->get_wp_user() );
    }

    /**
     * Is the vendor verified 
     *
     * @return boolean
     */
    public function is_verified(){ 
        return wcv_is_vendor_verified( $this->get_wp_user() ); 
    }

    /**
     * Is the vendor trusted
     *
     * @since 3.0.0 
     * @return boolean
     */
    public function is_trusted(){ 
        return wcv_is_vendor_trusted( $this->get_wp_user() );
    }

    /**
     * Is the vendor untrusted.
     *
     * @since 3.0.0 
     * @return boolean
     */
    public function is_untrusted(){
        return wcv_is_vendor_untrusted( $this->get_wp_user() ); 
    }

    /**
     * Is the vendor trusted.
     *
     * @return boolean
     */
    public function is_featured(){
        return wcv_is_vendor_featured( $this->get_wp_user() ); 
    }

    /** 
     * Load the vendor store data from the database 
     * 
     * @since 3.0.0 
     * @todo implemment vendor visibility taxonomy for required fields then populate here 
     */
    public function load_store_data(){ 

        // New Vendor, load defaults 
        if ( ! $this->id ){ 
            $this->store_data = wcv_vendor_store_data_defaults(); 
            return;
        } 

        $store_data = get_user_meta( $this->get_id(), $this->get_meta_key(), true ); 
        $store_data = is_array( $store_data ) ? $store_data : array(); 
        $store_data = wp_parse_args( $store_data, wcv_vendor_store_data_defaults() ); 
       
        $this->store_data = apply_filters( $this->get_hook_prefix() . '_store_data', $store_data, $this ); 

    }

    /**
	 * Return data changes only.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public function get_changes() {
		return $this->changes;
	}

	/**
	 * Merge changes with data, update and clear.
	 *
	 * @since 3.0.0
	 */
	public function apply_changes() {
        // No changes, nothing to update 
        if ( empty( $this->get_changes() ) ){ 
            return; 
        }

        $this->maybe_migrate_user_meta();
		$this->store_data  = array_replace_recursive( $this->store_data, $this->changes ); // @codingStandardsIgnoreLine
        $this->update_vendor_data(); 
		$this->changes = [];
	}

    /**
     * Update the user meta in the database. 
     */
    public function update_vendor_data(){ 
        update_user_meta( $this->get_id(), $this->get_meta_key(), $this->store_data ); 
    }


    /**
	 * Save should create or update based on object existence.
	 *
	 * @since  3.0.0
	 * @return int
	 */
	public function save() {

		/**
		 * Trigger action before saving to the DB. Allows you to adjust object props before save.
		 *
		 * @param Vendor    $this The vendor being saved.
		 * @param array     $store_date The vendor store data to be saved
		 */
		do_action( $this->get_hook_prefix() . '_before_vendor_save', $this, $this->store_data );

        $this->apply_changes(); 
     
		/**
		 * Trigger action after saving to the DB.
		 *
		 * @param Vendor    $this The vendor object being saved.
		 * @param Array     $store_data THe store data 
		 */
		do_action( $this->get_hook_prefix() . '_after_vendor_save', $this, $this->store_data );

		return $this->get_id();
	}
    

    /*
    |--------------------------------------------------------------------------
    | Getters
    |--------------------------------------------------------------------------
    */

    /**
     * Get the metakey used to store the vendor store data in the user meta table. 
     * 
     * @return string $meta_key the metakey used 
     */
    public function get_meta_key(){ 
        return $this->meta_key; 
    }

    /**
     * Get a property from the object 
     *  
     * @since  3.0.0
	 * @param  string $prop Name of store property to get.
	 * @return mixed
	 */
    public function get_store_prop( $prop ){ 
       
        $value = null; 

        if ( array_key_exists( $prop, $this->store_data ) ) {
			$value = array_key_exists( $prop, $this->changes ) ? $this->changes[ $prop ] : $this->store_data[ $prop ];
		}

        return $value; 
    }

    /**
     * Get the vendor_id
     *
     * @return int the WP_User->ID
     */
    public function get_id(){ 
        return $this->id; 
    }

    /**
     * Get the WP_User for the particular vendor
     *
     * @since  3.0.0
     * @returns WP_User WP_User object for the vendor 
     */
    public function get_wp_user(){
        return $this->wp_user; 
    }

    /**
     * Get the first name from the WP_User object 
     *
     * @since 3.0.0
	 * @return string 
     * 
     */
    public function get_first_name(){
        if ( $this->get_wp_user() ){
            return $this->get_wp_user()->get_first_name(); 
        }
    }

    /**
     * Get the last name from the WP_User object 
     *
     * @since 3.0.0
	 * @return string
     */
    public function get_last_name(){
        if ( $this->get_wp_user() ){
            return $this->get_wp_user()->get_last_name(); 
        }
    }

    /**
     * Get the vendors user email address from the WP_User object
     * 	 
     * @since 3.0.0
	 * @return string
     */
    public function get_user_email(){ 
        if ( $this->get_wp_user() ){
            return $this->get_wp_user()->get_user_email(); 
        }
    }

	/**
	 * Return this vendors's avatar.
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_avatar_url() {
		return wcv_get_avatar_url( $this->get_id() );
	}

    /**
	 * Return vendor store name. 
	 *
	 * @since  3.0.0
	 * @return string
	 */
    public function get_store_name(){
        return $this->get_store_prop( 'store_name' ); 
    }

    /**
     * Get the vendor store URL permalink. 
     * 
     * @since 3.0.0
     */
    public function get_store_url(){
        return wcv_get_storeurl( $this->get_id(), $this->get_slug() );
    }

    /**
     * Get store seller information
     *
     * @return string store seller info
     */
    public function get_info(){
        return $this->get_store_prop( 'info' );
    }

    /**
     * Get store description
     * 
     * @since 3.0.0
     * @return string The store description 
     */
    public function get_description( ){
        return $this->get_store_prop( 'description' ); 
    }

    /**
     * Get the company/blog URL of the vendor that is not their on site URL
     *
     * @return void
     */
    public function get_company_url(){
        return $this->get_store_prop( 'company_url' );
    }

    /**
     * Get the vendors store slug 
     *
     * @return string store slug
     */
    public function get_slug(){ 
        return $this->get_store_prop( 'slug' );
    }

    /**
     * Get the vendor stores phone number
     *
     * @return string phone number 
     */
    public function get_phone(){ 
        return $this->get_store_prop( 'phone' ); 
    }

    /**
     * Get the store email address
     *
     * @return string store email address
     */
    public function get_email(){ 
        return $this->get_store_prop( 'email' );
    }
   
    /**
     * Get the store address 
     *
     * @return array Store address array 
     */
    public function get_address(){
        return $this->get_store_prop( 'address' ); 
    }

    /**
     * Get the store address other, used for shipping purposes 
     *
     * @return array Store address other array 
     */
    public function get_address_other(){
        return $this->get_store_prop( 'address_other' );
    }

    /**
     * Get the store's SEO details 
     *
     * @return array Seo details. These can be customised as per requirements.
     */
    public function get_seo(){
        return $this->get_store_prop( 'seo' );
    }

    /**
     * Get Store social profiles 
     *
     * @return array of social profiles 
     */
    public function get_social(){
        return $this->get_store_prop( 'social' ); 
    }

    /**
     * Get the stores long / lat location
     *
     * @return array The longitude and latitude 
     */
    public function get_location(){ 
        return $this->get_store_prop( 'location' );
    }

    /**
     * Get the branding details array           
     *
     * @return array Branding details array
     */
    public function get_branding_details(){
        return $this->get_store_prop( 'branding' ); 
    }

    /**
     * Get the store banner attachment ID. 
     *
     * @return int The banner post ID. 
     */
    public function get_banner_id(){ 
        $branding = $this->get_branding_details(); 
        return $branding[ 'banner_id' ]; 
    }

    /**
     * Get the banner URL from the underlying WordPress attachment
     *
     * @return string Banner URL
     */
    public function get_banner(){
        return wp_get_attachment_url( $this->get_banner_id() );  
    }

    /**
     * Get the store icon attachment ID
     *
     * @return int  The attachment ID for the store icon 
     */
    public function get_icon_id(){ 
        $branding = $this->get_branding_details(); 
        return $branding[ 'icon_id' ]; 
    }

    /**
     * Get the store icon URl 
     *
     * @return string store icon URL
     */
    public function get_store_icon(){
        return wp_get_attachment_url( $this->get_icon_id() );  
    }

    /**
     * Get all payout details for the vendor
     *
     * @return array All available payout details 
     */
    public function get_payout(){ 
        return $this->get_store_prop( 'payout' ); 
    }

     /**
     * Get the payout details for that particular payout method. 
     *
     * @param string $payout the payout type to check
     * @return void
     */
    protected function get_payout_detail( $payout ){
        $value = null;
		if ( array_key_exists( $payout, $this->store_data['payout'] ) ) {
			$value = isset( $this->changes['payout'][ $payout ] ) ? $this->changes['payout'][ $payout ]: $this->store_data['payout'][ $payout ];

		}
		return $value;
    }

    /**
	 * Return vendors PayPal email address 
	 *
	 * @since  3.0.0
	 * @return string
	 */
    public function get_paypal_email(){
        $paypal = $this->get_payout_detail( 'paypal' ); 
        return $paypal['email'];  
    }

    /**
	 * Return vendors bank details 
	 *
	 * @since  3.0.0
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
    public function get_bank_details(){
        $bank_details = $this->get_payout_detail( 'bank' ); 
        return $bank_details;
    }

    /**
     * Get the give tax detail to see if the vendor recieves the tax during commission calculations 
     *
     * @return bool Whether to give the tax to the vendor 
     */
    public function get_give_tax(){ 
        return $this->get_store_prop( 'give_tax' ); 
    }

    /**
     * Get the give shipping detail to see if the vendor recieves the shipping during commission calculations 
     *
     * @return void
     */
    public function get_give_shipping(){
        return $this->get_store_prop( 'give_shipping' ); 
    }
   
    /**
     * Get any commission overrides that might be set at the vendor level 
     *
     * @return void
     */
    public function get_commission(){ 
        return $this->get_store_prop( 'commission' ); 
    }

     /**
     * Get vendor products.
     *
     * @return array $products Vendor Products. 
     */
    public function get_products(){ 
        $products = '';

        return $products;
    }

    /**
     * Get vendors orders. 
     * 
     * @since 3.0.0 
     * 
     * @return vendor_order objects 
     */
    public function get_orders( $args = [] ){
        $orders = '';

        return $orders;
    }


    /*
    |--------------------------------------------------------------------------
    | Setters
    |--------------------------------------------------------------------------
    */

    /**
	 * Sets a prop for a setter method.
	 *
	 * This stores changes in a special array so we can track what needs saving
	 * the the DB later.
	 *
	 * @since 3.0.0
	 * @param string $prop Name of prop to set.
	 * @param mixed  $value Value of the prop.
	 */
	protected function set_prop( $prop, $value ) {

		if ( array_key_exists( $prop, $this->store_data ) ) {
            if ( $value !== $this->store_data[ $prop ] || array_key_exists( $prop, $this->changes ) ) {
                $this->changes[ $prop ] = $value;
            }
		}
	}

    /**
	 * Sets a prop for a part for a setter method.
	 *
	 * @since 3.0.0
	 * @param string $prop    Name of prop to set.
	 * @param string $part    Name of part to set. branding, payout, commission
	 * @param mixed  $value   Value of the prop.
	 */
	protected function set_prop_part( $prop, $part, $value ) {
        $prop_part = $this->get_store_prop( $prop );
        $prop_part[ $part ] = $value; 
        $this->set_prop( $prop, $prop_part );        
	}

    /**
	 * Set a collection of props in one go, collect any errors, and return the result.
	 * Only sets using public methods.
	 *
	 * @since  3.0.0
	 *
	 * @param array  $props Key value pairs to set. Key is the prop and should map to a setter function name.
	 * @param string $context In what context to run this.
	 *
	 * @return bool|WP_Error
	 */
	public function set_props( $props, $context = 'set' ) {
		$errors = false;

		foreach ( $props as $prop => $value ) {
			try {
				/**
				 * Checks if the prop being set is allowed, and the value is not null.
				 */
				if ( is_null( $value ) || in_array( $prop, array( 'prop' ), true ) ) {
					continue;
				}
				$setter = "set_$prop";

				if ( is_callable( array( $this, $setter ) ) ) {
					$this->{$setter}( $value );
				}
			} catch ( WC_Data_Exception $e ) {
				if ( ! $errors ) {
					$errors = new WP_Error();
				}
				$errors->add( $e->getErrorCode(), $e->getMessage() );
			}
		}

		return $errors && count( $errors->get_error_codes() ) ? $errors : true;
	}

    /**
     * Set the user ID
     *
     * @since 3.0.0 
     * @param int $id WP User ID.
     */
    public function set_id( $id ){ 
        $this->id = absint( $id ); 
    }

    /**
     * Set the WP_User object
     *
     * @since 3.0.0 
     * @param WP_User $wp_user The WP_User Object
     */
    public function set_wp_user( $wp_user ){ 
        $this->wp_user = $wp_user; 
    }

    /**
     * Set the store data.
     *
     * @param array $data The store data. 
     */
    public function set_store_data( ){ 
        if ( $this->get_wp_user()->has_prop( $this->get_meta_key() ) ){
            $this->store_data = $this->get_wp_user()->get( $this->get_meta_key() ); 
        } else { 
            $this->store_data = wcv_vendor_store_data_defaults(); 
        }
    }

    /**
     * Set the vendor store name
     *
     * @param string $store_name String to set the store display name 
     * @return void
    */
    public function set_store_name( $store_name ){
        $this->set_prop( 'store_name', $store_name );
    }

    /**
     * Set store seller information that is displayed on the single product page   
     *
     * @param string $info Store seller info 
     * @return void
     */
    public function set_info( $info ){
        $this->set_prop( 'info', $info );
    }

    /**
     * Set the store description shown on the store archive page 
     *
     * @param string $description store description 
     * @return void
     */
    public function set_description( $description ){
        $this->set_prop( 'description', $description  );
    }

    /**
     * Set the company URL for the store if they have a different website/blog
     *
     * @param string $company_url External URL they may have
     * @return void
     */
    public function set_company_url( $company_url ){
        $this->set_prop( 'company_url', $company_url );
    }
    
    /**
     * Set the store slug for the vendor store URLs. 
     * 
     * @todo add a setting to make the url read-only after initial setup (like username for wp_user)
     *
     * @param string $slug the vendors URL slug 
     * @return void
     */
    public function set_slug( $slug ){
        $this->set_prop( 'slug', $slug );
    }

    /**
     * Set the store emails address, this is used in contact forms/widgets this is different to the WP User email address. 
     *
     * @param string $email Store email address used for customer communication
     * @return void
     */
    public function set_email( $email ){
        $this->set_prop( 'email',  $email );
    }

    /**
     * Set the store contact phone
     *
     * @param string $phone The vendor store contact phone number.
     */
    public function set_phone( $phone ){ 
        $this->set_prop( 'phone', $phone );
    }

    /**
     * Set the store address 
     *
     * @param array $address The store's physical address
     * @return void
     */
    public function set_address( $address ){
        $this->set_prop( 'address', $address );
    }

    /**
     * Set the stores other address such as a warehouse for shipping from 
     *
     * @param array $addres_other
     * @return void
     */
    public function set_address_other( $address_other ){
        $this->set_prop( 'address_other', $address_other );
    }

    /**
     * Set the store SEO information
     *
     * @param array $seo_data SEO information
     * @return void
     */
    public function set_seo( $seo ){
        $this->set_prop( 'seo', $seo );
    }

    /**
     * Set the social profile data. This is an array of each social media platform you want to display. 
     *
     * @param array $social_data The social profile data in the format of [ 'platform' => 'detail' ] 
     * @return void
     */
    public function set_social( $social_data ){ 
        $this->set_prop( 'social', $social_data );
    }

    /**
     * Set the location in long and lat 
     *
     * @param array $location The long/lat co-ordinates [ 'long' => 0, 'lat' => 0 ]
     * @return void
     */
    public function set_location( $location ){
        $this->set_prop( 'location', $location );
    }

    /**
     * Set the banner id used for branding.
     *
     * @param int $banner_id The post_id of the attachment
     * @return void
     */
    public function set_banner_id( $banner_id ){
        $this->set_prop_part( 'branding', 'banner_id', $banner_id );
    }

    /**
     * Set the store's icon id this is different from the users gravatar
     *
     * @param int $icon_id The post_id of the attachment 
     * @return void
     */
    public function set_icon_id( $icon_id ){
        $this->set_prop_part( 'branding', 'icon_id', $icon_id );
    }

    /**
     * Set all payout details at once
     *
     * @param array $payout
     */
    public function set_payout( $payout ){ 
        $this->set_prop( 'payout', $payout );
    }


    /**
     * Set the paypal email
     *
     * @param string $paypal_email
     * @return void
     */
    public function set_paypal_email( $paypal_email ){
        $paypal_details = ['email' => $paypal_email ]; 
        $this->set_prop_part( 'payout', 'paypal', $paypal_details );
    }

    /**
     * Set the bank details [ 'account_name' => '', 'account_number' => '', 'bank_name' => '', 'routing_number' => '', 'iban' => '','bic_swift' => '' ]
     *
     * @param array $bank_details The bank details 
     * @return void
     */
    public function set_bank_details( $bank_details ){
        $this->set_prop_part( 'payout', 'bank', $bank_details );
    }

    /**
     * Set the property to give tax to vendors during commission calculations
     *
     * @param bool $give_tax Give tax to vendor
     * @return void
     */
    public function set_give_tax( $give_tax ){
        $this->set_prop( 'give_tax', $give_tax ); 
    }

    /**
     * Set give shipping to vendors during commission calculations 
     *
     * @param bool $give_shipping Give shipping to vendor 
     * @return void
     */
    public function set_give_shipping( $give_shipping ){
        $this->set_prop( 'give_shipping', $give_shipping ); 
    }

    /**
     * Set the commission details this is an array that outlines the commission type and the other required details. 
     * 
     * Required keys: [ 'type' => 'percent', 'amount' => 0, 'fee' => 0 ]
     *
     * @param array $commission The commission details for the vendor commission override
     * @return void
     */
    public function set_commission( $commission ){
        $this->set_prop( 'commission', $commission ); 
    }

    /**
     * Migrate the vendor to the new system
     *
     * @return void
     *
     */
    public function maybe_migrate_user_meta() {

        if ( ! wc_string_to_bool( $this->get_wp_user()->get( 'wcv_vendor_migrated' ) ) ) {

            $prop_to_meta_map = wcv_get_prop_map();

            $vendor_props     = array();
    
            foreach ( $prop_to_meta_map as $prop => $meta ) {
                 $vendor_props[ $prop ] =  $this->recursive_get_prop_value( $prop_to_meta_map[ $prop ], $meta);
            }

            $this->changes = array_merge( $vendor_props, $this->changes );
    
        }
    }

    /**
	 * Retrive all vendor props by Recursive
	 *
	 * @param $prop The property to retrieve.
	 * @param $meta The meta key to retrieve.
	 *
	 * @return mixed
	 */
	private function recursive_get_prop_value( $prop, $meta ) {
		if ( is_array( $prop ) ) {
			foreach ( $prop as $meta_key => $sub_prop ) {
				$prop[ $meta_key ] = $this->recursive_get_prop_value( $prop[ $meta_key ], $sub_prop);
			}
		} else {
			$prop = $this->get_wp_user()->get( $meta );
		}
		return $prop;

	}

}