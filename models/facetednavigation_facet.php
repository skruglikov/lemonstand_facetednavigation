<?php

	class FacetedNavigation_Facet extends Db_ActiveRecord
	{
		public $table_name = 'facetednavigation_facets';

		public $enabled = true;

		public $has_many = array(
			'facet_values'=>array('class_name'=>'FacetedNavigation_FacetValue', 'foreign_key'=>'facet_id', 'order'=>'front_end_sort_order', 'delete'=>true)
		);

		public static function create()
		{
			return new self();
		}

		public function define_columns($context = null)
		{
			$this->define_column('name', 'Attribute Name')->order('asc')->validation()->fn('trim')->unique('Extra option set with name "%s" already exists.')->required('Please specify the product attribute name.');
			$this->define_column('description', 'Description')->defaultInvisible()->validation()->fn('trim');
			$this->define_column('url_name', 'URL Name')->validation()->fn('trim')->fn('mb_strtolower')->regexp('/^[0-9a-z_-]*$/i', 'URL Name can contain only latin characters, numbers and signs -, _, -')->unique('The URL Name "%s" already in use. Please select another URL Name.', array($this, 'configure_unique_validator'));
			$this->define_column('code', 'API Code')->defaultInvisible()->validation()->fn('trim')->fn('mb_strtolower')->unique('Extra option set with the specified  API code already exists.');
			$this->define_column('enabled', 'Enabled')->defaultInvisible();
			$this->define_column('is_disabled', 'Disabled');
			$this->define_column('front_end_sort_order', 'Sort Order');

			$this->define_multi_relation_column('facet_values', 'facet_values', 'Options', "@description")->invisible();
		}

		public function define_form_fields($context = null)
		{
			$this->add_form_field('enabled', 'left')->tab('Set Parameters')->renderAs(frm_checkbox)->comment('To show or hide the attribute from the product.');
			$this->add_form_field('is_disabled','right')->tab('Set Parameters')->comment('Disabling attribute on the front-end store.');
			$this->add_form_field('name', 'left')->tab('Set Parameters');
			$this->add_form_field('code', 'right')->tab('Set Parameters');
			$this->add_form_field('url_name')->tab('Set Parameters');
			$this->add_form_field('description')->comment('Information about attribute.', 'above')->size('tiny')->tab('Set Parameters');

			$this->add_form_field('facet_values')->tab('Set Parameters');
		}

		public function validateUrl($name, $value)
		{
			$urlName = trim($this->url_name);

			if (!strlen($urlName) && !$this->page)
				$this->validation->setError('Please specify either URL name.', $name, true);
				
			return true;
		}

		public function configure_unique_validator($checker, $product, $deferred_session_key)
		{
			return true;
		}

		public function after_modify($operation, $deferred_session_key)
		{
			FacetedNavigation_Module::update_catalog_version();
		}

		public function after_create() 
		{
			Db_DbHelper::query('update facetednavigation_facets set front_end_sort_order=:front_end_sort_order where id=:id', array(
				'front_end_sort_order'=>$this->id,
				'id'=>$this->id
			));

			$this->front_end_sort_order = $this->id;
		}

		public static function set_orders($item_ids, $item_orders)
		{
			if (is_string($item_ids))
				$item_ids = explode(',', $item_ids);
			
			if (is_string($item_orders))
				$item_orders = explode(',', $item_orders);

			foreach ($item_ids as $index=>$id)
			{
				$order = $item_orders[$index];
				Db_DbHelper::query(
					'update facetednavigation_facets set front_end_sort_order=:front_end_sort_order where id=:id',
					array(
						'front_end_sort_order'=>$order,
						'id'=>$id
					)
				);
			}

			FacetedNavigation_Module::update_catalog_version();
		}
	}

?>