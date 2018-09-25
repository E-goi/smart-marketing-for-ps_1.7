<?php
/**
 *  Smart Marketing
 *
 *  @author    E-goi
 *  @copyright 2018 E-goi
 *  @license   LICENSE.txt
 *  @package override/classes/webservice/WebserviceSpecificManagementEgoi
 */

if (!defined('_PS_VERSION_'))
    exit;

class WebserviceSpecificManagementEgoi extends WebserviceSpecificManagementSearchCore implements WebserviceSpecificManagementInterface 
{

    /** 
     * @var object
     */
    protected $objOutput;
    
    /**
     * @var object
     */
    protected $output;

    /** 
     * @var object
     */
    protected $wsObject;

    /* ------------------------------------------------
     * GETTERS & SETTERS
     * ------------------------------------------------ */

    /**
     * @param WebserviceOutputBuilderCore $obj
     * @return WebserviceSpecificManagementInterface
     */
    public function setObjectOutput(WebserviceOutputBuilderCore $obj)
    {
        $this->objOutput = $obj;
        return $this;
    }

    /**
     * Get WebService Object
     * 
     * @param WebserviceRequestCore $obj 
     * @return object
     */
    public function setWsObject(WebserviceRequestCore $obj)
    {
        $this->wsObject = $obj;
        return $this;
    }

    /**
     * @return object
     */
    public function getWsObject()
    {
        return $this->wsObject;
    }

    /**
     * @return object
     */
    public function getObjectOutput()
    {
        return $this->objOutput;
    }

