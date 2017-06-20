<?php

	class FacetedNavigation_Module extends Core_ModuleBase
	{
		private static $catalog_version_update = false;

		protected function createModuleInfo()
		{
			return new Core_ModuleInfo(
				"Faceted Navigation Module",
				"Filtering product catalog with preset criteria",
				"Sergey Kruglikov"
			);
		}

		public function subscribeEvents()
		{
			Backend::$events->addEvent('shop:onExtendProductsToolbar', $this, 'extend_products_toolbar');
			Backend::$events->addEvent('shop:onExtendProductForm', $this, 'extend_product_form');
			Backend::$events->addEvent('shop:onExtendProductModel', $this, 'extend_product_model');
		}

		public function extend_products_toolbar($controller)
		{
			$controller->renderPartial(PATH_APP.'/modules/facetednavigation/controllers/partials/_products_toolbar.htm');
		}

		public function extend_product_model($product)
		{
			$product->add_relation('has_many', 'mars', array('class_name'=>'FacetedNavigation_Product', 'foreign_key'=>'product_id', 'order'=>'sort_order', 'delete'=>true));
			$product->define_multi_relation_column('mars', 'mars', 'Mars', '@sort_order');
		}

		public function extend_product_form($product, $context)
		{
			$product->add_form_field('mars')->tab('Mars');
		}

		public function load_resources($controller)
		{
			$controller->addJavaScript('/modules/facetednavigation/resources/javascript/facetednavigation.js');
			$controller->addCss('/modules/facetednavigation/resources/css/facetednavigation.css');
		}

		/**
		 * Catalog cache version management
		 */
		
		public static function get_catalog_version()
		{
			return Db_ModuleParameters::get( 'facetednavigation', 'catalog_version', 0 );
		}
		
		public static function update_catalog_version()
		{
			if (self::$catalog_version_update)
				return;
			
			Db_ModuleParameters::set( 'facetednavigation', 'catalog_version', time() );
		}
		
		public static function begin_catalog_version_update()
		{
			self::$catalog_version_update = true;
		}

		public static function end_catalog_version_update()
		{
			self::$catalog_version_update = false;
			self::update_catalog_version();
		}
	}

?>