    /**
     * @param $segments
     * @return object
     */
    public function setUrlSegment($segments)
    {
        $this->urlSegment = $segments;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlSegment()
    {
        return $this->urlSegment;
    }

    /**
     * @return void
     */
    public function manage() {}

    /**
     * This must be return a string with specific values as WebserviceRequest expects.
     *
     * @return string
     */
    public function getContent()
    {
        $ids = $this->wsObject->urlFragments['ids'];
        if(isset($ids) && ($ids)){

            $ids = str_replace(" ", "", filter_var($ids, FILTER_SANITIZE_STRING));

            // verify if value is numeric
            $each_id = explode("," ,$ids); 
            foreach ($each_id as $val) {
                if (!is_numeric($val)) {
                    return json_encode(array());
                }
            }

            $sql = 'SELECT '._DB_PREFIX_.'product.*, '._DB_PREFIX_.'product_lang.name, '._DB_PREFIX_.'product_lang.available_now, 
                    '._DB_PREFIX_.'product_lang.link_rewrite, '._DB_PREFIX_.'product_lang.description_short 
                    FROM '._DB_PREFIX_.'product, '._DB_PREFIX_.'product_lang WHERE '._DB_PREFIX_.'product.id_product = '._DB_PREFIX_.'product_lang.id_product 
                    AND '._DB_PREFIX_.'product.id_product IN ('.$ids.')
                    AND '._DB_PREFIX_.'product_lang.id_lang = "1"';
            $products_info = Db::getInstance()->executeS($sql);

            $url = explode('api', $_SERVER['REQUEST_URI']);

            $items = array();
            foreach($products_info as $product_data){

                $price = number_format($product_data['price'], 2);
                $sale_price = number_format($product_data['wholesale_price'], 2);

                if (!empty(Tools::getValue('decimal_space')) && is_numeric(Tools::getValue('decimal_space'))) {
                    $price = number_format($price, Tools::getValue('decimal_space'));
                    $sale_price = number_format($sale_price, Tools::getValue('decimal_space'));
                }

                if (!empty(Tools::getValue('decimal_sep'))) {
                    $price = str_replace('.', str_replace('"', '', Tools::getValue('decimal_sep')), $price);
                    $sale_price = str_replace('.', str_replace('"', '', Tools::getValue('decimal_sep')), $sale_price);
                }

                $items['items']['item'][] = array(
                    'id' => (int)$product_data['id_product'],
                    'name' => $product_data['name'],
                    'sku' => $product_data['reference'],
                    'regular_price' => $price,
                    'sale_price' => $sale_price,
                    'sale_dates_from' => (int)$product_data['wholesale_price'] ? $product_data['date_add'] : null,
                    'sale_dates_to' => null,
                    'image_thumbnail' => "<img src='".$this->getImageLink($product_data['id_product'], ImageType::getFormattedName('small'))."' />",
                    'image_medium' => "<img src='".$this->getImageLink($product_data['id_product'], ImageType::getFormattedName('medium'))."' />",
                    'image_medium_large' => "<img src='".$this->getImageLink($product_data['id_product'], ImageType::getFormattedName('large'))."' />",
                    'image_large' => "<img src='".$this->getImageLink($product_data['id_product'])."' />",
                    'image_home-blog-post' => "<img src='".$this->getImageLink($product_data['id_product'], ImageType::getFormattedName('home'))."' />",
                    'image_home-event-post' => "<img src='".$this->getImageLink($product_data['id_product'], ImageType::getFormattedName('home'))."' />",
                    'image_event-detail-post' => "<img src='".$this->getImageLink($product_data['id_product'], ImageType::getFormattedName('home'))."' />",
                    'image_shop_thumbnail' => "<img src='".$this->getImageLink($product_data['id_product'], ImageType::getFormattedName('cart'))."' />",
                    'image_shop_catalog' => "<img src='".$this->getImageLink($product_data['id_product'], ImageType::getFormattedName('home'))."' />",
                    'upsell_ids' => array(),
                    'crosssell_ids' => array(),
                    'manage_stock' => "no",
                    'stock_quantity' => (int)$product_data['quantity'] ?: null,
                    'stock_status' => $product_data['available_now'],
                    'weight' => $product_data['weight'],
                    'length' => $product_data['depth'],
                    'width' => $product_data['width'],
                    'height' => $product_data['height'],
                    'shipping_class' => null,
                    'excerpt' => $product_data['description_short'],
                    'categories' => $this->getCategoryInfo((int)$product_data['id_category_default']),
                    'tags' => false,
                    'virtual' => false,
                    'downloadable' => "no",
                    'download_limit' => "-1",
                    'download_expiry' => "-1",
                    'url' => "http://".$_SERVER['SERVER_NAME'].$url[0]."index.php?id_product=".$product_data['id_product']."&controller=product"
                );
            }

            return json_encode($items);
        }

        return json_encode(array());
    }

    /**
     * Get Category from Product ID
     * 
     * @param int|bool $category_id
     * @return array
     */
    private function getCategoryInfo($category_id = false)
    {
        if($category_id) {
            $sql = 'SELECT '._DB_PREFIX_.'category.*, '._DB_PREFIX_.'category_lang.*
                    FROM '._DB_PREFIX_.'category, '._DB_PREFIX_.'category_lang 
                    WHERE '._DB_PREFIX_.'category.id_category = '._DB_PREFIX_.'category_lang.id_category 
                    AND '._DB_PREFIX_.'category.id_category = '.$category_id.'
                    AND '._DB_PREFIX_.'category_lang.id_lang = "1"';
            $category_info = Db::getInstance()->executeS($sql);

            if($category_info) {
                
                $groups = array();
                $raw_groups = $this->getGroups($category_id);
                foreach ($raw_groups as $group) {
                    $groups[] = $group['id_group'];
                }

                $cat = $category_info[0];
                return array(
                    'term_id' => $category_id,
                    'name' => $cat['name'],
                    'slug' => $cat['link_rewrite'],
                    'term_group' => $groups,
                    'term_taxonomy_id' => $category_id,
                    'taxonomy' => null,
                    'description' => $cat['description'],
                    'parent' => $cat['id_parent'],
                    'count' => 1,
                    'filter' => "raw",
                    'meta_value' => 0
                );
            }
        }
        return null;
    }

    /**
     * Get Groups by Category
     * 
     * @param int $category_id
     * @return array            
     */
    private function getGroups($category_id)
    {
        $sql = 'SELECT id_group FROM '._DB_PREFIX_.'category_group WHERE id_category = '.(int)$category_id;
        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get Image from Product ID
     *
     * @param  $id  
     * @param  $type
     * @return string      
     */
    private function getImageLink($id, $type = null)
    {
        return $_SERVER['SERVER_NAME']._THEME_PROD_DIR_.$id.'/'.$id.($type ? '-'.$type : '').'.jpg';
    }
}
